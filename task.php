<?php

include_once 'common.inc';
include_once 'task/new_task_form.php';
include_once 'task/close_task_form.php';
include_once 'task/reopen_task_form.php';
include_once 'log/new_log_form.php';
include_once 'task/new_task_form.php';
include_once 'task/task_form.php';
include_once 'task/query_tasks.php';
include_once 'task/tasks_list.php';
include_once 'project/query_projects.php';

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

function prepare_page_data() {
    global $task_id, $task;

    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    $task = query_task($connection, $task_id);
    $project_id = $task['project_id'];

    // if the task has subtasks, then we'll list them;  otherwise, this task
    // can be assigned, so we'll need a list of users and a list of timeboxes.
    
    $open_tasks = FALSE;
    $task['subtask-list'] = query_subtasks($connection, $task_id, $open_tasks);
    if (count($task['subtask-list']) == 0) {
        $task['users_list'] = query_project_users($connection, $project_id);
        $task['timebox_list'] = query_project_timeboxes($connection, $project_id, $task['timebox_end_date']);
    }
    
    global $total_hours;
    $total_hours = 0;
    $task['log-list'] = query_task_log($connection, $task_id, $total_hours);
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

    if (count($task['subtask-list']) > 0) {
        echo "
            <div id='tasks-header'>
                <h4>Subtasks</h4>
            </div>
            <div id='task-$task_id-subtask-list' class='task-list object-list'>";
        show_tasks_list($task['subtask-list']);
        echo "
            </div> <!-- /task-list -->";
    }

    echo "
        <div id='work-log-header'>
            <div class='work-log-details'>Total hours: $total_hours</div>
            <h4>Work log</h4>
        </div>
        <div id='task-$task_id-work-log-list' class='work-log-list'>";
    if (count($task['log-list']) == 0) {
        echo "
            No work has been logged for this task";
    } else {
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
    }
    echo "
        </div> <!-- /work-log-list ;-->";
}



include_once ('template.inc');

?>
