<?php
	include_once "../interface/recsys-interface.php";
	include_once "../database/glass-database-manager.php";
	include_once "word-segmenter.php";
	include_once "keyword-recommender.php";
	include_once "OpenSlopeOne.php";
	
	class KeywordRecommenderSystem implements iKeywordRecommenderSystem{
		private $recommenders; // an associative array. recommender name is key, iKeywordRecommender is value
		private $weights; // an associative array. recommender name is key, weight is value

		public function __construct(){
			$this->recommenders = array();	
			$this->weights = array();	
		}
		
		public function addRecommender($name, $weight, $recommender){
			$this->recommenders[$name] = $recommender;
			$this->weights[$name] = $weight;
		}
		
		public function adjustWeight($name, $newWeight){
			$this->weights[$name] = $newWeight;
		}
	
		public function removeRecommender($name){
			unset($this->recommenders[$name]);
			unset($this->weights[$name]);
		}
	
		public function recommend($keywords, $queryId){
			assert('count($this->recommenders) == 1');

			foreach($this->recommenders as $name=>$recommender){
				$recommend_items = $recommender->recommend($keywords, $queryId);
			}
			
			return $recommend_items;
		}
	}
	