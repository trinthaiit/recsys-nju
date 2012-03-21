<?php
/**
 * Extract search engine query keywords from given url.
 * @author: kavinyao@gmail.com
 */
$params = array('q', 'p', 'query', 'wd', 'searchFor', 'text');

function __extract_keywords($url){
    $query_str = parse_url($url, PHP_URL_QUERY);
    parse_str($query_str, $queries);

    global $params;
    foreach($params as $param){
        if(isset($queries[$param]))
            return $queries[$param];
    }

    return '';
}

function extract_keywords($url){
    mb_internal_encoding('UTF-8');
    return mb_strtolower(__extract_keywords($url));
}
?>
