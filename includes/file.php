<?php

define("fs_DATA",     "/data");
define("fs_INFO",     "/info");
define("fs_TAGS", "/tags");
define("fs_VIEW", "/view");

class fs_info_struct extends struct_fixed_array {
    var $Filename       = "Untitled";
    var $Size           = 0;
    var $Mimetype       = "application/octet-stream";
    var $CreationTime   = 0;
    var $Owner          = null;
    var $Public         = true;
}

class fs_tags_struct extends struct_array {
    /* empty */
}

/**
 * Allocates space for a new file
 *
 * @param string $fileID
 */

function fs_allocate_space($fileID) {

    $basePath = fs_get_basedir($fileID);
    if(fs_file_exists($fileID)) {
        trigger_error("Cannot allocate file space: File ID already exists!", 
                        E_USER_WARNING);
        return false;
    } elseif(!mkdir($basePath, 0700)) {
        trigger_error("Cannot allocate file space: mkdir() failed!", 
                        E_USER_WARNING);
        return false;
    }

    touch($basePath . fs_DATA);
    touch($basePath . fs_VIEW);
    touch($basePath . fs_TAGS);
    
    return io_write_ini_file($basePath . fs_INFO, new fs_info_struct());
}

function fs_get_mimetype($fileID, $fallback = "application/octet-stream") {
    global $CONFIG;

    $mimetype = $fallback;
    $dataURI = fs_get_data_uri($fileID);

    if(is_callable("finfo_file") && $finfo = @finfo_open(FILEINFO_MIME)) {
        $mimetype = finfo_file($finfo, realpath(fs_get_data_uri($dataURI)));
        finfo_close($finfo);
    } elseif(is_callable('exec') && @exec('file -v')) {
        $mimetype = exec('file -bi '.escapeshellarg($dataURI));
    }

    return strtok($mimetype, ',');
}

function fs_get_basedir($fileID) {
    global $_CONFIG;
    if(ctype_xdigit($fileID)) {
        return realpath($_CONFIG['FileBase']) . '/' . $fileID;
    } else {
        trigger_error("fs_get_basedir(): Illegal file id!", E_USER_WARNING);
        return false;
    }
}

function fs_file_exists($fileID) {
    return is_dir(fs_get_basedir($fileID));
}

/**
 * Returns unique file id
 *
 * @param string $fileID
 * @return Unique file id
 */

function fs_generate_id() {
    do {
        $fileID = substr(md5(uniqid()), 0, 8);
    } while(fs_file_exists($fileID));
    return $fileID;
}

function fs_remove_file($fileID) {
    $baseDir = fs_get_basedir($fileID);
    if(!is_dir($baseDir)) {
        trigger_error(
            "fs_remove_file: Invalid file id or directory", E_USER_WARNING);
        return false;
    }
    
    foreach(scandir($baseDir) as $item) {
        if($item != '.' && $item != '..') {
            unlink($baseDir . '/' . $item);
        }
    }

    return rmdir($baseDir);
}




function fs_get_info_struct($fileID) {
    $array = io_parse_ini_file(fs_get_basedir($fileID) . fs_INFO);
    return new fs_info_struct($array);
}

function fs_get_tags_struct($fileID) {   
    $array = io_parse_ini_file(fs_get_basedir($fileID) . fs_TAGS);
    return new fs_tags_struct($array);
}

function fs_get_data_uri($fileID, $prefix = true) {    
    $dataPath = fs_get_basedir($fileID) . fs_DATA;
    if(file_exists($dataPath)) {
        return ($prefix ? 'file://' : '') . realpath($dataPath);
    } else {
        return false;
    }
}

function fs_get_view_uri($fileID, $prefix = true) {   
    $extDataPath = fs_get_basedir($fileID) . fs_VIEW;
    if(file_exists($extDataPath)) {
        return ($prefix ? 'file://' : '') . realpath($extDataPath);
    } else {
        return false;
    }
}






function fs_set_info($fileID, fs_info_struct $struct) {
    return io_write_ini_file(fs_get_basedir($fileID) . fs_INFO, $struct);
}

function fs_set_tags($fileID, fs_tags_struct $struct) {
    return io_write_ini_file(fs_get_basedir($fileID) . fs_tags, $array);
}

function fs_set_data($fileID, $source) {
    if(is_uploaded_file($source)) {
        return move_uploaded_file($source, fs_get_basedir($fileID) . fs_DATA);
    } else {
        return copy($source, fs_get_basedir($fileID) . fs_DATA);
    }
}

function fs_set_view($fileID, $source) {
    return copy($source, fs_get_basedir($fileID) . fs_VIEW);
}

?>
