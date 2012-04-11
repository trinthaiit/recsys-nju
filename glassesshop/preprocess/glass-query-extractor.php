<?php
	include_once "../interface/recsys-interface.php";

	class GlassQueryExtractor implements iQueryExtractor{
		private $params = array('q', 'p', 'query', 'wd', 'searchFor', 'text');
		private $stopwords;

		public function __construct(){
			$this->stopwords = $this->__load_stopwords(__DIR__ . "/stopword.txt");
		}

		public function extractQuery($url, $delimiter = " "){
			mb_internal_encoding('UTF-8');
			$keyword_string = mb_strtolower($this->__extract_keywords($url));
			if($keyword_string != ''){
				// has query
				$preprocessed_keywords = $this->__preprocess_keyword_string($keyword_string);
				$keyword_string = implode($delimiter, $preprocessed_keywords);
			}
			return $keyword_string;
		}

		private function __extract_keywords($url){
			$url = urldecode($url);
		    $query_str = parse_url($url, PHP_URL_QUERY);
		    parse_str($query_str, $queries);
		    
		    foreach($this->params as $param){
		        if(isset($queries[$param]))
		            return $queries[$param];
		    }
		    return '';
		}

		/**
		 * split the keyword_string, remove the stopword, extract word stem from inflected variants
		 */
		private function __preprocess_keyword_string($keyword_string){
			$keyword_string = preg_replace("/[,&\+\/-]/", ' ', $keyword_string);
			$keys = preg_split('@ @', $keyword_string, NULL, PREG_SPLIT_NO_EMPTY);
			$result = array();
			foreach($keys as $key){
				if(!in_array($key, $this->stopwords)){
					// remove numeric string, I'm not sure whether it is proper
					if($key != '' && !is_numeric($key)){
						$result[] = $key;
					}
				}
			}
			return $result;
		}

		/**
		  * load the stopword list from the file stopword.txt
		  * return the array containing the stopwords
		  */
		private function __load_stopwords($filePath){
			$file_handle = fopen($filePath, "r");
			$stopwords = array();
			while(!feof($file_handle)){
				$line = fgets($file_handle);
				$line = rtrim($line, "\r\n");
				$stopwords[] = $line;
			}
			fclose($file_handle);
			return $stopwords;
		}
	}
?>