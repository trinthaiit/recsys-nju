<?php
	include_once "../interface/recsys-interface.php";
	include_once "glass-query-extractor.php";
	include_once "../database/glass-database-manager.php";

	class GlassRawDataProcessor implements iRawDataProcessor{
		private $dm;
		private $qe;
		private $delimiter;
		public function __construct(){
			$this->dm = GlassDatabaseManager::getInstance();
			$this->qe = new GlassQueryExtractor();
			$this->delimiter = " ";
		}

		public function processRawData(){
			$this->__generate_tables();
			$this->__fill_query_table();
			$this->__fill_item_table();
			$this->__fill_query_item_table();
		}

		private function __generate_tables(){
			$this->dm->executeSqlFile("tables.sql");
		}

		private function __fill_query_table(){
			$this->dm->query("BEGIN");
			$querys = $this->dm->query("SELECT userid, refer FROM user WHERE refer IS NOT NULL AND refer <> '' AND refer <> 'null'");			
			while($row = mysql_fetch_array($querys)){
				$keyword_string = $this->qe->extractQuery($row['refer'], $this->delimiter);
				if($keyword_string != ""){
					$insert_sql = "INSERT INTO Query (userId, query) VALUES ('{$row['userid']}', '{$keyword_string}')";
					$this->dm->query($insert_sql);				
				}
			}
			$this->dm->query("COMMIT");
		}

		private function __fill_item_table(){
			$this->dm->query("BEGIN");
			$items = $this->dm->query("SELECT pageinfo FROM visit WHERE pagetype = 'product'");			
			while($row = mysql_fetch_array($items)){
				$insert_sql = "INSERT INTO Item (name) VALUES ('{$row['pageinfo']}')";
				$this->dm->query($insert_sql, true);
			}
			$this->dm->query("COMMIT");
		}

		private function __fill_query_item_table(){
			$this->dm->query("BEGIN");
			$querys = $this->dm->query("SELECT id, userId FROM Query");			
			while($row = mysql_fetch_array($querys)){
				$userId = $row['userId'];
				$items = $this->dm->query("SELECT pageinfo FROM visit WHERE pagetype = 'product' AND userId = '{$userId}'");
				while($item_row = mysql_fetch_array($items)){
					$orders = $this->dm->query("SELECT count(id) FROM orderrecord WHERE userid = '{$userId}' AND item = '{$item_row['pageinfo']}'");
					$order_num = mysql_fetch_array($orders);
					$order_num = $order_num[0];
					if($order_num != 0){
						$bought = 2;
					} 
					else{
						$bought = 0;
					}
					$insert_sql = "INSERT INTO Query_Item (queryId, itemId, bought) VALUES ({$row['id']}, '{$item_row['pageinfo']}', {$bought})";
					$this->dm->query($insert_sql);
				}
			}
			$this->dm->query("COMMIT");
		}
	}
?>