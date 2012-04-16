<?php
	include_once "../interface/recsys-interface.php";
	include_once "../database/glass-database-manager.php";

	class RandomRecommender implements iKeywordRecommender{
		private $dm;

		public function __construct(){
			$this->dm = GlassDatabaseManager::getInstance();
		}

		public function preprocess($tables, $startTime = null){

		}

		public function cleanup(){

		}

		public function recommend($keywords){
			$items = array();
			$item_result = $this->dm->query("SELECT * FROM item ORDER BY RAND() limit 100"); // I think 100 is enough
			while($item_row = mysql_fetch_array($item_result)){
				$items[$item_row['name']] = 1; // every item has the same weight
			}

			return $items;
		}
	}
?>