<html>
	<?php
		$age = $_POST[ 'age'];
		$zip = $_POST[ 'zip'];
		$find_genre = $_POST[ 'find_genre'];
		$find_sex = $_POST[ 'find_sex'];

	?>
<head>
	<title>User 's  Info</title>
</head>
<body>
	<h1>User 's Info</h1>
	<h2>Confirm Details</h2>
	<?php
		echo "<p>Order processed at" ;
		echo date('H:i, jS F Y');
		echo "</p>";
		echo '<p>Your infomation is as follows</p>';
		echo "user's age: ".$age."<br />";
		echo "user's zip: ".$zip."<br />";
		echo "user's find_genre: ".$find_genre."<br />";
		echo "user's find_sex: ".$find_sex."<br />";

		$fp = fopen("user.txt",'a');
		$outputstring = $age."\t".$find_sex."\t".$find_genre."\n";
		fwrite($fp, $outputstring);
		fclose($fp);	
	?>
</body>
</html>
