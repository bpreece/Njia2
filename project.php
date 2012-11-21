<?php

include_once 'common.inc';
include_once 'task/new_task_form.php';
include_once 'timebox/new_timebox_form.php';
include_once 'project/close_project_form.php';
include_once 'project/reopen_project_form.php';
include_once 'project/add_user_to_project_form.php';
include_once 'project/remove_user_from_project_form.php';
include_once 'project/project_options_form.php';
include_once 'project/project_form.php';

global $project_id, $project;
global $show_closed_tasks;
global $timebox_end_date;
$show_closed_tasks = FALSE;
$timebox_end_date = '';

function process_query_string() {
    global $show_closed_tasks;
    global $timebox_end_date;
    global $project_id, $project;
    
    if (isset($_GET['id'])) {
        $project_id = $_GET['id'];
    } else {
        header ('Location: projects.php');
    }
    
    if (isset($_GET['tx'])) {
        $show_closed_tasks = TRUE;
    }
    
    if (isset($_GET['s'])) {
        $timebox_end_date = $_GET['s'];
    }
}

global $project, $project_id;

function process_form_data() {
    process_project_form()
    || process_new_task_form()
    || process_close_project_form()
    || process_reopen_project_form()
    || process_new_timebox_form()
    || process_add_user_to_project_form()
    || process_remove_user_from_project_form();
}

function prepare_page_data() {
    global $project_id, $project;
    
    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    $session_id = get_session_id();
    $user_id = get_session_user_id();
    $sql_project_id = mysqli_real_escape_string($connection, $project_id);
    $project_query = "SELECT P.* , 
            O.`user_id` AS `owner_id` , O.`login_name` AS `owner_name` 
        FROM `access_table` AS A 
        INNER JOIN `project_table` AS P ON A.`project_id` = P.`project_id` 
        INNER JOIN `user_table` AS O ON P.`project_owner` = O.`user_id`
        WHERE P.`project_id` = '$sql_project_id' AND A.`user_id` = $user_id";
    $project_result = mysqli_query($connection, $project_query);
    if (! $project_result) {
        set_user_message(mysqli_error($connection), 'failure');
        return null;
    }    
    $project = mysqli_fetch_array($project_result);
    if (! $project) {
        set_user_message("Project $project_id is not a valid project", 'warning');
        return;
    }
    if ($project['project_status'] == 'closed') {
        set_user_message('This project has been closed', 'warning');
    } else {
        $project['can-close'] = TRUE;
    }
    $project_id = $project['project_id'];
    
    global $show_closed_tasks;
    $task_query = "SELECT T.`task_id` , T.`task_summary` , `task_status` ,  `timebox_id` 
        FROM `task_table` AS T
        WHERE T.`project_id` = '$sql_project_id' AND T.`parent_task_id` IS NULL";
    if (! $show_closed_tasks) {
        $task_query .= "
            AND T.`task_status` <> 'closed' ";
    }
    $task_query .= "
        ORDER BY T.`task_id`";

    $task_result = mysqli_query($connection, $task_query);
    $project['task_list'] = array();
    while ($task = mysqli_fetch_array($task_result)) {
        $project['task_list'][$task['task_id']] = $task;
        if ($task['task_status'] != 'closed') {
            $project['can-close'] = FALSE;
        }
    }
    
    global $timebox_end_date;
    $timebox_query = "SELECT X.* FROM `timebox_table` AS X
        WHERE X.`project_id` = '$sql_project_id'";
    if ($timebox_end_date) {
        $timebox_query .= "
            AND X.`timebox_end_date` >= '$timebox_end_date'";
    } else {
        $timebox_query .= "
            AND X.`timebox_end_date` >= NOW()";
    }
    $timebox_query .= "
        ORDER BY X.`timebox_end_date`";

    $project['timebox_list'] = array();
    
    $timebox_result = mysqli_query($connection, $timebox_query);
    while ($timebox = mysqli_fetch_array($timebox_result)) {
        $project['timebox_list'][$timebox['timebox_id']] = $timebox;
    }
    
    $user_query = "SELECT U.`user_id` , U.`login_name` 
        FROM `access_table` AS A
        INNER JOIN `user_table` AS U ON A.`user_id` = U.`user_id`
        WHERE A.`project_id` = '$sql_project_id'
        ORDER BY U.`login_name`";
    $user_result = mysqli_query($connection, $user_query);
    $project['user_list'] = array();
    while ($user = mysqli_fetch_array($user_result)) {
        $project['user_list'][$user['user_id']] = $user['login_name'];
    }
    
    return $project;
}

