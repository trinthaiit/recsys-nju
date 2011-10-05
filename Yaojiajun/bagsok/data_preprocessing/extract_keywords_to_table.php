<?php
	include 'extract_keywords.php';
	
	// connect to mysql
	ini_set("max_execution_time",2400);
	$db = mysql_connect("localhost", "recsys-nju", "recsys-nju");
	mysql_select_db("bagsok", $db);
	
	// empty table keywords_from_userinfo
	mysql_query("TRUNCATE table 'keywords_from_userinfo';");
	
	// fetch refer from table userinfo, extract keywords
	$refers = mysql_query("select refer from userinfo where refer is not null and refer <> '' and refer not like '%mbaobao%'");
	$keywords_num = 0;
	while($row = mysql_fetch_array($refers)){
		$keyword = extract_keywords($row['refer']);
		$keyword = addslashes($keyword);
		if($keyword != ''){
			$insert_sql = "insert into keywords_from_userinfo (keywords) values('".$keyword."')";
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
	echo 'keywords_num = '.$keywords_num;
	mysql_close($db);
	
?>
