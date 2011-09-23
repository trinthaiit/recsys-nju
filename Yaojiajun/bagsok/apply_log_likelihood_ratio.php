<?php
	include 'words_association_log_likelihood_ratio.php';
	include 'database_manager.php';
	
	$db = DatabaseManager::connectDB();
	$keywords_set = mysql_query("SELECT keyword FROM keyword;");
	$row_number = mysql_num_rows($keywords_set);
	echo "<table border='1px'>";
	echo "<tr><th>word1</th><th>word2</th><th>ratio</th><th>chi square</th></tr>";
	for($i = 0; $i < $row_number; $i++){
		for($j = $i + 1; $j < $row_number; $j++){
			mysql_data_seek($keywords_set, $i);
			$row = mysql_fetch_row($keywords_set);
			$word1 = $row[0];
			echo $word1;
			mysql_data_seek($keywords_set, $j);
			$row = mysql_fetch_row($keywords_set);
			$word2 = $row[0];
			$result = compute_ratio($word1, $word2);
			$chi_square = chi_square($result['window_frequency'], $result['residual_window_size'], 
								     $result['residual_frequency'], $result['residual_corpus_size']);
			echo "<tr><td>$word1</td><td>$word2</td><td>$result[ratio]</td><td>$chi_square</td></tr>";
		}
	}
	echo "</table>";
	DatabaseManager::closeDB($db);
?>