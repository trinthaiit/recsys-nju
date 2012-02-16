<?php
	require 'extract_keywords.php';
	require 'dbconfig.php';
	
	$con = mysql_connect($db_host , $db_user, $db_pass);
	
	if(!$con){
		die(mysql_error());
	}
	
	mysql_select_db("thesexylingerie");
	$result = mysql_query("select refer from user");
	if(!$result){
		die("no result!");
	}
	
	$all = 0;
	$available_count = 0;
	$id = 507;
	mysql_query("update user set keyword = null");
	while($row = mysql_fetch_array($result)){
		$search_url = $row[0];
		$keyword_str = extract_keywords($search_url);
		
		/* digital and punctuation filter*/
		$keyword_str = preg_replace('/\s/',' ',preg_replace("/[[:punct:]]/",' ',strip_tags(html_entity_decode(str_replace(array('£¿','£¡','£¤','£¨','£©','£º','¡®','¡¯','¡°','¡±','¡¶','¡·','£¬','¡­','¡£','¡¢','nbsp','Â£','-'),'',$keyword_str),ENT_QUOTES,'UTF-8'))));
		$keyword_str = preg_replace("/[0-9]/", "", $keyword_str);
		
		mysql_query("update user set keyword = '".$keyword_str."' where id = '".$id."'");
		$id++;
			
		if($keyword_str != "" && $keyword_str != "sexy lingerie"){			
			echo $keyword_str."<br />";
			$available_count++;
		}
		$all++;	
	}
	echo $all." ".$available_count;