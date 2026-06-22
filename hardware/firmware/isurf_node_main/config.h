#ifndef CONFIG_H
#define CONFIG_H

// ==========================================
// PIN DEFINITIONS
// ==========================================

// Sensors
#define PIN_DHT             2     // DHT22 (Suhu & Kelembaban Udara)
#define PIN_SOIL_MOISTURE   A0    // Analog Capacitive Soil Moisture Sensor
#define PIN_SOIL_TEMP       A1    // Analog Soil Temperature Sensor (PT100/Thermistor)
#define PIN_PH_SENSOR       A2    // Analog Soil pH Sensor

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

// Network & API Configuration
#define WIFI_SSID       "NAMA_WIFI_ANDA"
#define WIFI_PASSWORD   "PASSWORD_WIFI_ANDA"
#define SERVER_IP       "192.168.1.100"      // GANTI DENGAN IP KOMPUTER SERVER (Tidak bisa pakai localhost)
#define SERVER_PORT     "8000"
#define DEVICE_CODE     "ESP32_MAIN_01"      // GANTI DENGAN KODE DEVICE DI DATABASE
#define API_KEY         "supersecure"

// Timing Intervals
#define SENSOR_READ_INTERVAL 10000UL      // 10 detik
#define DATA_SEND_INTERVAL   300000UL     // 5 menit
#define CONFIG_SYNC_INTERVAL 3600000UL    // 1 jam

#endif // CONFIG_H
