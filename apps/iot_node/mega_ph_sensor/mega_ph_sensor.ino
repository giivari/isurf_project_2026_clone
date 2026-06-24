// iSURF IoT Node - Arduino Mega 2560 + ESP8266 + pH Sensor
// Hardware: Arduino Mega 2560
// Sensor: pH Sensor Analog (A0)
// Internet: ESP8266-01 (Serial1: RX1=19, TX1=18)

#define PH_PIN A0
#define ESP_SERIAL Serial1 // Arduino Mega Hardware Serial 1 (Pin 18 & 19)

// Konfigurasi Jaringan & API (SESUAIKAN DENGAN MILIK ANDA)
const char* WIFI_SSID = "NAMA_WIFI_HOTSPOT_ANDA";
const char* WIFI_PASS = "PASSWORD_WIFI_ANDA";
const char* API_HOST = "isurf.digdaya.net";
const int API_PORT = 443; // Gunakan 80 jika ESP8266 tidak mendukung SSL/HTTPS
const char* API_KEY = "supersecure";
const char* SENSOR_ID = "tes_123"; // ID Sensor yang didaftarkan di Web

// Kalibrasi pH
float calibration_value = 21.34 - 0.7; // Sesuaikan dengan hasil kalibrasi Anda
unsigned long last_send_time = 0;
const unsigned long SEND_INTERVAL = 60000; // Kirim data setiap 60 detik (60000 ms)

void setup() {
  Serial.begin(9600);
  ESP_SERIAL.begin(115200); // Default baudrate ESP8266 biasanya 115200
  
  Serial.println("=== iSURF IoT Node (Mega 2560) ===");
  Serial.println("Inisialisasi ESP8266...");
  
  // Reset ESP8266
  sendCommand("AT+RST", 2000, "ready");
  
  // Set ESP8266 sebagai Station Mode (Client)
  sendCommand("AT+CWMODE=1", 1000, "OK");
  
  // Hubungkan ke WiFi
  Serial.print("Menghubungkan ke WiFi: ");
  Serial.println(WIFI_SSID);
  String wifiCmd = "AT+CWJAP=\"" + String(WIFI_SSID) + "\",\"" + String(WIFI_PASS) + "\"";
  sendCommand(wifiCmd, 8000, "OK");
  
  Serial.println("Sistem Siap!");
}

void loop() {
  unsigned long current_time = millis();
  
  // Eksekusi pengiriman setiap SEND_INTERVAL
  if (current_time - last_send_time >= SEND_INTERVAL || last_send_time == 0) {
    last_send_time = current_time;
    
    // 1. Baca nilai Sensor pH
    int sensorValue = analogRead(PH_PIN); 
    float voltage = sensorValue * (5.0 / 1023.0);
    float phValue = 3.5 * voltage + calibration_value; // Rumus standar pH meter
    
    // Batasi pH antara 0 - 14
    if (phValue < 0.0) phValue = 0.0;
    if (phValue > 14.0) phValue = 14.0;
    
    Serial.print("Nilai Analog: "); Serial.print(sensorValue);
    Serial.print(" | Tegangan: "); Serial.print(voltage);
    Serial.print("V | pH: "); Serial.println(phValue);
    
    // 2. Kirim ke Server Backend
    sendDataToServer(phValue);
  }
}

// Fungsi untuk mengirim data JSON ke Backend
void sendDataToServer(float phValue) {
  Serial.println("Membangun koneksi ke server...");
  
  // Buka koneksi TCP SSL ke server (Ganti "SSL" menjadi "TCP" dan port 80 jika gagal)
  String connCmd = "AT+CIPSTART=\"SSL\",\"" + String(API_HOST) + "\"," + String(API_PORT);
  if (!sendCommand(connCmd, 5000, "OK")) {
    Serial.println("Gagal terhubung ke server!");
    return;
  }
  
  // Buat Payload JSON
  String jsonPayload = "{\"sensors\":[{\"sensor_id\":\"" + String(SENSOR_ID) + "\",\"value\":" + String(phValue) + "}]}";
  
  // Buat HTTP Request Header
  String httpRequest = "POST /isurf/v1/api/iot/ingest HTTP/1.1\r\n";
  httpRequest += "Host: " + String(API_HOST) + "\r\n";
  httpRequest += "Content-Type: application/json\r\n";
  httpRequest += "X-API-Key: " + String(API_KEY) + "\r\n";
  httpRequest += "Content-Length: " + String(jsonPayload.length()) + "\r\n";
  httpRequest += "Connection: close\r\n\r\n";
  httpRequest += jsonPayload;
  
  // Kirim perintah panjang data
  String sendCmd = "AT+CIPSEND=" + String(httpRequest.length());
  if (sendCommand(sendCmd, 2000, ">")) {
    Serial.println("Mengirim Data HTTP...");
    ESP_SERIAL.print(httpRequest);
    
    // Baca respon dari server
    long int time = millis();
    while((time+5000) > millis()) {
      while(ESP_SERIAL.available()) {
        char c = ESP_SERIAL.read();
        Serial.print(c);
      }
    }
    Serial.println("\n--- Selesai ---");
  } else {
    Serial.println("Gagal memulai pengiriman data (CIPSEND)");
  }
  
  // Tutup koneksi
  sendCommand("AT+CIPCLOSE", 1000, "OK");
}

// Fungsi pembantu untuk mengirim AT Command dan menunggu respon
bool sendCommand(String command, const int timeout, String expected_response) {
  ESP_SERIAL.println(command);
  long int time = millis();
  String response = "";
  
  while((time+timeout) > millis()) {
    while(ESP_SERIAL.available()) {
      char c = ESP_SERIAL.read();
      response += c;
    }
    if(response.indexOf(expected_response) != -1) {
      // Ditemukan respon yang diharapkan
      return true;
    }
  }
  // Cetak respon jika gagal untuk debugging
  if (response.length() > 0) {
    Serial.print("Respon ESP: ");
    Serial.println(response);
  }
  return false;
}
