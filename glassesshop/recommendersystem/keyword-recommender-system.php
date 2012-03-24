<?php
	include_once "../interface/recsys-interface.php";
	include_once "../database/glass-database-manager.php";
	include_once "word-segmenter.php";
	include_once "keyword-recommender.php";
	
	define("KEY_LINK_JACCARD",1);
	define("KEY_COL_SLOPEONE",2);
	
	class KeywordRecommenderSystem implements iKeywordRecommenderSystem{
		private $dm;
		private $re;
		
		public function __construct(){
			$this->dm = GlassDatabaseManager::getInstance();
			$this->re = new KeywordRecommender();
		}
		
		public function wordAssociationWithJaccardPreprocess($threshold){
			$this->dm->query("BEGIN");
			$this->dm->query("truncate keyword_link");
			$result = $this->dm->query("SELECT keyword,count FROM keyword where count > 1");
			$keyword_count = array();
			
			if(!$result){
			    die('no result available');
			}else{
				while($row = mysql_fetch_array($result)){
					$keyword_count[$row['keyword']] = $row['count']; 
				}
			    foreach($keyword_count as $key => $count){
			    	foreach($keyword_count as $key1 => $count1){
			    		if($key != $key1 && $key != null && $key1 != null){
				    		$nAB = mysql_num_rows($this->dm->query("select id from query where query like '%".$key."%".$key1."%' or query like '%".$key1."%".$key."%'"));
				    		if($count + $count1 - $nAB != 0)
				    			$jaccard = $nAB/($count + $count1 - $nAB);
				    		else
				    			$jaccard = 1;
				    		if($jaccard > $threshold){
				       	 		$this->dm->query("INSERT INTO keyword_link(keyword, keyword_expand, link) VALUE ('".$key."', '".$key1."','".$jaccard."')");
				    		}
			    		}
			    	}
			    }
			 }
			 $this->dm->query("COMMIT");
		}
		
		public function collaborativeFilteringWithSlopeOne($table_name){
			
		}
		
		public function addRecommender($recommender, $factor = ''){
			if($recommender == KEY_LINK_JACCARD){
				KeywordRecommenderSystem::wordAssociationWithJaccardPreprocess($factor);
			}
			else if($recommender == KEY_COL_SLOPEONE){
				KeywordRecommenderSystem::collaborativeFilteringWithSlopeOne($factor);
			}
			else
				;
		}
	
	    public function adjustWeight($recommender, $keywords= '', $weightArray = '', $factor = ''){
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
				echo "<br />------------------------------------------------------------<br />";
				print_r($weightArray);
				echo "<br />------------------------------------------------------------<br />";
				return $weightArray;				
			}
	    }
	
	    public function removeRecommender($name){
	    	
	    }
	
	    public function recommend($recommender, $keywords){
	    	if($recommender == KEY_LINK_JACCARD){
	    		$weightArray = $this->re->recommend($keywords);
	    		$this->adjustWeight(1,$keywords,$weightArray,0.001);
	    	}	
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