<?php
	/** preprocess the keywords extracted from the table userinfo
	  * including : remove the stopword, extract word stem from inflected variants
	  */
	include "class.stemmer.inc.php";  
	$stemmer = new Stemmer();
	
	// $stopword_list = array('for', 'of', 'in', 'it', 'online', 'is', 'on', 'and', 'at', 'with');
	$stopword_list = loadStopwords("stopword.txt");
	$map_dictionary = array("woman" => "women", "woman's" => "women", "women's" => "women",
							"man" => "men", "man's" => "men", "men's" => "men",
							"bags" => "bag");
						
	// connect to mysql
	ini_set("max_execution_time",2400);
	$db = mysql_connect("localhost", "recsys-nju", "recsys-nju");
	mysql_select_db("bagsok", $db);
	
	$keywords_set = mysql_query("SELECT * FROM keywords_from_userinfo");
	assert('$keywords_set != false');
	while($keywords_row = mysql_fetch_array($keywords_set)){
		$preprocessed_keywords = preprocess_keywords($keywords_row['keywords']);
		
		$query_sql = "UPDATE keywords_from_userinfo SET keywords = '" . addslashes(' ' . implode(" ", $preprocessed_keywords) . ' ') .
					 "' WHERE id = " . $keywords_row['id'] . ";";
		$query_result = mysql_query($query_sql);
		if(!$query_result) { echo $query_sql; echo "<br>"; echo mysql_error(); echo "<br>"; }
		
		foreach($preprocessed_keywords as $preprocessed_keyword){
			$query_sql = "INSERT INTO keyword (keyword) VALUES ('" . addslashes($preprocessed_keyword) . "');";
			$query_result = mysql_query($query_sql);
			if(!$query_result) { echo $query_sql; echo "<br>"; echo mysql_error(); echo "<br>"; }
		}
	}
	
	mysql_close($db);
	
	
	/**
	  * split the keywords, remove the stopword, extract word stem from inflected variants
	  */
	function preprocess_keywords($keywords){
		global $stopword_list;
		global $map_dictionary;
		global $stemmer;
		$keywords = preg_replace("/[,\+\/-]/", ' ', $keywords);
		$keys = preg_split('@ @', $keywords, NULL, PREG_SPLIT_NO_EMPTY);
		$result = array();
		foreach($keys as $key){
			if(!in_array($key, $stopword_list)){
				if(array_key_exists($key, $map_dictionary)){
					$result[] = $map_dictionary[$key];
				}
				else{
					$key = $stemmer->stem($key);
					// remove numeric string, I'm not sure whether it is proper
					if($key != '' && !is_numeric($key)){
						$result[] = $key;
					}
				}
			}
		}
		return $result;
	}
	
	/**
	  * load the stopword list from the file stopword.txt
	  * return the array containing the stopwords
	  */
	function loadStopwords($filePath){
		$file_handle = fopen($filePath, "r");
		$stopwords = array();
		while(!feof($file_handle)){
			$line = fgets($file_handle);
			$line = rtrim($line, "\r\n");
			$stopwords[] = $line;
		}
		fclose($file_handle);
		return $stopwords;
	}
?>