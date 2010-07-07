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

function io_parse_ini_file($filename, $process_sections = false) {

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

    $lock_dir = $_CONFIG['LockDir'] . '/' . sha1($path);
    @rmdir($lock_dir);
    @ignore_user_abort(0);
}

function io_write_csv_file($filename, array $header, array $data) {
    if(!($csv_file = io_fopen($filename, 'w'))) {
        return false;
    }

    fputcsv($csv_file, $header, io_CSVDELIMN);
    $header = array_flip($header);
    foreach($data as $row) {
        fputcsv($csv_file, array_merge($header, $row), io_CSVDELIMN);
    }

    return fclose($csv_file);
}

function io_append_to_csv_file($filename, array $header, array $row) {
    if(!($csv_file = io_fopen($filename, 'a'))) {
        return false;
    }

    if(filesize($filename) == 0) {
        fputcsv($csv_file, $header, io_CSVDELIMN);
    }

    fputcsv($csv_file, array_merge(array_flip($header), $row), io_CSVDELIMN);

    return fclose($csv_file);
}

function io_parse_csv_file($filename) {
    if(!($csv_file = io_fopen($filename, 'r'))) {
        return false;
    }

    $c = 0;
    $array = array();

    while($row = fgetcsv($csv_file, io_CSVLENGTH, io_CSVDELIMN)) {
        if($c++ == 0) {
            $header = $row;
        } else {
            $array[$c] = array_combine($header, $row);
        }
    }

    fclose($csv_file);

    return $array;
}


function io_query_csv_file($filename, $field, $criterion) {
    if(!($csv_file = io_fopen($filename, 'r'))) {
        return false;
    }

    $c = 0;
    $array = array();

    while($row = fgetcsv($csv_file, io_CSVLENGTH, io_CSVDELIMN)) {
        if($c++ == 0) {
            $header = $row;
            if(!in_array($field, $header)) {
                return false;
            }
        } else {
            $row = array_combine($header, $row);
            if(is_callable($criterion)) {
                if(call_user_func($criterion, $row[$field])) {
                    $array[$c] = $row;
                }
            } elseif($row[$field] == $criterion) {
                $array[$c] = $row;
            }
        }
    }

    fclose($csv_file);

    return $array;
}

function io_select_csv_file($filename, array $range) {
    if(
        count($range) == 0 ||
        !($csv_file = io_fopen($filename, 'r')) ||
        !($header = fgetcsv($csv_file, io_CSVLENGTH, io_CSVDELIMN))
    ) {
        return false;
    }

    $c = 0;
    $array = array();

    sort($range, SORT_NUMERIC);
    $cur = array_shift($range);

    while(!feof($csv_file) && $c <= $cur) {
        if($cur == $c) {
            $row = fgetcsv($csv_file, io_CSVLENGTH, io_CSVDELIMN);
            $array[$c] = array_combine($header, $row);
            $cur = array_shift($range);
        } else {
            fgets($csv_file, io_CSVLENGTH);
        }
        $c++;
    }

    fclose($csv_file);
    return $array;
}

function io_delete_by_query_from_csv_file($filename, $field, $criterion) {
    if(!($csv_writer = io_fopen($filename, 'r+'))) {
        return false;
    }
    $csv_reader = fopen($filename, 'r');

    $c = 0;
    $deleted = 0;
    $array = array();

    while($row = fgets($csv_reader, io_CSVLENGTH)) {
        if($c++ == 0) {
            $header = str_getcsv($row, io_CSVDELIMN);
            fseek($csv_writer, strlen($row));
            if(!in_array($field, $header)) {
                fclose($csv_reader);
                fclose($csv_writer);
                return false;
            }
            continue;
        } else {
            $parsed_row = str_getcsv($row, io_CSVDELIMN);
            $parsed_row = array_combine($header, $parsed_row);
            if(is_callable($criterion)) {
                if(call_user_func($criterion, $parsed_row[$field])) {
                    $deleted += strlen($row);
                    continue;
                }
            } elseif($parsed_row[$field] == $criterion) {
                $deleted += strlen($row);
                continue;
            }
        }
        fwrite($csv_writer, $row);
    }

    ftruncate($csv_writer, filesize($filename)-$deleted);

    fclose($csv_reader);
    fclose($csv_writer);

    return ($deleted > 0);
}

function io_delete_from_csv_file($filename, array $row) {
    if(!($csv_writer = io_fopen($filename, 'r+'))) {
        return false;
    }
    $csv_reader = fopen($filename, 'r');

    $found = false;
    $header = fgetcsv($csv_reader, io_CSVLENGTH, io_CSVDELIMN);
    $row_string = string_to_csv($row, $header);

    while(($line = fgets($csv_reader, io_CSVLENGTH))) {
        if($line == $row_string) {
            $found = true;
            break;
        }
    }

    if($found) {
        fseek($csv_writer, ftell($csv_reader) - strlen($row_string));
        stream_copy_to_stream($csv_reader, $csv_writer);
        ftruncate($csv_writer, ftell($csv_reader) - strlen($row_string));
    }

    fclose($csv_reader);
    fclose($csv_writer);

    return $found;
}

function io_remove_path($path) {
    if (is_dir($path)) {
        $files = array_diff(scandir($path), array('.', '..'));

        foreach($files as $file) {
            io_remove_path(realpath($path) . '/' . $file);
        }

        return rmdir($path);
    } elseif(is_file($path)) {
        return unlink($path);
    }

    return false;
}


?>
