<?php

include_once 'common.inc';
include_once 'timebox/timebox_list_options_form.php';
include_once 'timebox/timebox_form.php';
include_once 'timebox/new_timebox_form.php';
include_once 'timebox/query_timeboxes.php';
include_once 'task/tasks_list.php';

global $timebox_id, $timebox;
global $show_closed_tasks;
$show_closed_tasks = FALSE;

function get_stylesheets() {
    $stylesheets = array('timebox.css');
    return $stylesheets;
}

function get_page_id() {
    global $timebox_id;
    return "timebox-$timebox_id";
}

function get_page_class() {
    return 'timebox-page';
}

function process_query_string() {
    global $timebox_id, $timebox;
    global $show_closed_tasks;
    
    if (isset($_GET['id'])) {
        $timebox_id = $_GET['id'];
    }
    
    if (isset($_GET['tx'])) {
        $show_closed_tasks = TRUE;
    }
}

function process_form_data() {
    process_timebox_form()
    || process_new_timebox_form();
}

function prepare_page_data() {
    global $timebox_id, $timebox;

    if (connect_to_database_session()) {
        $timebox = query_user_timeboxes($timebox_id);

        global $show_closed_tasks;
        $timebox['task-list'] = query_timebox_tasks($timebox_id, $show_closed_tasks);
    }
}

function show_sidebar() {
    global $timebox_id, $timebox;
    
    if (! $timebox) {
        return;
    }
    
    echo "
        <div class='sidebar-block'>";
    show_new_timebox_form($timebox['project_id']);
    echo "
        </div>";
}

function show_content() {
    global $timebox, $timebox_id;
    global $show_closed_tasks;
    
    if (! $timebox) {
        return;
    }
    
    echo "                
        <h3>Timebox $timebox_id</h3>";
    show_timebox_form($timebox_id, $timebox);
    
    echo "
        <div id='tasks-header'>
            <div class='header-controls'>";
    show_timebox_options_form($timebox_id, $show_closed_tasks);
    echo "
            </div>
            <h4>Tasks</h4>
        </div>
        <div class='task-list object-list'>";
    if (count($timebox['task-list']) == 0) {
        echo "
            <div>There are no tasks to display.</div>";
    } else {
        show_tasks_list($timebox['task-list']);
    }
    echo "
        </div> <!-- /task-list -->";
}

include_once ('template.inc');

?>
