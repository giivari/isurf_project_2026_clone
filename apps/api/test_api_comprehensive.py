import requests
import json
import time

BASE_URL = "http://localhost:8000/api"
print("Starting Comprehensive API Tests...")

# 1. Auth Test
try:
    auth_data = {"username": "admin", "password": "password123"}
    r = requests.post(f"{BASE_URL}/auth/login", data=auth_data)
    assert r.status_code == 200, f"Auth failed: {r.status_code} {r.text}"
    token = r.json()["access_token"]
    headers = {"Authorization": f"Bearer {token}"}
    print("[PASS] Auth Test Passed")
except Exception as e:
    print(f"[FAIL] Auth Test Failed: {e}")
    exit(1)

# 2. Areas & Devices Test
try:
    # Get Areas
    r = requests.get(f"{BASE_URL}/areas/")
    assert r.status_code == 200
    areas = r.json()
    assert len(areas) > 0, "No areas found"
    area_id = areas[0]["id"]
    print("[PASS] Fetch Areas Passed")

    # Add Sensor
    sensor_data = {"id": "TEST-SENS-01", "name": "Test Sensor", "data_type": "Suhu Udara", "area_id": area_id, "min_threshold": 0.0, "max_threshold": 100.0}
    r = requests.post(f"{BASE_URL}/sensors/", json=sensor_data, headers=headers)
    assert r.status_code == 200, f"Add sensor failed: {r.text}"
    print("[PASS] Add Sensor Passed")

    # Delete Sensor
    r = requests.delete(f"{BASE_URL}/sensors/TEST-SENS-01", headers=headers)
    assert r.status_code == 200
    print("[PASS] Delete Sensor Passed")

    # Add Actuator
    act_data = {"id": "TEST-ACT-01", "name": "Test Actuator", "area_id": area_id, "flow_rate_per_sec": 1.5}
    r = requests.post(f"{BASE_URL}/actuators/", json=act_data, headers=headers)
    assert r.status_code == 200, f"Add actuator failed: {r.text}"
    print("[PASS] Add Actuator Passed")

    # Delete Actuator
    r = requests.delete(f"{BASE_URL}/actuators/TEST-ACT-01", headers=headers)
    assert r.status_code == 200
    print("[PASS] Delete Actuator Passed")

except Exception as e:
    print(f"[FAIL] Devices Test Failed: {e}")

# 3. Data Request Test
try:
    # Create Data Request (Public Endpoint)
    req_data = {
        "full_name": "Test User",
        "email": "test@user.com",
        "nim_nip": "123456789",
        "institution": "Universitas Test",
        "data_type": "Data Sensor 1 Bulan",
        "requested_sensors": "Suhu, TDS",
        "reason": "Penelitian",
        "date_start": "2026-01-01",
        "date_end": "2026-02-01"
    }
    files = {'document': ('dummy.pdf', b'dummy content', 'application/pdf')}
    r = requests.post(f"{BASE_URL}/data-requests/", data=req_data, files=files)
    assert r.status_code == 200, f"Create Data Request failed: {r.text}"
    req_id = r.json()["id"]
    print("[PASS] Create Data Request Passed")

    # Review Data Request (Admin Endpoint)
    review_data = {"status": "APPROVED", "admin_notes": "Ok, approved."}
    r = requests.put(f"{BASE_URL}/data-requests/{req_id}/review", json=review_data, headers=headers)
    assert r.status_code == 200, f"Review Data Request failed: {r.text}"
    assert r.json()["status"] == "APPROVED"
    print("✅ Review Data Request Passed")
except Exception as e:
    print(f"[FAIL] Data Request Test Failed: {e}")

# 4. IoT Gateway Test
try:
    payload = {
        "sensors": [
            {"sensor_id": "PH-GH1-01", "value": 6.5},
            {"sensor_id": "TDS-GH1-01", "value": 850.5}
        ]
    }
    r = requests.post("http://localhost:8000/ingest", json=payload, headers={"X-API-Key": "supersecure"})
    assert r.status_code == 200, f"IoT Ingest Failed: {r.text}"
    print("✅ IoT Ingestion Passed")
except Exception as e:
    print(f"[FAIL] IoT Gateway Test Failed: {e}")

print("All automated API tests finished.")
