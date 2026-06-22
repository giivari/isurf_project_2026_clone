from pydantic import BaseModel, ConfigDict
from typing import Optional
from datetime import datetime

class SensorBase(BaseModel):
    name: str
    data_type: str
    min_threshold: Optional[float] = None
    max_threshold: Optional[float] = None
    is_online: bool = True

class SensorCreate(SensorBase):
    id: str
    area_id: Optional[int] = None

class SensorResponse(SensorBase):
    id: str
    area_id: Optional[int] = None
    created_at: datetime
    updated_at: datetime

    model_config = ConfigDict(from_attributes=True)

class SensorThresholdUpdate(BaseModel):
    min_threshold: Optional[float] = None
    max_threshold: Optional[float] = None
