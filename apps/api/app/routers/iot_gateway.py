from fastapi import APIRouter, Depends, HTTPException, BackgroundTasks, Header
from sqlalchemy.orm import Session
from pydantic import BaseModel
from datetime import datetime
from typing import List, Dict
import threading

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

# ============ BUFFER RATA-RATA 1 MENIT (In-Memory) ============
# Struktur: { "sensor_id": { "values": [float], "first_time": datetime } }
sensor_buffer: Dict[str, dict] = {}
buffer_lock = threading.Lock()
BUFFER_INTERVAL_SEC = 60  # Simpan rata-rata setiap 60 detik


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
                    act.is_auto_enabled = False # Disable auto to prevent loop
                    
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
                        message=f"Pompa {act.name} dimatikan paksa setelah menyala {act.auto_off_duration_sec} detik (Mode Otomatis dinonaktifkan).",
                        value=diff,
                        threshold_exceeded=act.auto_off_duration_sec,
                        is_read=False
                    )
                    db_session.add(alert)
                    print(f"[{datetime.now()}] AUTO-SHUTOFF TRIGGERED! Actuator {act.id} turned OFF.")
        
        db_session.commit()
    except Exception as e:
        print(f"Watchdog error: {e}")


def flush_buffer_if_ready(sensor_id: str, sensor, db: Session, background_tasks: BackgroundTasks, now: datetime):
    """Cek apakah buffer sensor sudah 1 menit. Jika ya, simpan rata-rata ke DB."""
    with buffer_lock:
        buf = sensor_buffer.get(sensor_id)
        if not buf or len(buf["values"]) == 0:
            return
        
        elapsed = (now - buf["first_time"]).total_seconds()
        if elapsed < BUFFER_INTERVAL_SEC:
            return  # Belum 1 menit, skip
        
        # Hitung rata-rata
        values = buf["values"]
        average = sum(values) / len(values)
        sample_count = len(values)
        
        # Reset buffer
        sensor_buffer[sensor_id] = {"values": [], "first_time": now}
    
    # Simpan rata-rata ke DB (di luar lock agar tidak blocking)
    current_date = now.date()
    current_time = now.time().replace(microsecond=0)
    
    # Cek status berdasarkan threshold
    status = "Normal"
    is_threshold_violation = False
    exceeded_val = 0.0

    if sensor.min_threshold is not None and average < sensor.min_threshold:
        status = "Rendah"
        is_threshold_violation = True
        exceeded_val = sensor.min_threshold
    elif sensor.max_threshold is not None and average > sensor.max_threshold:
        status = "Tinggi"
        is_threshold_violation = True
        exceeded_val = sensor.max_threshold

    log = SensorLog(
        date=current_date,
        time=current_time,
        reading=round(average, 2),
        anomalies=False,
        status=status,
        sensor_id=sensor_id
    )
    db.add(log)
    
    if is_threshold_violation:
        alert = Alert(
            sensor_id=sensor.id,
            alert_type="Threshold Violation",
            message=f"Sensor {sensor.name} rata-rata 1 menit: {round(average, 2)}, berstatus {status} (Batas: {exceeded_val}). Dari {sample_count} sampel.",
            value=round(average, 2),
            threshold_exceeded=exceeded_val,
            is_read=False
        )
        db.add(alert)
    
    print(f"[{now}] BUFFER FLUSH: sensor={sensor_id}, samples={sample_count}, avg={round(average, 2)}")
    
    # Trigger aggregation & automation with the average value
    if sensor.area_id:
        background_tasks.add_task(aggregate_sensor_data, sensor.area_id, sensor.data_type, current_date, current_time)
        background_tasks.add_task(evaluate_conditions, sensor.area_id, sensor.data_type, average)


