from sqlalchemy import Column, Integer, String, Text, DateTime, Date, ForeignKey, JSON
from sqlalchemy.orm import relationship
from datetime import datetime, timezone
from ..database import Base

class DataRequest(Base):
    __tablename__ = "data_requests"

    id = Column(Integer, primary_key=True, index=True)
    tracking_code = Column(String(50), unique=True, index=True, nullable=False)
    full_name = Column(String(255), nullable=False)
    email = Column(String(255), nullable=False)
    nim_nip = Column(String(50), nullable=False)
    reason = Column(Text, nullable=False)
    document_path = Column(String(255), nullable=False)
    data_type = Column(String(20), nullable=False)  # 'monitoring', 'analytics'
    requested_sensors = Column(JSON)
    date_start = Column(Date)
    date_end = Column(Date)
    status = Column(String(20), default="pending")  # 'pending', 'approved', 'rejected'
    admin_notes = Column(Text)
    download_token = Column(String(64))
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))
    reviewed_at = Column(DateTime)
    reviewed_by = Column(Integer, ForeignKey("users.id", ondelete="SET NULL"))

    reviewer = relationship("User", foreign_keys=[reviewed_by])
