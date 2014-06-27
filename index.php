<?php

include_once 'common.inc';

/*
include_once 'data/index.php';
if (isset($_POST)) {
    // todo - handle form data
} else if (isset($_GET)) {
    // todo - handle URL parameters
}
 */

$page = array(
    'view' => 'view/front-page.php',
    'styles' => array( 'css/index.css', ),
    'page-id' => 'front-page', 
    'page-class' => 'no-header',
    'sidebar-views' => array(
        "view/login-block.php",
        'view/readme-block.php',
    ),
);

include_once ('template.inc');
