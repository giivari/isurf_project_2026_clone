#ifndef SENSOR_MANAGER_H
#define SENSOR_MANAGER_H

#include <Arduino.h>

class SensorManager {
private:
    float airTemp;
    float airHumidity;
    float soilTemp;
    float soilMoisture;
    float soilPh;

    // Raw buffers
    int phAnalogRaw;
    int soilTempRaw;
    int soilMoistRaw;

public:
    SensorManager();
    void begin();
    
    void readAllSensors();
    float getAirTemp();
    float getAirHumidity();
    float getSoilTemp();
    float getSoilMoisture();
    float getSoilPh();
    
    void updateDisplay(bool wifiConnected, bool pumpOn, bool fanOn);
    String buildJsonPayload();
};

#endif // SENSOR_MANAGER_H
