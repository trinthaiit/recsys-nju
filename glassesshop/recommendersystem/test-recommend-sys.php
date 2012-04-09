<?php
	include_once '../recommendersystem/keyword-recommender.php';
	include_once '../recommendersystem/keyword-recommender-system.php';
	/**
	 * wordAssociationWithJaccardPreprocess() and preprocess() involve a large number of calculations
	 * so you only need to use it in the first run
	 */ 
	 
	$keywords = "discount designer eyeglass frames";
	$tables = array();
	$tables['query'] = "query";
	$tables['query_item'] = "query_item";
	$test_recommend = new KeywordRecommender();
	//$test_recommend->preprocess($tables);
	$test_recommender_sys = new KeywordRecommenderSystem();
	//$test_recommender_sys->wordAssociationWithJaccardPreprocess(0.2,$tables);// 0.2 is the jaccard factor
	
	$test_recommender_sys->addRecommender(KEY_LINK_JACCARD, 0.001);
	$list = $test_recommender_sys->recommend($keywords);