from fastapi import APIRouter, Depends, HTTPException, UploadFile, File, Form
from sqlalchemy.orm import Session
from typing import List, Optional
from datetime import datetime, date
import uuid
import os
import shutil
import json
import csv
from io import StringIO
from fastapi.responses import StreamingResponse

from ..database import get_db
from ..models.data_request import DataRequest
from ..models.user import User
from ..models.sensor import Sensor
from ..models.reading import AreaAggregation
from ..schemas.data_request import DataRequestResponse, DataRequestReview
from ..utils.auth import get_current_user
from ..utils.rbac import require_operator

router = APIRouter()

UPLOAD_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), "../../../web/frontend/web/uploads/requests"))
os.makedirs(UPLOAD_DIR, exist_ok=True)

@router.post("/", response_model=DataRequestResponse)
async def create_request(
    full_name: str = Form(...),
    email: str = Form(...),
    nim_nip: str = Form(...),
    reason: str = Form(...),
    data_type: str = Form(...),
    requested_sensors: str = Form(...),  # JSON string
    date_start: date = Form(...),
    date_end: date = Form(...),
    document: UploadFile = File(...),
    db: Session = Depends(get_db)
):
    if not document.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Only PDF files are allowed")

    # Save document
    filename = f"{uuid.uuid4()}_{document.filename}"
    file_path = os.path.join(UPLOAD_DIR, filename)
    with open(file_path, "wb") as buffer:
        shutil.copyfileobj(document.file, buffer)

    # Parse sensors
    try:
        sensors_list = json.loads(requested_sensors)
    except (json.JSONDecodeError, TypeError):
        sensors_list = []

    tracking_code = str(uuid.uuid4())[:8].upper()

    db_request = DataRequest(
        tracking_code=tracking_code,
        full_name=full_name,
        email=email,
        nim_nip=nim_nip,
        reason=reason,
        document_path=f"/uploads/requests/{filename}",
        data_type=data_type,
        requested_sensors=sensors_list,
        date_start=date_start,
        date_end=date_end,
        status="PENDING"
    )
    db.add(db_request)
    db.commit()
    db.refresh(db_request)

    return db_request

@router.get("/status/{tracking_code}", response_model=DataRequestResponse)
def check_status(tracking_code: str, db: Session = Depends(get_db)):
    db_request = db.query(DataRequest).filter(DataRequest.tracking_code == tracking_code).first()
    if not db_request:
        raise HTTPException(status_code=404, detail="Request not found")
    return db_request

@router.get("/", response_model=List[DataRequestResponse])
def get_all_requests(db: Session = Depends(get_db)):
    return db.query(DataRequest).order_by(DataRequest.created_at.desc()).all()

@router.put("/{request_id}/review", response_model=DataRequestResponse)
def review_request(
    request_id: int, 
    review: DataRequestReview, 
    db: Session = Depends(get_db),
    user: dict = Depends(require_operator)
):
    db_request = db.query(DataRequest).filter(DataRequest.id == request_id).first()
    if not db_request:
        raise HTTPException(status_code=404, detail="Request not found")

    status_upper = review.status.upper()
    if status_upper not in ["PENDING", "REVIEW", "APPROVED", "REJECTED"]:
        raise HTTPException(status_code=400, detail="Invalid status")

    db_request.status = status_upper
    db_request.admin_notes = review.admin_notes
    from datetime import timezone
    db_request.reviewed_at = datetime.now(timezone.utc)
    db_request.reviewed_by = 1 # Default admin ID for MVP
    
    if status_upper == "APPROVED":
        db_request.download_token = str(uuid.uuid4())
    else:
        db_request.download_token = None

    db.commit()
    db.refresh(db_request)
    return db_request

@router.get("/download/{download_token}")
def download_data(download_token: str, db: Session = Depends(get_db)):
    db_request = db.query(DataRequest).filter(DataRequest.download_token == download_token, DataRequest.status == "APPROVED").first()
    if not db_request:
        raise HTTPException(status_code=404, detail="Invalid token or request not approved")
        
    # Generate CSV Data
    output = StringIO()
    writer = csv.writer(output)
    
    query = db.query(AreaAggregation).filter(
        AreaAggregation.date >= db_request.date_start,
        AreaAggregation.date <= db_request.date_end
    )
    
    if db_request.requested_sensors and "all" not in db_request.requested_sensors:
        query = query.filter(AreaAggregation.data_type.in_(db_request.requested_sensors))
        
    readings = query.order_by(AreaAggregation.date.desc(), AreaAggregation.time.desc()).limit(1000).all()
    
    writer.writerow(["ID", "Area ID", "Data Type", "Average Value", "Min Value", "Max Value", "Date", "Time"])
    for r in readings:
        writer.writerow([r.id, r.area_id, r.data_type, r.avg_value, r.min_value, r.max_value, r.date, r.time])
        
    output.seek(0)
    
    return StreamingResponse(
        iter([output.getvalue()]),
        media_type="text/csv",
        headers={"Content-Disposition": f"attachment; filename=isurf_data_{db_request.tracking_code}.csv"}
    )

from fastapi import Query
@router.get("/custom-download")
def custom_download_data(
    date_start: date = Query(...),
    date_end: date = Query(...),
    sensors: List[str] = Query(None),
    db: Session = Depends(get_db)
):
    # Generates custom CSV without token
    output = StringIO()
    writer = csv.writer(output)
    
    query = db.query(AreaAggregation).filter(
        AreaAggregation.date >= date_start,
        AreaAggregation.date <= date_end
    )
    
    if sensors and "all" not in sensors:
        query = query.filter(AreaAggregation.data_type.in_(sensors))
        
    readings = query.order_by(AreaAggregation.date.desc(), AreaAggregation.time.desc()).limit(1000).all()
    
    writer.writerow(["ID", "Area ID", "Data Type", "Average Value", "Min Value", "Max Value", "Date", "Time"])
    for r in readings:
        writer.writerow([r.id, r.area_id, r.data_type, r.avg_value, r.min_value, r.max_value, r.date, r.time])
        
    output.seek(0)
    
    filename_timestamp = datetime.now().strftime("%Y%m%d%H%M")
    return StreamingResponse(
        iter([output.getvalue()]),
        media_type="text/csv",
        headers={"Content-Disposition": f"attachment; filename=isurf_custom_data_{filename_timestamp}.csv"}
    )
