<?php
	include_once "../interface/recsys-interface.php";
	include_once "../database/glass-database-manager.php";

	class PerfectRecommender implements iKeywordRecommender{
		private $dm;
		private $dummy;

		public function __construct($config){
			$this->dm = GlassDatabaseManager::getInstance();
			$this->dummy = array();
		}

		public function preprocess($tables, $startTime = null){
			// get one hundred dummy items
			$item_result = $this->dm->query("SELECT name FROM item LIMIT 100");
			while($item_row = mysql_fetch_array($item_result)){
				$this->dummy[$item_row['name']] = -1;
			}
		}

		public function cleanup(){

		}

		public function recommend($keywords, $queryId){
			$recommend_items = array();
			$item_result = $this->dm->query("SELECT * FROM query_item WHERE queryId = {$queryId}");
			while($item_row = mysql_fetch_array($item_result)){
				$recommend_items[$item_row['itemId']] = $item_row['bought'];
			}
			$recommend_items = $recommend_items + $this->dummy;

			arsort($recommend_items);
			return $recommend_items;
		}
	}
?>