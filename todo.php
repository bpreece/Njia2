<?php

include_once 'common.inc';
include_once 'project/new_project_form.php';
include_once 'task/close_task_form.php';
include_once 'todo/todo_list_form.php';
include_once 'user/query_users.php';

function get_stylesheets() {
    $stylesheets = array('todo.css');
    return $stylesheets;
}

function get_page_class() {
    return 'todo-page';
}

global $projects, $user, $user_id, $user_list;

function process_query_string() {
    global $user_id;

    if (isset($_GET['id'])) {
        $user_id = $_GET['id'];
    }
}

function process_form_data() {
    process_new_project_form()
    || process_close_task_form();
}

function prepare_page_data() {
    global $projects, $user_id, $user, $user_list;

    $connection = connect_to_database_session();
    if (!$connection) {
        return;
    }

    if (! $user_id) {
        $user_id = get_session_user_id();
    }
    $user = query_user_vitals($user_id);
    $session_id = get_session_id();
    $projects = query_user_tasks($user_id);
    $user_list = query_known_users($user_id);

    return $projects;
}

function show_sidebar() {
    global $user_id, $user_list;
    
    echo "
        <div class='sidebar-block'>";
    show_todo_list_form($user_list, $user_id);
    echo "
        </div>";
    
    echo "
        <div class='sidebar-block'>";
    show_new_project_form();
    echo "
        </div>";
}

function show_content() 
{
    global $projects, $user;
    
    if (! $user) {
        return;
    }
    
    echo "
        <h3><a class='object-ref' href='user.php?id=${user['user_id']}'>${user['login_name']}</a></h3>";
    if (! $projects) {
        echo "
            <div>There are no tasks in the current to-do list for ${user['login_name']}.</div>";
        return;
    }

    echo "
        <div id=project-list>";
    foreach ($projects as $project_id => &$project) {
        foreach($project['timebox-list'] as $timebox_id => &$timebox) {
            echo "
            <div id='project-$project_id' class='project'>
                <div class='project-header object-header'>
                    <div class='project-id'>$project_id</div>
                    <div class='project-name'>
                        <a class='object-ref' href='project.php?id=$project_id'>{$project['project-name']}</a>
                    </div>
                    <div class='timebox-info'>
                        <div class='timebox-id'>$timebox_id</div>
                        <div class='timebox-end-date'>
                            <a class='object-ref' href='timebox.php?id=$timebox_id'>${timebox['timebox-end-date']}</a>
                        </div>
                        <div class='timebox-name'>
                            <a class='object-ref' href='timebox.php?id=$timebox_id'>{$timebox['timebox-name']}</a>
                        </div> <!-- /timebox-name -->
                    </div> <!-- /timebox-info -->
                </div> <!-- /project-info -->
                <div class='task-list'>";
            foreach ($timebox['task-list'] as $task_id => &$task) {
                echo "        
                    <div id='task-$task_id' class='task'>
                        <div style='float:right'>";
                show_close_task_form($task_id, FALSE);
                echo "
                        </div>
                        <div class='task-id'>$task_id</div>
                        <div class='task-summary'>
                            <a class='object-ref' href='task.php?id=$task_id'>{$task['task-summary']}</a>
                        </div>
                    </div> <!-- /task-$task_id -->";
            }
            echo "    
                </div> <!-- /task-list -->
            </div> <!-- /project-$$project_id -->";
        }
    }
    echo "</div> <!-- /project-list -->";
}

include_once ('template.inc');
?>
