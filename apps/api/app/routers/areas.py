from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from typing import List
from pydantic import BaseModel

from ..database import get_db
from ..models.area import Area
from ..models.sensor import Sensor
from ..models.irrigation import AreaConditionRule, AreaScheduleRule
from datetime import time
from ..utils.rbac import require_operator

router = APIRouter()

class AreaCreate(BaseModel):
    name: str
    plant: str = None
    description: str = None

class AreaResponse(AreaCreate):
    id: int
    class Config:
        from_attributes = True

@router.post("/", response_model=AreaResponse)
def create_area(area: AreaCreate, db: Session = Depends(get_db), user: dict = Depends(require_operator)):
    db_area = Area(**area.dict())
    db.add(db_area)
    db.commit()
    db.refresh(db_area)
    return db_area

@router.get("/", response_model=List[AreaResponse])
def get_areas(db: Session = Depends(get_db)):
    return db.query(Area).all()

@router.get("/{area_id}", response_model=AreaResponse)
def get_area(area_id: int, db: Session = Depends(get_db)):
    db_area = db.query(Area).filter(Area.id == area_id).first()
    if not db_area:
        raise HTTPException(status_code=404, detail="Area not found")
    return db_area

class ThresholdUpdate(BaseModel):
    data_type: str
    min_threshold: float
    max_threshold: float

@router.put("/{area_id}/sensors/thresholds")
def update_area_sensor_thresholds(area_id: int, payload: ThresholdUpdate, db: Session = Depends(get_db), user: dict = Depends(require_operator)):
    sensors = db.query(Sensor).filter(Sensor.area_id == area_id, Sensor.data_type == payload.data_type).all()
    if not sensors:
        return {"message": "No sensors found for this data type in this area"}
    
    for s in sensors:
        s.min_threshold = payload.min_threshold
        s.max_threshold = payload.max_threshold
    
    db.commit()
    return {"message": f"Updated thresholds for {len(sensors)} sensors", "count": len(sensors)}

class ConditionCreate(BaseModel):
    data_type: str
    operator: str
    value: float
    action: str

class ConditionResponse(ConditionCreate):
    id: int
    area_id: int
    class Config:
        from_attributes = True

class ScheduleCreate(BaseModel):
    time: time
    action: str

class ScheduleResponse(ScheduleCreate):
    id: int
    area_id: int
    class Config:
        from_attributes = True

@router.get("/{area_id}/conditions", response_model=List[ConditionResponse])
def get_area_conditions(area_id: int, db: Session = Depends(get_db)):
    return db.query(AreaConditionRule).filter(AreaConditionRule.area_id == area_id).all()

@router.post("/{area_id}/conditions", response_model=ConditionResponse)
def create_area_condition(area_id: int, rule: ConditionCreate, db: Session = Depends(get_db), user: dict = Depends(require_operator)):
    db_area = db.query(Area).filter(Area.id == area_id).first()
    if not db_area:
        raise HTTPException(status_code=404, detail="Area not found")
        
    db_rule = AreaConditionRule(
        data_type=rule.data_type,
        operator=rule.operator,
        value=rule.value,
        action=rule.action,
        area_id=area_id
    )
    db.add(db_rule)
    db.commit()
    db.refresh(db_rule)
    return db_rule

@router.delete("/{area_id}/conditions/{rule_id}")
def delete_area_condition(area_id: int, rule_id: int, db: Session = Depends(get_db), user: dict = Depends(require_operator)):
    db_rule = db.query(AreaConditionRule).filter(AreaConditionRule.id == rule_id, AreaConditionRule.area_id == area_id).first()
    if not db_rule:
        raise HTTPException(status_code=404, detail="Condition not found")
        
    db.delete(db_rule)
    db.commit()
    return {"message": "Condition deleted successfully"}


@router.get("/{area_id}/schedules", response_model=List[ScheduleResponse])
def get_area_schedules(area_id: int, db: Session = Depends(get_db)):
    return db.query(AreaScheduleRule).filter(AreaScheduleRule.area_id == area_id).all()

@router.post("/{area_id}/schedules", response_model=ScheduleResponse)
def create_area_schedule(area_id: int, rule: ScheduleCreate, db: Session = Depends(get_db), user: dict = Depends(require_operator)):
    db_area = db.query(Area).filter(Area.id == area_id).first()
    if not db_area:
        raise HTTPException(status_code=404, detail="Area not found")
        
    db_rule = AreaScheduleRule(
        time=rule.time,
        action=rule.action,
        area_id=area_id
    )
    db.add(db_rule)
    db.commit()
    db.refresh(db_rule)
    return db_rule

@router.delete("/{area_id}/schedules/{rule_id}")
def delete_area_schedule(area_id: int, rule_id: int, db: Session = Depends(get_db), user: dict = Depends(require_operator)):
    db_rule = db.query(AreaScheduleRule).filter(AreaScheduleRule.id == rule_id, AreaScheduleRule.area_id == area_id).first()
    if not db_rule:
        raise HTTPException(status_code=404, detail="Schedule not found")
        
    db.delete(db_rule)
    db.commit()
    return {"message": "Schedule deleted successfully"}
