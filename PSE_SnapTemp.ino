#include <Ethernet.h>
#include "DHT.h"
#define DHTPIN 2
#define DHTTYPE DHT22
DHT dht(DHTPIN, DHTTYPE);
#include <LiquidCrystal_I2C.h>
LiquidCrystal_I2C lcd(0x27, 16, 2);
#define MQ_PIN                       (A3)
#define RL_VALUE                     (5)
#define RO_CLEAN_AIR_FACTOR          (9.83)
#define CALIBARAION_SAMPLE_TIMES     (20)
#define CALIBRATION_SAMPLE_INTERVAL  (200)
#define READ_SAMPLE_INTERVAL         (10)
#define READ_SAMPLE_TIMES            (2)
#define GAS_LPG                      (0)
#define GAS_CO                       (1)
#define GAS_SMOKE                    (2)
#define button 4
#define led_Red 3
#define buzzer 5
int humidity ;
float temperature;
float fahrenheit;
int lpg;
int co;
int smoke;
float LPGCurve[3] =  {2.3, 0.21, -0.47};
float COCurve[3] =  {2.3, 0.72, -0.34};
float SmokeCurve[3] = {2.3, 0.53, -0.44};
float Ro  =  10;
unsigned long time_lcd = 5000;
unsigned long time_update = 30000;
unsigned long time_sensor = 5000;
unsigned long last_time_1 = 0;
unsigned long last_time_2 = 0;
unsigned long last_time_3 = 0;
byte count_lcd  = 0;
byte count_data = 0;
byte count_sensor = 0;
bool buttonState = 0;
String id = "01";
String company_code = "8210";
String macaddress = "AAADBEEFFADD";
byte mac[] = { 0xAA, 0xAD, 0xBE, 0xEF, 0xFA, 0xDD };
char server[] = "dev-snaptemp.ad.sritranggroup.com";
IPAddress myDns(192, 168, 0, 33);
EthernetClient client;


void setup() {
  Serial.begin(9600);
  lcd.begin();
  lcd.backlight();
  lcd.setCursor(2, 0);
  lcd.print("loading.....");
  pinMode(button, INPUT);
  pinMode(led_Red, OUTPUT);
  pinMode(buzzer, OUTPUT);

  /**** start the Ethernet connection: *****/
  Serial.println("Initialize Ethernet with DHCP: ");
  if (Ethernet.begin(mac) == 0) {
    Serial.print("Failed to configure Ethernet using DHCP");
    lcd.clear();
    lcd.setCursor(1, 1);
    lcd.print("Not Connected");
    delay(5000);
    if (Ethernet.hardwareStatus() == EthernetNoHardware) {
      Serial.print("Ethernet shield was not found.  Sorry, can't run without hardware. :(");
    } else if (Ethernet.linkStatus() == LinkOFF) {
      Serial.print("Ethernet cable is not connected.");
    }

    /**** no point in carrying on, so do nothing forevermore: ****/
    while (true) {
      delay(1);
    }
  }
  Serial.println("MQ_2 Calibrating...");
  Ro = MQCalibration(MQ_PIN);
  Serial.print("Ro = ");
  Serial.print(Ro);
  Serial.println(" Kohm");
  Serial.print("\nIP address : ");
  Serial.println(Ethernet.localIP());
  Serial.print("MAC address : ");
  Serial.println(macaddress);
  Serial.print("Company Code : ");
  Serial.println(company_code);
  lcd.clear();
  lcd.setCursor(3, 0);
  lcd.print("Connected");
  lcd.setCursor(0, 1);
  lcd.print("IP:");
  lcd.setCursor(4, 1);
  lcd.print(Ethernet.localIP());
  delay(3000);
  dht.begin();
}

void loop() {
  delay(1000);
  Serial.print("\nIP address : ");
  Serial.println(Ethernet.localIP());
  read_sensor();
  lcd_print();
  alert();
  update_database();
}

void lcd_print() {
  if ( millis() - last_time_1 > time_lcd) {
    last_time_1 = millis();
    if (count_lcd  == 0) {
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Humidity : ");
      lcd.setCursor(11, 0);
      lcd.print(humidity);
      lcd.setCursor(15, 0);
      lcd.print("%");

      lcd.setCursor(0, 1);
      lcd.print(" Temp : ");
      lcd.setCursor(8, 1);
      lcd.print(temperature);
      lcd.setCursor(14, 1);
      lcd.print("*C");
      count_lcd ++;
    }
    else if (count_lcd  == 1) {
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print(" Gas  :");
      lcd.setCursor(11, 0);
      lcd.print(lpg);
      lcd.setCursor(13, 0);
      lcd.print("ppm");
      lcd.setCursor(0, 1);
      lcd.print("Smoke : ");
      lcd.setCursor(11, 1);
      lcd.print(smoke);
      lcd.setCursor(13, 1);
      lcd.print("ppm");
      count_lcd = 0;
    }
  }
}

