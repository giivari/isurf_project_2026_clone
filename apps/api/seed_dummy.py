import os
import sys
import random
from datetime import datetime, timedelta

sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from app.database import SessionLocal, Base, engine
from app.models.area import Area
from app.models.sensor import Sensor
from app.models.actuator import Actuator
from app.models.reading import SensorLog, AreaAggregation
from app.models.water import WaterUsageLog
from app.models.alert import Alert
from app.models.data_request import DataRequest

def seed_data():
    Base.metadata.create_all(bind=engine)
    db = SessionLocal()
    try:
        # Clear existing data to prevent duplicates when running multiple times
        db.query(Alert).delete()
        db.query(WaterUsageLog).delete()
        db.query(AreaAggregation).delete()
        db.query(SensorLog).delete()
        db.query(Actuator).delete()
        db.query(Sensor).delete()
        db.query(Area).delete()
        db.query(DataRequest).delete()
        db.commit()

        now = datetime.now()

        # 1. Areas
        areas_data = [
            Area(id=1, name='Greenhouse A (Hidroponik NFT)', plant='Selada Air, Pakcoy, Kangkung', description='Fokus pada sayuran daun dengan sistem Nutrient Film Technique (NFT). Terdiri dari 5 rak utama.'),
            Area(id=2, name='Greenhouse B (Soil-based)', plant='Tomat Cherry, Paprika, Cabai', description='Budidaya sayuran buah dengan media tanah konvensional dan sistem irigasi tetes (Drip Irrigation).'),
            Area(id=3, name='Greenhouse C (Aeroponik)', plant='Kentang Granola, Mint', description='Sistem budidaya aeroponik untuk umbi-umbian dan herbal. Akar menggantung dan disemprot nutrisi bertekanan.')
        ]
        db.add_all(areas_data)
        db.commit()

        # 2. Sensors
        sensors_data = [
            # GH 1 (Hidroponik)
            Sensor(id='DHT-GH1-01', area_id=1, name='Sensor Suhu & Kelembaban Udara 1', data_type='Suhu Udara', min_threshold=18.0, max_threshold=28.0, is_online=True),
            Sensor(id='DHT-GH1-02', area_id=1, name='Sensor Suhu & Kelembaban Udara 2', data_type='Kelembaban Udara', min_threshold=50.0, max_threshold=80.0, is_online=True),
            Sensor(id='TDS-GH1-01', area_id=1, name='Sensor Nutrisi TDS/EC Tandon', data_type='TDS Nutrisi', min_threshold=800.0, max_threshold=1200.0, is_online=True),
            Sensor(id='PH-GH1-01', area_id=1, name='Sensor pH Air Tandon', data_type='pH Air', min_threshold=5.5, max_threshold=6.5, is_online=True),
            Sensor(id='WL-GH1-01', area_id=1, name='Sensor Water Level Tandon', data_type='Level Air', min_threshold=20.0, max_threshold=100.0, is_online=True),
            
            # GH 2 (Soil-based)
            Sensor(id='MST-GH2-01', area_id=2, name='Sensor Kelembaban Tanah A', data_type='Kelembaban Tanah', min_threshold=40.0, max_threshold=80.0, is_online=True),
            Sensor(id='MST-GH2-02', area_id=2, name='Sensor Kelembaban Tanah B', data_type='Kelembaban Tanah', min_threshold=40.0, max_threshold=80.0, is_online=True),
            Sensor(id='TMP-GH2-01', area_id=2, name='Sensor Suhu Tanah', data_type='Suhu Tanah', min_threshold=20.0, max_threshold=28.0, is_online=False),
            Sensor(id='PH-GH2-01', area_id=2, name='Sensor pH Tanah', data_type='pH Tanah', min_threshold=6.0, max_threshold=7.0, is_online=True),
            
            # GH 3 (Aeroponik)
            Sensor(id='TMP-GH3-01', area_id=3, name='Sensor Suhu Ruang Akar', data_type='Suhu Akar', min_threshold=15.0, max_threshold=22.0, is_online=True),
            Sensor(id='PRS-GH3-01', area_id=3, name='Sensor Tekanan Pompa Kabut', data_type='Tekanan', min_threshold=40.0, max_threshold=80.0, is_online=True),
        ]
        db.add_all(sensors_data)
        db.commit()

        # 3. Actuators
        actuators_data = [
            # GH 1
            Actuator(id='PMP-GH1-01', area_id=1, name='Pompa Sirkulasi NFT Utama', valve_status='ON', is_auto_enabled=True, flow_rate_per_sec=1.5),
            Actuator(id='PMP-GH1-02', area_id=1, name='Pompa Dosing Nutrisi A/B', valve_status='OFF', is_auto_enabled=True, flow_rate_per_sec=0.1),
            Actuator(id='FAN-GH1-01', area_id=1, name='Exhaust Fan Atap', valve_status='OFF', is_auto_enabled=True, flow_rate_per_sec=0.0),
            
            # GH 2
            Actuator(id='PMP-GH2-01', area_id=2, name='Pompa Irigasi Tetes', valve_status='OFF', is_auto_enabled=True, flow_rate_per_sec=2.0),
            Actuator(id='VLV-GH2-01', area_id=2, name='Solenoid Valve Bedengan A', valve_status='OFF', is_auto_enabled=False, flow_rate_per_sec=0.5),
            
            # GH 3
            Actuator(id='PMP-GH3-01', area_id=3, name='Pompa High-Pressure Aeroponik', valve_status='ON', is_auto_enabled=True, flow_rate_per_sec=0.8),
        ]
        db.add_all(actuators_data)
        db.commit()

        # 4. Generate Timeseries Data (Raw Logs & Aggregations)
        # 3 Days of data, 24 hours each
        for day_offset in range(3, -1, -1):
            for hour in range(24):
                time_point = now - timedelta(days=day_offset, hours=(23-hour))
                
                # Base dynamic environmental patterns
                hour_of_day = time_point.hour
                is_day = 6 <= hour_of_day <= 18
                temp_factor = (hour_of_day - 6) / 12.0 if is_day else (18 - hour_of_day) / 12.0 # simplified bell curve
                
                # GH 1 Data
                t_air_1 = 22.0 + (8.0 * temp_factor) + random.uniform(-1, 1)
                h_air_1 = 85.0 - (20.0 * temp_factor) + random.uniform(-3, 3)
                tds_1 = 950.0 - (day_offset * 10) + random.uniform(-5, 5) # slowly decreasing over days
                ph_1 = 6.2 + random.uniform(-0.1, 0.1)
                wl_1 = 90.0 - (day_offset * 5) - (hour * 0.2) # slowly draining
                
                # GH 2 Data
                sm_2 = 65.0 - (day_offset * 5) + random.uniform(-2, 2)
                st_2 = 24.0 + (4.0 * temp_factor) + random.uniform(-0.5, 0.5)
                ph_2 = 6.5 + random.uniform(-0.05, 0.05)
                
                # GH 3 Data
                t_root_3 = 20.0 + (2.0 * temp_factor) + random.uniform(-0.5, 0.5)
                prs_3 = 60.0 + random.uniform(-5, 5)

                # Raw Sensor Logs (1 per hour to keep it lightweight but present)
                raw_logs = [
                    SensorLog(sensor_id='DHT-GH1-01', date=time_point.date(), time=time_point.time(), reading=t_air_1, status="Normal"),
                    SensorLog(sensor_id='DHT-GH1-02', date=time_point.date(), time=time_point.time(), reading=h_air_1, status="Normal"),
                    SensorLog(sensor_id='TDS-GH1-01', date=time_point.date(), time=time_point.time(), reading=tds_1, status="Normal"),
                    SensorLog(sensor_id='PH-GH1-01', date=time_point.date(), time=time_point.time(), reading=ph_1, status="Normal"),
                    SensorLog(sensor_id='WL-GH1-01', date=time_point.date(), time=time_point.time(), reading=wl_1, status="Normal"),
                    
                    SensorLog(sensor_id='MST-GH2-01', date=time_point.date(), time=time_point.time(), reading=sm_2, status="Normal"),
                    SensorLog(sensor_id='MST-GH2-02', date=time_point.date(), time=time_point.time(), reading=sm_2+2, status="Normal"),
                    SensorLog(sensor_id='PH-GH2-01', date=time_point.date(), time=time_point.time(), reading=ph_2, status="Normal"),
                    
                    SensorLog(sensor_id='TMP-GH3-01', date=time_point.date(), time=time_point.time(), reading=t_root_3, status="Normal"),
                    SensorLog(sensor_id='PRS-GH3-01', date=time_point.date(), time=time_point.time(), reading=prs_3, status="Normal"),
                ]
                db.add_all(raw_logs)

                # Aggregations
                aggs = [
                    AreaAggregation(area_id=1, data_type='Suhu Udara', min_value=t_air_1-1, max_value=t_air_1+1, avg_value=t_air_1, date=time_point.date(), time=time_point.time()),
                    AreaAggregation(area_id=1, data_type='Kelembaban Udara', min_value=h_air_1-2, max_value=h_air_1+2, avg_value=h_air_1, date=time_point.date(), time=time_point.time()),
                    AreaAggregation(area_id=1, data_type='TDS Nutrisi', min_value=tds_1-10, max_value=tds_1+10, avg_value=tds_1, date=time_point.date(), time=time_point.time()),
                    AreaAggregation(area_id=1, data_type='pH Air', min_value=ph_1-0.2, max_value=ph_1+0.2, avg_value=ph_1, date=time_point.date(), time=time_point.time()),
                    AreaAggregation(area_id=1, data_type='Level Air', min_value=wl_1-1, max_value=wl_1+1, avg_value=wl_1, date=time_point.date(), time=time_point.time()),
                    
                    AreaAggregation(area_id=2, data_type='Kelembaban Tanah', min_value=sm_2-3, max_value=sm_2+3, avg_value=sm_2, date=time_point.date(), time=time_point.time()),
                    AreaAggregation(area_id=2, data_type='Suhu Tanah', min_value=st_2-1, max_value=st_2+1, avg_value=st_2, date=time_point.date(), time=time_point.time()),
                    AreaAggregation(area_id=2, data_type='pH Tanah', min_value=ph_2-0.1, max_value=ph_2+0.1, avg_value=ph_2, date=time_point.date(), time=time_point.time()),
                    
                    AreaAggregation(area_id=3, data_type='Suhu Akar', min_value=t_root_3-0.5, max_value=t_root_3+0.5, avg_value=t_root_3, date=time_point.date(), time=time_point.time()),
                    AreaAggregation(area_id=3, data_type='Tekanan', min_value=prs_3-2, max_value=prs_3+2, avg_value=prs_3, date=time_point.date(), time=time_point.time()),
                ]
                db.add_all(aggs)
                
        db.commit()

        # 5. Water Usage Logs (Simulate scattered watering events)
        water_logs = []
        for i in range(15):
            days_ago = random.uniform(0, 3)
            time_point = now - timedelta(days=days_ago)
            water_logs.append(WaterUsageLog(
                actuator_id='PMP-GH2-01', 
                timestamp=time_point, 
                water_discharged=random.uniform(5.0, 20.0), 
                water_remaining=random.uniform(200.0, 450.0)
            ))
            water_logs.append(WaterUsageLog(
                actuator_id='PMP-GH3-01', 
                timestamp=time_point + timedelta(hours=1), 
                water_discharged=random.uniform(1.0, 3.0), 
                water_remaining=random.uniform(100.0, 150.0)
            ))
        db.add_all(water_logs)
        db.commit()

        # 6. Alerts (Mix of resolved and unresolved)
        alerts_data = [
            Alert(sensor_id='MST-GH2-01', alert_type='Critical', message='Kelembaban Tanah turun drastis di bawah 40%. Irigasi darurat diperlukan.', value=35.5, threshold_exceeded=4.5, is_read=False, created_at=now - timedelta(hours=1)),
            Alert(sensor_id='TMP-GH2-01', alert_type='Warning', message='Sensor Suhu Tanah terputus (Koneksi Timeout > 5 menit)', value=0.0, is_read=False, created_at=now - timedelta(hours=3)),
            Alert(sensor_id='PH-GH1-01', alert_type='Warning', message='pH Air naik mencapai 6.8', value=6.8, threshold_exceeded=0.3, is_read=True, created_at=now - timedelta(days=1), resolved_at=now - timedelta(hours=20)),
            Alert(sensor_id='TDS-GH1-01', alert_type='Critical', message='Nutrisi TDS turun di bawah 800 ppm. Pertumbuhan terancam.', value=750.0, threshold_exceeded=50.0, is_read=True, created_at=now - timedelta(days=2), resolved_at=now - timedelta(days=1, hours=10)),
            Alert(sensor_id='PRS-GH3-01', alert_type='Warning', message='Tekanan pompa kabut tidak stabil, indikasi filter tersumbat.', value=38.0, threshold_exceeded=2.0, is_read=False, created_at=now - timedelta(minutes=45)),
        ]
        db.add_all(alerts_data)
        db.commit()

        # 7. Data Requests (Rich details)
        reqs_data = [
            DataRequest(
                tracking_code='REQ-2026-001', full_name='Fahri Ramadhan', 
                email='fahri.r@apps.ipb.ac.id', nim_nip='G1401211045', 
                reason='Implementasi algoritma LSTM untuk prediksi mikroklimat rumah kaca hidroponik.', 
                document_path='/uploads/requests/doc_fahri.pdf', data_type='monitoring',
                requested_sensors=["Suhu Udara", "Kelembaban Udara", "Level Air"],
                date_start=(now - timedelta(days=30)).date(),
                date_end=now.date(),
                status='pending',
                created_at=now - timedelta(days=2)
            ),
            DataRequest(
                tracking_code='REQ-2026-002', full_name='Dr. Anisa Larasati', 
                email='anisa.l@ui.ac.id', nim_nip='198507222010122001', 
                reason='Penelitian komparasi nilai pH tanah dan nutrisi terhadap pertumbuhan varietas paprika (Capsicum annuum).', 
                document_path='/uploads/requests/surat_tugas_anisa.pdf', data_type='analytics',
                requested_sensors=["pH Tanah", "Suhu Tanah", "Kelembaban Tanah"],
                date_start=(now - timedelta(days=60)).date(),
                date_end=(now - timedelta(days=5)).date(),
                status='approved',
                admin_notes='Sesuai dengan MoU antara lab',
                download_token='token_abcd_1234',
                created_at=now - timedelta(days=5),
                reviewed_at=now - timedelta(days=4)
            ),
            DataRequest(
                tracking_code='REQ-2026-003', full_name='Budi Santoso', 
                email='budi.santoso@startup-agri.co.id', nim_nip='ID-EMP-203', 
                reason='Pengembangan dashboard IoT komersial', 
                document_path='/uploads/requests/proposal_budi.pdf', data_type='monitoring',
                requested_sensors=["Suhu Udara", "Kelembaban Udara"],
                date_start=(now - timedelta(days=10)).date(),
                date_end=now.date(),
                status='rejected',
                admin_notes='Tujuan komersial tidak diizinkan tanpa izin khusus Dekanat',
                created_at=now - timedelta(days=1)
            )
        ]
        db.add_all(reqs_data)
        db.commit()

        print("Data dummy V2 (Rich Dataset) berhasil disuntikkan secara lengkap!")
        
    except Exception as e:
        db.rollback()
        print(f"Error seeding data: {e}")
    finally:
        db.close()

if __name__ == "__main__":
    seed_data()
