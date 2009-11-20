<?php

function layout_print($content) {
    global $_CONFIG;
    $layoutDir = $_CONFIG['LayoutDir'];
    if(!file_exists($layoutDir . $content . '.php')) {
        trigger_error("Cannot print layout: Content not found", E_USER_WARNING);
        return false;
    }
    
    include($layoutDir . 'header.php');
    include($layoutDir . $content . '.php');
    include($layoutDir . 'footer.php');
}

function layout_get_http_root() {
    static $httpRoot;

    if(!isset($httpRoot)) {
        $httpRoot = empty($_SERVER['HTTPS']) ? "http://" : "https://";
        $httpRoot .= $_SERVER['SERVER_NAME'];
        $httpRoot .= ($_SERVER['SERVER_PORT'] != 80 &&
                        $_SERVER['SERVER_PORT'] != 443) 
                        ?  ':'.$_SERVER['SERVER_PORT'] : '';
        $httpRoot .= ((dirname($_SERVER['SCRIPT_NAME']) != '/') 
                        ? dirname($_SERVER['SCRIPT_NAME']) : '').'/';
    }

    return $httpRoot;
}

?>
