import statistics
from sqlalchemy.orm import Session
from ..database import SessionLocal
from ..models.reading import SensorLog, AreaAggregation
from ..models.sensor import Sensor

def aggregate_sensor_data(area_id: int, data_type: str, current_date, current_time):
    db: Session = SessionLocal()
    try:
        # Get all recent readings for this area and data type at this specific time/date
        logs = db.query(SensorLog).join(Sensor).filter(
            Sensor.area_id == area_id,
            Sensor.data_type == data_type,
            SensorLog.date == current_date,
            # We aggregate for this specific payload timestamp
            # In a real app, maybe group by minute, but for simplicity we'll just take the exact time
            SensorLog.time == current_time
        ).all()

        if not logs:
            return

        values = [log.reading for log in logs]
        
        # Trimmed mean if more than 3 sensors
        if len(values) > 3:
            values.sort()
            values = values[1:-1] # remove lowest and highest
            
        avg_val = statistics.mean(values) if values else 0.0
        min_val = min(values) if values else 0.0
        max_val = max(values) if values else 0.0

        aggregation = AreaAggregation(
            date=current_date,
            time=current_time,
            data_type=data_type,
            min_value=min_val,
            max_value=max_val,
            avg_value=avg_val,
            area_id=area_id
        )
        db.add(aggregation)
        db.commit()
    except Exception as e:
        print(f"Aggregation error: {e}")
    finally:
        db.close()
