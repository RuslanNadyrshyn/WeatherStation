#include <ESP8266WiFi.h>                 // Підключення бібліотек
#include <Adafruit_Sensor.h>                  
#include <Adafruit_BME280.h>
#include <Wire.h>  

#define SEALEVELPRESSURE_HPA (1013.25)   // Задаємо значення тиску
                                         // на висоті рівня моря
Adafruit_BME280 bme;                     // I2C

const char* ssid = "TP-LINK_3BFCCA";     // Назва Wi-Fi мережі
const char* password = "09081999";       // Пароль від Wi-Fi мережі

const char* host = "192.168.1.104";      // Локальна адреса
const uint16_t port = 80;                // Порт

float temp, pressure, alt, hum;          // Ініціалізація змінних
int counter=0, maxcount=10;


void setup()
{
  Serial.begin(115200);                  // Ініціалізація послідовного зв'язку
  
 if (!bme.begin(0x76)) {                 // Перевірка ініціалізації датчика
    Serial.println("Could not find a valid BME280 sensor!");
    while (1);                                       
  }   
  
  Serial.print("Connecting to " );                   
  WiFi.begin(ssid, password);            // Підключення до Wi-Fi мережі
  while (WiFi.status() != WL_CONNECTED)              
  {
    delay(500);
    Serial.print(".");
  }
  Serial.println(" connected");
  Serial.println("IP address: ");        // Виведення локальної адреси 
  Serial.println(WiFi.localIP());        // до Serial порта   
}

void loop() {
   delay(200); 
   temp = bme.readTemperature();                    // Зчитування температури з датчика
   pressure = bme.readPressure()/100.0F;            // Зчитування тиску з датчика у Па, приведення до гПа
   alt = bme.readAltitude(SEALEVELPRESSURE_HPA);    // Зчитування висоти над рівнем моря з датчика
   hum = bme.readHumidity();                        // Зчитування вологості з датчика
   
  WiFiClient client;                                // Ініціалізація Wi-Fi клієнта
  if (client.connect(host, port))
  {
      client.print("GET /insert.php?");             // Створення GET-запиту до файла insert.php
      client.print("temp=");
      client.print(temp);                           // Запис температури
      client.print("&press=");
      client.print(pressure);                       // Запис тиску
      client.print("&alt=");
      client.print(alt);                            // Запис висоти
      client.print("&hum=");
      client.print(hum);                            // Запис вологості
      client.print("&counter=");
      client.print(counter);                        // Запис лічильника
      client.print("&maxcount=");
      client.print(maxcount);                       // Запис maxcount
      Serial.println("Temperature: "+(String)temp); // Вивід даних до Serial-порту
      Serial.println("Pressure: "+(String)pressure);
      Serial.println("Altitude: "+(String)alt);
      Serial.println("Humidity: "+(String)hum);
      Serial.println("Counter: "+(String)counter);      
      client.println(" HTTP/1.1");
      client.print("Host: ");
      client.println(host);
      client.println("Connection: close");
      client.println();
      
      if(counter==maxcount)                         // Скидання лічильника
        counter=0;
      else                                          // Інкремент лічильника
        counter++;            
      while (client.connected())                    
        if (client.available())
          String line = client.readStringUntil('\n');
      client.stop();                                // Зупинка клієнта
  }
  else
    client.stop();                                  // Помилка підключення
}