void update_database() {

  if ( millis() - last_time_2 > time_update) {
    last_time_2 = millis();
    if (count_data == 0) {
      String data = "h=";
      data += humidity;
      data += "&t=";
      data += temperature;
      data += "&lpg=";
      data += lpg;
      data += "&co=";
      data += co;
      data += "&s=";
      data += smoke;
      data += "&c_code=";
      data += company_code;
      data += "&id=";
      data += id;
      //data += "&m=";
      //data += macaddress;
      Serial.println(data);
      if (client.connect(server, 80)) {
        Serial.println("connected");
        client.println("POST /line_notify.php HTTP/1.1");
        client.println("Host: dev-snaptemp.ad.sritranggroup.com");
        client.println("Connection: close");
        client.println("Content-Type: application/x-www-form-urlencoded;");
        client.print("Content-Length: ");
        client.println(data.length());
        client.println();
        client.println(data);
      } else {
        Serial.println("connection failed");
        Ethernet.begin(mac);
      }

      while (client.connected()) {
        if (client.available()) {
          char c = client.read();
          Serial.print(c);
        }
      }
      count_data ++;
    }
    else if (count_data == 1) {
      count_data = 0;
    }
  }
} 

void alert() {
  byte sw = digitalRead(button);
  Serial.print("\nBUTTON_STATUS = ");
  Serial.println(sw);
  if (temperature > 35 || smoke > 100 || lpg > 100 || co > 100) {
    buttonState = digitalRead(button);
    Serial.println("!!! Alert !!!");
    if (buttonState == 1) {
      Serial.println("Button ON");
      digitalWrite(led_Red, HIGH);
      digitalWrite(buzzer, LOW);
    }
    else {
      Serial.println("Button OFF");
      digitalWrite(led_Red, HIGH);
      digitalWrite(buzzer, HIGH);
    }
  }
  else {
    Serial.println("NO Alert ");
    digitalWrite(led_Red, LOW);
    digitalWrite(buzzer, LOW);
  }
}


void read_sensor() {
  if ( millis() - last_time_3 > time_sensor) {
    last_time_3 = millis();
    if (count_sensor == 0) {
      humidity = dht.readHumidity();
      float temp =  dht.readTemperature();
      temperature = temp - 2.00 ;
      float fahr =  dht.readTemperature(true);
      fahrenheit = fahr - 2.00;
      if (isnan(humidity) || isnan(temperature) || isnan(fahrenheit)) {
        Serial.println(F("Failed to read from DHT sensor!"));
        return;
      }
      lpg = MQGetGasPercentage(MQRead(MQ_PIN) / Ro, GAS_LPG);
      co  = MQGetGasPercentage(MQRead(MQ_PIN) / Ro, GAS_CO);
      smoke = MQGetGasPercentage(MQRead(MQ_PIN) / Ro, GAS_SMOKE);

      if (lpg == 0) {
        lpg = 1;
      }
      if (co == 0) {
        co = 1;
      }
      if (smoke == 0) {
        smoke = 1;
      }

      Serial.print(F("\nHumidity: "));
      Serial.print(humidity);
      Serial.print(F("%  Temperature: "));
      Serial.print(temperature);
      Serial.print(F("°C "));
      Serial.print(fahrenheit);
      Serial.println(F("°F  "));
      Serial.print("LPG: ");
      Serial.print(lpg);
      Serial.print( " ppm" );
      Serial.print("    ");
      Serial.print("CO: ");
      Serial.print(co);
      Serial.print( " ppm" );
      Serial.print("    ");
      Serial.print("SMOKE: ");
      Serial.print(smoke);
      Serial.println( " ppm" );
      Serial.println(" ");
      count_sensor ++;
    }
    else if (count_sensor == 1) {
      count_sensor = 0;
    }
  }
}

float MQResistanceCalculation(int raw_adc)
{
  return ( ((float)RL_VALUE * (1023 - raw_adc) / raw_adc));
}

float MQCalibration(int mq_pin)
{
  int i;
  float val = 0;

  for (i = 0; i < CALIBARAION_SAMPLE_TIMES; i++) {
    val += MQResistanceCalculation(analogRead(mq_pin));
    delay(CALIBRATION_SAMPLE_INTERVAL);
  }
  val = val / CALIBARAION_SAMPLE_TIMES;

  val = val / RO_CLEAN_AIR_FACTOR;


  return val;
}

float MQRead(int mq_pin)
{
  int i;
  float rs = 0;

  for (i = 0; i < READ_SAMPLE_TIMES; i++) {
    rs += MQResistanceCalculation(analogRead(mq_pin));
    delay(READ_SAMPLE_INTERVAL);
  }

  rs = rs / READ_SAMPLE_TIMES;

  return rs;
}

int MQGetGasPercentage(float rs_ro_ratio, int gas_id)
{
  if ( gas_id == GAS_LPG ) {
    return MQGetPercentage(rs_ro_ratio, LPGCurve);
  } else if ( gas_id == GAS_CO ) {
    return MQGetPercentage(rs_ro_ratio, COCurve);
  } else if ( gas_id == GAS_SMOKE ) {
    return MQGetPercentage(rs_ro_ratio, SmokeCurve);
  }

  return 0;
}

int  MQGetPercentage(float rs_ro_ratio, float * pcurve)
{
  return (pow(10, ( ((log(rs_ro_ratio) - pcurve[1]) / pcurve[2]) + pcurve[0])));
}
