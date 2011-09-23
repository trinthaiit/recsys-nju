<?php
	class DatabaseManager{
		public static function connectDB(){
			ini_set("max_execution_time",2400);
			$db = mysql_connect("localhost", "recsys-nju", "recsys-nju");
			mysql_select_db("bagsok", $db);
			return $db;
		}
		
		public static function closeDB($db){
			mysql_close($db);
		}
	}
?>