#include <ESP8266WiFi.h>                        // Підключення бібліотек
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecure.h>
#include <Adafruit_Sensor.h>                  
#include <Adafruit_BME280.h>
#include <Wire.h>  
#include <iostream>

#define SEALEVELPRESSURE_HPA (1013.25)            // Задаємо значення тиску на висоті рівня моря
#define LED 2                                     // Макрос світлодіоду
#define DELAY 5000                                // Макрос затримки між ітераціями 10 сек

Adafruit_BME280 bme;                              // I2C

const char* ssid = "TP-Link_9416";                // Назва Wi-Fi мережі
const char* password = "12187638";                // Пароль від Wi-Fi мережі

String host = "https://weather.vps.webdock.cloud";// Адреса хоста   
const uint16_t port = 80;                         // Порт

float temp, pressure, alt, hum;                   // Ініціалізація змінних
int counter=0, maxcount=10;

void setup()
{
  Serial.begin(115200);                           // Ініціалізація послідовного зв'язку
  
  pinMode(LED, OUTPUT);
  if (!bme.begin(0x76)) {                         // Перевірка ініціалізації датчика
    Serial.println("Could not find a valid BME280 sensor!");
    while (1);                                       
  }   
  
  Serial.print("Connecting to " );                   
  WiFi.begin(ssid, password);                     // Підключення до Wi-Fi мережі
  while (WiFi.status() != WL_CONNECTED)              
  {
    delay(500);
    Serial.print(".");
  }
  Serial.println(" connected");
  Serial.println("IP address: ");                 // Виведення локальної адреси 
  Serial.println(WiFi.localIP());                 // до Serial порта   
}

void loop() {
  delay(DELAY);                                   // Затримка перед виконанням ітерації
  digitalWrite(LED, LOW);                         // Увімкнення світлодіоду
  temp = bme.readTemperature();                   // Зчитування температури з датчика
  pressure = bme.readPressure()/100.0F;           // Зчитування тиску з датчика у Па, приведення до гПа
  alt = bme.readAltitude(SEALEVELPRESSURE_HPA);   // Зчитування висоти над рівнем моря з датчика
  hum = bme.readHumidity();                       // Зчитування вологості з датчика

  Serial.println("\nTemperature: "+(String)temp); // Вивід даних до Serial-порту
  Serial.println("Pressure: "+(String)pressure);
  Serial.println("Altitude: "+(String)alt);
  Serial.println("Humidity: "+(String)hum);
  Serial.println("Counter: "+(String)counter+"/"+(String)maxcount); 

  if (WiFi.status() == WL_CONNECTED) {            // Перевірка підключення до Wi-Fi
    WiFiClientSecure client;

    client.setInsecure();                         // Використання безпечного протоколу (https) 
    HTTPClient https;

    String data = "/src/insert.php?temp="+(String)temp+ // Заповнення строки даних
      "&press="+(String)pressure+
      "&alt="+(String)alt+
      "&hum="+(String)hum+
      "&counter="+(String)counter+
      "&maxcount="+(String)maxcount;              
    String fullUrl = host + data;                 // Конкатенація строк адреси хоста та даних
    Serial.println("Requesting " + fullUrl);      // Вивід до Serial порта URL запиту
    if (https.begin(client, fullUrl)) {
      int httpCode = https.GET();                 // Відправлення URL запиту
      Serial.println("Response code: " + String(httpCode)); // Вивід коду відповіді
      https.end();                                // Завершення роботи протоколу
    } else {                                      // Вивід повідомлення при помилці
      Serial.printf("[HTTPS] Unable to connect\n");
    }
  }

  counter == maxcount ? counter=0 : counter++;    // Тернарний оператор скидання лічильника

  digitalWrite(LED, HIGH);                        // Вимкнення світлодіоду
}

