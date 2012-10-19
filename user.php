<?php


include_once('common.inc');

function get_stylesheets() {
    $stylesheets = array('user.css');
    return $stylesheets;
}

function get_page_id() {
    global $user;
    $page_class = "user-${user['user_id']}";
    return $page_class;
}

function get_page_class() {
    return 'user-page';
}

global $user;

function process_query_string() {
    global $user;
    $user_id = NULL;
    if (isset($_GET['id'])) {
        $user_id = $_GET['id'];
    } else {
        $user_id = get_session_user_id();
    }
    $user = query_user($user_id);
}

function process_form_data() {
    if (isset($_POST['submit-button'])) {
        process_user_form();
    }
}

function process_user_form() {
    
}

function query_user() {
    
}

function show_sidebar() {
    global $user;
    echo "
        <h3>Options</h3>";
    if (! $user) {
        return;
    }

    echo "
    <div class='sidebar-block'>
        <form id='close-task-form' method='post'>
            <input type='hidden' name='task-id' value='${task['task_id']}'>
            <input type='submit' name='reopen-task-button' value='Reopen this task'></input>
        </form>
    </div>";
}

function show_content() 
{    
    global $user;
    if (!$user) {
        set_user_message("There was an error retrieving the information", 'warning');
        return;
    }
    
    echo "
        <h3>User ${user['user_id']}</h3>
        <form id='task-form' class='main-form' method='post'>
            <input type='hidden' name='task-id' value='${user['user_id']}'>
        </form>";
}

include_once ('template.inc');

?>
