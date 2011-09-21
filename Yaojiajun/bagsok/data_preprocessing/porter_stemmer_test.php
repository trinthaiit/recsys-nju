<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
	<title>porter stemmer</title> 
</head>
<body>
	<form action="porter_stemmer_test.php" method="get">
		<input name="word" size="100">
		<button type="submit">GO</button>
	</form>
</body>
</html>
 
<?php
	include 'class.stemmer.inc.php';
	include 'porter_stemmer.php';
	$word = isset($_GET['word']) ? $_GET['word'] : '';
	$stemmer = new Stemmer();
	echo "class.stemmer.inc.php:  " . $stemmer->stem($word);
	echo "<br>";
	echo "porter_stemmer.php:   " . PorterStemmer::Stem($word);
?>