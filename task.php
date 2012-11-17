<?php

include_once 'common.inc';
include_once 'task/new_task_form.php';
include_once 'task/close_task_form.php';
include_once 'task/reopen_task_form.php';
include_once 'log/new_log_form.php';
include_once 'task/task_form.php';

function get_stylesheets() {
    $stylesheets = array('task.css');
    return $stylesheets;
}

function get_page_id() {
    return 'task-page';
}

function get_page_class() {
    global $task;
    if (! $task) {
        return "";
    }
    $page_class = "task-${task['task_id']}";
    if ($task['task_status'] == 'closed') {
        $page_class .= " task-closed";
    }
    return $page_class;
}

global $task, $task_id, $total_hours;

function process_query_string() {
    global $task_id, $task;
    if (isset($_GET['id'])) {
        $task_id = $_GET['id'];
    }
}

function process_form_data() {
        process_task_form()
        || process_new_task_form()
        || process_close_task_form()
        || process_reopen_task_form()
        || process_new_log_form();
}

function process_add_subtask_form() {
    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    $parent_task_id = mysqli_real_escape_string($connection, $_POST['parent-task-id']);
    $project_id = mysqli_real_escape_string($connection, $_POST['project-id']);
    $subtask_summary = mysqli_real_escape_string($connection, $_POST['subtask-summary']);
    
    $subtask_query = "INSERT INTO `task_table` 
        ( `task_summary` , `project_id` , `parent_task_id` , `user_id` , 
            `timebox_id` , `task_created_date` )
        ( SELECT '$subtask_summary' , '$project_id' , '$parent_task_id' , 
            `user_id` , `timebox_id` , CURRENT_TIMESTAMP()
          FROM `task_table` WHERE `task_id` = '$parent_task_id' )";

    $subtask_results = mysqli_query($connection, $subtask_query);
    if (! $subtask_results) {
        set_user_message(mysqli_error($connection), "warning");
        return null;
    }    
    $new_task_id = mysqli_insert_id($connection);
    
    $parent_task_query = "UPDATE `task_table` SET 
        `user_id` = NULL , `timebox_id` = NULL
        WHERE `task_id` = '$parent_task_id'";
    $parent_task_results = mysqli_query($connection, $parent_task_query);
    if (! $parent_task_results) {
        set_user_message(mysqli_error($connection), "warning");
    }

    header("Location:task.php?id=$new_task_id");
}

function prepare_page_data() {
    global $task_id, $task;

    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    $session_id = get_session_id();
    $task_id = mysqli_real_escape_string($connection, $task_id);
    $task_query = "SELECT T . * , P.`project_name` , X.`timebox_name` , 
                PT.`task_summary` AS `parent_task_summary` , 
                PT.`task_status` AS `parent_task_status` ,
                U.`login_name`
                FROM `session_table` AS S
                INNER JOIN `access_table` AS A ON S.`user_id` = A.`user_id` 
                INNER JOIN `task_table` AS T ON A.`project_id` = T.`project_id` 
                INNER JOIN `project_table` AS P ON T.`project_id` = P.`project_id` 
                LEFT JOIN `timebox_table` AS X ON T.`timebox_id` = X.`timebox_id` 
                LEFT JOIN `task_table` AS PT ON t.`parent_task_id` = PT.`task_id`
                LEFT JOIN `user_table` AS U ON T.`user_id` = U.`user_id`
                WHERE S.`session_id` = '$session_id' and T.`task_id` = '$task_id'";
    
    $task_result = mysqli_query($connection, $task_query);
    if (! $task_result) {
        set_user_message(mysqli_error($connection), 'warning');
        return;
    } else if (mysqli_num_rows($task_result) == 0) {
        set_user_message("Task ID $task_id is not recognized", 'warning');
        return;
    }
    $task = mysqli_fetch_array($task_result);
    if ($task['task_status'] == 'closed') {
        set_user_message('This task has been closed', 'warning');
    } else {
        $task['can-close'] = TRUE;
    }
    $project_id = $task['project_id'];

    // if the task has subtasks, then we'll list them;  otherwise, this task
    // can be assigned, so we'll need a list of users and a list of timeboxes.
    
    $subtask_query = "SELECT T.`task_id` , T.`task_summary` , `task_status`
                 FROM `task_table` AS T
                 WHERE T.`parent_task_id` = '$task_id'
                 ORDER BY T.`task_id`";
    $subtask_result = mysqli_query($connection, $subtask_query);
    if (! $subtask_result) {
        set_user_message(mysqli_error($connection), 'warning');
    } else if (mysqli_num_rows($subtask_result) > 0) { 
        $task['subtask_list'] = array();
        while ($subtask = mysqli_fetch_array($subtask_result)) {
            $task['subtask_list'][$subtask['task_id']] = $subtask;
            if ($subtask['task_status'] != 'closed') {
                $task['can-close'] = FALSE;
            }
        }
    } else {
        $user_query = "SELECT U.`user_id` , U.`login_name`
                     FROM `access_table` AS A 
                     INNER JOIN `user_table` AS U on U.`user_id` = A.`user_id`
                     WHERE A.`project_id` = '$project_id'
                     ORDER BY U.`login_name`";
        $user_result = mysqli_query($connection, $user_query);
        $task['users_list'] = array();
        if (! $user_result) {
            set_user_message(mysqli_error($connection), 'warning');
        } else {
            while ($user = mysqli_fetch_array($user_result)) {
                $task['users_list'][$user['user_id']] = $user;
            }
        }

        $timebox_query = "SELECT X.`timebox_id` , X.`timebox_name` , X.`timebox_end_date` 
                     FROM `timebox_table` AS  X
                     WHERE X.`project_id` = '$project_id'
                     ORDER BY X.`timebox_end_date`, X.`timebox_id`";
        $timebox_result = mysqli_query($connection, $timebox_query);
        $task['timebox_list'] = array();
        if (! $timebox_result) {
            set_user_message(mysqli_error($connection), 'warning');
        } else {
            while ($timebox = mysqli_fetch_array($timebox_result)) {
                $task['timebox_list'][$timebox['timebox_id']] = $timebox;
            }
        }
    }
    
    global $total_hours;
    
    $log_query = "SELECT L.`log_id` , L.`work_hours` , L.`description` , 
            L.`user_id` , L.`log_time` , 
            U.`login_name` AS `user_name`
        FROM `log_table` AS L
        LEFT JOIN `user_table` AS U ON U.`user_id` = L.`user_id` 
        WHERE L.`task_id` = '$task_id'
        ORDER BY L.`log_id` DESC";
    $log_result = mysqli_query($connection, $log_query);
    $task['log-list'] = array();
    if (! $log_result) {
        set_user_message(mysqli_errno($connection), 'failure');
    } else {
        while ($log = mysqli_fetch_array($log_result)) {
            $task['log-list'][$log['log_id']] = $log;
            $total_hours += $log['work_hours'];
        }
    }
}

