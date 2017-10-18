<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" charset="UTF-8"/>
<style>
canvas {
    border: 2px solid #d3d3d3;
    background-color: #f1f1f1;
	background-image: url("kortti.png");
	background-size: auto;
	background-position: center top;
    background-repeat: no-repeat;
}

input[type=text] {
    width: 680px;
    padding: 12px 20px;
    margin: 8px 0;
    box-sizing: border-box;
}

input[type=button] {
    width: 200px;
    padding: 12px 20px;
    margin: 8px 0;
    box-sizing: border-box;
}

input[id="highscorename"] {
	width: 635px;
}
.column {
    float: left;
}

.gamepanel {
	padding: 15px;
}

.scoret {
	background-color: #f1f1f1;
	padding: 15px;
	border-style: dotted;
	
}

.highscore {
	float: left;
	font-family: Georgia, Serif, "Times New Roman";
	font-weight: 900;
	
}



</style>
</head>
<body onload="load()">


<script>
var postCodeKeys = [];
var currentScore = 0;
var currentMistakes = 0;
var TIMELIMIT = 20;
var timer = TIMELIMIT;
var gameState;

function load() {	
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open("GET", "http://users.metropolia.fi/~villenau/koodausnopeustesti/postinumerot.json", true);
	xmlhttp.send();
	xmlhttp.onload = function() {
		var postCodeData = JSON.parse(this.responseText);
		for(var k in postCodeData) {
			postCodeKeys.push({
				postCode: k,
				city: postCodeData[k]
			});
		}
		startGame();
	}
}
	

function startGame() {
	gameState = false;
	document.getElementById('highscoreinput').style.display = 'none';
	document.getElementById("userInput").value = "";
	
	recipient = new component(newRecipient(), 130, 240, "black", "20px Helvetica");
	address = new component(newAddress(), 130, 270, "black", "20px Helvetica");
	postalCodeCanvas = new component(newPostalCode(), 130, 320, "black", "20px Helvetica");
	clock = new component("Aika: 0", 10, 590, "black", "50px Helvetica");
	score = new component("Oikein: 0", 350, 590, "green", "50px Helvetica");
	mistakes = new component("Virheet: 0", 580, 590, "red", "50px Helvetica");	
    gameCanvas.start();
}

var gameCanvas = {
    canvas : document.createElement("canvas"),
    start : function() {
        this.canvas.width = 820;
        this.canvas.height = 600;
        this.context = this.canvas.getContext("2d");
		var gamediv = document.getElementById("gamepanel");
        gamediv.insertBefore(this.canvas, gamediv.childNodes[0]);
		interval = setInterval(updateGameArea, 20)
    },
    clear : function() {
        this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
    }
}


function component (text, x, y, color, sizeAndFont) {
	this.x = x;
	this.y = y;
	this.text = text;
	
	this.update = function(){
		ctx = gameCanvas.context;
		ctx.font = sizeAndFont;
		ctx.fillStyle = color;
		ctx.fillText(this.text, this.x, this.y);	
    }
}

function newPostalCode() {
	var randomCode = Math.floor((Math.random() * postCodeKeys.length));
	var postCodeAndCity = postCodeKeys[randomCode].postCode + " " + postCodeKeys[randomCode]["city"];
	var postCode = postCodeAndCity.substr(0,5);
	var city = postCodeAndCity.substr(5);
	return postCode + city;
}

function newRecipient() {
	var recipients = ["Mikko Mallikas", "Makko Millokas", "Kekko Ekkonen", "Posti Pate", "Lasse Lajittelija", "B4U"]
	var randomRecipient = Math.floor((Math.random() * recipients.length));
	return recipients[randomRecipient];
}


function newAddress() {
	var addresses = ["Koodaajankatu 3a", "En osaa koodata katu 17B", "Mitä tämä on katu on 1A", "Katuosoite 294 B 20"]
	var randomAddress = Math.floor((Math.random() * addresses.length));
	return addresses[randomAddress];
}

