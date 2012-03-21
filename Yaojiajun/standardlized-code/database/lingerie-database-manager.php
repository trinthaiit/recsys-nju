<?php
	include_once "base-database-manager.php";

	class LingerieDatabaseManager extends DatabaseManager{
		private static $dm;

		public static function getInstance(){
			if(!self::$dm){
				self::$dm = new self();
			}
			return self::$dm;
		}

		private function __construct(){
			$this->connect("localhost", "root", "yaojiajun"); // connect database
			$this->useDatabase("apriori_test"); // use "thesexylingerie" database
		}
	}

?>