function show_sidebar() {
    global $task_id, $task;

    if ($task['task_status'] == 'closed') {
        if ($task['parent_task_status'] != 'closed') {
            echo "
            <div class='sidebar-block'>";
            show_reopen_task_form($task_id);
            echo "
            </div>";
        }
    } else {
        echo "
        <div class='sidebar-block'>";
        show_new_task_form($task['project_id'], $task_id);
        echo "
        </div>";
        echo "
        <div class='sidebar-block'>";
        show_new_log_form($task_id);
        echo "
        </div>";
        if ($task['can-close']) {
            echo "
            <div class='sidebar-block'>";
            show_close_task_form($task_id);
            echo "
            </div>";
        }
    }
}

function show_content() 
{    
    global $task, $total_hours;
    
    if (!$task) {
        set_user_message("There was an error retrieving the task", 'warning');
        return;
    }
    
    $task_id = $task['task_id'];
    echo "
        <h3>Task $task_id</h3>";
    show_task_form($task_id, $task);
    echo "
        ";

    if (array_key_exists('subtask_list', $task)) {
        echo "
            <div id='tasks-header'>
                <h4>Subtasks</h4>
            </div>
            <div id='task-$task_id-subtask-list' class='task-list object-list'>";
        foreach ($task['subtask_list'] as $subtask_id => $subtask) {
            echo "
                <div id='task-$subtask_id' class='task object-element'>
                    <div class='task-header object-header object-${subtask['task_status']}'>
                        <div class='task-details'>";
            if ($subtask['task_status'] != 'open') {
                echo "
                            ${subtask['task_status']}";
            }
            echo "
                        </div> <!-- /task-details -->
                        <div class='task-id'>$subtask_id</div>
                        <div class='task-summary'>
                            <a class='object-ref' href='task.php?id=$subtask_id'>${subtask['task_summary']}</a>
                        </div> <!-- /task-summary -->";
            echo "
                    </div> <!-- /task-info -->
                </div> <!-- /task-$subtask_id -->";
        }
        echo "
            </div> <!-- /task-list -->";
    }

    if (array_key_exists('log-list', $task)) {
        echo "
            <div id='work-log-header'>
                <div class='work-log-details'>Total hours: $total_hours</div>
                <h4>Work log</h4>
            </div>
            <div id='task-$task_id-work-log-list' class='work-log-list'>";
        foreach ($task['log-list'] as $log_id => $log) {
            echo "
                <div id='log-$log_id' class='log-entry'>
                    <div class='log-time'>${log['log_time']}</div>
                    <div class='log-description'>
                        ${log['description']}
                    </div> <!-- /log-description -->
                    <div class='log-details'>
                        <a class='object-ref' href='user.php?id=${log['user_id']}'>${log['user_name']}</a>";
            if ($log['work_hours']) {
                echo ", ${log['work_hours']} hours";
            }
            echo "
                    </div>
                </div> <!-- /log-$log_id -->
                ";
        }
        echo "
            </div> <!-- /work-log-list ;-->";
    }
}



include_once ('template.inc');

?>
