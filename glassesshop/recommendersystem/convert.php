<?php
	$connection = mysql_connect($host = "localhost", $username = "root", $password = "");
	mysql_select_db("glassesshop");
	
	$item = array();
	$user = array();
	
	$item_results = mysql_query("select * from item");
	while($item_row = mysql_fetch_array($item_results)){
		$item[$item_row['id']] = $item_row['id_no'];
	}
	$user_results = mysql_query("select * from keyword");
	while($user_row = mysql_fetch_array($user_results)){
		$user[$user_row['keyword']] = $user_row['id'];
	}
	
	$pair_results = mysql_query("select * from keyword_item_weight");
	while($pair_row = mysql_fetch_array($pair_results)){
		mysql_query("insert into oso_user_ratings values(".$user[$pair_row['keyword']].",".$item[$pair_row['item']].",".$pair_row['weight'].")");
	}