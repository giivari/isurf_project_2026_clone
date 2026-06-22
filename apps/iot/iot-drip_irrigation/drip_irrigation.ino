#include <Arduino.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>

// --- KONFIGURASI WIFI & API ---
const char* ssid = "NAMA_WIFI_ANDA";
const char* password = "PASSWORD_WIFI_ANDA";
const char* apiUrl = "http://192.168.1.xxx:8000/iot/ingest"; // Ganti dengan IP komputer/server backend
const char* apiKey = "supersecure";

// --- KONFIGURASI OLED ---
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET -1
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

// --- PIN MAPPING (Khusus ESP32) ---
// Note: ESP32 ADC pins recommended are 32-39
const uint8_t TDS_PIN = 32;
const uint8_t SOIL_PIN = 33;
const uint8_t PH_PIN = 34;
const uint8_t US_TRIG_PIN = 5;
const uint8_t US_ECHO_PIN = 18;
const uint8_t RELAY_PIN = 19;

// --- CLASS SENSOR & AKTUATOR DARI SKEMA_ALAT ---

class AnalogTDSSensor {
  private:
    uint8_t pin;
    float referenceVoltage;
    int adcResolution;

    float readVoltage() {
        int rawValue = analogRead(pin);
        // ESP32 ADC max is 4095
        return (rawValue / (float)adcResolution) * referenceVoltage;
    }

    float calculateTDS(float voltage, float temperature) {
        float compensationCoefficient = 1.0 + 0.02 * (temperature - 25.0);
        float compensationVoltage = voltage / compensationCoefficient;
        float tdsValue = (133.42 * pow(compensationVoltage, 3) 
                          - 255.86 * pow(compensationVoltage, 2) 
                          + 857.39 * compensationVoltage) * 0.5;
        if (tdsValue < 0) tdsValue = 0;
        return tdsValue;
    }

  public:
    AnalogTDSSensor(uint8_t analogPin, float refVolt = 3.3, int resolution = 4095) {
        pin = analogPin;
        referenceVoltage = refVolt;
        adcResolution = resolution;
    }

    void begin() {
        pinMode(pin, INPUT);
    }

    float getTDS(float currentTemperature = 25.0) {
        float voltage = readVoltage();
        return calculateTDS(voltage, currentTemperature);
    }
};

class SoilMoistureSensor {
  private:
    uint8_t analogPin;
    int dryCalibrationValue;
    int wetCalibrationValue;

  public:
    SoilMoistureSensor(uint8_t aPin, int dryValue = 4095, int wetValue = 1000) {
        analogPin = aPin;
        dryCalibrationValue = dryValue;
        wetCalibrationValue = wetValue;
    }

    void begin() {
        pinMode(analogPin, INPUT);
    }

    float getMoisturePercentage() {
        int rawValue = analogRead(analogPin);
        if (dryCalibrationValue > wetCalibrationValue) {
            if (rawValue > dryCalibrationValue) rawValue = dryCalibrationValue;
            if (rawValue < wetCalibrationValue) rawValue = wetCalibrationValue;
        } else {
            if (rawValue < dryCalibrationValue) rawValue = dryCalibrationValue;
            if (rawValue > wetCalibrationValue) rawValue = wetCalibrationValue;
        }
        return map(rawValue, dryCalibrationValue, wetCalibrationValue, 0, 100);
    }
};

class UltrasonicSensor {
  private:
    uint8_t triggerPin;
    uint8_t echoPin;
    unsigned long timeoutDuration;

  public:
    UltrasonicSensor(uint8_t trig, uint8_t echo, unsigned long timeout = 30000) {
        triggerPin = trig;
        echoPin = echo;
        timeoutDuration = timeout;
    }

    void begin() {
        pinMode(triggerPin, OUTPUT);
        pinMode(echoPin, INPUT);
        digitalWrite(triggerPin, LOW);
    }

    float getDistance() {
        digitalWrite(triggerPin, LOW);
        delayMicroseconds(2);
        digitalWrite(triggerPin, HIGH);
        delayMicroseconds(10);
        digitalWrite(triggerPin, LOW);
        long duration = pulseIn(echoPin, HIGH, timeoutDuration);
        if (duration == 0) return -1.0; 
        return (duration * 0.0343) / 2.0;
    }
};

class MotorizedValve {
  private:
    uint8_t relayPin;
    bool isValveOpen;

  public:
    MotorizedValve(uint8_t pin) {
        relayPin = pin;
        isValveOpen = false;
    }

    void begin() {
        pinMode(relayPin, OUTPUT);
        digitalWrite(relayPin, HIGH); // Assuming HIGH is off for relay
    }

    void open() {
        digitalWrite(relayPin, LOW); 
        isValveOpen = true;
    }

    void close() {
        digitalWrite(relayPin, HIGH); 
        isValveOpen = false;
    }
};

