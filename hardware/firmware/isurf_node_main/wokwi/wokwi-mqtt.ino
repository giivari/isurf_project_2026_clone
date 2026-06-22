#include <ArduinoJson.h>
#include <DHT.h>
#include <PubSubClient.h>
#include <WiFi.h>
#include <WiFiClientSecure.h>


// Konfigurasi WiFi bawaan Wokwi
const char *ssid = "Wokwi-GUEST";
const char *password = "";

// Konfigurasi Kluster HiveMQ Cloud
const char *mqtt_server =
    "40ce76f98591453e962925f524ea06fa.s1.eu.hivemq.cloud"; // sesuai server mqtt
                                                           // nya
const int mqtt_port = 8883;
const char *mqtt_user = "isurf";
const char *mqtt_pass = "...";

WiFiClientSecure espClient;
PubSubClient client(espClient);
const char *topic_publish = "isurf/device/sensor";

// --- DEKLARASI PIN ---
#define DHTPIN 15     // Pin Data DHT22 -> GPIO 15
#define DHTTYPE DHT22 // Tipe DHT
DHT dht(DHTPIN, DHTTYPE);

#define PIN_TDS 34 // Pin Potensiometer 1 -> GPIO 34 (Analog)
#define PIN_PH 35  // Pin Potensiometer 2 -> GPIO 35 (Analog)

void setup_wifi() {
  delay(10);
  Serial.print("Menghubungkan ke WiFi Wokwi...");
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi terhubung!");
}

void reconnect() {
  while (!client.connected()) {
    Serial.print("Menghubungkan ke kluster HiveMQ...");
    String clientId = "ESP32Wokwi-" + String(random(0, 1000));

    if (client.connect(clientId.c_str(), mqtt_user, mqtt_pass)) {
      Serial.println("Terhubung ke MQTT!");
    } else {
      Serial.print("Gagal, status=");
      Serial.print(client.state());
      Serial.println(" mencoba lagi dalam 5 detik...");
      delay(5000);
    }
  }
}

void setup() {
  Serial.begin(115200);

  // Inisiasi modul fisik
  dht.begin();
  analogReadResolution(12); // Resolusi ADC ESP32: 0 - 4095

  setup_wifi();
  espClient.setInsecure(); // Wajib untuk terhubung ke MQTT Port 8883 tanpa
                           // validasi sertifikat
  client.setServer(mqtt_server, mqtt_port);
}

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();

  static unsigned long lastMsg = 0;
  long now = millis();

  if (now - lastMsg > 5000) {
    lastMsg = now;

    // 1. Membaca Sensor DHT22 Aktual
    float hum = dht.readHumidity();
    float temp = dht.readTemperature();

    // 2. Membaca Potensiometer Analog
    int val_tds = analogRead(PIN_TDS);
    int val_ph = analogRead(PIN_PH);

    // Mencegah error jika DHT sedang kalibrasi
    if (isnan(hum) || isnan(temp)) {
      Serial.println("Menunggu bacaan DHT22...");
      return;
    }

    // 3. Konversi nilai Potensiometer (0-4095) ke rentang Sensor yang kita
    // inginkan
    float tds = map(val_tds, 0, 4095, 0, 1000); // 0 - 1000 ppm
    float ph = (val_ph / 4095.0) * 14.0;        // 0.0 - 14.0 pH

    // 4. JSON Payload
    StaticJsonDocument<256> doc;
    doc["device_id"] = "ESP32_MODUL_WOKWI";
    doc["temperature"] = temp;
    doc["humidity"] = hum;
    doc["tds"] = tds;
    doc["ph"] = ph;

    char payload[256];
    serializeJson(doc, payload);

    // 5. Mengirimnya ke Cloud
    client.publish(topic_publish, payload);
    Serial.println("Data Sensor Aktual Terkirim: " + String(payload));
  }
}
