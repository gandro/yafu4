<?php

function zip_create_view($fileID) {
    if(!extension_loaded("zip")) {
        return false;
    }

    $sourceFile = fs_get_data_uri($fileID, false);
    $zipFile = zip_open($sourceFile);
    if(!is_resource($zipFile)) {
        return false;
    }
    while(($zipEntry = zip_read($zipFile)) !== false) {
        echo zip_entry_name($zipEntry);
        echo " - ";
        echo zip_entry_filesize($zipEntry);
        echo PHP_EOL;
    }
}

function zip_get_tags($fileID) {

}

function zip_print_view_html($fileID) {

}

function zip_get_view_uris($fileID) {

}

?>
