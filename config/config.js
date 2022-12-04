const WEATHER_SOURCE = "https://openweathermap.org/img/wn/";
const PNG_ENDING = "@2x.png";
// const HOST="https://weather.vps.webdock.cloud";
const HOST="";

const COUNTER_LIST = [20, 50, 100, 200, 500, 1000];

const OPTIONS = [
    { value: "id", text: "ID" },
    { value: "date", text: "Час" },
    { value: "temp", text: "Температура" },
    { value: "press", text: "Тиск" },
    { value: "alt", text: "Висота" },
    { value: "hum", text: "Вологість" }
];

const ORDERS = [
    { value: "DESC", text: "По спаданню" },
    { value: "ASC", text: "По зростанню" }
];