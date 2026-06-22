from pydantic import BaseModel, ConfigDict
from typing import Optional
from datetime import datetime

class ActuatorBase(BaseModel):
    name: str
    flow_rate_per_sec: float = 0.0
    valve_status: str = 'OFF'
    is_auto_enabled: bool = True

class ActuatorCreate(ActuatorBase):
    id: str
    area_id: Optional[int] = None

class ActuatorResponse(ActuatorBase):
    id: str
    area_id: Optional[int] = None
    created_at: datetime
    updated_at: datetime

    model_config = ConfigDict(from_attributes=True)
