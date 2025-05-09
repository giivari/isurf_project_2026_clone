#include <Wire.h>
#include <SPI.h>
#include <SD.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>

#define OLED_RESET 4
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64

Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

const int TDS_PIN = A1; // Pin analog untuk sensor TDS
const int pH_PIN = A2;  // Sambungkan kabel hitam (output) ke pin A2
const int SOIL_MOISTURE_PIN = A0; // Pin analog untuk sensor kelembapan tanah
const int US_TRIG_PIN = 3; // Pin trigger ultrasonik
const int US_ECHO_PIN = 2; // Pin echo ultrasonik

//SD CARDD
const int CHIP_SELECT_PIN = 53; // Pin CS untuk modul microSD

// const int VALVE_PIN = 5;

const float AREA = 50;

const int relayEnable = 2;
const int thresholdMax = 800;
const int thresholdMin = 10;

//define sound speed in cm/uS
#define SOUND_SPEED 0.034

File dataFile;

void setup() {
  Serial.begin(9600);

  pinMode(relayEnable, OUTPUT);
  pinMode(US_TRIG_PIN, OUTPUT); // Sets the trigPin as an Output
  pinMode(US_ECHO_PIN, INPUT); // Sets the echoPin as an Input
  pinMode(SOIL_MOISTURE_PIN, INPUT);

  // Inisialisasi layar OLED
  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println("SSD1306 allocation failed");
    for (;;);
  }else{
    Serial.println("SSD1306 successful");
  }

  // Inisialisasi modul microSD
  if (!SD.begin(CHIP_SELECT_PIN)) {
    Serial.println("Failed to initialize SD card");
    return;
  }
  Serial.println("SD card initialized.");

  // Tulis header ke file data pada microSD
  dataFile = SD.open("sensor_data.txt", FILE_WRITE);
  if (dataFile) {
    dataFile.println("Timestamp, TDS, pH, Soil Moisture, Distance (cm)");
    dataFile.close();
  } else {
    Serial.println("Error opening data file.");
  }
}

void loop() {
  // Baca data dari setiap sensor
  float tds = readTDS();
  // float pH = readPH();
  float soilMoisture = readSoilMoisture();
  float distance = readUltrasonicVolume();
  float pH = readPH();
  Serial.print("soilMoisture: ");
  Serial.println(soilMoisture);
  // Conditioning for Soil Moisture
  if (soilMoisture <= 750) {
    Serial.println ("Wet Soil");
  } else {
    Serial.println ("Dry Soil");
  }


  Serial.print("tds: ");
  Serial.println(tds);
  Serial.print("distance: ");
  Serial.println(distance);
  if (distance > 8170) {
    Serial.println("Wadah Air Penuh");
  } else {
    Serial.println("Wadah Kurang Air");
  }

  // controlValve(soilMoisture);

  // Tampilkan data di layar OLED
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(WHITE);
  display.setCursor(0, 0);
  display.print("TDS: ");
  display.println(tds);
  // display.print("pH: ");
  // display.println(pH);
  // display.print("Soil Moisture: ");
  // display.println(soilMoisture);
  // display.print("Distance: ");
  // display.println(distance);
  display.display();

  // Simpan data ke kartu microSD
  saveDataToSD(tds, -1, pH, distance);

  delay(1000); // Delay 1 detik
}

float readTDS() {
  int rawValue = analogRead(TDS_PIN);
  float voltage = rawValue * (5.0 / 1023.0);
  float tdsValue = (133.42 * voltage * voltage * voltage - 255.86 * voltage * voltage + 857.39 * voltage) * 0.5;
  return rawValue;
}

float readSoilMoisture() {
  int sensorValue = analogRead(SOIL_MOISTURE_PIN);

  sensorValue = map(sensorValue, thresholdMax, thresholdMin, 0, 100);
  Serial.print("Moisture: ");
  Serial.print(sensorValue);
  Serial.println("%");

  if (sensorValue < 0)
  {
    digitalWrite(relayEnable, LOW);
    Serial.println("Relay ON");
  }
  else
  {
    digitalWrite(relayEnable, HIGH);
    Serial.println("Relay OFF");
  }
  
  return sensorValue;
}

float readUltrasonicVolume() {
  digitalWrite(US_TRIG_PIN, LOW);
  delayMicroseconds(5);
  digitalWrite(US_TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(US_TRIG_PIN, LOW);
  float duration = pulseIn(US_ECHO_PIN, HIGH);
  Serial.print("duration:");
  Serial.println(duration);
  // float distanceCM = duration / 35 / 2;
  // float distOUT = ((-1.2231 * distanceCM) + 160.18);
  float distance = (duration / 2) * SOUND_SPEED;
  // float distout = ((distance*AREA) * -81.666) + 8637.4;
  return distance*AREA;
}

void saveDataToSD(float tds, float pH, float soilMoisture, float distance) {
  dataFile = SD.open("sensor_data.txt", FILE_WRITE);
  if (dataFile) {
    dataFile.print(millis());
    dataFile.print(", ");
    dataFile.print(tds);
    dataFile.print(", ");
    dataFile.print(pH);
    dataFile.print(", ");
    dataFile.print(soilMoisture);
    dataFile.print(", ");
    dataFile.println(distance);
    dataFile.close();
  } else {
    Serial.println("Error opening data file.");
  }
}

float readPH() {
  // Membaca nilai analog dari sensor pH
  int sensorValue = analogRead(pH_PIN);
  delay(500);

  // Rumus didapat berdasarkan datasheet
  float outputValue = (-0.0456 * sensorValue) + 16.6644;

  // Menampilkan nilai sensor dan nilai pH di Serial Monitor
  Serial.print("adc = ");
  Serial.print(sensorValue);
  Serial.print(" | pH = ");
  Serial.println(outputValue);

  return outputValue;
}


// void controlValve(float soilMoisture) {
//   int referenceMoisture = 50;

//   if (soilMoisture < referenceMoisture) {
//     digitalWrite(VALVE_PIN, HIGH);
//     Serial.println("Valve opened");
//   } else {
//     digitalWrite(VALVE_PIN, LOW);
//     Serial.println("Valve closed");
//   }
// }