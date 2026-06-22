from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from typing import List
from pydantic import BaseModel

from ..database import get_db
from ..models.sensor import Sensor
from ..utils.rbac import require_operator

router = APIRouter()

class SensorCreate(BaseModel):
    id: str
    name: str
    data_type: str
    min_threshold: float = None
    max_threshold: float = None
    is_online: bool = False
    area_id: int = None

class SensorResponse(SensorCreate):
    class Config:
        from_attributes = True

@router.post("/", response_model=SensorResponse)
def create_sensor(sensor: SensorCreate, db: Session = Depends(get_db), user: dict = Depends(require_operator)):
    db_sensor = db.query(Sensor).filter(Sensor.id == sensor.id).first()
    if db_sensor:
        raise HTTPException(status_code=400, detail="Sensor already registered")
    
    db_sensor = Sensor(**sensor.dict())
    db.add(db_sensor)
    db.commit()
    db.refresh(db_sensor)
    return db_sensor

@router.get("/", response_model=List[SensorResponse])
def get_sensors(db: Session = Depends(get_db)):
    from datetime import datetime
    sensors = db.query(Sensor).all()
    now = datetime.now()
    
    dirty = False
    for s in sensors:
        if s.updated_at and (now - s.updated_at).total_seconds() > 300:
            if s.is_online:
                s.is_online = False
                dirty = True
    
    if dirty:
        db.commit()
        
    return sensors

@router.get("/{sensor_id}", response_model=SensorResponse)
def get_sensor(sensor_id: str, db: Session = Depends(get_db)):
    db_sensor = db.query(Sensor).filter(Sensor.id == sensor_id).first()
    if not db_sensor:
        raise HTTPException(status_code=404, detail="Sensor not found")
    return db_sensor

@router.put("/{sensor_id}", response_model=SensorResponse)
def update_sensor(sensor_id: str, sensor: SensorCreate, db: Session = Depends(get_db), user: dict = Depends(require_operator)):
    db_sensor = db.query(Sensor).filter(Sensor.id == sensor_id).first()
    if not db_sensor:
        raise HTTPException(status_code=404, detail="Sensor not found")
    
    db_sensor.name = sensor.name
    db_sensor.data_type = sensor.data_type
    db_sensor.min_threshold = sensor.min_threshold
    db_sensor.max_threshold = sensor.max_threshold
    db_sensor.is_online = sensor.is_online
    db_sensor.area_id = sensor.area_id

    db.commit()
    db.refresh(db_sensor)
    return db_sensor

@router.delete("/{sensor_id}")
def delete_sensor(sensor_id: str, db: Session = Depends(get_db), user: dict = Depends(require_operator)):
    db_sensor = db.query(Sensor).filter(Sensor.id == sensor_id).first()
    if not db_sensor:
        raise HTTPException(status_code=404, detail="Sensor not found")
    
    db.delete(db_sensor)
    db.commit()
    return {"message": "Sensor deleted successfully"}
