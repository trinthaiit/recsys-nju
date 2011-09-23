<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
	<title>porter stemmer</title> 
</head>
<body>
	<form action="words_association_log_likelihood_ratio.php" method="get">
		<div>
			<label> Stimulus Word: </label>
			<input name="stimulus_word" size="100">
		</div>
		<div>
			<label> Cooccurring Word: </label>
			<input name="cooccurring_word" size="100">
		</div>
		<button type="submit">GO</button>
	</form>
</body>
</html>

<?php
	// if(isset($_GET['stimulus_word']) && isset($_GET['cooccurring_word'])){
		// // connect to mysql
		// ini_set("max_execution_time",2400);
		// $db = mysql_connect("localhost", "recsys-nju", "recsys-nju");
		// mysql_select_db("bagsok", $db);
		
		// $stimulus_word = $_GET['stimulus_word'];
		// $cooccurring_word = $_GET['cooccurring_word'];
		// $result = compute_ratio($stimulus_word, $cooccurring_word);
		// $non_stimulus_word = $result['residual_frequency']+$result['residual_corpus_size'];
		// $non_cooccurring_word = $result['residual_corpus_size']+$result['residual_window_size'];
		// echo "stimulus word is $stimulus_word and cooccurring word is $cooccurring_word <br>";
		// echo '<table border="1px">';
		// echo '<tr><th></th><th>cw</th><th>~cw</th><th>总计</th></tr>';
		// echo "<tr><th>sw</th><td>$result[window_frequency]</td><td>$result[residual_window_size]</td><td>$result[total_window_size]</td></tr>";
		// echo "<tr><th>~sw</th><td>$result[residual_frequency]</td><td>$result[residual_corpus_size]</td><td>$non_stimulus_word</td></tr>";
		// echo "<tr><th>总计</th><td>$result[cooccurring_word_total_frequency]</td><td>$non_cooccurring_word</td><td>$result[total_corpus_size]</td></tr>";
		// echo '</table>';
		// echo "log-likelihood ratio = $result[ratio] <br>";
		
		// //chi square test
		// $chi_square = chi_square($result['window_frequency'], $result['residual_window_size'], 
								 // $result['residual_frequency'], $result['residual_corpus_size']);
		// echo "chi_square = $chi_square <br>";
		// mysql_close($db);
	// }
	
	function compute_ratio($stimulus_word, $cooccurring_word){
		$result = array();
		
		$window_frequency_sql = "SELECT COUNT(keywords) FROM keywords_from_userinfo WHERE keywords LIKE '% " . 
								$stimulus_word . " %' and keywords LIKE '% " . $cooccurring_word . " %';";
		$window_frequency = query_count($window_frequency_sql);
		
		$cooccurring_word_total_frequency_sql = "SELECT COUNT(keywords) FROM keywords_from_userinfo WHERE keywords LIKE '% ".
								$cooccurring_word . " %';";
		$cooccurring_word_total_frequency = query_count($cooccurring_word_total_frequency_sql);
		$residual_frequency = $cooccurring_word_total_frequency - $window_frequency;
		
		$total_window_size_sql = "SELECT COUNT(keywords) FROM keywords_from_userinfo WHERE keywords LIKE '% ".
								$stimulus_word . " %';";
		$total_window_size = query_count($total_window_size_sql);
		$residual_window_size = $total_window_size - $window_frequency;
		
		$total_corpus_size_sql = "SELECT COUNT(keywords) FROM keywords_from_userinfo";
		$total_corpus_size = query_count($total_corpus_size_sql);
		$residual_corpus_size = $total_corpus_size - $total_window_size - $cooccurring_word_total_frequency + $window_frequency;
		
		$ratio = 0;
		if($window_frequency == 0){
			$ratio = 0;
		}
		else if($residual_frequency == 0 || $residual_window_size == 0){
			$ratio = 1000; // represent infinite
		}
		else{
			$ratio = 2 * ($window_frequency * log($window_frequency) + $residual_window_size * log($residual_window_size) + 
			              $residual_frequency * log($residual_frequency) + $residual_corpus_size * log($residual_corpus_size) + 
						  $total_corpus_size * log($total_corpus_size) - 
						  ($window_frequency + $residual_window_size) * log($window_frequency + $residual_window_size) - 
						  ($window_frequency + $residual_frequency) * log($window_frequency + $residual_frequency) - 
						  ($residual_window_size + $residual_corpus_size) * log($residual_window_size + $residual_corpus_size) - 
						  ($residual_frequency + $residual_corpus_size) * log($residual_frequency + $residual_corpus_size));						  
		}
		
		
		$result['window_frequency'] = $window_frequency;
		$result['total_window_size'] = $total_window_size;
		$result['residual_window_size'] = $residual_window_size;
		$result['cooccurring_word_total_frequency'] = $cooccurring_word_total_frequency;
		$result['residual_frequency'] = $residual_frequency;
		$result['residual_corpus_size'] = $residual_corpus_size;
		$result['total_corpus_size'] = $total_corpus_size;
		$result['ratio'] = $ratio;
		return $result;
	}
	
	function chi_square($A, $B, $C, $D){
		$sum = $A + $B + $C + $D;
		$T11 = (($A + $B) * ($A + $C)) / $sum;
		$T12 = (($A + $B) * ($B + $D)) / $sum;
		$T21 = (($A + $C) * ($C + $D)) / $sum;
		$T22 = (($B + $D) * ($C + $D)) / $sum;
		$chi_square = (pow(($A - $T11), 2) / $T11 + 
					  pow(($B - $T12), 2) / $T12 +
					  pow(($C - $T21), 2) / $T21 +
					  pow(($D - $T22), 2) / $T22);
		return $chi_square;
	}
	
	function query_count($sql){
		$sql_result = mysql_query($sql);
		if(!$sql_result){
			echo "$sql <br>";
			echo mysql_error() . '<br>';
		}
		$row = mysql_fetch_array($sql_result);
		return $row[0];
	}
?>