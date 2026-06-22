from pydantic import BaseModel, ConfigDict
from typing import Optional
from datetime import datetime, date, time

class ReadingBase(BaseModel):
    reading: float

class ReadingCreate(ReadingBase):
    sensor_id: str
    anomalies: Optional[bool] = False
    status: Optional[str] = "Normal"

class ReadingResponse(ReadingBase):
    id: int
    sensor_id: str
    date: date
    time: time
    anomalies: Optional[bool] = False
    status: Optional[str] = "Normal"
    created_at: datetime

    model_config = ConfigDict(from_attributes=True)

class AreaAggregationResponse(BaseModel):
    id: int
    area_id: int
    date: date
    time: time
    data_type: str
    min_value: Optional[float] = None
    max_value: Optional[float] = None
    avg_value: Optional[float] = None
    created_at: datetime

    model_config = ConfigDict(from_attributes=True)
