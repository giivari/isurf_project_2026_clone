// iSURF IoT Node - ESP32 + pH Sensor
// Hardware: NodeMCU ESP32
// Sensor: pH Sensor Analog
// Pin Analog: VP (GPIO 36)
//
// Logika: Alat hanya membaca sensor & mengirim data mentah tiap 2 detik.
// Server yang mengurus rata-rata 1 menit dan deteksi anomali.

#include <WiFi.h>
#include <HTTPClient.h>

#define PH_PIN 36 // Pin VP (ADC1_CH0)

// Konfigurasi Jaringan & API (SESUAIKAN DENGAN MILIK ANDA)
const char* WIFI_SSID = "NAMA_WIFI_HOTSPOT_ANDA";
const char* WIFI_PASS = "PASSWORD_WIFI_ANDA";
const char* API_URL = "https://isurf.digdaya.net/isurf/v1/api/iot/ingest";
const char* API_KEY = "supersecure";
const char* SENSOR_ID = "tes_123"; // ID Sensor yang didaftarkan di Web

// Kalibrasi pH
float calibration_value = 21.34 - 0.7; // Sesuaikan dengan hasil kalibrasi Anda

// Interval pengiriman: setiap 2 detik
const unsigned long SEND_INTERVAL = 2000;
unsigned long lastSendTime = 0;

void setup() {
  Serial.begin(115200);
  delay(1000);
  
  Serial.println("\n=== iSURF IoT Node (ESP32) ===");
  Serial.println("Mode: Kirim data mentah tiap 2 detik ke server");
  
  // Hubungkan ke WiFi
  Serial.print("Menghubungkan ke WiFi: ");
  Serial.println(WIFI_SSID);
  
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi Terhubung!");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\nGagal terhubung ke WiFi! Cek nama & password.");
  }
  
  // Konfigurasi ADC ESP32 (Agar lebih akurat)
  analogReadResolution(12); // Resolusi 12-bit (0-4095)
  
  Serial.println("Sistem Siap!\n");
}

void loop() {
  unsigned long currentTime = millis();
  
  if (currentTime - lastSendTime >= SEND_INTERVAL || lastSendTime == 0) {
    lastSendTime = currentTime;
    
    // Baca nilai Sensor pH
    int sensorValue = analogRead(PH_PIN);
    float voltage = sensorValue * (3.3 / 4095.0);
    float phValue = 3.5 * voltage + calibration_value;
    
    // Batasi pH antara 0 - 14
    if (phValue < 0.0) phValue = 0.0;
    if (phValue > 14.0) phValue = 14.0;
    
    Serial.print("pH: ");
    Serial.print(phValue);
    
    // Kirim ke Server jika WiFi terhubung
    if (WiFi.status() == WL_CONNECTED) {
      sendDataToServer(phValue);
    } else {
      Serial.println(" | WiFi terputus, reconnecting...");
      WiFi.begin(WIFI_SSID, WIFI_PASS);
    }
  }
}

// Fungsi untuk mengirim data JSON ke Backend
void sendDataToServer(float phValue) {
  HTTPClient http;
  http.begin(API_URL);
  
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-API-Key", API_KEY);
  
  String jsonPayload = "{\"sensors\":[{\"sensor_id\":\"" + String(SENSOR_ID) + "\",\"value\":" + String(phValue) + "}]}";
  
  int httpResponseCode = http.POST(jsonPayload);
  
  if (httpResponseCode > 0) {
    Serial.print(" | Terkirim (HTTP ");
    Serial.print(httpResponseCode);
    Serial.println(")");
  } else {
    Serial.print(" | Gagal (Error ");
    Serial.print(httpResponseCode);
    Serial.println(")");
  }
  
  http.end();
}
