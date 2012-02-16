<?php
	include_once 'database_manager.php';
	
	$db = DatabaseManager::connectDB();
	
	//empty table keyword
	DatabaseManager::query("TRUNCATE table keyword");
	
	$stopword_list = loadStopwords("stopword.txt");
	
	$keywords_set = DatabaseManager::query("SELECT id, keywords FROM preprocessed_user");
	while($keywords_row = mysql_fetch_array($keywords_set)){
		$preprocessed_keywords = preprocess_keywords($keywords_row['keywords']);
		DatabaseManager::query("UPDATE preprocessed_user SET keywords = '" . addslashes(' ' . implode(" ", $preprocessed_keywords) . ' ') .
						       "' WHERE id = {$keywords_row['id']}");
		foreach($preprocessed_keywords as $preprocessed_keyword){
			DatabaseManager::query("INSERT INTO keyword(keyword) VALUE('" . addslashes($preprocessed_keyword) . "')");
		}
	}
	
	DatabaseManager::query("TRUNCATE table keyword_train");
	
	$keywords_set_train = DatabaseManager::query("SELECT id, keywords FROM preprocessed_user_train");
	while($keywords_row_train = mysql_fetch_array($keywords_set_train)){
		$preprocessed_keywords_train = preprocess_keywords($keywords_row_train['keywords']);
		DatabaseManager::query("UPDATE preprocessed_user_train SET keywords = '" . addslashes(' ' . implode(" ", $preprocessed_keywords_train) . ' ') .
						       "' WHERE id = {$keywords_row['id']}");
		foreach($preprocessed_keywords_train as $preprocessed_keyword_train){
			DatabaseManager::query("INSERT INTO keyword_train(keyword) VALUE('" . addslashes($preprocessed_keyword_train) . "')");
		}
	}
	
	DatabaseManager::closeDB($db);
	
	/**
	  * split the keywords, remove the stopword, extract word stem from inflected variants
	  */
	function preprocess_keywords($keywords){
		global $stopword_list;
		$keywords = preg_replace("/[,&\+\/-]/", ' ', $keywords);
		$keys = preg_split('@ @', $keywords, NULL, PREG_SPLIT_NO_EMPTY);
		$result = array();
		foreach($keys as $key){
			if(!in_array($key, $stopword_list)){
				// remove numeric string, I'm not sure whether it is proper
				if($key != '' && !is_numeric($key)){
					$result[] = $key;
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