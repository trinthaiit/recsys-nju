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
	
		public function recommend($keywords){
			// pseudo-code
			//$recommendItems = array(); // each element is an array, stands for each recommender's recommend
			$finalRecList = array();
			foreach($this->recommenders as $name=>$recommender){
				//$recommendItems[] = $recommender->recommend($keywords);
				$weightArrayTemp = $recommender->recommend($keywords);
				foreach($weightArrayTemp as $p_name => $p_weight){
					if(isset($finalRecList[$p_name]))
						$finalRecList[$p_name] += $p_weight*$this->weights[$name];
					else
						$finalRecList[$p_name] = $p_weight*$this->weights[$name];
				}
			}
			//print_r($finalRecList);
			return $finalRecList;
		}
	}
	