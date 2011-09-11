<?php
/**
 * search engine keyword param extracing module
 * using regex
 * @author: kavinyao@gmail.com
 */

/**
 * Configurations for search engines
 */
$engine_param_config = array(
    array(
        "domain" => "google",
        "kw" => "q",
        "charset" => "utf-8"
    ),
    array(
        "domain" => "yahoo",
        "kw" => "p",
        "charset" => "utf-8"
    ),
    array(
        "domain" => "bing",
        "kw" => "q",
        "charset" => "utf-8"
    ),
    array(
        "domain" => "exava",
        "kw" => "q",
        "charset" => "utf-8"
    ),
    array(
        "domain" => "thefind",
        "kw" => "query",
        "charset" => "utf-8"
    ),
    array(
        "domain" => "yandex",
        "kw" => "text",
        "charset" => "utf-8"
    ),
    array(
        "domain" => "soso",
        "kw" => "q",
        "charset" => "utf-8"
    ),
    array(
        "domain" => "mywebsearch",
        "kw" => "searchFor",
        "charset" => "utf-8"
    ),
    array(
        "domain" => "avg",
        "kw" => "q",
        "charset" => "utf-8"
    ),
    array(
        "domain" => "reliancenetconnect",
        "kw" => "q",
        "charset" => "utf-8"
    ),
    array(
        "domain" => "baidu",
        "kw" => "wd",
        "charset" => "utf-8"
    ),
    array(
        "domain" => "sogou",
        "kw" => "query",
        "charset" => "gbk"
    ),
);

/**
 * Inner function, using regex to extract keyword param.
 */
function __extract_keywords($url, $engine='google', $param='q'){
    $pattern = '#^http://(\w+\.)*' . $engine. '(\.\w{2,3})+/.*?\?(\w+=.*?&)*' . $param . '=(?<keywords>.*?)([&\#].*)?$#';
    preg_match($pattern, $url, $matches);

    $result = array(
        'url' => $url, 
        'engine' => $engine
    );

    $result['keywords'] = isset($matches['keywords']) ? $matches['keywords'] : '';
    return $result;
}

/**
 * Wrapper function
 */
function extract_keywords($url){
    global $engine_param_config;

    foreach($engine_param_config as $config){
        $result = __extract_keywords($url, $config['domain'], $config['kw']);
        if($result['keywords']){
            $keywords = urldecode($result['keywords']);
            //remove non-ASCII characters
            $keywords = iconv('UTF-8', 'ISO-8859-1//IGNORE', $keywords);
            //remove special characters
            $keywords = str_replace(array("\"", "\\", "?"), " ", $keywords);

            return strtolower($keywords);
        }
    }

    return '';
}
?>
