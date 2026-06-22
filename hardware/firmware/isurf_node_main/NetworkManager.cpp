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
    
    // Timeout agak lama untuk WiFi
    if(sendCommand(cmd, "WIFI GOT IP", 15000) || sendCommand("AT+CWJAP?", WIFI_SSID, 2000)) {
        connected = true;
        Serial.println("WiFi Connected!");
        
        #if CONNECTION_MODE == NET_MODE_MQTT
            return connectMQTT();
        #else
            return true;
        #endif
    } else {
        connected = false;
        Serial.println("WiFi Connection Failed.");
        return false;
    }
}

bool NetworkManager::connectMQTT() {
    Serial.println("Configuring MQTT...");
    
    // AT+MQTTUSERCFG=0,1,"clientID","user","pass",0,0,""
    String cmd = "AT+MQTTUSERCFG=0,1,\"";
    cmd += DEVICE_CODE;
    cmd += "\",\"";
    cmd += MQTT_USER;
    cmd += "\",\"";
    cmd += MQTT_PASS;
    cmd += "\",0,0,\"\"";
    
    if(!sendCommand(cmd, "OK", 3000)) {
        Serial.println("MQTT Config Failed");
        return false;
    }
    
    Serial.println("Connecting to MQTT Broker...");
    // AT+MQTTCONN=0,"broker",port,1
    cmd = "AT+MQTTCONN=0,\"";
    cmd += MQTT_BROKER;
    cmd += "\",";
    cmd += MQTT_PORT;
    cmd += ",1";
    
    if(sendCommand(cmd, "OK", 10000)) {
        Serial.println("MQTT Connected!");
        
        // Subscribe to control topic
        cmd = "AT+MQTTSUB=0,\"";
        cmd += MQTT_TOPIC_SUB;
        cmd += "\",1";
        sendCommand(cmd, "OK", 3000);
        return true;
    }
    
    Serial.println("MQTT Connection Failed");
    return false;
}

bool NetworkManager::isConnected() {
    return connected;
}

bool NetworkManager::sendData(String jsonPayload) {
    if (!connected) return false;
    
    #if CONNECTION_MODE == NET_MODE_MQTT
        Serial.println("Publishing Data via MQTT...");
        
        // Menggunakan MQTTPUBRAW agar aman mempublikasikan string JSON yang mengandung kutip ganda
        String cmd = "AT+MQTTPUBRAW=0,\"";
        cmd += MQTT_TOPIC_PUB;
        cmd += "\",";
        cmd += jsonPayload.length();
        cmd += ",1,0";
        
        if(sendCommand(cmd, "OK", 3000)) {
            ESP_SERIAL.print(jsonPayload);
            // Tunggu respon setelah raw data dikirim
            String response = readEspResponse(3000);
            if(response.indexOf("OK") != -1) {
                Serial.println("Data Published Successfully");
                return true;
            }
        }
        Serial.println("MQTT Publish Failed");
        return false;
        
    #else
        // HTTP Logic
        Serial.println("Sending Data to API...");
        
        String cmd = "AT+CIPSTART=\"TCP\",\"";
        cmd += SERVER_IP;
        cmd += "\",";
        cmd += SERVER_PORT;
        
        if(!sendCommand(cmd, "CONNECT", 5000)) {
            Serial.println("TCP Connection Failed");
            return false;
        }
        
        String httpRequest = "POST /api/readings HTTP/1.1\r\n";
        httpRequest += "Host: " + String(SERVER_IP) + ":" + String(SERVER_PORT) + "\r\n";
        httpRequest += "Content-Type: application/json\r\n";
        httpRequest += "X-API-Key: " + String(API_KEY) + "\r\n";
        httpRequest += "X-Device-Code: " + String(DEVICE_CODE) + "\r\n";
        httpRequest += "Content-Length: " + String(jsonPayload.length()) + "\r\n";
        httpRequest += "Connection: close\r\n\r\n";
        httpRequest += jsonPayload;
        
        cmd = "AT+CIPSEND=" + String(httpRequest.length());
        if(!sendCommand(cmd, ">", 3000)) {
            Serial.println("Failed to get > prompt");
            return false;
        }
        
        ESP_SERIAL.print(httpRequest);
        String response = readEspResponse(5000);
        
        if (response.indexOf("200 OK") > 0 || response.indexOf("201 Created") > 0 || response.indexOf("success") > 0) {
            Serial.println("Data Sent Successfully");
            return true;
        }
        
        Serial.println("Data Send Failed or Error Response");
        return false;
    #endif
}

