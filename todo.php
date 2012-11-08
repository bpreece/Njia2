<?php

include_once('common.inc');

function get_stylesheets() {
    $stylesheets = array('todo.css');
    return $stylesheets;
}

function get_page_class() {
    return 'todo-page';
}

global $projects, $user, $user_id, $user_list;

function process_query_string() {
    global $projects, $user_id;
    $user_id = NULL;
    if (isset($_GET['id'])) {
        $user_id = $_GET['id'];
    } else {
        $user_id = get_session_user_id();
    }
}

function process_form_data() {
    if (isset($_POST['show-todo-button'])) {
        header("Location: todo.php?id=${_POST['user-id']}");
    } else if (isset($_POST['add-project-button'])) {
        process_add_project_form();
    } else if (isset($_POST['close-task-button'])) {
        process_close_task();
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

function process_close_task() {
    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    $task_id = mysqli_real_escape_string($connection, $_POST['task-id']);
    $query = "UPDATE `task_table` 
        SET `task_status` = 'closed' 
        WHERE `task_id` = '$task_id'";
    $results = mysqli_query($connection, $query);
    if (! $results) {
        set_user_message(mysqli_error($connection), "warning");
        return null;
    }    
}

function prepare_page_data() {
    global $projects, $user_id, $user, $user_list;

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
                ORDER BY X.`timebox_end_date` , P.`project_id` , T.`task_id`";

    $task_results = mysqli_query($connection, $task_query);
    $projects = array();
    if (! $task_results) {
        set_user_message(mysqli_error($connection), 'failure');
        return;
    }
    $num_tasks = mysqli_num_rows($task_results);
    while ($result = mysqli_fetch_array($task_results)) {
        $project_id = $result['project_id'];
        if (! array_key_exists($project_id, $projects)) {
            $projects[$project_id] = array(
                'project-id' => $project_id,
                'project-name' => $result['project_name'],
                'timebox-list' => array(),
            );
        }

        $timebox_id = $result['timebox_id'];
        if (! array_key_exists($timebox_id, $projects[$project_id]['timebox-list'])) {
            $projects[$project_id]['timebox-list'][$timebox_id] = array(
                'timebox-id' => $result['timebox_id'],
                'timebox-name' => $result['timebox_name'],
                'timebox-end-date' => $result['timebox_end_date'],
                'task-list' => array(),
            );
        }

        $task_id = $result['task_id'];
        if (! array_key_exists($task_id, $projects[$project_id]['timebox-list'][$timebox_id]['task-list'])) {
            $projects[$project_id]['timebox-list'][$timebox_id]['task-list'][$task_id] = array(
                'task-id' => $task_id,
                'parent-task-id' => $result['parent_task_id'],
                'task-summary' => $result['task_summary'],
            );
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
    if (! $user) {
        return;
    }
    echo "
        <div class='sidebar-block'>
            <form id='show-todo-form' method='post'>
                <div id='user-id-field' class='group'>
                    <label for='user-id'>Show to-do list for:</label>
                    <select name='user-id' style='width:100%'>";
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
                <div id='subtask-summary' class='group'>
                    <label for='project-name'>Project name:</label>
                    <input style='width:100%' type='text' name='project-name'></input>
                </div>
                <input type='submit' name='add-project-button' value='Create a new project'></input>
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
        <h3><a class='object-ref' href='user.php?id=${user['user_id']}'>${user['login_name']}</a></h3>";
    if (! $projects) {
        echo "
            <div>There are no tasks in your current to-do list.</div>";
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
                        <div style='float:right'>
                            <form id='close-task-$task_id' method='post'>
                                <input type='hidden' name='task-id' value='$task_id'></input>
                                <input type='submit' class='close-button' name='close-task-button' title='Close this task' value=''></input>
                            </form>
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
