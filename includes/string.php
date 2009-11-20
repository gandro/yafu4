<?php

function string_to_unicode($string) {
    if(string_is_utf8($string)) {
        $string = utf8_encode($string);
    }
    return $string;
}

function string_to_html($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8', false);
}

function string_is_utf8($string) {
    /* see http://www.php.net/manual/en/function.mb-detect-encoding.php#50087 */
    return preg_match('%^(?:
            [\x09\x0A\x0D\x20-\x7E]            # ASCII
        | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
        |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
        | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
        |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
        |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
        |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
    )*$%xs', $string); 
}

function string_filesize_to_bytes($string) {
    $value = trim($string); 
    $lastChar = strtoupper($value[strlen($value)-1]);
    $value = intval($value);

    switch($lastChar) {
        case 'G':
            $value *= 1024;
        case 'M':
            $value *= 1024;
        case 'K':
            $value *= 1024;
    }

    return $value;
}

?>