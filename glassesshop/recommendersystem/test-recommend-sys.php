<?php
	include_once 'keyword-recommender-system.php';
	
	$keywords = "cheap";
	$test_recommender_sys = new KeywordRecommenderSystem();
	//$test_recommender_sys->wordAssociationWithJaccardPreprocess(0.2);
	$test_recommender_sys->recommend(1, $keywords);
