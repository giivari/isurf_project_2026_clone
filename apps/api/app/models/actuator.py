from sqlalchemy import Column, Integer, String, Float, DateTime, ForeignKey, func, Boolean
from sqlalchemy.orm import relationship
from ..database import Base

class Actuator(Base):
    __tablename__ = "actuators"

    id = Column(String(50), primary_key=True, index=True) # ID unik alat
    name = Column(String(100), nullable=False)
    flow_rate_per_sec = Column(Float, nullable=False, default=0.0)
    valve_status = Column(String(20), default='OFF') # 'ON' / 'OFF'
    is_auto_enabled = Column(Boolean, default=True)
    area_id = Column(Integer, ForeignKey("areas.id", ondelete="CASCADE"), nullable=True)
    auto_off_duration_sec = Column(Integer, default=0)
    last_turned_on_at = Column(DateTime, nullable=True)
    created_at = Column(DateTime, default=func.now())
    updated_at = Column(DateTime, default=func.now(), onupdate=func.now())

    area = relationship("Area", back_populates="actuators")
    water_logs = relationship("WaterUsageLog", back_populates="actuator", cascade="all, delete-orphan")
