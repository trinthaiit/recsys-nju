<?php
	include_once "../interface/recsys-interface.php";
	include_once "../database/glass-database-manager.php";
	include_once "word-segmenter.php";
	include_once "keyword-recommender.php";
	include_once "OpenSlopeOne.php";
	
	define("KEY_LINK_JACCARD",1);
	define("KEY_COL_SLOPEONE",2);
	
	class KeywordRecommenderSystem implements iKeywordRecommenderSystem{
		private $dm;
		private $re;
		private $KEY_LINK;
		private $KEY_COL;
		private $user;
		private $item;
		
		public function __construct(){
			$this->dm = GlassDatabaseManager::getInstance();
			$this->re = new KeywordRecommender();
			$this->KEY_COL = 0;
			$this->KEY_LINK = 0;
			$user_results = $this->dm->query("select * from keyword");
			while($user_row = mysql_fetch_array($user_results)){
				$this->user[$user_row['keyword']] = $user_row['id'];
			}
			$item_results = $this->dm->query("select * from item");
			while($item_row = mysql_fetch_array($item_results)){
				$this->item[$item_row['id']] = $item_row['name'];
			}
		}
		
		public function addRecommender($recommender, $factor){
			if($recommender == KEY_LINK_JACCARD){
				//KeywordRecommenderSystem::wordAssociationWithJaccardPreprocess(0.2);
				$this->KEY_LINK = $factor;
			}
			else if($recommender == KEY_COL_SLOPEONE){
				//KeywordRecommenderSystem::collaborativeFilteringWithSlopeOne();
				$this->KEY_COL = $factor;
			}
			else
				;
		}
		
		 public function adjustWeight($recommender, $keywords= '', $weightArray = '', $factor = ''){
		 	
		 }
	
	    public function makeCombineRecList($recommender, $keywords= '', $weightArray = '', $factor = ''){
	    	if($recommender == KEY_LINK_JACCARD){
				$expand_keywords = KeywordRecommenderSystem::fetch_expand_key($keywords);
				foreach ($expand_keywords as $expand_key) {
					$expand_weight = KeywordRecommenderSystem::fetch_product_weight($expand_key);
					foreach($expand_weight as $p_name => $p_weight){
						if(isset($weightArray[$p_name]))
							$weightArray[$p_name] += $p_weight*$factor;
						else
							$weightArray[$p_name] = $p_weight*$factor;
					}
				}
				arsort($weightArray);
				// echo "<br />------------------------------------------------------------<br />";
				// //print_r($weightArray);
				// echo "<br />------------------------------------------------------------<br />";
				return $weightArray;				
			}
			else if($recommender == KEY_COL_SLOPEONE){
				$keywords = array_unique(explode(' ', $keywords));
				$openslopeone = new OpenSlopeOne();
		
				foreach ($keywords as $key){
					if(key_exists($key, $this->user)){
						$weightArrayTemp = $openslopeone->getRecommendedItemsByUser($this->user[$key]);
						if($weightArrayTemp != NULL){
							foreach($weightArrayTemp as $p_name => $p_weight){
								if(isset($weightArray[$this->item[$p_name]]))
									$weightArray[$this->item[$p_name]] += $p_weight*$factor;
								else
									$weightArray[$this->item[$p_name]] = $p_weight*$factor;
							}
						}
					}
				}
				arsort($weightArray);
				// echo "<br />------------------------------------------------------------<br />";
				// //print_r($weightArray);
				// echo "<br />------------------------------------------------------------<br />";
				return $weightArray;
			}
	    }
	
	    public function removeRecommender($recommender){
	    	if($recommender == KEY_LINK_JACCARD){
				$this->KEY_LINK = 0;
			}
			else if($recommender == KEY_COL_SLOPEONE){
				$this->KEY_COL = 0;
			}
			else
				;
	    }
	
	    public function recommend($keywords){
	    	$weightArray = $this->re->recommend($keywords);
	    	if($this->KEY_LINK > 0 && $this->KEY_COL <= 0){	
	    		$weightArray = $this->makeCombineRecList(KEY_LINK_JACCARD,$keywords,$weightArray,$this->KEY_LINK);
	    	}
	    	if($this->KEY_COL > 0 && $this->KEY_LINK <= 0){
	    		$weightArray = $this->makeCombineRecList(KEY_COL_SLOPEONE,$keywords,$weightArray,$this->KEY_COL);
	    	}
	    	if($this->KEY_COL > 0 && $this->KEY_LINK > 0){
	    		$weightArray = $this->makeCombineRecList(KEY_LINK_JACCARD,$keywords,$weightArray,$this->KEY_LINK);
	    		$weightArray = $this->makeCombineRecList(KEY_COL_SLOPEONE,$keywords,$weightArray,$this->KEY_COL);
	    	}
	    	return $weightArray;	
	    }
	    
	    public function fetch_expand_key($str){
	    	$this->dm->query("BEGIN");
	    	$keywords = array_unique(explode(' ', $str));
	    	$expand_keywords = array();
	    	foreach ($keywords as $key){
	    		$expand_results = $this->dm->query("select keyword_expand from keyword_link where keyword = '".$key."'");
	    		while($expand_row = mysql_fetch_array($expand_results)){
	    			if(!in_array($expand_row[0], $keywords))
	    				$expand_keywords[] = $expand_row[0];
	    		}
	    	}
	    	$expand_keywords = array_unique($expand_keywords);
	    	$this->dm->query("COMMIT");
	    	//print_r($expand_keywords);
	    	echo "<br />";
	    	return $expand_keywords;
	    }
	    
		public function fetch_product_weight($str){		
			$product = array();
			$result = $this->dm->query("select * from keyword_item_weight where keyword = '".$str."'");
			while ($row = mysql_fetch_array($result)){
				if(isset($product[$row['item']]))
					$product[$row['item']] += $row['weight'];
				else
					$product[$row['item']] = $row['weight'];
			}
			return $product;
		}
	}