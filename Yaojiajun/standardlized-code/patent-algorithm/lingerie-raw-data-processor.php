<?php
	include_once "../interface/recsys-interface.php"
	include_once "../database/database-manager.php"

	class LingerieRawDataProcessor implements iRawDataProcessor{
		private $dm;
		private $product_identifier;

		public function __construct(){
			$dm = LingerieDatabaseManager::getInstance();
			$product_identifier = "http://www.thesexylingerie.co.uk/product/";
		}

		public function processRawData(){
			$dm->createCommonTables();

			// init recsys_item table
			$query = "SELECT page FROM visit where page LIKE '{$product_identifier}%'";
			$page_result = $dm->query($query);
			while($row = mysql_fetch_array($page_result)){
				$item_name = str_replace($product_identifier, "", $row[0]);
				$query = "INSERT "
			}
		}

	}
?>