@router.post("/ingest")
def ingest_data(payload: IngestPayload, background_tasks: BackgroundTasks, db: Session = Depends(get_db), x_api_key: str = Header(...)):
    if x_api_key != "supersecure":  # in production, load from env var
        raise HTTPException(status_code=401, detail="Invalid API Key")
    
    now = datetime.now()
    current_date = now.date()
    current_time = now.time().replace(microsecond=0)

    anomaly_detected = False

    for item in payload.sensors:
        sensor = db.query(Sensor).filter(Sensor.id == item.sensor_id).first()
        if not sensor:
            continue

        # Update heartbeat
        sensor.is_online = True
        sensor.updated_at = now

        # ====== CEK ANOMALI FISIK (langsung simpan, tidak di-buffer) ======
        is_anomaly = False
        anomaly_msg = ""
        dt = sensor.data_type.lower()
        if dt == "ph" and (item.value < 0 or item.value > 14):
            is_anomaly = True
            anomaly_msg = f"Nilai pH {item.value} tidak masuk akal (di luar skala 0-14)."
        elif dt in ["kelembaban", "kelembapan", "humidity", "kelembaban tanah", "kelembaban udara"] and (item.value < 0 or item.value > 100):
            is_anomaly = True
            anomaly_msg = f"Nilai kelembaban {item.value}% di luar skala (0-100)."
        elif dt in ["suhu", "temperature", "suhu udara"] and (item.value < -50 or item.value > 100):
            is_anomaly = True
            anomaly_msg = f"Nilai suhu {item.value}°C sangat tidak wajar."

        if is_anomaly:
            # ANOMALI: Langsung simpan ke DB & kirim alert tanpa menunggu buffer
            log = SensorLog(
                date=current_date,
                time=current_time,
                reading=item.value,
                anomalies=True,
                status="Kritis",
                sensor_id=item.sensor_id
            )
            db.add(log)
            
            alert = Alert(
                sensor_id=sensor.id,
                alert_type="Data Anomaly",
                message=anomaly_msg,
                value=item.value,
                threshold_exceeded=None,
                is_read=False
            )
            db.add(alert)
            anomaly_detected = True
            print(f"[{now}] ANOMALI INSTAN: sensor={item.sensor_id}, value={item.value}")
            continue  # Jangan masukkan anomali ke buffer rata-rata

        # ====== CEK THRESHOLD VIOLATION INSTAN ======
        is_threshold_violation = False
        if sensor.min_threshold is not None and item.value < sensor.min_threshold:
            is_threshold_violation = True
        elif sensor.max_threshold is not None and item.value > sensor.max_threshold:
            is_threshold_violation = True

        if is_threshold_violation:
            # THRESHOLD VIOLATION: Langsung simpan & alert
            status = "Rendah" if (sensor.min_threshold is not None and item.value < sensor.min_threshold) else "Tinggi"
            exceeded_val = sensor.min_threshold if status == "Rendah" else sensor.max_threshold
            
            log = SensorLog(
                date=current_date,
                time=current_time,
                reading=item.value,
                anomalies=False,
                status=status,
                sensor_id=item.sensor_id
            )
            db.add(log)
            
            alert = Alert(
                sensor_id=sensor.id,
                alert_type="Threshold Violation",
                message=f"Sensor {sensor.name} membaca nilai {item.value}, berstatus {status} (Batas: {exceeded_val}).",
                value=item.value,
                threshold_exceeded=exceeded_val,
                is_read=False
            )
            db.add(alert)
            anomaly_detected = True
            print(f"[{now}] THRESHOLD VIOLATION INSTAN: sensor={item.sensor_id}, value={item.value}")
            
            # Trigger automation immediately for threshold violations
            if sensor.area_id:
                background_tasks.add_task(evaluate_conditions, sensor.area_id, sensor.data_type, item.value)
            continue  # Jangan masukkan ke buffer rata-rata

        # ====== DATA NORMAL: Masukkan ke buffer rata-rata 1 menit ======
        with buffer_lock:
            if item.sensor_id not in sensor_buffer:
                sensor_buffer[item.sensor_id] = {"values": [], "first_time": now}
            sensor_buffer[item.sensor_id]["values"].append(item.value)
        
        # Cek apakah buffer sudah 1 menit, jika ya flush
        flush_buffer_if_ready(item.sensor_id, sensor, db, background_tasks, now)
        
        # Failsafe Cut-off Logic
        if sensor.area_id:
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

    try:
        db.commit()

        # Trigger actuator watchdog
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
