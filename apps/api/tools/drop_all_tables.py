import sys
from pathlib import Path
sys.path.append(str(Path(__file__).resolve().parent.parent))

from app.database import engine
from sqlalchemy import text
from app.models.irrigation import AreaConditionRule, AreaScheduleRule

def main():
    tables = [
        'users', 'areas', 'sensors', 'actuators', 'sensor_logs',
        'area_aggregations', 'alerts', 'area_condition_rules',
        'area_schedule_rules', 'water_usage_logs', 'data_requests', 'plants'
    ]

    with engine.connect() as conn:
        print("Disabling foreign key checks...")
        conn.execute(text('SET FOREIGN_KEY_CHECKS=0;'))
        
        for t in tables:
            print(f"Dropping table {t} if exists...")
            conn.execute(text(f'DROP TABLE IF EXISTS {t}'))
            
        print("Enabling foreign key checks...")
        conn.execute(text('SET FOREIGN_KEY_CHECKS=1;'))
        conn.commit()
    
    print("All tables dropped successfully.")

if __name__ == "__main__":
    main()
