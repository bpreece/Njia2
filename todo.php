<?php

include_once('common.inc');

function get_stylesheets() {
    $stylesheets = array('todo.css');
    return $stylesheets;
}

function get_page_id() {
    return 'todo-page';
}

global $projects, $user, $user_list;

function process_query_string() {
    global $projects;
    $user_id = NULL;
    if (isset($_GET['id'])) {
        $user_id = $_GET['id'];
    } else {
        $user_id = get_session_user_id();
    }
    $projects = query_tasks($user_id);
}

function process_form_data() {
    if (isset($_POST['show-todo-button'])) {
        header("Location: todo.php?id=${_POST['user-id']}");
    } else if (isset($_POST['add-project-button'])) {
        process_add_project_form();
    }
}

function process_add_project_form() {
    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }
    
    $user_id = get_session_user_id();

    $project_name = mysqli_real_escape_string($connection, $_POST['project-name']);

    $project_query = "INSERT INTO `project_table` 
            ( `project_name` , `project_owner` ) VALUES ( '$project_name' , '$user_id' )";
    $project_results = mysqli_query($connection, $project_query);
    if (! $project_results) {
        set_user_message(mysqli_error($connection), 'failure');
        return;
    }
    $project_id = mysqli_insert_id($connection);
    
    $access_query = "INSERT INTO `access_table` 
            ( `user_id` , `project_id` ) VALUES ( '$user_id' , '$project_id' )";
    $access_results = mysqli_query($connection, $access_query);
    if (! $access_results) {
        set_user_message(mysqli_error($connection), 'failure');
        return;
    }
    
    header ("Location: project.php?id=$project_id");
}

function query_tasks($user_id) {
    global $projects, $user, $user_list;

    $connection = connect_to_database_session();
    if (!$connection) {
        return;
    }
    if ($user_id) {
        $user = query_user($connection, $user_id);
        if (! $user) {
            header ('Location: todo.php');
        }
    } else {
        $user_id = get_session_user_id();
        $user = get_session_user();
    }

    $session_id = get_session_id();
    $task_query = "SELECT P.`project_id` , P.`project_name` , 
                T.`task_id` , T.`task_summary` , T.`parent_task_id` , 
                X.`timebox_id` , X.`timebox_name` , X.`timebox_end_date` 
                FROM  `access_table` AS A 
                INNER JOIN `project_table` AS P ON A.`project_id` = P.`project_id` 
                INNER JOIN `task_table` AS T ON P.`project_id` = T.`project_id` 
                INNER JOIN `timebox_table` AS X ON T.`timebox_id` = X.`timebox_id` 
                WHERE A.`user_id` = '$user_id' AND
                    T.`user_id` = '$user_id' AND
                    T.`task_status` <> 'closed' AND X.`timebox_end_date` >= CURRENT_DATE()
                ORDER BY T.`task_id`";

    $task_results = mysqli_query($connection, $task_query);
    if ($task_results == false) {
        set_user_message(mysqli_error($connection), 'failure');
        return;
    }
    $num_tasks = mysqli_num_rows($task_results);
    if ($num_tasks > 0) {
        $projects = array();
        for ($i = 0; $i < $num_tasks; $i++) {
            $result = mysqli_fetch_array($task_results);

            $project_id = $result['project_id'];
            if (! array_key_exists($project_id, $projects)) {
                $project = array();
                $project['project-id'] = $project_id;
                $project['project-name'] = $result['project_name'];
                $project['project-tasks'] = array();
                $projects[$project_id] = $project;
            }
            $tasks = $projects[$project_id]['project-tasks'];

            $task_id = $result['task_id'];
            if (! array_key_exists($task_id, $tasks)) {
                $task = array();
                $task['task-id'] = $task_id;
                $task['parent-task-id'] = $result['parent_task_id'];
                $task['task-summary'] = $result['task_summary'];
                $task['timebox-id'] = $result['timebox_id'];
                $task['timebox-name'] = $result['timebox_name'];
                $task['timebox-end-date'] = $result['timebox_end_date'];
                $projects[$project_id]['project-tasks'][$task_id] = $task;
            }
        }
    }
    
    $users_query = "SELECT DISTINCT U.`user_id` , U.`login_name` 
                FROM  `access_table` AS A1 
                INNER JOIN `project_table` AS P ON A1.`project_id` = P.`project_id` 
                INNER JOIN `access_table` AS A2 ON P.`project_id` = A2.`project_id`
                INNER JOIN `user_table` as U ON A2.`user_id` = U.`user_id` 
                WHERE A1.`user_id` = '$user_id'
                ORDER BY U.`login_name`";
    $users_result = mysqli_query($connection, $users_query);
    if (! $users_result) {
        set_user_message(mysqli_error($connection), 'failure');
        return;
    }
    $user_list = array();
    while ($project_user = mysqli_fetch_array($users_result)) {
        $user_list[$project_user['user_id']] = $project_user['login_name'];
    }

    return $projects;
}

