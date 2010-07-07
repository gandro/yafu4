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

function string_bytes_to_filesize($size) {
    global $_CONFIG;

    /* Note: Since php doesn't have support for large intergers,
     * calculating any filesize larger than 2 GiB on a 32 bit machine
     * may does not work as expected.
     */

    if($_CONFIG['SiPrefixes']) {
        $unit = array('B', 'kB', 'MB', 'GB');
        $base = 1000;
    } else {
        $unit = array('B', 'KiB', 'MiB', 'GiB');
        $base = 1024;
    }

    for($i=0; $i<=count($unit); $i++) {
        $step = (int) pow($base, $i);
        if($size < $step*$base) {
            if($size%$step == 0) {
                return $size/$step.($nbsp?'&nbsp;':' ').$unit[$i];
            } else {
                $locale = localeconv();
                return number_format($size/$step, 2,
                    $locale['decimal_point'], $locale['thousands_sep']).
                    ' '.$unit[$i];
            }
        }
    }
}

function string_to_csv(array $row, array $header) {
    $sorted_row = array_merge(array_flip($header), $row);
    if(count($sorted_row) != count($row)) {
        return false;
    }

    $memory = fopen("php://memory", 'r+');
    fputcsv($memory, $sorted_row, io_CSVDELIMN);
    rewind($memory);
    
    $csv_string = stream_get_contents($memory);
    fclose($memory);
    
    return $csv_string;
}

?>
