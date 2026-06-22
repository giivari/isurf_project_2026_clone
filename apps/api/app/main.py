from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from .routers import areas, sensors, actuators, readings, iot_gateway, auth, alerts, irrigation, data_requests

app = FastAPI(title="iSURF IoT Monitoring API", root_path="/isurf/v1")

# Configure CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"], # Allow all for development. Restrict in prod.
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Include routers
app.include_router(auth.router, prefix="/api")
app.include_router(areas.router, prefix="/api/areas")
app.include_router(sensors.router, prefix="/api/sensors")
app.include_router(actuators.router, prefix="/api/actuators")
app.include_router(readings.router, prefix="/api/readings")
app.include_router(alerts.router, prefix="/api/alerts")
app.include_router(irrigation.router, prefix="/api/irrigation")
app.include_router(data_requests.router, prefix="/api/data-requests")
app.include_router(iot_gateway.router)

@app.get("/")
def read_root():
    return {"message": "Welcome to iSURF API"}
