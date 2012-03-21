<?php
	include_once "base-database-manager.php";

	class GlassDatabaseManager extends DatabaseManager{
		private static $dm;

		public static function getInstance(){
			if(!self::$dm){
				self::$dm = new self();
			}
			return self::$dm;
		}

		private function __construct(){
			$this->connect("localhost", "glass", "glass"); // connect database
			$this->useDatabase("glassesshop"); // use "glassesshop" database
		}
	}
?>