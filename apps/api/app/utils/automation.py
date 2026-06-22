from sqlalchemy.orm import Session
from ..database import SessionLocal
from ..models.irrigation import AreaConditionRule
from ..models.actuator import Actuator
from ..models.water import WaterUsageLog

def evaluate_conditions(area_id: int, data_type: str, current_value: float):
    db: Session = SessionLocal()
    try:
        rules = db.query(AreaConditionRule).filter(
            AreaConditionRule.area_id == area_id,
            AreaConditionRule.data_type == data_type
        ).all()

        if not rules:
            return

        for rule in rules:
            condition_met = False
            if rule.operator == ">" and current_value > rule.value:
                condition_met = True
            elif rule.operator == "<" and current_value < rule.value:
                condition_met = True
            elif rule.operator == "==" and current_value == rule.value:
                condition_met = True

            if condition_met:
                # Find the actuator for this area (assume 1 main pump for now)
                actuators = db.query(Actuator).filter(
                    Actuator.area_id == area_id,
                    Actuator.is_auto_enabled == True
                ).all()

                for actuator in actuators:
                    if actuator.valve_status != rule.action:
                        if actuator.valve_status == "ON" and rule.action == "OFF":
                            duration_sec = 60
                            volume_used = duration_sec * actuator.flow_rate_per_sec
                            latest_log = db.query(WaterUsageLog).order_by(WaterUsageLog.id.desc()).first()
                            max_capacity = 100.0
                            sisa_air = latest_log.water_remaining if latest_log else max_capacity
                            new_sisa = max(0.0, sisa_air - volume_used)
                            
                            new_log = WaterUsageLog(
                                water_discharged=volume_used,
                                water_remaining=new_sisa,
                                actuator_id=actuator.id
                            )
                            db.add(new_log)
                        actuator.valve_status = rule.action
                db.commit()

    except Exception as e:
        print(f"Automation error: {e}")
    finally:
        db.close()
