<?php
include("global.php");

$action = empty($_GET['a']) ? '' : $_GET['a'];

do {
    switch($action) {
        case 'upload':
            if(!isset($_FILES['file'])) {
                trigger_error("Invalid Request", E_USER_ERROR);
            }
            $fileInfo = upload_from_rfc1867($_FILES['file'], true);
            if($fileInfo != false) {
                search_add_to_index($fileInfo);
                $action = 'info';
                continue(2);
            }
            break;

        case 'search':
            $selected = isset($_GET['t']) ? $_GET['t'] : time();
            $selected = search_get_first_of_period($selected);

            $until = isset($_GET['e']) ? (int) $_GET['e'] : 1;

            $query = isset($_GET['q']) ? trim($_GET['q']) : '';

            if($until > 1) {
                $range = search_get_range_of_timestamps($selected,
                            search_get_timestamp_by_offset(
                                $selected, -1*$until
                            )
                        );
            } else {
                $range = array($selected);
            }


            if(strlen($query) > 0) {
                $result = search_full_text($query, $range);
            } else {
                $result = array();
                foreach($range as $date) {
                    $result = array_merge($result, search_get_list_for_date($date));
                }
            }

            $timeline = search_get_timeline($range[0], 7, $range);
            $date_pos = array_search($range[0], $timeline);

            layout_print("search", array(
                'selected' => $selected,
                'until' => $until,
                'query' => htmlspecialchars($query),
                'result' => &$result,
                'timeline' => &$timeline
            ));
            break;

        case 'info':
            /* if $fileInfo is set, it's a new upload */
            if(isset($fileInfo)) {
                $isPrivate = true;
            } else {
                $fileID = isset($_GET['f']) ? trim($_GET['f']) : null;
                $fileInfo = fs_get_info_struct($fileID);
                $isPrivate = !user_validate_name($fileInfo['Owner']) ? false :
                                ($fileInfo['Owner'] == session_get_user());
            }

            layout_print("fileinfo", array(
                'info' => $fileInfo,
                'private' => $isPrivate
            ));
            break;

        case 'login':
            layout_print("login");
            break;

        case 'logout':
            session_logout("login");
            break;

        case 'loggedin':
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $permanent = isset($_POST['permanent']) ? true : false;

            if(session_login($username, $password, $permanent)) {
                echo("Logged in!");
            } else {
                echo("Error!");
            }

        case 'index':
        default:
            layout_print("upload_form");
            break;
    }
    break;
} while(true);

?>
