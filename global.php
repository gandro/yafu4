<?php

@ini_set("display_errors", true);
@error_reporting(E_ALL | E_STRICT);

define('INCLUDES', 'includes/');

include(INCLUDES . 'compat.php');
include(INCLUDES . 'struct.php');
include(INCLUDES . 'string.php');
include(INCLUDES . 'io.php');

include(INCLUDES . 'file.php');
include(INCLUDES . 'user.php');
include(INCLUDES . 'session.php');
include(INCLUDES . 'upload.php');
include(INCLUDES . 'search.php');
include(INCLUDES . 'layout.php');
include(INCLUDES . 'language.php');

include(INCLUDES . 'config.php');
?>
