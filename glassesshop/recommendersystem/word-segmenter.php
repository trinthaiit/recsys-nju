<?php
	include_once "../interface/recsys-interface.php";
	
	class WordSegmenter implements iWordSegmenter{
		public function segmentWords($sentence){
			$keywords = explode(' ', $sentence);
			return $keywords;
		}
	}