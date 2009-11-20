<?php

function rar_create_view($fileID) {
    $sourceFile = fs_get_data_uri($fileID, false);
    $rar_file = rar_open($sourceFile) or die("Failed to open Rar archive");
    $entries_list = rar_list($rar_file);
    print_r($entries_list);
}

function thumbnail_get_tags($fileID) {

}

function thumbnail_print_view_html($fileID) {

}

function thumbnail_get_view_uris($fileID) {

}

?>
