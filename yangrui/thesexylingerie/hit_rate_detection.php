<?php
	require 'search.php';
	
	/** 可修改参数:测试时确定取推荐列表的前几位 默认20位 */
	$test_factor = 20;

	$con = mysql_connect($db_host , $db_user, $db_pass);
	if(!$con){
	    die(mysql_error());
	}
	mysql_select_db('thesexylingerie_test');
	
	$result = mysql_query("SELECT * FROM preprocessed_user_test");
	$all = 0;//记录有效测试用户数
	$hit_num = 0;//记录测试集中实际浏览过推荐列表中的商品的用户数量
	
	if(!$result){
	    die('no result available');
	}
	else{
		while($row = mysql_fetch_array($result)){
			$all++;
			$userid = $row['userid'];
			$date = $row['date'];
			$keywords = $row['keywords'];
			
			echo $userid." ".$date."<br />";
			
			$product = recommendation_list($keywords);
			
			$page_result = mysql_query("select page from visit where userid = ".$userid);
			while($page_row = mysql_fetch_array($page_result)){
				//echo $userid." ".$page_row[0]."<br />";
				$i = 0;
				foreach($product as $p_name => $p_weight){
					$hit_break = false;
					$i++;
					if($i > $test_factor)
						break;
					if($p_name == $page_row[0])
					{
						$hit_num++;
						echo "hit!<br />";
						$hit_break = true;
						break;
					}
				}
				if($hit_break)
					break;
			}
		}
	}
	echo "hit_rate = ".$hit_num/$all;
	
	
	