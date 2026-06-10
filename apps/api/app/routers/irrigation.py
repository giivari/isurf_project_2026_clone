from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from pydantic import BaseModel
from typing import Optional, List
from datetime import datetime, timedelta, time
from ..database import get_db
from ..utils.auth import get_current_user
from ..utils.rbac import require_operator, require_admin
from ..models.irrigation import IrrigationSchedule
from ..models.device import Device
from ..schemas.irrigation import IrrigationScheduleCreate, IrrigationScheduleResponse

router = APIRouter(prefix="/irrigation", tags=["irrigation"])

MANUAL_OVERRIDES = {}

class TriggerRequest(BaseModel):
    device_id: int
    action: str # "ON" or "OFF"
    duration_minutes: Optional[int] = 30

@router.get("/status")
def get_status(device_id: int = 1, db: Session = Depends(get_db)):
    override = MANUAL_OVERRIDES.get(device_id)
    if override and override["until"] > datetime.now():
        return {"status": "Manual Override", "main_valve": override["state"], "until": override["until"].isoformat()}
    return {"status": "System Operational", "main_valve": "Auto"}

@router.post("/trigger", dependencies=[Depends(require_operator)])
def manual_trigger(request: TriggerRequest, db: Session = Depends(get_db)):
    until = datetime.now() + timedelta(minutes=request.duration_minutes)
    MANUAL_OVERRIDES[request.device_id] = {
        "state": request.action,
        "until": until
    }
    action_text = "turned ON" if request.action == "ON" else "turned OFF"
    return {
        "status": "success", 
        "message": f"Manual override triggered: Pump {action_text} for device {request.device_id} until {until.strftime('%H:%M:%S')}"
    }

@router.get("/schedules", response_model=List[IrrigationScheduleResponse])
def get_schedules(device_id: int = None, db: Session = Depends(get_db)):
    query = db.query(IrrigationSchedule)
    if device_id:
        query = query.filter(IrrigationSchedule.device_id == device_id)
    return query.all()

@router.post("/schedules", response_model=IrrigationScheduleResponse, dependencies=[Depends(require_operator)])
def create_schedule(schedule: IrrigationScheduleCreate, db: Session = Depends(get_db)):
    db_schedule = IrrigationSchedule(**schedule.model_dump())
    db.add(db_schedule)
    db.commit()
    db.refresh(db_schedule)
    return db_schedule

@router.delete("/schedules/{schedule_id}", dependencies=[Depends(require_admin)])
def delete_schedule(schedule_id: int, db: Session = Depends(get_db)):
    db_schedule = db.query(IrrigationSchedule).filter(IrrigationSchedule.id == schedule_id).first()
    if not db_schedule:
        raise HTTPException(status_code=404, detail="Schedule not found")
    db.delete(db_schedule)
    db.commit()
    return {"status": "success", "message": f"Schedule {schedule_id} deleted"}

@router.get("/state/{device_code}")
def get_actuator_state(device_code: str, db: Session = Depends(get_db)):
    device = db.query(Device).filter(Device.device_code == device_code).first()
    if not device:
        raise HTTPException(status_code=404, detail="Device not found")
    
    # 1. Check Manual Override
    override = MANUAL_OVERRIDES.get(device.id)
    now = datetime.now()
    if override and override["until"] > now:
        return {
            "pump": True if override["state"] == "ON" else False,
            "reason": f"manual_{override['state'].lower()}"
        }
    
    # 2. Check Schedules
    day_str = now.strftime("%a") # e.g. "Mon", "Tue"
    
    schedules = db.query(IrrigationSchedule).filter(
        IrrigationSchedule.device_id == device.id,
        IrrigationSchedule.is_active == True
    ).all()
    
    for sch in schedules:
        if day_str in sch.days_of_week or "Everyday" in sch.days_of_week:
            start_dt = datetime.combine(now.date(), sch.start_time)
            end_dt = start_dt + timedelta(minutes=sch.duration_minutes)
            if start_dt <= now <= end_dt:
                return {
                    "pump": True,
                    "reason": f"schedule_{sch.name}"
                }
                
    # 3. Default fallback
    return {
        "pump": False,
        "reason": "auto_off"
    }
