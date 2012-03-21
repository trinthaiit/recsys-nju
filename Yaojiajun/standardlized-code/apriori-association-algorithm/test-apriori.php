<?php
	include_once "../database/lingerie-database-manager.php";
	include_once "apriori-association-keyword-recommender.php";

	$dm = LingerieDatabaseManager::getInstance();
	$recommender = new AprioriAssociationKeywordRecommender($dm);
	$recommender->preprocess();
?>