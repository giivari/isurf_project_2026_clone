from pydantic import BaseModel, ConfigDict
from typing import Optional, List
from datetime import datetime
from .sensor import SensorResponse

class DeviceBase(BaseModel):
    device_code: str
    name: str
    type: Optional[str] = None
    location: Optional[str] = None
    firmware_version: Optional[str] = None

class DeviceCreate(DeviceBase):
    pass

class DeviceResponse(DeviceBase):
    id: int
    status: str
    last_heartbeat: Optional[datetime] = None
    created_at: datetime
    updated_at: datetime

    model_config = ConfigDict(from_attributes=True)
