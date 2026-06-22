#ifndef CONFIG_H
#define CONFIG_H

// ==========================================
// PIN DEFINITIONS
// ==========================================

// Sensors
#define PIN_TDS_SENSOR      A0    // DFRobot TDS Sensor
#define PIN_PH_SENSOR       A1    // Analog Soil pH Sensor

// Actuators
#define PIN_RELAY_PUMP      8     // Relay Channel 1 (Water Pump/Valve)
#define PIN_RELAY_FAN       9     // Relay Channel 2 (Mini Fan Brushless)

// SD Card (SPI)
// MOSI = 51, MISO = 50, SCK = 52 on Arduino Mega
#define PIN_SD_CS           53    // Chip Select for MicroSD

// OLED Display (I2C)
// SDA = 20, SCL = 21 on Arduino Mega
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64

// ==========================================
// SYSTEM SETTINGS
// ==========================================

// ESP8266 Serial Communication (Mega Built-in usually uses Serial3 for ESP8266)
#define ESP_SERIAL Serial3
#define ESP_BAUDRATE 115200

// ==========================================
// CONNECTION MODE SELECTION
// ==========================================
#define NET_MODE_HTTP 0
#define NET_MODE_MQTT 1

// PILIH MODE KONEKSI DI SINI:
#define CONNECTION_MODE NET_MODE_MQTT

// ==========================================
// Network & API Configuration (UMUM)
// ==========================================
#define WIFI_SSID       "NAMA_WIFI_ANDA"
#define WIFI_PASSWORD   "PASSWORD_WIFI_ANDA"
#define DEVICE_CODE     "ESP32_MAIN_01"      // GANTI DENGAN KODE DEVICE DI DATABASE

// --- PENGATURAN MODE HTTP LOKAL ---
#define SERVER_IP       "192.168.1.100"      // GANTI DENGAN IP KOMPUTER SERVER (Tidak bisa pakai localhost)
#define SERVER_PORT     "8000"
#define API_KEY         "supersecure"

// --- PENGATURAN MODE MQTT ---
#define MQTT_BROKER     "broker.hivemq.com" // Disarankan pakai broker public non-ssl (1883) untuk stabilitas ESP AT
#define MQTT_PORT       1883                // Jika pakai HiveMQ Cloud (MQTTS), ganti ke 8883
#define MQTT_USER       "isurf"             // Isi jika MQTT broker butuh username
#define MQTT_PASS       "..."      // Isi jika MQTT broker butuh password
#define MQTT_TOPIC_PUB  "isurf/device/sensor"
#define MQTT_TOPIC_SUB  "isurf/device/control"

// Timing Intervals
#define SENSOR_READ_INTERVAL 10000UL      // 10 detik
#define DATA_SEND_INTERVAL   300000UL     // 5 menit
#define CONFIG_SYNC_INTERVAL 3600000UL    // 1 jam

#endif // CONFIG_H
