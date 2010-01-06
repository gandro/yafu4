<?php

function upload_from_rfc1867($fileArray, $publicUpload = true) {
    if(!upload_check_rfc1867_array($fileArray)) {
        return false;
    }
    $fileInfo = new fs_info_struct();
    $fileInfo['FileID'] = fs_generate_id();
    $fileInfo['Filename'] = string_to_unicode($fileArray['name']);
    /*if(strlen($fileInfo['Filename']) > 96) {
        $fileInfo['Filename'] = substr($fileInfo['Filename'], 0, 92);
        $fileInfo['Filename'] .= substr($fileInfo['Filename'], -4);
    } FIXME not needed..? */
    $fileInfo['Size'] = (int) $fileInfo['size'];
    $fileInfo['CreationTime'] = time();
    $fileInfo['Owner'] = session_get_user();
    $fileInfo['Public'] = (bool) $publicUpload;
    
    if(
        fs_allocate_space($fileInfo['FileID']) && 
        fs_set_info($fileInfo) &&
        fs_set_data($fileInfo['FileID'], $fileArray['tmp_name'])
    ) {
        return $fileInfo;
    }
    return false;
}

function upload_flip_multi_rfc1867_array($filesArray) {
    $revertedArray = array();

    foreach($filesArray as $field => $subArray) {
        foreach($subArray as $index => $value) {
            if(!empty($filesArray['tmp_name'][$index])) {
                $revertedArray[$index][$field] = $value;
            }
        }
    }

    return $revertedArray;
}

function upload_check_rfc1867_array($fileArray) {
    global $_CONFIG;

    if(isset($fileArray['error']) && $fileArray['error'] !=  UPLOAD_ERR_OK) {
        switch($fileArray['error']) {
            case UPLOAD_ERR_INI_SIZE: 
                trigger_error(
                    "The uploaded file exceeds the filesize limit on the server.",
                    E_USER_WARNING
                );
                break;
            case UPLOAD_ERR_PARTIAL:
                trigger_error(
                    "The uploaded file was only partially uploaded.",
                    E_USER_WARNING
                );
                break;
            case UPLOAD_ERR_NO_FILE:
                trigger_error("No file was uploaded.", E_USER_WARNING);
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                trigger_error(
                    "No temporary directory on the server", E_USER_WARNING
                );
                break;
            case UPLOAD_ERR_CANT_WRITE:
                trigger_error(
                    "Failed to write file to server.", E_USER_WARNING
                );
                break;
            case UPLOAD_ERR_EXTENSION:
                trigger_error(
                    "File upload stopped by extension.", E_USER_WARNING
                );
                break;
            default:
                trigger_error(
                    "Unkown error during file upload.", E_USER_WARNING
                );
        }
    } elseif(
        !isset($fileArray['name']) || !isset($fileArray['type']) ||
        !isset($fileArray['size']) || !isset($fileArray['tmp_name']) ||
        !isset($fileArray['error']) || !is_uploaded_file($fileArray['tmp_name'])
    ) {
        trigger_error(
            "Internal error: The \$_FILES array is not valid.", E_USER_WARNING
        );
    } elseif($fileArray['size'] > $_CONFIG['MaxFilesize']) {
        trigger_error(
            "The uploaded file exceeds the filesize limit.",
            E_USER_WARNING
        );
    } elseif($fileArray['size'] == 0) {
        trigger_error("The uploaded file is empty.", E_USER_WARNING);
    } else {
        return true;
    }
    return false;
}