function get_stylesheets() {
    $stylesheets = array('project.css');
    return $stylesheets;
}

function get_page_id() {
    return 'project-page';
}

function get_page_class() {
    global $project;
    if (! $project) {
        return '';
    }
    $page_class = "project-${project['project_id']}";
    if ($project['project_status'] == 'closed') {
        $page_class .= " project-closed";
    }
    return $page_class;
}

function show_sidebar() {
    global $project_id, $project;

    if (! $project) {
        return;
    }

    if ($project['project_status'] == 'closed') {
        echo "
            <div class='sidebar-block'>";
        show_reopen_project_form($project_id);
        echo "
            </div>";
    } else {
        echo "
            <div class='sidebar-block'>";
        show_new_task_form($project_id);
        echo "
            </div>";
        
        echo "
            <div class='sidebar-block'>";
        show_new_timebox_form($project_id);
        echo "
            </div>";
        
        if ($project['project_owner'] == get_session_user_id()) {
            echo "
                <div class='sidebar-block'>";
            show_add_user_to_project_form($project_id);
            echo "
                </div>";
        }
        
        if ($project['project_owner'] == get_session_user_id() && $project['can-close']) {
            echo "
                <div class='sidebar-block'>";
            show_close_project_form($project_id);
            echo "
                </div>";
        }
    }
}

function show_content() 
{
    global $show_closed_tasks;
    global $timebox_end_date, $end_date;
    global $project_id, $project;
    
    echo "
        <h3>Project $project_id</h3>";
    
    if (! $project) {
        return;
    }
    
    show_project_form($project_id, $project);

    echo "
        <div id='tasks-header'>
            <div class='header-controls'>";
    show_project_options_form($project_id, 'closed-tasks', $show_closed_tasks, $timebox_end_date);
    echo "
            </div>
            <h4>Tasks</h4>
        </div>
        <div class='task-list object-list'>";
    foreach ($project['task_list'] as $task_id => $task) {
        echo "
            <div id='task-$task_id' class='task object-element'>
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

    echo "
        <div id='timeboxes-header'>
            <div class='header-controls'>";
    show_project_options_form($project_id, 'timeboxes', $show_closed_tasks, $timebox_end_date);
    echo "
            </div>
            <h4>Timeboxes</h4>
        </div>
        <div class='timebox-list object-list'>";
    foreach ($project['timebox_list'] as $timebox_id => $timebox) {
        echo "
            <div id='timebox-$timebox_id' class='timebox object-element'>
                <div class='timebox-details'>${timebox['timebox_end_date']}</div>
                <div class='timebox-header object-header'>
                    <div class='timebox-id'>$timebox_id</div>
                    <div class='timebox-name'>
                        <a class='object-ref' href='timebox.php?id=$timebox_id'>${timebox['timebox_name']}</a>
                    </div> <!-- /timebox-name -->
                </div> <!-- /timebox-info -->
            </div> <!-- /timebox-$timebox_id -->";
    }
    echo "
        </div> <!-- /timebox-list -->";
    
    echo "
        <h4>People</h4>
        <div class='user-list object-list'>";
    foreach ($project['user_list'] as $user_id => $user_name) {
        echo "
            <div id='user-$user_id' class='user object-element'>
                <div class='user-header object-header'>
                    <div class='user-id'>$user_id</div>";
        $project_owner = $project['project_owner'];
        if ($project_owner == get_session_user_id() && $project_owner != $user_id) {
            echo "
                    <div style='float:right'>";
            show_remove_user_from_project_form($project_id, $user_id, $user_name);
            echo "
                    </div> <!-- /remove-user-$user_id-form -->";
        }
        echo "
                    <div class='user-name'>
                        <a class='object-ref' href='user.php?id=$user_id'>$user_name</a>
                    </div> <!-- /user-name -->
                </div> <!-- /user-info -->
            </div> <!-- /user-$user_id -->";
    }
    echo "
        </div> <!-- /user-list -->
        ";
}

include_once ('template.inc');

?>
