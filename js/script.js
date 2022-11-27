
var db = [];
for (let i = 0; i < 15; i++) {
    db.push({
        id: i,
        date: "date"+i,
        time: "time"+i,
        temp: "temp"+i,
        press: "press"+i,
        alt: "alt"+i,
        hum: "hum"+i
    })
}
console.log("db: ", db);
// var myObject = [{
//     firstname: "Jane",
//     lastname: "Doe",
//     email: "jdoe@email.com"
// }, {
//     firstname: "Ja",
//     lastname: "joe",
//     email: "je@email.com"
// }, {
//     firstname: "Janet",
//     lastname: "joes",
//     email: "jsse@email.com"
// }];


// var data = document.getElementById('data');
// myObject.forEach(function(element) {
//    var firstname = document.create('div');
//    var lastname = document.create('div');
//    var email = document.create('div');

//    firstname.innerHTML = element.firstname;
//    lastname.innerHTML = element.lastname;
//    email.innerHTML = element.email;

//    data.appendChild(firstname);
//    data.appendChild(lastname);
//    data.appendChild(email);
// });

function displayResult() {
    document.getElementById("myHeader").innerHTML = "Have a nice day!";
}


function fetchResult(result) {	
    var temp = [];
    var press = [];
    var alt = [];
    var hum = [];
    var date = [];
    
    result.forEach(element => {
        temp.push(element.temp_bme280);
        press.push(element.press_bme280);
        alt.push(element.alt_bme280);
        hum.push(element.hum_bme280);
        date.push(element.date_bme280);
    });

    return [temp, press, alt, hum, date];
}
