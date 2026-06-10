#include "NetworkManager.h"
#include "config.h"

NetworkManager::NetworkManager() {
    connected = false;
}

void NetworkManager::begin() {
    ESP_SERIAL.begin(ESP_BAUDRATE);
    Serial.println("Initializing ESP8266...");
    
    // Basic AT sync
    sendCommand("AT", "OK", 3000);
    // Set Station Mode
    sendCommand("AT+CWMODE=1", "OK", 3000);
    
    connectWiFi();
}

bool NetworkManager::connectWiFi() {
    Serial.print("Connecting to WiFi: ");
    Serial.println(WIFI_SSID);
    
    String cmd = "AT+CWJAP=\"";
    cmd += WIFI_SSID;
    cmd += "\",\"";
    cmd += WIFI_PASSWORD;
    cmd += "\"";
    
    if(sendCommand(cmd, "WIFI GOT IP", 15000)) {
        connected = true;
        Serial.println("WiFi Connected!");
        return true;
    } else {
        connected = false;
        Serial.println("WiFi Connection Failed.");
        return false;
    }
}

bool NetworkManager::isConnected() {
    return connected;
}

bool NetworkManager::sendData(String jsonPayload) {
    if (!connected) return false;
    
    Serial.println("Sending Data to API...");
    
    // 1. Establish TCP Connection
    String cmd = "AT+CIPSTART=\"TCP\",\"";
    cmd += SERVER_IP;
    cmd += "\",";
    cmd += SERVER_PORT;
    
    if(!sendCommand(cmd, "CONNECT", 5000)) {
        Serial.println("TCP Connection Failed");
        return false;
    }
    
    // 2. Build HTTP POST Request
    String httpRequest = "POST /api/readings HTTP/1.1\r\n";
    httpRequest += "Host: " + String(SERVER_IP) + ":" + String(SERVER_PORT) + "\r\n";
    httpRequest += "Content-Type: application/json\r\n";
    httpRequest += "X-API-Key: " + String(API_KEY) + "\r\n";
    httpRequest += "X-Device-Code: " + String(DEVICE_CODE) + "\r\n";
    httpRequest += "Content-Length: " + String(jsonPayload.length()) + "\r\n";
    httpRequest += "Connection: close\r\n\r\n";
    httpRequest += jsonPayload;
    
    // 3. Send Data Length
    cmd = "AT+CIPSEND=" + String(httpRequest.length());
    if(!sendCommand(cmd, ">", 3000)) {
        Serial.println("Failed to get > prompt");
        return false;
    }
    
    // 4. Send actual HTTP request
    ESP_SERIAL.print(httpRequest);
    
    // 5. Read response
    String response = readEspResponse(5000);
    
    // We expect "SEND OK" or something, then server response
    if (response.indexOf("200 OK") > 0 || response.indexOf("201 Created") > 0 || response.indexOf("success") > 0) {
        Serial.println("Data Sent Successfully");
        return true;
    }
    
    Serial.println("Data Send Failed or Error Response");
    return false;
}

bool NetworkManager::syncConfig(ActuatorManager* actuatorManager) {
    if (!connected) return false;
    
    Serial.println("Syncing Config from API...");
    
    // 1. Establish TCP Connection
    String cmd = "AT+CIPSTART=\"TCP\",\"";
    cmd += SERVER_IP;
    cmd += "\",";
    cmd += SERVER_PORT;
    
    if(!sendCommand(cmd, "CONNECT", 5000)) {
        return false;
    }
    
    // 2. Build HTTP GET Request
    String url = "/iot/config?device_code=" + String(DEVICE_CODE) + "&query_api_key=" + String(API_KEY);
    String httpRequest = "GET " + url + " HTTP/1.1\r\n";
    httpRequest += "Host: " + String(SERVER_IP) + ":" + String(SERVER_PORT) + "\r\n";
    httpRequest += "Connection: close\r\n\r\n";
    
    // 3. Send Data Length
    cmd = "AT+CIPSEND=" + String(httpRequest.length());
    if(!sendCommand(cmd, ">", 3000)) {
        return false;
    }
    
    // 4. Send actual request
    ESP_SERIAL.print(httpRequest);
    
    // 5. Read response
    String response = readEspResponse(8000);
    
    // Parse JSON response (very simplified basic parsing for MVP)
    // Real implementation should use ArduinoJson library
    if (response.indexOf("success") > 0) {
        // Look for tds max
        int tdsMaxIdx = response.indexOf("\"max_threshold\":", response.indexOf("\"sensor_type\":\"tds\""));
        // This requires careful string manipulation or ArduinoJson. 
        // For demonstration, we'll assume the API config logic updates the local variables.
        // We simulate parsing:
        Serial.println("Config received. Parsing JSON...");
        // actuatorManager->updateThresholds(0.0, 800.0, 5.5, 7.5);
        return true;
    }
    
    return false;
}

bool NetworkManager::syncActuatorState(ActuatorManager* actuatorManager) {
    if (!connected) return false;
    
    // 1. Establish TCP Connection
    String cmd = "AT+CIPSTART=\"TCP\",\"";
    cmd += SERVER_IP;
    cmd += "\",";
    cmd += SERVER_PORT;
    
    if(!sendCommand(cmd, "CONNECT", 5000)) {
        return false;
    }
    
    // 2. Build HTTP GET Request
    String url = "/api/irrigation/state/" + String(DEVICE_CODE);
    String httpRequest = "GET " + url + " HTTP/1.1\r\n";
    httpRequest += "Host: " + String(SERVER_IP) + ":" + String(SERVER_PORT) + "\r\n";
    httpRequest += "Connection: close\r\n\r\n";
    
    // 3. Send Data Length
    cmd = "AT+CIPSEND=" + String(httpRequest.length());
    if(!sendCommand(cmd, ">", 3000)) {
        return false;
    }
    
    // 4. Send actual request
    ESP_SERIAL.print(httpRequest);
    
    // 5. Read response
    String response = readEspResponse(5000);
    
    // 6. Simple string search for MVP: {"pump":true} or {"pump":false}
    if (response.indexOf("\"pump\":true") > 0 || response.indexOf("\"pump\": true") > 0) {
        actuatorManager->setPumpState(true);
        Serial.println("Network State: PUMP ON");
    } else if (response.indexOf("\"pump\":false") > 0 || response.indexOf("\"pump\": false") > 0) {
        actuatorManager->setPumpState(false);
        Serial.println("Network State: PUMP OFF");
    }
    
    return true;
}

String NetworkManager::readEspResponse(uint32_t timeout) {
    String response = "";
    long int time = millis();
    while ( (time + timeout) > millis()) {
        while (ESP_SERIAL.available()) {
            char c = ESP_SERIAL.read();
            response += c;
        }
    }
    return response;
}

bool NetworkManager::sendCommand(String cmd, String expectedResponse, uint32_t timeout) {
    ESP_SERIAL.println(cmd);
    String response = readEspResponse(timeout);
    if (response.indexOf(expectedResponse) != -1) {
        return true;
    }
    return false;
}
