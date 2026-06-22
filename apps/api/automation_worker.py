import time
from datetime import datetime
from sqlalchemy.orm import Session
from sqlalchemy import desc
from app.database import SessionLocal
from app.models import AreaConditionRule, AreaScheduleRule, Actuator, Sensor, SensorLog

def check_conditions(db: Session):
    conditions = db.query(AreaConditionRule).all()
    
    for cond in conditions:
        # Find sensors in this area with the matching data_type
        sensors = db.query(Sensor).filter(
            Sensor.area_id == cond.area_id, 
            Sensor.data_type == cond.data_type
        ).all()
        
        sensor_ids = [s.id for s in sensors]
        if not sensor_ids:
            continue
            
        # Get the latest log for these sensors
        latest_logs = db.query(SensorLog).filter(SensorLog.sensor_id.in_(sensor_ids))\
                        .order_by(desc(SensorLog.date), desc(SensorLog.time)).limit(len(sensor_ids)).all()
                        
        if not latest_logs:
            continue
            
        # Average the values or check any? Let's check average.
        avg_val = sum(log.reading for log in latest_logs) / len(latest_logs)
        
        is_match = False
        if cond.operator == '<' and avg_val < cond.value:
            is_match = True
        elif cond.operator == '>' and avg_val > cond.value:
            is_match = True
            
        if is_match:
            # Apply action to all auto-enabled actuators in the area
            actuators = db.query(Actuator).filter(
                Actuator.area_id == cond.area_id,
                Actuator.is_auto_enabled == True
            ).all()
            for act in actuators:
                if act.valve_status != cond.action:
                    act.valve_status = cond.action
                    db.commit()
                    print(f"[{datetime.now()}] Condition Match! Actuator {act.id} turned {cond.action}")

def check_schedules(db: Session):
    schedules = db.query(AreaScheduleRule).all()
    now = datetime.now().time()
    
    for sched in schedules:
        # We need to trigger if the current time matches the schedule time within a 1 minute window
        # To avoid multiple triggers, we check if hour and minute match
        if sched.time.hour == now.hour and sched.time.minute == now.minute:
            actuators = db.query(Actuator).filter(
                Actuator.area_id == sched.area_id,
                Actuator.is_auto_enabled == True
            ).all()
            for act in actuators:
                if act.valve_status != sched.action:
                    act.valve_status = sched.action
                    db.commit()
                    print(f"[{datetime.now()}] Schedule Match! Actuator {act.id} turned {sched.action}")

def main():
    print("Starting Automation Worker...")
    while True:
        db = SessionLocal()
        try:
            check_conditions(db)
            check_schedules(db)
        except Exception as e:
            print(f"Error in worker: {e}")
        finally:
            db.close()
            
        time.sleep(10)

if __name__ == "__main__":
    main()
