<?php

include_once 'common.inc';
include_once 'timebox/timebox_list_options_form.php';
include_once 'timebox/timebox_form.php';

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
    process_timebox_form();
}

function prepare_page_data() {
    global $timebox_id, $timebox;

    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    $session_id = get_session_id();
    $timebox_query = "SELECT X.* , P.`project_name` 
                FROM `session_table` AS S
                INNER JOIN `access_table` AS A ON S.`user_id` = A.`user_id` 
                INNER JOIN `timebox_table` AS X ON A.`project_id` = X.`project_id` 
                INNER JOIN `project_table` AS P ON X.`project_id` = P.`project_id`
                WHERE S.`session_id` = '$session_id' and X.`timebox_id` = '$timebox_id'";
    
    $timebox_result = mysqli_query($connection, $timebox_query);
    if (! $timebox_result) {
        set_user_message(mysqli_error($connection), "warning");
        return;
    } else if (mysqli_num_rows($timebox_result) == 0) {
        set_user_message("Timebox ID $timebox_id not recognized", 'warning');
        return;
    }
    $timebox = mysqli_fetch_array($timebox_result);
    
    global $show_closed_tasks;
    $task_query = "SELECT T.`task_id` , T.`task_summary` , T.`task_status` 
                FROM `task_table` AS T
                WHERE T.`timebox_id` = '$timebox_id' ";
    if (! $show_closed_tasks) {
        $task_query .= "
                    AND T.`task_status` <> 'closed' ";
    }
    $task_result = mysqli_query($connection, $task_query);
    $timebox['task_list'] = array();
    if (! $task_result) {
        set_user_message(mysqli_error($connection), "warning");
    } else {
        while ($task = mysqli_fetch_array($task_result)) {
            $timebox['task_list'][$task['task_id']] = $task;
        }
    }

    return $timebox;
}

function show_sidebar() {
    global $timebox;
    global $show_closed_tasks;
    
    if (! $timebox) {
        return;
    }
    
    echo "
        <div class='sidebar-block'>";
    show_timebox_list_options_form($show_closed_tasks);
    echo "
        </div>";
}

function show_content() {
    global $timebox, $timebox_id;
    if (! $timebox) {
        return;
    }
    
    echo "                
        <h3>Timebox $timebox_id</h3>";
    show_timebox_form($timebox_id, $timebox);
                
    if (array_key_exists('task_list', $timebox)) {
        echo "
            <h4>Tasks</h4>
            <div class='task-list'>";
        foreach ($timebox['task_list'] as $task_id => $task) {
            echo "
                <div id='task-$task_id' class='task'>
                    <div class='task-header object-header object-${task['task_status']}'>
                        <div class='task-details'>";
            if ($task['task_status'] != 'open') {
                echo "
                            ${task['task_status']}";
            }
            echo "
                        </div> <!-- /task-details -->
                        <div class='task-id'>$task_id</div>
                        <div class='task-summary'>
                            <a class='object-ref' href='task.php?id=$task_id'>${task['task_summary']}</a>
                        </div> <!-- /task-summary -->";
            echo "
                    </div> <!-- /task-info -->
                </div> <!-- /task-$task_id -->";
        }
        echo "
            </div> <!-- /task-list -->";
    }
}

include_once ('template.inc');

?>
