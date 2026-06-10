#ifndef NETWORK_MANAGER_H
#define NETWORK_MANAGER_H

#include <Arduino.h>
#include "ActuatorManager.h"

class NetworkManager {
private:
    bool connected;
    String readEspResponse(uint32_t timeout = 2000);
    bool sendCommand(String cmd, String expectedResponse, uint32_t timeout = 2000);

public:
    NetworkManager();
    void begin();
    
    bool isConnected();
    bool connectWiFi();
    
    // HTTP API Calls
    bool sendData(String jsonPayload);
    bool syncConfig(ActuatorManager* actuatorManager);
    bool syncActuatorState(ActuatorManager* actuatorManager);
};

#endif // NETWORK_MANAGER_H
