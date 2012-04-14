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
	$col_recommend = new KeywordRecommender(KEY_COL_SLOPEONE);
	//$test_recommend->preprocess($tables);
	$test_recommender_sys = new KeywordRecommenderSystem();
	//$test_recommend->wordAssociationWithJaccardPreprocess(0.2,$tables);// 0.2 is the jaccard factor
	//$test_recommend->collaborativeFilteringWithSlopeOnePreprocess();
	
	//$test_recommender_sys->addRecommender(KEY_COL_SLOPEONE, 0.001);
	$test_recommender_sys->addRecommender("", 1 ,$test_recommend);
	$test_recommender_sys->addRecommender(KEY_COL_SLOPEONE, 0.001 ,$col_recommend);
	$list = $test_recommender_sys->recommend($keywords);
	//print_r($list);