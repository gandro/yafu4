<?php

define("SEARCH_DAY",   'DAY');
define("SEARCH_WEEK",  'WEEK');
define("SEARCH_MONTH", 'MONTH');
define("SEARCH_YEAR",  'YEAR');

function search_create_index() {
    global $_CONFIG;

    $indexArray = array();

    foreach(scandir($_CONFIG['FileBase']) as $prefix) {
        if($prefix == '.' || $prefix == '..') continue;
        foreach(scandir($_CONFIG['FileBase'] . '/' . $prefix) as $fileID) {
            if($fileID != '.' && $fileID != '..') {
                $data = struct_as_array(fs_get_info_struct($fileID));
                $index = search_get_filename_for_date($data['CreationTime']);

                $indexArray[$index][] = $data;
            }
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
        case SEARCH_YEAR:
            $fmt = 'Y';
            break;
        case SEARCH_MONTH:
            $fmt = 'Y-m';
            break;
        case SEARCH_WEEK:
            $fmt = 'Y-W';
            break;
        case SEARCH_DAY:
            $fmt = 'Y-m-d';
            break;
    }

    return ($fullpath ? $_CONFIG['IndexDir'] . '/' : '') . date($fmt, $date);
}

function search_get_human_readable_date($date) {
    global $_CONFIG;
    if(!empty($_CONFIG['CustomSearchDate'])) {
        return date($_CONFIG['CustomSearchDate'], $date);
    } else {
        switch($_CONFIG['SearchResolution']) {
            case SEARCH_YEAR:
                $fmt = '%Y';
                break;
            case SEARCH_MONTH:
                $fmt = '%b %Y';
                break;
            case SEARCH_WEEK:
                $fmt = '%Y-W%V';
                break;
            case SEARCH_DAY:
                $fmt = '%x';
                break;
        }
    }
    return strftime($fmt, $date);
}

define("TIMELINE_AVAILABLE", 'available');
define("TIMELINE_DISABLED",  'disabled');
define("TIMELINE_SELECTED",  'selected');

function search_get_timeline($date, $pagecount, $chosen) {
    global $_CONFIG;

    $date = search_get_first_of_period($date);
    $timeline = array();

    $date_pos = (int) ($pagecount/2);
    $max_timestamp = search_get_first_of_period(time());
    $min_timestamp = search_get_first_of_period($_CONFIG['SearchBigBang']);

    for($i=0; $i<$pagecount; $i++) {
        $timestamp = search_get_timestamp_by_offset($date, $date_pos-$i);
        $type = TIMELINE_AVAILABLE;

        if(in_array($timestamp, $chosen)) {
            $type = TIMELINE_SELECTED;
        } elseif($timestamp > $max_timestamp || $timestamp < $min_timestamp) {
            $type = TIMELINE_DISABLED;
        }

        $timeline[$i] = array(
            'date' => $timestamp,
            'type' => $type
        );
    }


   return $timeline;
}

function search_get_timestamp_by_offset($date, $offset) {
    global $_CONFIG;

    $offset = ($offset > 0) ? ('+'.intval($offset)) : (int)$offset;
    return strtotime(
        $offset.' '.$_CONFIG['SearchResolution'],
        $date
    );
}

define("SEARCH_MAX_RANGE", 1024);

function search_get_range_of_timestamps($start, $end) {
    $range = array();

    for($i=0; $i>SEARCH_MAX_RANGE*-1; $i--) {
        $timestamp = search_get_timestamp_by_offset($start, $i);
        if($timestamp <= $end) break;

        $range[] = $timestamp;
    }

    return $range;
}

function search_get_first_of_period($date) {
    global $_CONFIG;

    switch($_CONFIG['SearchResolution']) {
        case SEARCH_YEAR:
            $new_date = mktime(0, 0, 0, 1, 1, date('Y', $date));
            break;
        case SEARCH_MONTH:
            $new_date = mktime(0, 0, 0, date('n', $date), 1, date('Y', $date));
            break;
        case SEARCH_WEEK:
            while(date('N', $date) > 1) {
                $date -= 86400;
            }
        case SEARCH_DAY:
            $new_date = mktime(0, 0, 0, date('n', $date), date('j', $date), date('Y', $date));
            break;
    }

    return $new_date;
}

function search_full_text($query, array $range) {
    $results = array();
    $callback = 'search_full_text_callback';
    $callback(null, strtolower($query)); /* set query */

    foreach($range as $date) {
        $results = array_merge($results,
                    search_get_query_for_date($date, 'Filename', $callback));
    }

    return $results;
}

function search_full_text_callback($value, $set_query = null) {
    static $query = '';
    if(!is_null($set_query)) {
        $query = (strrchr($set_query, '*') || strrchr($set_query, '?')) ?
                $set_query : '*'.$set_query.'*';
    }
    return fnmatch($query, strtolower($value));
}

?>
