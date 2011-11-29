<?php
	class DatabaseManager{
		public static function connectDB(){
			ini_set("max_execution_time",3600);
			$db = mysql_connect("localhost", "root", "");
			mysql_select_db("thesexylingerie", $db);
			return $db;
		}
		
		public static function query($query_string){
			$result = mysql_query($query_string);
			if(!$result){
				echo $query_string;
				echo "<br>";
				echo mysql_error();
				echo "<br>";
			}
			return $result;
		}
		
		public static function closeDB($db){
			mysql_close($db);
		}
	}
?>