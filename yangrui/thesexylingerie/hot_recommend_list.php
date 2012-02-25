<?php
	require 'dbconfig.php';

	$product_identifier = "http://www.thesexylingerie.co.uk/product/";
	$test_factor = 5;

	$con = mysql_connect($db_host , $db_user, $db_pass);
	if(!$con){
	    die(mysql_error());
	}
	mysql_select_db('thesexylingerie_test');
	
	$all = 0;
	$hit_num = 0;
	
	$result = mysql_query("select count(page),page from visit where page LIKE '{$product_identifier}%' group by page
					order by count(page) desc");
	$rec_list = array();
	for($i = 0; $i< $test_factor; $i++){
		$row = mysql_fetch_array($result);
		$rec_list[$i] = $row['page'];
	}
		
	$user_result = mysql_query("SELECT distinct userid FROM preprocessed_user_test");
	while($user_row = mysql_fetch_array($user_result)){
		$all++;
		$hit_break = false;
		$userid = $user_row[0];
		$page_result = mysql_query("select page from visit where page LIKE '{$product_identifier}%' and userid = ".$userid);
		if(mysql_num_rows($page_result) == 0)
			$all--;
		else{
			while ($page_row = mysql_fetch_array($page_result)){
				foreach ($rec_list as $key=>$value){
					if($page_row[0] == $value){
						$hit_break = true;
						$hit_num++;
						break;
					}
				}
				if($hit_break)
				 	break;
			}
		}
	}
	
	echo "hit_rate = ".$hit_num."/".$all." = ".$hit_num/$all;