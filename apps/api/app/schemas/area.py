from pydantic import BaseModel, ConfigDict
from typing import Optional
from datetime import datetime

class AreaBase(BaseModel):
    name: str
    plant: Optional[str] = None
    description: Optional[str] = None

class AreaCreate(AreaBase):
    pass

class AreaResponse(AreaBase):
    id: int
    created_at: datetime
    updated_at: datetime

    model_config = ConfigDict(from_attributes=True)
