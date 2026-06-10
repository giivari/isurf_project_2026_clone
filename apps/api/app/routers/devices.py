from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from typing import List
from ..database import get_db
from ..models.device import Device
from ..models.sensor import Sensor
from ..schemas.device import DeviceResponse, DeviceCreate
from ..schemas.sensor import SensorResponse, SensorThresholdUpdate

router = APIRouter(prefix="/devices", tags=["devices"])

@router.post("/", response_model=DeviceResponse)
def create_device(payload: DeviceCreate, db: Session = Depends(get_db)):
    # Check if device code already exists
    existing_device = db.query(Device).filter(Device.device_code == payload.device_code).first()
    if existing_device:
        raise HTTPException(status_code=400, detail="Device code already registered")
        
    # Create the new device
    new_device = Device(
        device_code=payload.device_code,
        name=payload.name,
        type=payload.type,
        location=payload.location,
        status="offline"
    )
    db.add(new_device)
    db.commit()
    db.refresh(new_device)
    
    # Optionally, create default sensors for the new device
    default_sensors = [
        {"name": "Soil Moisture", "sensor_type": "moisture", "min_threshold": 30.0, "max_threshold": 80.0},
        {"name": "Soil pH", "sensor_type": "ph", "min_threshold": 5.5, "max_threshold": 7.5},
        {"name": "Water TDS", "sensor_type": "tds", "min_threshold": 0.0, "max_threshold": 500.0},
        {"name": "Air Temperature", "sensor_type": "temperature", "min_threshold": 20.0, "max_threshold": 35.0}
    ]
    
    for s_data in default_sensors:
        s = Sensor(
            device_id=new_device.id,
            name=s_data["name"],
            sensor_type=s_data["sensor_type"],
            min_threshold=s_data["min_threshold"],
            max_threshold=s_data["max_threshold"],
            is_active=True
        )
        db.add(s)
        
    db.commit()
    return new_device

@router.get("/", response_model=List[DeviceResponse])
def read_devices(skip: int = 0, limit: int = 100, db: Session = Depends(get_db)):
    devices = db.query(Device).order_by(Device.created_at.desc()).offset(skip).limit(limit).all()
    return devices

@router.get("/{device_id}", response_model=DeviceResponse)
def read_device(device_id: int, db: Session = Depends(get_db)):
    device = db.query(Device).filter(Device.id == device_id).first()
    if device is None:
        raise HTTPException(status_code=404, detail="Device not found")
    return device

@router.get("/{device_id}/sensors", response_model=List[SensorResponse])
def read_device_sensors(device_id: int, db: Session = Depends(get_db)):
    device = db.query(Device).filter(Device.id == device_id).first()
    if device is None:
        raise HTTPException(status_code=404, detail="Device not found")
    
    sensors = db.query(Sensor).filter(Sensor.device_id == device_id).all()
    return sensors

@router.put("/{device_id}/sensors/{sensor_id}/thresholds", response_model=SensorResponse)
def update_sensor_thresholds(device_id: int, sensor_id: int, payload: SensorThresholdUpdate, db: Session = Depends(get_db)):
    device = db.query(Device).filter(Device.id == device_id).first()
    if device is None:
        raise HTTPException(status_code=404, detail="Device not found")
    
    sensor = db.query(Sensor).filter(Sensor.id == sensor_id, Sensor.device_id == device_id).first()
    if sensor is None:
        raise HTTPException(status_code=404, detail="Sensor not found")
    
    sensor.min_threshold = payload.min_threshold
    sensor.max_threshold = payload.max_threshold
    
    db.commit()
    db.refresh(sensor)
    return sensor
