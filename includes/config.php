<?php

/**
 * Default configuration
 *
 */
 
/* WARNING: DO NOT EDIT! *** WARNING: DO NOT EDIT! *** WARNING: DO NOT EDIT! */

class config_struct extends struct_fixed_array {
    var $FileBase     = 'data/files/';
    var $UserBase     = 'data/users/';
    var $IndexDir     = 'data/index/';
    var $LockDir      = 'data/.locks/';
    var $LayoutDir    = 'layout/';
    var $CookieTTL    = 1209600; /* 14 days */
    var $MaxFilesize  = 5000000;
}

$_CONFIG = new config_struct();
