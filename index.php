<?php
include("global.php");
$action = empty($_GET['a']) ? '' : $_GET['a'];

switch($action) {
    case 'upload':
        if(!isset($_FILES['file'])) {
            trigger_error("Invalid Request", E_USER_ERROR);
        }
        var_dump(upload_from_rfc1867($_FILES['file'], true));
        break;
    case 'index':
    default:
        layout_print("upload_form");
        break;
}
/*$header = io_parse_csv_header("Hallo     ;Welt  ;Test    ");
$array = array("Hallo" => ".66o7", "Welt" => "abcdefghijklmopq", "Test" => 2332425234234);
echo ($string = io_generate_csv_string($array, $header));
print_r(io_parse_csv_string($string, $header));*/
//io_create_csv_file("test.txt", array("Hallo" => 10, "Testdings" => 4, "Welt" => 23));
//var_dump(io_append_csv_rows("test.txt", array(    array("Test" => "3", "Hallo" => "Zeile 3", "Welt" => "ja"))));
//var_dump(io_select_csv_rows("test.txt", array(1, 3)));
?>