bool NetworkManager::syncConfig(ActuatorManager* actuatorManager) {
    if (!connected) return false;
    
    #if CONNECTION_MODE == NET_MODE_MQTT
        // Pada MQTT, config bisa di-push via topik lain, tidak perlu polling
        // Return true untuk mengabaikan fungsi ini dalam mode MQTT
        return true; 
    #else
        Serial.println("Syncing Config from API...");
        
        String cmd = "AT+CIPSTART=\"TCP\",\"";
        cmd += SERVER_IP;
        cmd += "\",";
        cmd += SERVER_PORT;
        
        if(!sendCommand(cmd, "CONNECT", 5000)) {
            return false;
        }
        
        String url = "/iot/config?device_code=" + String(DEVICE_CODE) + "&query_api_key=" + String(API_KEY);
        String httpRequest = "GET " + url + " HTTP/1.1\r\n";
        httpRequest += "Host: " + String(SERVER_IP) + ":" + String(SERVER_PORT) + "\r\n";
        httpRequest += "Connection: close\r\n\r\n";
        
        cmd = "AT+CIPSEND=" + String(httpRequest.length());
        if(!sendCommand(cmd, ">", 3000)) {
            return false;
        }
        
        ESP_SERIAL.print(httpRequest);
        String response = readEspResponse(8000);
        
        if (response.indexOf("success") > 0) {
            Serial.println("Config received. Parsing JSON...");
            return true;
        }
        return false;
    #endif
}

bool NetworkManager::syncActuatorState(ActuatorManager* actuatorManager) {
    if (!connected) return false;
    
    #if CONNECTION_MODE == NET_MODE_MQTT
        // State aktuator di-handle asinkron oleh checkMessages()
        return true;
    #else
        String cmd = "AT+CIPSTART=\"TCP\",\"";
        cmd += SERVER_IP;
        cmd += "\",";
        cmd += SERVER_PORT;
        
        if(!sendCommand(cmd, "CONNECT", 5000)) {
            return false;
        }
        
        String url = "/api/irrigation/state/" + String(DEVICE_CODE);
        String httpRequest = "GET " + url + " HTTP/1.1\r\n";
        httpRequest += "Host: " + String(SERVER_IP) + ":" + String(SERVER_PORT) + "\r\n";
        httpRequest += "Connection: close\r\n\r\n";
        
        cmd = "AT+CIPSEND=" + String(httpRequest.length());
        if(!sendCommand(cmd, ">", 3000)) {
            return false;
        }
        
        ESP_SERIAL.print(httpRequest);
        String response = readEspResponse(5000);
        
        if (response.indexOf("\"pump\":true") > 0 || response.indexOf("\"pump\": true") > 0) {
            actuatorManager->setPumpState(true);
            Serial.println("Network State: PUMP ON");
        } else if (response.indexOf("\"pump\":false") > 0 || response.indexOf("\"pump\": false") > 0) {
            actuatorManager->setPumpState(false);
            Serial.println("Network State: PUMP OFF");
        }
        
        return true;
    #endif
}

void NetworkManager::checkMessages(ActuatorManager* actuatorManager) {
    #if CONNECTION_MODE == NET_MODE_MQTT
        // Membaca pesan asinkron yang masuk dari ESP8266
        // Format payload masuk dari AT+MQTTSUB:
        // +MQTTSUBRECV:0,"isurf/device/control",2,ON
        if (ESP_SERIAL.available()) {
            String response = ESP_SERIAL.readStringUntil('\n');
            if (response.indexOf("+MQTTSUBRECV") != -1) {
                Serial.println("MQTT Message Arrived: " + response);
                
                // Cek isi pesan untuk parsing aktuator
                if (response.indexOf("ON") != -1) {
                    actuatorManager->setPumpState(true);
                    Serial.println("MQTT Action: PUMP ON");
                } else if (response.indexOf("OFF") != -1) {
                    actuatorManager->setPumpState(false);
                    Serial.println("MQTT Action: PUMP OFF");
                }
            }
        }
    #endif
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
