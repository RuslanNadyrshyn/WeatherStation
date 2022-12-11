const WEATHER_SOURCE = "https://openweathermap.org/img/wn/";
const PNG_ENDING = "@2x.png";

const CITIES = ['Київ', 'Львів', 'Харків', 'Дніпро', 'Тернопіль', 
'Івано-Франківськ','Вінниця', 'Запоріжжя', 'Хмельницький', 
'Одеса', 'Луцьк', 'Рівне', 'Полтава', 'Черкаси', 'Житомир', 
'Кропивницький', 'Чернівці', 'Миколаїв', 'Чернігів', 'Суми', 
'Ужгород', 'Херсон', 'Донецьк','Луганськ'];

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

const DB_HEADER = ["ID", "Дата", "Час", "Температура", "Тиск", "Висота", "Вологість"];



