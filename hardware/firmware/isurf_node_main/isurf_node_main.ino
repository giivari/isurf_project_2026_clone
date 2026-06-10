#include "config.h"
#include "SensorManager.h"
#include "ActuatorManager.h"
#include "NetworkManager.h"
#include "SDManager.h"

// Global Manager Instances
SensorManager   sensorManager;
ActuatorManager actuatorManager;
NetworkManager  networkManager;
SDManager       sdManager;

// Timing variables
unsigned long lastSensorRead = 0;
unsigned long lastDataSend   = 0;
unsigned long lastStateSync  = 0;
const unsigned long STATE_SYNC_INTERVAL = 30000; // Poll server every 30s for pump state

void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("\n--- iSURF Node Starting ---");

  // Initialize Managers
  sensorManager.begin();
  actuatorManager.begin();
  sdManager.begin();
  networkManager.begin();

  // Initial Sync from Server
  Serial.println("Initial Config Sync...");
  networkManager.syncConfig(&actuatorManager);
  
  Serial.println("--- System Ready ---");
}

void loop() {
  unsigned long currentMillis = millis();

  // 1. SENSOR READING & ACTUATOR CONTROL
  if (currentMillis - lastSensorRead >= SENSOR_READ_INTERVAL) {
    lastSensorRead = currentMillis;
    
    // Read sensors
    sensorManager.readAllSensors();
    
    // Evaluate thresholds (Fallbacks in case server connection is lost)
    // actuatorManager.evaluate() is overridden by Network state if connected.
    // For this implementation, we will keep evaluate() for Fan control 
    // and let syncActuatorState() handle the Pump.
    actuatorManager.evaluate(
      sensorManager.getTdsValue(),
      sensorManager.getPhValue(),
      sensorManager.getTemperature()
    );

    // Update OLED Display
    sensorManager.updateDisplay(
      networkManager.isConnected(), 
      actuatorManager.isPumpOn(), 
      actuatorManager.isFanOn()
    );
  }

  // 2. DATA SENDING TO BACKEND
  if (currentMillis - lastDataSend >= DATA_SEND_INTERVAL) {
    lastDataSend = currentMillis;
    
    String payload = sensorManager.buildJsonPayload();
    bool success = networkManager.sendData(payload);
    
    // Fallback to SD Card if network fails
    if (!success) {
      sdManager.logData(sensorManager.getTdsValue(), sensorManager.getPhValue(), sensorManager.getTemperature());
    }
  }

  // 3. PERIODIC CONFIG SYNC
  if (currentMillis - lastConfigSync >= CONFIG_SYNC_INTERVAL) {
    lastConfigSync = currentMillis;
    networkManager.syncConfig(&actuatorManager);
  }
  
  // 4. PERIODIC ACTUATOR STATE SYNC (Fast Polling)
  if (currentMillis - lastStateSync >= STATE_SYNC_INTERVAL) {
    lastStateSync = currentMillis;
    networkManager.syncActuatorState(&actuatorManager);
  }
}
