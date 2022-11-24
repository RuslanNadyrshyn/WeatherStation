
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
