<?php
	include_once 'extract_keywords.php';
	include_once 'database_manager.php';
	
	$db = DatabaseManager::connectDB();
	
	$train_factor = 0.8;
	
	//empty table preprocessed_user
	DatabaseManager::query("TRUNCATE table preprocessed_user");
	
	// fetch refer from table userinfo, extract keywords
	$refers = mysql_query("select userid, date, refer from user where refer is not null and refer <> '' ");
	$keywords_num = 0;
	while($row = mysql_fetch_array($refers)){
		$keyword = extract_keywords($row['refer']);
		$keyword = addslashes($keyword);
		if($keyword != '' && !is_numeric($keyword)){
			$insert_sql = "insert into preprocessed_user (userid, date, keywords) values('{$row['userid']}', '{$row['date']}', '{$keyword}')";
			$insert_result = mysql_query($insert_sql);
			if($insert_result){ // insert successfully
				$keywords_num++;
			}
			else{
				echo $insert_sql;
				echo '<br>';
				echo mysql_error();
				echo '<br>';
			}
		}
	}
	
	DatabaseManager::query("TRUNCATE table preprocessed_user_train");
	DatabaseManager::query("insert into preprocessed_user_train select * from preprocessed_user where id <=".$keywords_num*$train_factor."");
	DatabaseManager::query("TRUNCATE table preprocessed_user_test");
	DatabaseManager::query("insert into preprocessed_user_test select * from preprocessed_user where id >".$keywords_num*$train_factor."");
	
	echo 'keywords_num = '.$keywords_num;	
	
	DatabaseManager::closeDB($db);
	
?>
	