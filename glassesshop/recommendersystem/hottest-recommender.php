<?php
	include_once "../interface/recsys-interface.php";
	include_once "../database/glass-database-manager.php";

	class HottestRecommender implements iKeywordRecommender{
		private $dm;
		private $hottestItems;

		public function __construct(){
			$this->dm = GlassDatabaseManager::getInstance();
			$this->hottestItems = array();
		}

		public function preprocess($tables, $startTime = null){
			// get one hundred hottest items
			$item_result = $this->dm->query("SELECT pageinfo item, count(id) item_count FROM visit WHERE pagetype = 'product' AND pageinfo <> '' AND userId NOT IN (SELECT userId FROM {$tables['query_test']}) GROUP BY pageinfo ORDER BY count(id) DESC ");
			while($item_row = mysql_fetch_array($item_result)){
				$this->hottestItems[$item_row['item']] = $item_row['item_count']; // use count as weight, sort is handled by DBMS
			}
		}

		public function cleanup(){

		}

		public function recommend($keywords){
			return $this->hottestItems;
		}
	}

?>