from fastapi import APIRouter, Depends, HTTPException, BackgroundTasks, Header
from sqlalchemy.orm import Session
from pydantic import BaseModel
from datetime import datetime
from typing import List

from ..database import get_db
from ..models.sensor import Sensor
from ..models.reading import SensorLog
from ..models.alert import Alert
from ..utils.aggregation import aggregate_sensor_data
from ..utils.automation import evaluate_conditions
from ..models.actuator import Actuator

router = APIRouter()

class SensorPayload(BaseModel):
    sensor_id: str
    value: float
    status: str = "Normal"

class IngestPayload(BaseModel):
    sensors: List[SensorPayload]

from ..models.water import WaterUsageLog

def check_actuator_limits(db_session: Session):
    try:
        now = datetime.now()
        actuators = db_session.query(Actuator).filter(Actuator.valve_status == "ON", Actuator.auto_off_duration_sec > 0).all()
        for act in actuators:
            if act.last_turned_on_at:
                diff = (now - act.last_turned_on_at).total_seconds()
                if diff >= act.auto_off_duration_sec:
                    # Time to turn off
                    act.valve_status = "OFF"
                    
                    # Log water usage
                    volume_used = diff * act.flow_rate_per_sec
                    latest_log = db_session.query(WaterUsageLog).order_by(WaterUsageLog.id.desc()).first()
                    max_capacity = 100.0
                    sisa_air = latest_log.water_remaining if latest_log else max_capacity
                    new_sisa = max(0.0, sisa_air - volume_used)
                    
                    new_log = WaterUsageLog(
                        water_discharged=volume_used,
                        water_remaining=new_sisa,
                        actuator_id=act.id
                    )
                    db_session.add(new_log)
                    
                    # Generate an alert
                    alert = Alert(
                        sensor_id=None,
                        alert_type="Auto Shutoff",
                        message=f"Pompa {act.name} dimatikan otomatis setelah menyala {act.auto_off_duration_sec} detik.",
                        value=diff,
                        threshold_exceeded=act.auto_off_duration_sec,
                        is_read=False
                    )
                    db_session.add(alert)
                    print(f"[{datetime.now()}] AUTO-SHUTOFF TRIGGERED! Actuator {act.id} turned OFF.")
        
        db_session.commit()
    except Exception as e:
        print(f"Watchdog error: {e}")

@router.post("/ingest")
def ingest_data(payload: IngestPayload, background_tasks: BackgroundTasks, db: Session = Depends(get_db), x_api_key: str = Header(...)):
    if x_api_key != "supersecure":  # in production, load from env var
        raise HTTPException(status_code=401, detail="Invalid API Key")
    
    now = datetime.now()
    current_date = now.date()
    current_time = now.time().replace(microsecond=0)

    processed_areas = set()

    for item in payload.sensors:
        sensor = db.query(Sensor).filter(Sensor.id == item.sensor_id).first()
        if not sensor:
            continue

        # Update heartbeat
        sensor.is_online = True
        sensor.updated_at = now

        # Check Physical Anomalies
        is_anomaly = False
        anomaly_msg = ""
        dt = sensor.data_type.lower()
        if dt == "ph" and (item.value < 0 or item.value > 14):
            is_anomaly = True
            anomaly_msg = f"Nilai pH {item.value} tidak masuk akal (di luar skala 0-14)."
        elif dt in ["kelembaban", "kelembapan", "humidity"] and (item.value < 0 or item.value > 100):
            is_anomaly = True
            anomaly_msg = f"Nilai kelembaban {item.value}% di luar skala (0-100)."
        elif dt in ["suhu", "temperature"] and (item.value < -50 or item.value > 100):
            is_anomaly = True
            anomaly_msg = f"Nilai suhu {item.value}°C sangat tidak wajar."

        # Check User Thresholds (Status)
        status = "Normal"
        is_threshold_violation = False
        exceeded_val = 0.0

        if sensor.min_threshold is not None and item.value < sensor.min_threshold:
            status = "Rendah"
            is_threshold_violation = True
            exceeded_val = sensor.min_threshold
        elif sensor.max_threshold is not None and item.value > sensor.max_threshold:
            status = "Tinggi"
            is_threshold_violation = True
            exceeded_val = sensor.max_threshold
            
        if is_anomaly:
            status = "Kritis"

        log = SensorLog(
            date=current_date,
            time=current_time,
            reading=item.value,
            anomalies=is_anomaly,
            status=status,
            sensor_id=item.sensor_id
        )
        db.add(log)

        # Trigger alert
        if is_anomaly:
            alert = Alert(
                sensor_id=sensor.id,
                alert_type="Data Anomaly",
                message=anomaly_msg,
                value=item.value,
                threshold_exceeded=None,
                is_read=False
            )
            db.add(alert)
        elif is_threshold_violation:
            alert = Alert(
                sensor_id=sensor.id,
                alert_type="Threshold Violation",
                message=f"Sensor {sensor.name} membaca nilai {item.value}, berstatus {status} (Batas: {exceeded_val}).",
                value=item.value,
                threshold_exceeded=exceeded_val,
                is_read=False
            )
            db.add(alert)
        
        if sensor.area_id:
            processed_areas.add((sensor.area_id, sensor.data_type))
            # Failsafe Cut-off Logic
            if sensor.data_type == "Level Air" and item.value < 5.0:
                actuators = db.query(Actuator).filter(Actuator.area_id == sensor.area_id).all()
                for act in actuators:
                    if act.valve_status == "ON":
                        act.valve_status = "OFF"
                        alert = Alert(
                            sensor_id=sensor.id,
                            alert_type="Failsafe Triggered",
                            message=f"Failsafe aktif! Pompa {act.name} dimatikan paksa karena level air kritis.",
                            value=item.value,
                            threshold_exceeded=5.0,
                            is_read=False
                        )
                        db.add(alert)
                        print(f"[{datetime.now()}] FAILSAFE TRIGGERED! Actuator {act.id} turned OFF.")

            # Trigger automation rules evaluation
            background_tasks.add_task(evaluate_conditions, sensor.area_id, sensor.data_type, item.value)

    try:
        db.commit()

        # Trigger aggregation in background
        for area_id, data_type in processed_areas:
            background_tasks.add_task(aggregate_sensor_data, area_id, data_type, current_date, current_time)

        # Trigger actuator watchdog
        # Create a new session for the background task to avoid threading issues
        from ..database import SessionLocal
        def run_watchdog():
            db_bg = SessionLocal()
            try:
                check_actuator_limits(db_bg)
            finally:
                db_bg.close()
        background_tasks.add_task(run_watchdog)

        return {"status": "ok", "message": f"Ingested {len(payload.sensors)} readings"}
    except Exception as e:
        db.rollback()
        import traceback
        return {"status": "error", "message": str(e), "traceback": traceback.format_exc()}
