<?php

define("io_CSVDELIMN", ';');
define("io_CSVLENGTH", 512);

function io_fopen($filename, $mode, $use_include_path=false, $context=null) {
    $open = ($mode == 'w' || $mode == 'w+' || $mode == 'a' || $mode == 'a+') ?
                'r+' : $mode;

    if($open == 'r') {
        $lock = LOCK_SH;
    } else {
        touch($filename);
        $lock = LOCK_EX;
    }
    if(is_null($context)) {
        $fd = fopen($filename, $open, $use_include_path);
    } else {
        $fd = fopen($filename, $open, $use_include_path, $context);
    }

    if(!is_resource($fd)) {
        return false;
    } elseif(!flock($fd, $lock)) {
        fclose($fd);
        return false;
    }

    switch($mode) {
        case 'w':
        case 'w+':
            ftruncate($fd, 0);
            break;
        case 'a':
        case 'a+':
            fseek($fd, 0, SEEK_END);
        default:
            break;
    }

    return $fd;
}

function io_parse_ini_file($filename, $process_sections= false) {

    if($iniFile = io_fopen($filename, 'r')) {
        $array = parse_ini_file($filename, $process_sections);
        fclose($iniFile);
        return $array;
    }
    return false;
}

function io_write_ini_file($filename, $array, 
                            $process_sections = false, $startcomment = "") {

    if(!($iniFile = io_fopen($filename, 'w'))) {
        return false;
    }

    if(trim($startcomment) != '') {
        fwrite($iniFile, '; ' . $startcomment . PHP_EOL);
    }

    foreach($array as $section => $items) {
        if(is_array($items)) {
            if($process_sections) {
                if(ftell($iniFile) > 0) {
                    fwrite($iniFile, PHP_EOL);
                }
                fwrite($iniFile, '[' . $section . ']' . PHP_EOL);
            }
        } else {
            $items = array($section => $items);
        }
        foreach($items as $key => $value) {
            if($value === '' || $value === false) {
                $value = 'no';
            } elseif($value === '1' || $value === true) {
                $value = 'yes';
            } elseif(is_null($value)) {
                $value = 'null';
            } elseif(!is_numeric($value)) {
                $value = "\"" . addslashes($value) . "\"";
            }
            fwrite($iniFile, $key . ' = ' . $value . PHP_EOL);
        }
    }
    return fclose($iniFile);
}

function io_lock_path($path) {
    global $_CONFIG;

    $lock_dir = $_CONFIG['LockDir'] . '/' . sha1($path);
    @ignore_user_abort(1);

    $start_time = time();

    do {
        if(@mkdir($lock_dir) || (time() - $start_time) > 3) {
            break;        
        }
    } while(usleep(50));
}

function io_unlock_path($path) {
    global $_CONFIG;

    $lockDir = $_CONFIG['LockDir'] . '/' . sha1($path);
    @rmdir($lockDir);
    @ignore_user_abort(0);
}

function io_create_csv_file($filename, $header) {
    if(!($csv_file = io_fopen($filename, 'w'))) {
        return false;
    }

    $header_str = "";
    foreach($header as $column => $length) {
        $header_str .= substr(str_pad($column, $length), 0, $length);
        $header_str .= io_CSVDELIMN;
    }
    $header_str[strlen($header_str)-1] = PHP_EOL;

    return (fwrite($csv_file, $header_str) !== false) && fclose($csv_file);
}

function io_scroll_csv_rows($filename, $start=0, $length=-1) {
    if(!($csv_file = io_fopen($filename, 'r'))
        || !($header = fgets($csv_file, io_CSVLENGTH))) {

        return false;
    }

    $row_len = strlen($header);
    $header = io_parse_csv_header($header);

    $length = ($length >= 0) ? $length : filesize($filename) / $row_len;

    $array = array();

    fseek($csv_file, $row_len * ($start+1));
    for($i=0; $i<$length; $i++) {
        if(!($row = fread($csv_file, $row_len))) {
            break;
        }

        $array[$i+$start] = io_parse_csv_string($row, $header);
    }
    fclose($csv_file);
    return $array;
}

function io_select_csv_rows($filename, array $range) {
    if(!($csv_file = io_fopen($filename, 'r'))
        || !($header = fgets($csv_file, io_CSVLENGTH))) {

        return false;
    }
    $row_len = strlen($header);
    $header = io_parse_csv_header($header);


    $array = array();
    foreach($range as $row_index) {
        fseek($csv_file, $row_len * ($row_index+1));
        if($row = fread($csv_file, $row_len)) {
            $array[$row_index] = io_parse_csv_string($row, $header);
        }
    }
    fclose($csv_file);
    return $array;
}

function io_append_csv_rows($filename, array $rows) {
    if(!($csv_file = io_fopen($filename, 'r+'))
        || !($header = fgets($csv_file, io_CSVLENGTH))) {

        return false;
    }
    $header = io_parse_csv_header($header);

    fseek($csv_file, 0, SEEK_END);
    foreach($rows as $row) {
        fwrite($csv_file, io_generate_csv_string($row, $header));
    }

    return fclose($csv_file);
}

function io_insert_csv_rows($filename, array $rows, $position) {

}

function io_delete_csv_rows($filename, array $row_indexes) {
    if(
        count($row_indexes) == 0 ||
        !($csv_writer = io_fopen($filename, 'r+')) ||
        !($header = fgets($csv_writer, io_CSVLENGTH))
    ) {
        return false;
    }

    $row_len = strlen($header);
    sort($row_indexes, SORT_NUMERIC);

    $csv_reader = fopen($filename, 'r');
    $line_nr = 0;
    $data_len = $row_len;


}

function io_generate_csv_string($array, $header) {
    $string = "";
    foreach($header as $column => $length) {
        if(is_numeric($array[$column])) {
            $string .= substr(str_pad($array[$column], $length), 0, $length);
        } else {
            $string .= '"' . substr($array[$column], 0, $length-2).'"';
            if(($padding = $length - strlen($array[$column]) - 2) > 0) {
                $string .= str_repeat(' ', $padding);
            }
        }
        $string .= io_CSVDELIMN;
    }
    $string[strlen($string)-1] = PHP_EOL;
    return $string;
}

function io_parse_csv_string($string, $header) {
    $array = array();
    $str_pointer = 0;
    foreach($header as $column => $length) {
        $array[$column] = trim(substr($string, $str_pointer, $length));
        if($array[$column][0] == '"'
            && $array[$column][strlen($array[$column])-1] == '"') {

            $array[$column] = substr($array[$column], 1, -1);
        }
        $str_pointer += $length + 1;
    }
    return $array;
}

function io_parse_csv_header($string) {
    $string = str_replace(PHP_EOL, '', $string);
    $header_columns = explode(io_CSVDELIMN, $string);
    foreach($header_columns as $column) {
        $header[trim($column)] = strlen($column);
    }
    return $header;
}

?>