function updateGameArea() {
    gameCanvas.clear();
	if (gameState) {
		timer -= 0.02
	}
	score.text = "Oikein: " + currentScore;
	mistakes.text = "Virheet: " + currentMistakes;
	clock.text = "Aika: " + timer.toFixed(1);
	if (timer > 0) {
		postalCodeCanvas.update();
		clock.update();
		score.update();
		mistakes.update();
		address.update();
		recipient.update();
	} else {
		displayEndResult();
		gameState = false;
	}
}

function handleKeyPress(e){
	var key=e.keyCode || e.which;
	if (key==13 && gameState){
		var number;
		number = document.getElementById("userInput").value;	
		if (isNaN(number)) {	
			currentMistakes += 1;
		} else {
		
			if (number == postalCodeCanvas.text.substr(0,5)) {
				currentScore += 1;
			} else {
				currentMistakes += 1;
			}
			
		}
		document.getElementById("userInput").value = "";
		postalCodeCanvas.text = newPostalCode();
		address.text = newAddress();
		recipient.text = newRecipient();
		score.update();
		mistakes.update();
	} else if (!gameState){
		gameState = true;
	}
	
}

function displayEndResult() {
		document.getElementById('restart_btn').style.display = 'inline';
		if (currentScore > 0) {
			document.getElementById('highscoreinput').style.display = 'inline';
		}
		postalCodeCanvas.update();
		score.update();
		mistakes.update();
		address.update();
		recipient.update();
		timer = 0.0;
		clock.update();
}

function restart() {
	gameState = false;
	document.getElementById("userInput").value = "";
	timer = TIMELIMIT;
	currentScore = 0;
	currentMistakes = 0;
}

function sendScore() {
	if (currentScore > 0 && !(document.getElementById("highscorename").value === "")) {
		var xmlhttp = new XMLHttpRequest();
		var username = document.getElementById("highscorename").value;
		var scoretosend = currentScore;	
		xmlhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				location.reload(true);
			}	
		};
		xmlhttp.open("POST", "highscores.php?name=" + username + "&highscore=" + scoretosend, true);
		xmlhttp.send();
	}
}

function changeGameMode() {
	var mode;
	var form = document.forms["moodi"]
	var radios = form.elements["gameMode"];
	for (var i=0, len=radios.length; i<len; i++) {
		if (radios[i].checked) {
			mode = radios[i].value;
			break;
		}
	}
	if (mode == "lemppa" && gameState == false) {
		timer = 18000;
		updateGameArea();
	} else if (mode == "normal" && gameState == false) {
		timer = TIMELIMIT;
		updateGameArea();	
	}
}

</script>

<div class="column game" id="gamepanel">
	<br>
	<form id="moodi" onsubmit="return false;">
		<input type="radio" name="gameMode" id="gameMode" onclick="changeGameMode()" value="normal" checked> Normaalimoodi (20 sekuntia)
		<br>
		<input type="radio" name="gameMode" id="gameMode" onclick="changeGameMode()" value="lemppa"> Lemppaurakkamoodi (5 tuntia)
	</form>
	<p>Aloita kirjoittamalla postinumero ja paina enteriä:</p>
	<input type="text" id="userInput" onkeypress="handleKeyPress(event)">

	
	<br>
	<input type="button" id="restart_btn" onclick="restart()" value="Restart">
	<br>

	<p id="highscoreinput">
	Nimi: <input type="text" name="name" id="highscorename"><br>
	<input type="button" id="sendScore_btn" onclick="sendScore()" value="Lähetä score">
	</p>
</div>
<div class="column scoret"> 
	<p class="highscore">
	HIGH SCORES:
	<br>
	<?php
	$listOfScores = array();
	$listOfNames = array();

	$myfile = fopen("highscores.csv", "r") or die("Unable to open file!");
		
	while(!feof($myfile)) {
		$line = fgets($myfile);
		$highscore = explode(";", $line, 2);
		array_push($listOfNames, $highscore[0]);
		array_push($listOfScores, $highscore[1]);
	}

	fclose($myfile);

	$highscorelist = array_combine($listOfNames, $listOfScores);
	arsort($highscorelist, 1);
	foreach($highscorelist as $x => $x_value) {
		echo $x . " " . $x_value;
		echo "<br>";
	}
	?>
	</p>
</div>


</body>
</html>
