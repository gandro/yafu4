<?php

define("s_DAY", 'DAY');
define("s_WEEK", 'WEEK');
define("s_MONTH", 'MONTH');
define("s_YEAR", 'YEAR');

function search_create_index() {
    global $_CONFIG;

    $indexArray = array();

    
    foreach(scandir($_CONFIG['FileBase']) as $fileID) {
        if($fileID != '.' && $fileID != '..') {
            $data = struct_as_array(fs_get_info_struct($fileID));
            $index = search_get_filename_for_date($data['CreationTime']);

            $indexArray[$index][] = $data;
        }
    }
    
    foreach($indexArray as $index => &$data) {
        $timeArray = array();
        foreach($data as $key => &$row) {
            $timeArray[$key] = $row['CreationTime'];
        }

        array_multisort($timeArray, SORT_ASC, $data);
        io_write_csv_file(
            $_CONFIG['IndexDir'] . '/' . $index, search_get_csv_header(), $data
        );

        unset($timeArray);
        unset($data);
    }
}

function search_get_csv_header() {
    static $csv_header;
    if(!isset($csv_header)) {
        $csv_header = array_keys(get_class_vars('fs_info_struct'));
    }
    return $csv_header;
}

function search_add_to_index(fs_info_struct $file) {
    $filename = search_get_filename_for_date($file['CreationTime'], true);
    $file = struct_as_array($file);
    
    return io_append_to_csv_file($filename, search_get_csv_header(), $file);
}

function search_remove_from_index(fs_info_struct $file) {
    $index = search_get_filename_for_date($file['CreationTime'], true);
    $file = struct_as_array($file);
        return io_delete_from_csv_file( $index, $file);    
}

function search_get_list_for_date($date) {
    $filename = search_get_filename_for_date($date, true);
    if(is_readable($filename)) {
        return io_parse_csv_file($filename);
    } else {
        return array();
    }
}

function search_get_query_for_date($date, $field, $criterion) {
    $filename = search_get_filename_for_date($date, true);
    if(is_readable($filename)) {
        return io_query_csv_file($filename, $field, $criterion);
    } else {
        return array();
    }
}

function search_get_filename_for_date($date, $fullpath = false) {
    global $_CONFIG;
    switch($_CONFIG['SearchResolution']) {
        case s_YEAR:
            $fmt = 'Y';
            break;
        case s_MONTH:
            $fmt = 'Y-m';
            break;
        case s_WEEK:
            $fmt = 'Y-W';
            break;
        case s_DAY:
            $fmt = 'Y-m-d';
            break;
    }

    return ($fullpath ? $_CONFIG['IndexDir'] . '/' : '') . date($fmt, $date);
}

function search_get_human_readable_date($date) {
    global $_CONFIG;
    if(!empty($_CONFIG['CustomSearchDate'])) {
        return date($date, $_CONFIG['CustomSearchDate']);
    } else {
        switch($_CONFIG['SearchResolution']) {
            case s_YEAR:
                $fmt = '%Y';
                break;
            case s_MONTH:
                $fmt = '%Y-%M';
                break;
            case s_WEEK:
                $fmt = '%Y-W%V';
                break;
            case s_DAY:
                $fmt = '%x';
                break;
        }
    }
    return strftime($fmt, $date);
}

function search_get_last_steps($date, $step_count = 7) {
    global $_CONFIG;
    $steps = array();
    for($i=0; $i<$step_count; $i++) {
        $step = '-'.$i.' '.strtolower($_CONFIG['SearchResolution']);
        $steps[$i] = strtotime($step, $date);
    }
    return $steps;
}

function search_full_text($query, $startdate, $range = 1) {
    $results = array();
    $callback = 'search_full_text_callback';
    $callback(null, strtolower($query));
    
    foreach(search_get_last_steps($startdate, $range) as $date) {
        $results += search_get_query_for_date($date, 'Filename', $callback);
    }

    return $results;
}

function search_full_text_callback($value, $set_query = null) {
    static $query = '';
    if(!is_null($set_query)) {
        $query = '*'.$set_query.'*';
    }
    return fnmatch($query, strtolower($value));
}

?>
