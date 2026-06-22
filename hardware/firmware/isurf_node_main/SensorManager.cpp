#include "SensorManager.h"
#include "config.h"

// If OLED libraries are available:
// #include <Wire.h>
// #include <Adafruit_GFX.h>
// #include <Adafruit_SSD1306.h>
// Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, -1);

SensorManager::SensorManager() {
    airTemp = 28.0;
    airHumidity = 65.0;
    soilTemp = 26.0;
    soilMoisture = 80.0;
    soilPh = 6.5;
}

void SensorManager::begin() {
    pinMode(PIN_PH_SENSOR, INPUT);
    pinMode(PIN_SOIL_MOISTURE, INPUT);
    pinMode(PIN_SOIL_TEMP, INPUT);

    // Initialize OLED if used
    // if(!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    //   Serial.println(F("SSD1306 allocation failed"));
    // }
    // display.clearDisplay();
    // display.setTextColor(WHITE);
}

void SensorManager::readAllSensors() {
    // 1. Read Air Temp & Humidity (Simulated DHT22)
    // Normally: dht.readTemperature(), dht.readHumidity()
    airTemp = 25.0 + random(-20, 20) / 10.0;     // 23.0 - 27.0
    airHumidity = 60.0 + random(-50, 50) / 10.0; // 55.0 - 65.0

    // 2. Read Soil Moisture
    soilMoistRaw = analogRead(PIN_SOIL_MOISTURE);
    // Convert 0-1023 to 0-100%
    soilMoisture = map(soilMoistRaw, 1023, 0, 0, 100); 

    // 3. Read Soil Temp
    soilTempRaw = analogRead(PIN_SOIL_TEMP);
    // Simulated conversion
    soilTemp = 24.0 + random(-10, 10) / 10.0;

    // 4. Read Soil pH
    phAnalogRaw = analogRead(PIN_PH_SENSOR);
    float phVoltage = phAnalogRaw * (5.0 / 1024.0);
    // Dummy conversion (need actual sensor calibration logic)
    soilPh = 3.5 * phVoltage; 
    
    // Constraint values
    if(soilPh < 0) soilPh = 0;
    if(soilPh > 14) soilPh = 14;
    if(soilMoisture < 0) soilMoisture = 0;
    if(soilMoisture > 100) soilMoisture = 100;

    Serial.print("AirT: "); Serial.print(airTemp); 
    Serial.print("C | AirH: "); Serial.print(airHumidity); 
    Serial.print("% | SoilT: "); Serial.print(soilTemp);
    Serial.print("C | SoilM: "); Serial.print(soilMoisture);
    Serial.print("% | pH: "); Serial.println(soilPh);
}

float SensorManager::getAirTemp() { return airTemp; }
float SensorManager::getAirHumidity() { return airHumidity; }
float SensorManager::getSoilTemp() { return soilTemp; }
float SensorManager::getSoilMoisture() { return soilMoisture; }
float SensorManager::getSoilPh() { return soilPh; }

void SensorManager::updateDisplay(bool wifiConnected, bool pumpOn, bool fanOn) {
    // Dummy display logic
    // display.clearDisplay();
    // display.setCursor(0,0);
    // display.print("iSURF: "); display.println(wifiConnected ? "WIFI OK" : "NO WIFI");
    // display.print("TDS: "); display.print(tdsValue); display.println(" ppm");
    // display.print("pH : "); display.println(phValue);
    // display.print("Pump:"); display.print(pumpOn ? "ON " : "OFF");
    // display.print(" Fan:"); display.println(fanOn ? "ON" : "OFF");
    // display.display();
}

String SensorManager::buildJsonPayload() {
    // Create JSON payload for FastAPI endpoint
    // HARUS SESUAI DENGAN SCHEMA DATABASE iSURF BARU
    String json = "{\"readings\": [";
    
    // Suhu Udara
    json += "{\"sensor_type\": \"Suhu Udara\", \"value\": " + String(airTemp) + "},";
    // Kelembaban Udara
    json += "{\"sensor_type\": \"Kelembaban Udara\", \"value\": " + String(airHumidity) + "},";
    // Suhu Tanah
    json += "{\"sensor_type\": \"Suhu Tanah\", \"value\": " + String(soilTemp) + "},";
    // Kelembaban Tanah
    json += "{\"sensor_type\": \"Kelembaban Tanah\", \"value\": " + String(soilMoisture) + "},";
    // pH Tanah
    json += "{\"sensor_type\": \"pH Tanah\", \"value\": " + String(soilPh) + "}";
    
    json += "]}";
    return json;
}
