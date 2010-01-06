<?php

function layout_print($_PAGE, $_CONTEXT = array()) {
    global $_CONFIG;
    if(!file_exists($_CONFIG['LayoutDir'] . $_PAGE . '.php')) {
        trigger_error("Cannot print layout: Content not found", E_USER_WARNING);
        return false;
    }
    
    include($_CONFIG['LayoutDir'] . 'header.php');
    include($_CONFIG['LayoutDir'] . $_PAGE . '.php');
    include($_CONFIG['LayoutDir'] . 'footer.php');
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