function query_user($connection, $user_id) {
    $user_query = "SELECT U.`user_id` , U.`login_name` 
                FROM `user_table` AS U 
                WHERE U.`user_id` = '$user_id'";
    $user_result = mysqli_query($connection, $user_query);
    if (! $user_result) {
        set_user_message(mysqli_error($connection), 'failure');
        return;
    }
    $num_users = mysqli_num_rows($user_result);
    if ($num_users == 0) {
        return;
    } else {
        return mysqli_fetch_array($user_result);
    }
}

function show_sidebar() {
    global $user, $user_list;
    echo "
        <h3>To-do options</h3>";
    if (! $user) {
        return;
    }
    echo "
        <div class='sidebar-block'>
            <form id='add-subtask-form' method='post'>
                <div id='subtask-summary'>
                    <label for='subtask-summary'>Show to-do list for:</label>
                    <select name='user-id'>";
            foreach ($user_list as $todo_user_id => $login_name) {
                $selected = ($user['user_id'] == $todo_user_id) ? "selected='selected'" : "";
                echo "
                        <option value='$todo_user_id' $selected>$login_name</option>";
            }
            echo "
                    </select>
                </div>
                <input type='submit' name='show-todo-button' value='Show to-do list'></input>
            </form>
        </div>
        <div class='sidebar-block'>
            <form id='add-project-form' method='post'>
                <div id='subtask-summary'>
                    <label for='project-name'>Project name:</label>
                    <input style='width:100%' type='text' name='project-name'></input>
                </div>
                <input type='submit' name='add-project-button' value='Create new project'></input>
            </form>
        </div>";
}

function show_content() 
{
    global $projects, $user;
    
    if (! $user) {
        return;
    }
    
    echo "
        <h3>To-do list for <span class='h3-user'>${user['login_name']}</span></h3>";
    if (! $projects) {
        echo "
            <div>There are no tasks in your current to-do list.</div>";
        return;
    }

    echo "
        <div id=todo-projects-list>";
    foreach ($projects as $project_id => &$project) {
        echo "
            <div id='project-$project_id' class='project-item'>
                <div class='project-info'>
                <div class='project-id'>$project_id</div>
                <div class='project-name'>
                    <a href='project.php?id=$project_id'>{$project['project-name']}</a>
                </div>
                </div> <!-- /project-info -->";
        $tasks = &$project['project-tasks'];
        echo "    
                <div class='project-tasks-list'>";
        foreach ($tasks as $task_id => &$task) {
            echo "        
                    <div id='task-$task_id' class='task-item'>
                        <div class='task-info'>
                            <div class='task-id'>$task_id</div>
                            <div class='task-summary'><a href='task.php?id=$task_id'>{$task['task-summary']}</a></div>
                            <div class='task-timebox-id'>{$task['timebox-id']}</div>
                            <div class='task-timebox-name'>{$task['timebox-name']}</div>
                            <div class='task-timebox-end-date'>{$task['timebox-end-date']}</div>
                        </div> <!-- /task-info -->
                    </div> <!-- /task-$task_id -->";
        }
        echo "    
                </div> <!-- /project-tasks-list -->
            </div> <!-- /project-$$project_id -->";
    }
    echo "</div> <!-- /todo-projects-list -->";
}

include_once ('template.inc');
?>
