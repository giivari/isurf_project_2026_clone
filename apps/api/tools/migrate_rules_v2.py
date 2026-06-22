import sys
from pathlib import Path
sys.path.append(str(Path(__file__).resolve().parent))

from app.database import engine, Base
from app.models import *
from sqlalchemy import text

# Drop the old table if it exists
try:
    with engine.connect() as conn:
        conn.execute(text("DROP TABLE IF EXISTS irrigation_rules CASCADE"))
        conn.commit()
        print("Dropped irrigation_rules table.")
except Exception as e:
    print(f"Could not drop irrigation_rules: {e}")

# Add the new columns or tables
try:
    Base.metadata.create_all(bind=engine)
    print("Created new tables.")
except Exception as e:
    print(f"Could not create tables: {e}")

# We also need to add `is_auto_enabled` to actuators if it doesn't exist
try:
    with engine.connect() as conn:
        conn.execute(text("ALTER TABLE actuators ADD COLUMN is_auto_enabled BOOLEAN DEFAULT TRUE"))
        conn.commit()
        print("Added is_auto_enabled to actuators.")
except Exception as e:
    print(f"Could not add is_auto_enabled to actuators (might already exist): {e}")

print("Migration completed.")
