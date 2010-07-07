<?php
define("LANGUAGE_PATH", INCLUDES . 'language/');
language_load("de-oeoe");
function language_load($language) {
    global $_LANGUAGE;

    if(preg_match('/^[A-Za-z]{1,8}[\-[A-Za-z]{1,8}]*$/', $language)) {
        if(is_readable(LANGUAGE_PATH . $language . '.php')) {
            $_LANGUAGE = include(LANGUAGE_PATH . $language . '.php');
        } else {
            $sublang = strtok($language, '-');
            if($sublang != $language) {
                language_load($sublang);
            }
        }
    }
}

function language_http_languages() {
    global $_CONFIG;

    if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {

        /* Note: The following code is taken from http://vrac.adwain.org/:
         *
         * Copyright © 2007 Xavier Lepaul <xavier AT lepaul DOT fr>
         * License: Creative Commons Attribution 3.0
         * http://creativecommons.org/licenses/by/3.0/
         */

        $reqLanguages = array();
        foreach(explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $language) {
            preg_match(
                '/([A-Za-z]{1,8}[\-[A-Za-z]{1,8}]*)(;q=([0-9\.]+))?/',
                $language, $matches
            );
            if(count($matches) != 2 && count($matches) != 4) continue;
            $reqLanguages[$matches[1]] = (count($matches)==2) ? 1 : $matches[3];
        }

        arsort($reqLanguages);

        return array_keys($reqLanguages);
    } else {
        return array("en");
    }
}

function language_list_languages() {
    $languages = io_parse_ini_file(LANGUAGE_PATH . 'alias.ini');
    return is_array($languages) ? $languages : array();
}

function L($translate) {
    global $_LANGUAGE;

    return isset($_LANGUAGE[$translate]) ? $_LANGUAGE[$translate] : $translate;
}

?>
