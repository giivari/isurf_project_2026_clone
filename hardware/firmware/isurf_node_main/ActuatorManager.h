#ifndef ACTUATOR_MANAGER_H
#define ACTUATOR_MANAGER_H

#include <Arduino.h>

class ActuatorManager {
private:
    bool pumpState;
    bool fanState;

    // Thresholds synced from API
    float tdsMin;
    float tdsMax;
    float phMin;
    float phMax;

public:
    ActuatorManager();
    void begin();
    
    // Updates local thresholds when synced from backend
    void updateThresholds(float tMin, float tMax, float pMin, float pMax);
    
    // Evaluates current sensor readings against thresholds
    void evaluate(float currentTds, float currentPh, float currentTemp);
    
    bool isPumpOn();
    bool isFanOn();
    
    // Explicit setters for network commands
    void setPumpState(bool state);
    void setFanState(bool state);
};

#endif // ACTUATOR_MANAGER_H
