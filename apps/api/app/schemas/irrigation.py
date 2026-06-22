from pydantic import BaseModel, ConfigDict
from typing import Optional
from datetime import datetime, time

class ConditionRuleBase(BaseModel):
    data_type: str
    operator: str
    value: float
    action: str

class ConditionRuleCreate(ConditionRuleBase):
    area_id: int

class ConditionRuleResponse(ConditionRuleBase):
    id: int
    area_id: int
    created_at: datetime
    updated_at: datetime

    model_config = ConfigDict(from_attributes=True)

class ScheduleRuleBase(BaseModel):
    time: time
    action: str

class ScheduleRuleCreate(ScheduleRuleBase):
    area_id: int

class ScheduleRuleResponse(ScheduleRuleBase):
    id: int
    area_id: int
    created_at: datetime
    updated_at: datetime

    model_config = ConfigDict(from_attributes=True)
