<?php

define("user_INFO",     "/info");
define("user_LIST",     "/list");

class user_info_struct extends struct_fixed_array {
    var $Password       = null;
    var $EMail          = null;
    var $RegisterDate   = 0;
    var $Remembered     = false;
}

function user_create($username, $password, $email) {

    if(($basePath = @user_get_basedir($username)) === false) {
        trigger_error("Cannot create user: Illegal Username!", 
                        E_USER_WARNING);
        return false;
    }

    if(user_exists($username)) {
        trigger_error("Cannot create user: User already exists!", 
                        E_USER_WARNING);
        return false;
    } elseif(strpos($email, '@') == false) {
        trigger_error("Cannot create user: Invalid e-mail address!", 
                        E_USER_WARNING);
        return false;
    } elseif(empty($password)) {
        trigger_error("Cannot create user: Invalid password!", E_USER_WARNING);
        return false;
    } elseif(!mkdir($basePath, 0700)) {
        trigger_error("Cannot create user: mkdir() failed!", E_USER_WARNING);
        return false;
    }

    touch($basePath . user_LIST);
    
    $userInfo = new user_info_struct(array(
        'Password'      => user_get_password_hash($password),
        'EMail'         => $email,
        'RegisterDate'  => time()
    ));

    return io_write_ini_file($basePath . user_INFO, $userInfo);
}

function user_delete($username) {
    if(!user_exists($username)) {
        trigger_error(
            "Cannot delete user: User not found!", E_USER_WARNING);
        return false;
    }

    $baseDir = user_get_basedir($username);    
    foreach(scandir($baseDir) as $item) {
        if($item != '.' && $item != '..') {
            unlink($baseDir . '/' . $item);
        }
    }

    return rmdir($baseDir);
}

function user_validate_name($username) {
    if(($len = strlen($username)) > 32) {
        return false;
    }

    for($i=0; $i<$len; $i++) {
        switch($username[$i]) {
            case ctype_alnum($username[$i]):
            case '-':
            case '_':
                break;
            default:
                return false;
        }
    }
    return true;
}

function user_get_password_hash($password) {
    return sha1(md5($password, true) . $password);    
}

function user_get_info_struct($username) {
    $array = io_parse_ini_file(user_get_basedir($username) . user_INFO);
    return new user_info_struct($array);
}

function user_set_info($username, user_info_struct $struct) {
    return io_write_ini_file(user_get_basedir($username) . user_INFO, $struct);
}

function user_get_basedir($username) {
    global $_CONFIG;
    if(user_validate_name($username)) {
        return realpath($_CONFIG['UserBase']) . '/' . $username;
    } else {
        trigger_error("user_get_basedir(): Illegal username!", E_USER_WARNING);
        return false;
    }
}

function user_exists($username) {
    return is_dir(user_get_basedir($username));
}

?>
