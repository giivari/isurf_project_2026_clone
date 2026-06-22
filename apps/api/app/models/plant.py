from sqlalchemy import Column, Integer, String, DateTime, func, Float
from sqlalchemy.orm import relationship
from ..database import Base

class Plant(Base):
    __tablename__ = "plants"

    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(100), unique=True, index=True, nullable=False)
    image_path = Column(String(255), nullable=True)
    description = Column(String(500), nullable=True)
    optimal_temperature = Column(Float, nullable=True)
    optimal_moisture = Column(Float, nullable=True)
    optimal_light = Column(Float, nullable=True)
    created_at = Column(DateTime, default=func.now())
    updated_at = Column(DateTime, default=func.now(), onupdate=func.now())

    # TODO: Future feature - not yet active
    # zones = relationship("Zone", back_populates="plant")
