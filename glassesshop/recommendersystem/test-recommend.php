<?php
	include_once 'keyword-recommender.php';
	
	$keywords = "discount designer eyeglass frames";
	$test_recommend = new KeywordRecommender();
	$test_recommend->recommend($keywords);