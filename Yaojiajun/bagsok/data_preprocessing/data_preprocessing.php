<?php
	/** preprocess the keywords extracted from the table userinfo
	  * including : remove the stopword, extract word stem from inflected variants
	  */
	$stopword_list = array('for', 'of', 'in', 'it', 'online');
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
		
		$query_sql = "UPDATE keywords_from_userinfo SET keywords = '" . addslashes(implode(" ", $preprocessed_keywords)) .
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
		
		$keys = explode(" ", $keywords);
		$result = array();
		foreach($keys as $key){
			if(!in_array($key, $stopword_list)){
				if(array_key_exists($key, $map_dictionary)){
					$result[] = $map_dictionary[$key];
				}
				else{
					$result[] = $key;
				}
			}
		}
		return $result;
	}

?>