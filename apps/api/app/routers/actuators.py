from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from typing import List
from pydantic import BaseModel

from ..database import get_db
from ..models.actuator import Actuator
from ..utils.rbac import require_operator

router = APIRouter()

class ActuatorCreate(BaseModel):
    id: str
    name: str
    flow_rate_per_sec: float
    valve_status: str = 'OFF'
    is_auto_enabled: bool = True
    area_id: int = None

class ActuatorResponse(ActuatorCreate):
    class Config:
        from_attributes = True

@router.post("/", response_model=ActuatorResponse)
def create_actuator(actuator: ActuatorCreate, db: Session = Depends(get_db), user: dict = Depends(require_operator)):
    db_act = db.query(Actuator).filter(Actuator.id == actuator.id).first()
    if db_act:
        raise HTTPException(status_code=400, detail="Actuator already registered")
    
    db_act = Actuator(**actuator.dict())
    db.add(db_act)
    db.commit()
    db.refresh(db_act)
    return db_act

@router.get("/", response_model=List[ActuatorResponse])
def get_actuators(db: Session = Depends(get_db)):
    return db.query(Actuator).all()

@router.get("/{actuator_id}", response_model=ActuatorResponse)
def get_actuator(actuator_id: str, db: Session = Depends(get_db)):
    db_act = db.query(Actuator).filter(Actuator.id == actuator_id).first()
    if not db_act:
        raise HTTPException(status_code=404, detail="Actuator not found")
    return db_act

@router.put("/{actuator_id}", response_model=ActuatorResponse)
def update_actuator(actuator_id: str, actuator: ActuatorCreate, db: Session = Depends(get_db), user: dict = Depends(require_operator)):
    db_act = db.query(Actuator).filter(Actuator.id == actuator_id).first()
    if not db_act:
        raise HTTPException(status_code=404, detail="Actuator not found")
    
    db_act.name = actuator.name
    db_act.flow_rate_per_sec = actuator.flow_rate_per_sec
    db_act.valve_status = actuator.valve_status
    db_act.area_id = actuator.area_id

    db.commit()
    db.refresh(db_act)
    return db_act

@router.delete("/{actuator_id}")
def delete_actuator(actuator_id: str, db: Session = Depends(get_db), user: dict = Depends(require_operator)):
    db_act = db.query(Actuator).filter(Actuator.id == actuator_id).first()
    if not db_act:
        raise HTTPException(status_code=404, detail="Actuator not found")
    
    db.delete(db_act)
    db.commit()
    return {"message": "Actuator deleted successfully"}

class ActuatorToggle(BaseModel):
    is_auto_enabled: bool

@router.put("/{actuator_id}/toggle_auto", response_model=ActuatorResponse)
def toggle_actuator_auto(actuator_id: str, payload: ActuatorToggle, db: Session = Depends(get_db), user: dict = Depends(require_operator)):
    db_act = db.query(Actuator).filter(Actuator.id == actuator_id).first()
    if not db_act:
        raise HTTPException(status_code=404, detail="Actuator not found")
    
    db_act.is_auto_enabled = payload.is_auto_enabled
    db.commit()
    db.refresh(db_act)
    return db_act
