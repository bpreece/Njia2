<?php

/*
 * REQUIRED.  Include standard functions which are used by all pages.
 */
include_once 'common.inc';
include_once 'login/login_form.php';
include_once 'user/user_list_options_form.php';

global $show_closed_accounts, $starting_index, $max_row_count;
global $users_list;

/**
 * OPTIONAL.  The page template automatically includes the default templates
 * layout.css and styles.css.  If this page requires additional stylesheets,
 * they should be returned in an array in the order that they should be 
 * processed by the browser
 * 
 * @return array
 */
function get_stylesheets() {
    $stylesheets = array('users.css');
    return $stylesheets;
}

/**
 * OPTIONAL.  Use this function to return the value of an 'id' attribute
 * for the HTML 'body' tag.
 * 
 * @return string
 */
/*
function get_page_id() {
    return 'stub-page';
}
 */

/**
 * OPTIONAL.  Use this function to return the value of a 'class' attribute
 * for the HTML 'body' tag.
 * 
 * @return string
 */
function get_page_class() {
    return 'users-page';
}

/**
 * OPTIONAL.  Use this function to process any $_GET parameters.  This 
 * function is called before process_form_data().
 */
function process_query_string() {
    global $show_closed_accounts;

    if (isset($_GET['xa'])) {
        $show_closed_accounts = TRUE;
    }
}

/**
 * OPTIONAL.  Use this function to process any $_POST parameters.  This 
 * function is called after process_query_string().
 */
function process_form_data() {
    process_new_login_form();
}

/**
 * OPTIONAL.
 */
function prepare_page_data() {
    global $show_closed_accounts, $starting_index, $max_row_count;
    global $users_list;
    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }
    
    if (! is_admin_session()) {
        header("Location: user.php");
    }
    
    if (! $starting_index) {
        $starting_index = 0;
    }
    if (! $max_row_count) {
        $max_row_count = 30;
    }
    
    $users_query = "SELECT U.`user_id` , U. `login_name`, U.`user_permissions` , 
        U.`user_creation_date` , U.`account_closed_date`
        FROM `user_table` AS U ";
    if (! $show_closed_accounts) {
        $users_query .= "
            WHERE U.`account_closed_date` IS NULL ";
    }
    $users_query .= "
        LIMIT $starting_index , $max_row_count";

    $users_result = mysqli_query($connection, $users_query);
    if (! $users_result) {
        set_user_message(mysqli_error($connection), 'failure');
        return;
    }
    $users_list = array();
    while ($user = mysqli_fetch_array($users_result)) {
        $users_list[$user['user_id']] = $user;
    }
}

/**
 * OPTIONAL.
 */
function show_sidebar() {
    global $show_closed_accounts;
    
    echo "
        <div class='sidebar-block'>";
    show_user_list_options_form($show_closed_accounts);
    echo "
        </div>";

    echo "
        <div class='sidebar-block'>";
    show_login_form(TRUE);
    echo "
        </div>";
}

/**
 * REQUIRED.
 */
function show_content() {
    global $users_list;
    global $starting_index, $max_row_count;

    echo "
        <h3>Users</h3>
        <div id='users-list' class='object-list'>";
    foreach ($users_list as $user_id => $user) {
        $user_css = 'user-header object-header';
        if ($user['account_closed_date']) {
            $user_css .= ' object-closed';
        }
        echo "
            <div id='user-$user_id' class='user object-element'>
                <div class='$user_css'>
                    <div class='user-details'>
                        <div class='user-creation-date date-time'>
                            ${user['user_creation_date']}
                        </div>
                        <div class='account-closed-date date-time'>
                            ${user['account_closed_date']}
                        </div>
                    </div> <!-- /user-details -->
                    <div class='user-id'>$user_id</div>
                    <div class='user-name'>
                        <a class='object-ref' href='user.php?id=$user_id'>${user['login_name']}</a>
                    </div>
                </div> <!-- /user-header -->
            </div> <!-- /user-$user_id -->";
    }
    echo "
        </div> <!-- /users-list -->";
}

/*
 * REQUIRED.
 */
include_once ('template.inc');

?>
