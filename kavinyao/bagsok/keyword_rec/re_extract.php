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
    "s1"=>array(
        "domain" => "google",
        "kw" => "q",
        "charset" => "utf-8"
    ),
    "s4"=>array(
        "domain" => "baidu",
        "kw" => "wd",
        "charset" => "utf-8"
    ),
    "s5"=>array(
        "domain" => "soso",
        "kw" => "q",
        "charset" => "utf-8"
    ),
    "s6"=>array(
        "domain" => "yahoo",
        "kw" => "p",
        "charset" => "utf-8"
    ),
    "s7"=>array(
        "domain" => "bing",
        "kw" => "q",
        "charset" => "utf-8"
    ),
    "s8"=>array(
        "domain" => "sogou",
        "kw" => "query",
        "charset" => "gbk"
    ),
);

/**
 * Inner function, using regex to extract keyword param.
 */
function __extract_keywords($url, $engine='google', $param='q'){
    $pattern = '#^http://(\w+\.)+' . $engine. '(\.\w{2,3})+/\w*\?(\w+=.*&)*' . $param . '=(?<keywords>.*?)($|(&.*$))#';
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
            //if($config['charset'] === 'utf-8')
            //    $keywords = iconv('UTF-8','gb2312//IGNORE',$keywords);
            return strtolower($keywords);
        }
    }

    return '';
}
?>