// --- INSTANSIASI OBJECT ---
AnalogTDSSensor tdsSensor(TDS_PIN, 3.3, 4095);
SoilMoistureSensor moistureSensor(SOIL_PIN, 3500, 1000); // Kalibrasi ESP32 ADC
UltrasonicSensor usSensor(US_TRIG_PIN, US_ECHO_PIN);
MotorizedValve mainPump(RELAY_PIN);

unsigned long prevMillisFast = 0;
unsigned long prevMillisMedium = 0;
unsigned long prevMillisSlow = 0;

const unsigned long INTERVAL_FAST = 60000;   // 1 Menit: Volume Air (Level Air)
const unsigned long INTERVAL_MEDIUM = 300000; // 5 Menit: TDS & pH
const unsigned long INTERVAL_SLOW = 600000;  // 10 Menit: Kelembaban Tanah

float readPH() {
    int sensorValue = analogRead(PH_PIN);
    // Contoh kalibrasi dummy, sesuaikan dengan sensor sebenarnya
    float voltage = sensorValue * (3.3 / 4095.0);
    return 3.5 * voltage; // Dummy formula
}

void setup() {
    Serial.begin(115200);

    tdsSensor.begin();
    moistureSensor.begin();
    usSensor.begin();
    mainPump.begin();

    if(!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
        Serial.println(F("SSD1306 allocation failed"));
    } else {
        display.clearDisplay();
        display.setTextColor(WHITE);
        display.setCursor(0,0);
        display.println("Booting iSURF...");
        display.display();
    }

    // Connect WiFi
    WiFi.begin(ssid, password);
    Serial.print("Connecting to WiFi");
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nWiFi Connected!");
}

void loop() {
    unsigned long currentMillis = millis();

    // Baca sensor
    float tds = tdsSensor.getTDS(25.0);
    float moisture = moistureSensor.getMoisturePercentage();
    float distance = usSensor.getDistance();
    float ph = readPH();
    
    // Perkiraan volume berdasarkan jarak (contoh dummy)
    float volumeAir = (distance > 0) ? (100 - distance) : 0; 

    // Update OLED
    display.clearDisplay();
    display.setCursor(0, 0);
    display.printf("WiFi: %s\n", (WiFi.status() == WL_CONNECTED) ? "OK" : "ERR");
    display.printf("TDS: %.1f ppm\n", tds);
    display.printf("Soil: %.1f %%\n", moisture);
    display.printf("pH: %.1f\n", ph);
    display.display();

    // Logika Failsafe lokal (opsional, karena API juga akan menghandle)
    if (moisture < 30) {
        mainPump.open();
    } else {
        mainPump.close();
    }

    // Kirim ke API dengan interval dinamis per sensor
    bool sendData = false;
    String jsonPayload = "{\"sensors\":[";
    bool isFirstItem = true;

    // 1. Fast Interval (Volume Air)
    if (currentMillis - prevMillisFast >= INTERVAL_FAST || prevMillisFast == 0) {
        prevMillisFast = currentMillis;
        sendData = true;
        if (!isFirstItem) jsonPayload += ",";
        jsonPayload += "{\"sensor_id\":\"VOL-GH1-01\",\"value\":" + String(volumeAir, 2) + "}";
        isFirstItem = false;
    }

    // 2. Medium Interval (TDS & pH)
    if (currentMillis - prevMillisMedium >= INTERVAL_MEDIUM || prevMillisMedium == 0) {
        prevMillisMedium = currentMillis;
        sendData = true;
        if (!isFirstItem) jsonPayload += ",";
        jsonPayload += "{\"sensor_id\":\"TDS-GH1-01\",\"value\":" + String(tds, 2) + "}";
        jsonPayload += ",{\"sensor_id\":\"PH-GH1-01\",\"value\":" + String(ph, 2) + "}";
        isFirstItem = false;
    }

    // 3. Slow Interval (Kelembaban Tanah)
    if (currentMillis - prevMillisSlow >= INTERVAL_SLOW || prevMillisSlow == 0) {
        prevMillisSlow = currentMillis;
        sendData = true;
        if (!isFirstItem) jsonPayload += ",";
        jsonPayload += "{\"sensor_id\":\"MST-GH2-01\",\"value\":" + String(moisture, 2) + "}";
        isFirstItem = false;
    }

    jsonPayload += "]}";

    // Jika ada data yang harus dikirim di siklus loop ini
    if (sendData) {
        if (WiFi.status() == WL_CONNECTED) {
            HTTPClient http;
            http.begin(apiUrl);
            http.addHeader("Content-Type", "application/json");
            http.addHeader("X-API-Key", apiKey);

            int httpResponseCode = http.POST(jsonPayload);
            
            if (httpResponseCode > 0) {
                Serial.printf("HTTP Response code: %d\n", httpResponseCode);
                String response = http.getString();
                Serial.println(response);
            } else {
                Serial.printf("Error code: %d\n", httpResponseCode);
            }
            http.end();
        } else {
            Serial.println("WiFi Disconnected, data tidak terkirim");
        }
    }
}