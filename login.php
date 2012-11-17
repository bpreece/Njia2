<?php
/*
 * NOTE: the form field 'name_field' is deliberately named with an underbar, 
 * not a hyphen, so that set_focus() can be called on it.
 */

include_once 'common.inc';
include_once 'login/login_form.php';

global $new_login;
$new_login = FALSE;

function get_stylesheets() {
    $stylesheets = array('login.css');
    return $stylesheets;
}

function get_page_id() {
    return 'login-page';
}

/*
function get_page_class() {
}
 */

function process_query_string() {
    global $new_login;
    
    if (isset($_GET['new'])) {
        $new_login = TRUE;
    }
}

function process_form_data() {
    process_login_form()
    || process_new_login_form();
}

function show_sidebar() {
    global $new_login;
    if ($new_login) {
        echo "
        <div class='sidebar-block'>
            <form method='GET'>
                <input type='submit' value='Use existing account'></input>
            </form>
        </div>";
    } else {
        echo "
        <div class='sidebar-block'>
            <form method='GET'>
                <input type='submit' name='new' value='Create an account'></input>
            </form>
        </div>";
    }
}

function show_content() {
    global $new_login;
    echo "
        <h3>Sign on</h3>
        <div id='login-main'>";
    show_login_form($new_login); 
    echo "
        </div>";
}

include_once 'template.inc';

?>