from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from typing import List
from datetime import datetime, timedelta

from ..database import get_db
from ..models.sensor import Sensor
from ..models.reading import AreaAggregation

router = APIRouter()

@router.get("/latest")
def get_latest_readings(db: Session = Depends(get_db)):
    """Returns the most recent aggregated data for each area and data_type."""
    yesterday = datetime.now() - timedelta(days=1)
    
    recent_aggs = db.query(AreaAggregation).filter(
        AreaAggregation.date >= yesterday.date()
    ).order_by(AreaAggregation.date.desc(), AreaAggregation.time.desc()).all()

    # Get thresholds for each data_type and area
    sensors = db.query(Sensor).all()
    thresholds_map = {}
    for s in sensors:
        key = f"{s.area_id}_{s.data_type}"
        thresholds_map[key] = {
            "min_threshold": s.min_threshold,
            "max_threshold": s.max_threshold
        }

    latest_map = {}
    for agg in recent_aggs:
        key = f"{agg.area_id}_{agg.data_type}"
        if key not in latest_map:
            t = thresholds_map.get(key, {})
            latest_map[key] = {
                "id": agg.id,
                "area_id": agg.area_id,
                "data_type": agg.data_type,
                "min_value": agg.min_value,
                "max_value": agg.max_value,
                "avg_value": agg.avg_value,
                "date": str(agg.date),
                "time": str(agg.time),
                "min_threshold": t.get("min_threshold"),
                "max_threshold": t.get("max_threshold")
            }
            
    return list(latest_map.values())

@router.get("/history/{area_id}/{data_type}")
def get_history(area_id: int, data_type: str, hours: int = 24, db: Session = Depends(get_db)):
    """Get history for a specific area and data_type."""
    cutoff = datetime.now() - timedelta(hours=hours)
    
    # Needs complex datetime filter
    aggs = db.query(AreaAggregation).filter(
        AreaAggregation.area_id == area_id,
        AreaAggregation.data_type == data_type,
        AreaAggregation.date >= cutoff.date()
    ).order_by(AreaAggregation.date.asc(), AreaAggregation.time.asc()).all()
    
    return [
        {
            "id": a.id,
            "avg_value": a.avg_value,
            "min_value": a.min_value,
            "max_value": a.max_value,
            "timestamp": f"{a.date} {a.time}"
        } for a in aggs
    ]
