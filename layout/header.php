<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>yet another file upload</title>
<base href="<?= layout_get_http_root() ?>" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" media="screen" href="layout/css/screen.css" />
<link rel="shortcut icon" href="images/favicon.ico" />
</head>
<body>
<div id="wrapper">
<div id="header">
    <div id="logo">
        <h1>Yet Another File Upload</h1>
        <div class="login">
            <? if(($user = session_get_user()) != false) : ?>
                <?= L("Logged in as: ").$user; ?>
                <a href="?a=logout"><?= L("Log out"); ?></a>
            <? else : ?>
                <a href="?a=login"><?= L("Log in"); ?></a>
            <? endif ?>
        </div>
    </div>
</div>
<ul id="menu">
    <li><a href="#" id="menu_upload">
        <?= L("Upload File"); ?>
    </a></li>
    <li><a href="#" id="menu_paste">
        <?= L("Paste Text"); ?>
    </a></li>
    <li class="chosen"><a href="?a=search" id="menu_search">
        <?= L("Search Upload"); ?>
    </a></li>
</ul>
<div id="contentbox">
