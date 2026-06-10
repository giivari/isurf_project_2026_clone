#include "ActuatorManager.h"
#include "config.h"

ActuatorManager::ActuatorManager() {
    pumpState = false;
    fanState = false;
    
    // Fallback default thresholds (will be overridden by API config)
    tdsMin = 0.0;
    tdsMax = 800.0;
    phMin = 5.5;
    phMax = 7.5;
}

void ActuatorManager::begin() {
    pinMode(PIN_RELAY_PUMP, OUTPUT);
    pinMode(PIN_RELAY_FAN, OUTPUT);
    
    // Relay modules are often active-low. 
    // Adjust logic depending on your specific relay module.
    digitalWrite(PIN_RELAY_PUMP, HIGH); // OFF
    digitalWrite(PIN_RELAY_FAN, HIGH);  // OFF
}

void ActuatorManager::updateThresholds(float tMin, float tMax, float pMin, float pMax) {
    if(tMin >= 0) tdsMin = tMin;
    if(tMax >= 0) tdsMax = tMax;
    if(pMin >= 0) phMin = pMin;
    if(pMax >= 0) phMax = pMax;
    
    Serial.println("Thresholds Updated Locally!");
}

void ActuatorManager::evaluate(float currentTds, float currentPh, float currentTemp) {
    // 1. Evaluate Water Pump Logic (e.g. if TDS is too high or pH is bad, maybe we trigger a flush valve)
    // For this example, let's say Pump is activated if TDS is below min (needs nutrient injection)
    bool needNutrients = (currentTds < tdsMin);
    
    if (needNutrients && !pumpState) {
        pumpState = true;
        digitalWrite(PIN_RELAY_PUMP, LOW); // ON
        Serial.println("Pump turned ON (TDS low)");
    } else if (!needNutrients && pumpState) {
        pumpState = false;
        digitalWrite(PIN_RELAY_PUMP, HIGH); // OFF
        Serial.println("Pump turned OFF");
    }
    
    // 2. Evaluate Fan Logic (based on temp or other factor)
    bool needCooling = (currentTemp > 30.0);
    
    if (needCooling && !fanState) {
        fanState = true;
        digitalWrite(PIN_RELAY_FAN, LOW); // ON
        Serial.println("Fan turned ON (High Temp)");
    } else if (!needCooling && fanState) {
        fanState = false;
        digitalWrite(PIN_RELAY_FAN, HIGH); // OFF
        Serial.println("Fan turned OFF");
    }
}

bool ActuatorManager::isPumpOn() { return pumpState; }
bool ActuatorManager::isFanOn() { return fanState; }

void ActuatorManager::setPumpState(bool state) {
    if (pumpState != state) {
        pumpState = state;
        digitalWrite(PIN_RELAY_PUMP, state ? LOW : HIGH); // Assuming active-low relay
    }
}

void ActuatorManager::setFanState(bool state) {
    if (fanState != state) {
        fanState = state;
        digitalWrite(PIN_RELAY_FAN, state ? LOW : HIGH); // Assuming active-low relay
    }
}
