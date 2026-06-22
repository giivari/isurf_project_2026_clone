import time
import random
import requests
import math

API_URL = "http://localhost:8000/iot/ingest"

def send_data(sensors_payload):
    payload = {
        "sensors": sensors_payload
    }
    headers = {
        "X-API-Key": "supersecure"
    }
    try:
        requests.post(API_URL, json=payload, headers=headers)
        for s in sensors_payload:
            print(f"Sent {s['sensor_id']}: {s['value']} ({s.get('status', 'Normal')})")
        print("---")
    except Exception as e:
        print(f"Failed to send data: {e}")

def main():
    print("Starting IoT Simulator...")
    step = 0
    while True:
        sensors_payload = []

        # Simulate Greenhouse A (Hidroponik NFT)
        # DHT-GH1-01 (Suhu Udara, ideal: 18-28)
        temp_A = 24 + 5 * math.sin(step * 0.05) + random.uniform(-0.5, 0.5)
        sensors_payload.append({"sensor_id": "DHT-GH1-01", "value": round(temp_A, 2)})

        # TDS-GH1-01 (TDS, ideal: 800-1200)
        tds_A = 1000 + 150 * math.sin(step * 0.1) + random.uniform(-10, 10)
        sensors_payload.append({"sensor_id": "TDS-GH1-01", "value": round(tds_A, 2)})

        # PH-GH1-01 (pH, ideal: 5.5-6.5)
        ph_A = 6.0 + 0.5 * math.sin(step * 0.02) + random.uniform(-0.1, 0.1)
        sensors_payload.append({"sensor_id": "PH-GH1-01", "value": round(ph_A, 2)})

        # Simulate Greenhouse B (Soil-based)
        # MST-GH2-01 (Kelembaban Tanah, ideal: 40-80)
        moist_B = 60 + 15 * math.sin(step * 0.08) + random.uniform(-2, 2)
        sensors_payload.append({"sensor_id": "MST-GH2-01", "value": round(moist_B, 2)})

        # Simulate Greenhouse C (Aeroponik)
        # TMP-GH3-01 (Suhu Akar, ideal: 15-22)
        temp_C = 18 + 3 * math.sin(step * 0.04) + random.uniform(-0.3, 0.3)
        sensors_payload.append({"sensor_id": "TMP-GH3-01", "value": round(temp_C, 2)})

        send_data(sensors_payload)

        step += 1
        time.sleep(30) # Send every 30 seconds

if __name__ == "__main__":
    main()
