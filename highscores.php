<html>
<body>
<?php

$name = $_REQUEST["name"];
if (strlen($name) > 10) {
	$name = substr($name,0,10);
}
$score = $_REQUEST["highscore"];
$newLine = "$name;$score\n";
$myfile = fopen("highscores.csv", "a") or die("Unable to open file!");
fwrite($myfile, $newLine);
fclose($myfile);
?>

</body>
</html>