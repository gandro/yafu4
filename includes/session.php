<?php

define("COOKIE_USER", 'yafu_user');
define("COOKIE_SUID", 'yafu_suid');

function session_login($username, $password, $permanent = false) {
    if(!user_exists($username) || empty($password)) {
        return false;
    }
    $loginSuccess = false;
    $userBaseDir = user_get_basedir($username);
    io_lock_path($userBaseDir);

    $userInfo = user_get_info_struct($username);
    if($userInfo['Password'] == user_get_password_hash($password)) {
        $sessionID = ($permanent) ? 
                        session_generate_permanent_id($username):
                        session_generate_temporary_id($username);

        $userInfo['Remembered'] = (bool)$permanent;

        $loginSuccess = (
            session_set_cookie($username, $sessionID, $permanent) &&
            user_set_info($username, $userInfo)
        );
    }
    io_unlock_path($userBaseDir);
    return $loginSuccess;
}

function session_logout() {
    $username = session_get_user();
    if(is_null($username)) {
        return false;
    }
    $userBaseDir = user_get_basedir($username);
    
    io_lock_path($userBaseDir);
    $userInfo = user_get_info_struct($username);
    if($userInfo['Remembered']) {
        $userInfo['Remembered'] = false;
        user_set_info($username, $userInfo);
    }
    io_unlock_path($userBaseDir);

    unset($_COOKIE[COOKIE_USER]);
    unset($_COOKIE[COOKIE_SUID]);

    setcookie(COOKIE_USER, '', time()-3600);
    setcookie(COOKIE_SUID, '', time()-3600);

    return is_null(session_get_user(true));
}

function session_get_user($forceCheck = false) {
    static $username = false;
    if($username === false || $forceCheck) {
        $username = session_check_id() ? $_COOKIE[COOKIE_USER] : null;
    }
    return $username;
}

function session_set_cookie($username, $sessionID, $permanent = false) {
    global $_CONFIG;
    $expires = ($permanent) ? time() + (2 * $_CONFIG['CookieTTL']) : 0;
    
    $_COOKIE[COOKIE_USER] = $username;
    $_COOKIE[COOKIE_SUID] = $sessionID;

    $userCookie = setcookie(COOKIE_USER, $username, $expires);
    $sidCookie  = setcookie(COOKIE_SUID, $sessionID, $expires);

    return ($userCookie && $sidCookie);
}

function session_check_id() {
    if(
        !isset($_COOKIE[COOKIE_USER]) || !user_exists($_COOKIE[COOKIE_USER]) ||
        !isset($_COOKIE[COOKIE_SUID]) || !ctype_xdigit($_COOKIE[COOKIE_SUID])
    ) {
        return false;
    }
    $username = $_COOKIE[COOKIE_USER];
    $sessionID = $_COOKIE[COOKIE_SUID];
    $userInfo = user_get_info_struct($username);

    if($userInfo['Remembered']) {
        $validID = session_generate_permanent_id($username, false);
        $olderID = session_generate_permanent_id($username, true);

        if($validID == $sessionID) {
            return true;
        } elseif($olderID == $sessionID) {
            return session_set_cookie($username, $validID, true);
        }
        return false;
    } else {
        return (session_generate_temporary_id($username) == $sessionID);
    }
    return false;
}

function session_generate_permanent_id($username, $olderTimeFrame = false) {
    global $_CONFIG;

    $userInfo = user_get_info_struct($username);

    $timeFrame = (int) (time() / $_CONFIG['CookieTTL']);
    $timeFrame -= ($olderTimeFrame) ? -1 : 0;

    return sha1($userInfo['Password'] . $username . $timeFrame);
}

function session_generate_temporary_id($username) {
    global $_CONFIG;
    
    $userInfo = user_get_info_struct($username);
    
    $remoteAddr = $_SERVER['REMOTE_ADDR'];
    
    $httpHeaders  = @$_SERVER['HTTP_ACCEPT'];
    $httpHeaders .= @$_SERVER['HTTP_ACCEPT_CHARSET'];
    $httpHeaders .= @$_SERVER['HTTP_ACCEPT_ENCODING'];
    $httpHeaders .= @$_SERVER['HTTP_ACCEPT_LANGUAGE'];

    return sha1($userInfo['Password'] . $username . $remoteAddr . $httpHeaders);
}



?>
