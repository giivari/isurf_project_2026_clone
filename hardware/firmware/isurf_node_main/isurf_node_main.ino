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
unsigned long lastConfigSync = 0; // Fixed undeclared variable
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

  // 1. ASYNC MESSAGE CHECK (KHUSUS MQTT)
  // Membaca pesan asinkron yang masuk kapan saja dari broker
  networkManager.checkMessages(&actuatorManager);

  // 2. SENSOR READING & ACTUATOR CONTROL
  if (currentMillis - lastSensorRead >= SENSOR_READ_INTERVAL) {
    lastSensorRead = currentMillis;
    
    // Read sensors
    sensorManager.readAllSensors();
    
    // Evaluate thresholds
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

  // 3. DATA SENDING TO BACKEND
  if (currentMillis - lastDataSend >= DATA_SEND_INTERVAL) {
    lastDataSend = currentMillis;
    
    String payload = sensorManager.buildJsonPayload();
    bool success = networkManager.sendData(payload);
    
    // Fallback to SD Card if network fails
    if (!success) {
      sdManager.logData(sensorManager.getTdsValue(), sensorManager.getPhValue(), sensorManager.getTemperature());
    }
  }

  // 4. PERIODIC CONFIG SYNC (Akan diabaikan otomatis jika mode MQTT)
  if (currentMillis - lastConfigSync >= CONFIG_SYNC_INTERVAL) {
    lastConfigSync = currentMillis;
    networkManager.syncConfig(&actuatorManager);
  }
  
  // 5. PERIODIC ACTUATOR STATE SYNC (Akan diabaikan otomatis jika mode MQTT)
  if (currentMillis - lastStateSync >= STATE_SYNC_INTERVAL) {
    lastStateSync = currentMillis;
    networkManager.syncActuatorState(&actuatorManager);
  }
}
