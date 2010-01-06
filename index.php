<?php
include("global.php");
$action = empty($_GET['a']) ? '' : $_GET['a'];

switch($action) {
    case 'upload':
        if(!isset($_FILES['file'])) {
            trigger_error("Invalid Request", E_USER_ERROR);
        }
        var_dump($f=upload_from_rfc1867($_FILES['file'], true));
        search_add_to_index($f);
        break;
    case 'search':
        $context['date'] = (isset($_GET['st']) && is_numeric($_GET['st']))
                             ? $_GET['st'] : time();
        layout_print("search_files", $context);
        break;
    case 'index':
    default:
        layout_print("upload_form");
        break;
}
//var_dump();
var_export(search_remove_from_index(fs_get_info_struct("d2a8a424")));
//var_dump(io_delete_from_csv_file("test.csv~", "Filename", "HAHA.txt"));
//var_dump(io_delete_from_csv_file("test.csv~", "Contact", "Nigel \"Shan\" Shanford"));
/*var_export(  io_append_to_csv_file("test.csv~", array("Filename", "Size", "Public"),

  array (
    'Filename' => 'Dateiname.txt',
    'Size' => '2326',
    'Public' => '1',
  )));
*/
//echo search_create_index();
//var_dump(search_get_list_for_date(1250712669));
/*$header = io_parse_csv_header("Hallo     ;Welt  ;Test    ");
$array = array("Hallo" => ".66o7", "Welt" => "abcdefghijklmopq", "Test" => 2332425234234);
echo ($string = io_generate_csv_string($array, $header));
print_r(io_parse_csv_string($string, $header));*/
//io_create_csv_file("test.txt", array("Hallo" => 10, "Testdings" => 4, "Welt" => 23));
//var_dump(io_append_csv_rows("test.txt", array(    array("Test" => "3", "Hallo" => "Zeile 3", "Welt" => "ja"))));
//var_dump(io_select_csv_rows("test.txt", array(1, 3)));
?>
