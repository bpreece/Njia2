<?php

include_once('common.inc');

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

global $task;

function process_form_data() {
    if (isset($_POST['update-button'])) {
        process_task_form();
    } else if (isset($_POST['add-subtask-button'])) {
        process_add_subtask_form();
    } else if (isset($_POST['close-task-button'])) {
        process_close_task_form();
    } else if (isset($_POST['reopen-task-button'])) {
        process_reopen_task_form();
    }
}

function process_task_form() {
    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    $task_id = mysqli_real_escape_string($connection, $_POST['task-id']);
    $task_summary = mysqli_real_escape_string($connection, $_POST['task-summary']);
    $task_discussion = mysqli_real_escape_string($connection, $_POST['task-discussion']);

    $query = "UPDATE `task_table` SET ";
    if (isset($_POST['task-user'])) {
        $user = mysqli_real_escape_string($connection, $_POST['task-user']);
        $query .= "`user_id`='$user', ";
    }
    if (isset($_POST['task-timebox'])) {
        $timebox = mysqli_real_escape_string($connection, $_POST['task-timebox']);
        $query .= "`timebox_id`='$timebox', ";
    }
    $query .= "`task_discussion`='$task_discussion', 
        `task_summary`='$task_summary' ,
        `task_modified_date` = CURRENT_TIMESTAMP() 
        WHERE `task_id` = '$task_id'";

    $results = mysqli_query($connection, $query);
    if (! $results) {
        set_user_message(mysqli_error($connection), "warning");
        return null;
    }

    set_user_message("The changes have been applied", 'success');
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
        `user_id` = null , `timebox_id` = null
        WHERE `task_id` = '$parent_task_id'";

    header("Location:task.php?id=$new_task_id");
}

function process_close_task_form() {
    update_task_status('closed');
}

function process_reopen_task_form() {
    update_task_status('open');
}

function update_task_status($status) {
    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    $task_id = mysqli_real_escape_string($connection, $_POST['task-id']);
    $query = "UPDATE `task_table` 
        SET `task_status` = '$status' 
        WHERE `task_id` = '$task_id'";
    $results = mysqli_query($connection, $query);
    if (! $results) {
        set_user_message(mysqli_error($connection), "warning");
        return null;
    }    
    
    header("Location:task.php?id=$task_id");
}

function process_query_string() {
    global $task_id, $task;
    if (isset($_GET['id'])) {
        $task_id = $_GET['id'];
        $task = query_task($task_id);
    }
}

function query_task($task_id) {
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
    $num_rows = mysqli_num_rows($task_result);
    if ($num_rows == 0) {
        set_user_message("Task ID $task_id not recognized", 'warning');
        return null;
    }
    
    $task = mysqli_fetch_array($task_result);
    if ($task['task_status'] == 'closed') {
        set_user_message('This task has been closed', 'warning');
    } else {
        $task['can-close'] = TRUE;
    }
    $project_id = $task['project_id'];
    
    $subtask_query = "SELECT T.`task_id` , T.`task_summary` , `task_status`
                 FROM `task_table` AS T
                 WHERE T.`parent_task_id` = '$task_id'
                 ORDER BY T.`task_id`";
    $subtask_result = mysqli_query($connection, $subtask_query);
    if (mysqli_num_rows($subtask_result) > 0) {
        $subtask_list = array();
        while ($subtask = mysqli_fetch_array($subtask_result)) {
            $subtask_list[$subtask['task_id']] = $subtask;
            if ($subtask['task_status'] != 'closed') {
                $task['can-close'] = FALSE;
            }
        }
        $task['subtask_list'] = $subtask_list;
    } else {
        $user_query = "SELECT U.`user_id` , U.`login_name`
                     FROM `access_table` AS A 
                     INNER JOIN `user_table` AS U on U.`user_id` = A.`user_id`
                     WHERE A.`project_id` = '$project_id'
                     ORDER BY U.`login_name`";
        $user_result = mysqli_query($connection, $user_query);
        if (mysqli_num_rows($user_result) > 0) {
            $user_list = array();
            while ($user = mysqli_fetch_array($user_result)) {
                $user_list[$user['user_id']] = $user;
            }
            $task['users_list'] = $user_list;
        }

        $timebox_query = "SELECT X.`timebox_id` , X.`timebox_name` , X.`timebox_end_date` 
                     FROM `timebox_table` AS  X
                     WHERE X.`project_id` = '$project_id'
                     ORDER BY X.`timebox_end_date`, X.`timebox_id`";
        $timebox_result = mysqli_query($connection, $timebox_query);
        if (mysqli_num_rows($timebox_result) > 0) {
            $timebox_list = array();
            while ($timebox = mysqli_fetch_array($timebox_result)) {
                $timebox_list[$timebox['timebox_id']] = $timebox;
            }
            $task['timebox_list'] = $timebox_list;
        }
    }
    
    return $task;
}


function show_sidebar() {
    global $task;
    echo "
        <h3>Options</h3>";
    if (! $task) {
        return;
    }
    if ($task['task_status'] == 'closed') {
        if ($task['parent_task_status'] != 'closed') {
            echo "
        <div class='sidebar-block'>
            <form id='close-task-form' method='post'>
                <input type='hidden' name='task-id' value='${task['task_id']}'>
                <input type='submit' name='reopen-task-button' value='Reopen this task'></input>
            </form>
        </div>";
        }
    } else {
        echo "
        <div class='sidebar-block'>
            <form id='add-subtask-form' method='post'>
                <input type='hidden' name='parent-task-id' value='${task['task_id']}'>
                <input type='hidden' name='project-id' value='${task['project_id']}'>
                <div id='subtask-summary'>
                    <label for='subtask-summary'>Subtask Summary:</label>
                    <input style='width:100%' type='text' name='subtask-summary'></input>
                </div>
                <input type='submit' name='add-subtask-button' value='Add Subtask'></input>
            </form>
        </div>";
        if ($task['can-close']) {
            echo "
        <div class='sidebar-block'>
            <form id='close-task-form' method='post'>
                <input type='hidden' name='task-id' value='${task['task_id']}'>
                <input type='submit' name='close-task-button' value='Close this task'></input>
            </form>
        </div>";
        }
    }
}

function show_content() 
{    
    global $task;
    if (!$task) {
        set_user_message("There was an error retrieving the task", 'warning');
        return;
    }
    
    echo "
        <h3>Task ${task['task_id']}</h3>
        <form id='task-form' class='main-form' method='post'>
            <input type='hidden' name='task-id' value='${task['task_id']}'>
            <input type='hidden' name='project-id' value='${task['project_id']}'>                
            
            <div id='project_name'>
                <label>Project:</label>
                <a class='object-ref' href='project.php?id=${task['project_id']}'>${task['project_name']}</a>
            </div>";
            
    if (isset($task['parent_task_summary'])) {
        echo "
            <div id='parent-task'>
                <label>Parent task:</label>
                <a class='object-ref' href='task.php?id=${task['parent_task_id']}'>${task['parent_task_summary']}</a>
            </div>";
    }

    echo "
            <div id='task-summary'>
                <label for='task-summary'>Summary:</label>
                <input style='width:50%' type='text' name='task-summary' value='${task['task_summary']}'></input>
            </div>
            
            <div id='task-discussion'>
                <label for='task-discussion'>Discussion:</label>
                <textarea name='task-discussion' rows='10' style='width:50%'>${task['task_discussion']}</textarea>
            </div>";

    if (! array_key_exists('subtask_list', $task)) {
        if (array_key_exists('users_list', $task)) {
            echo "
            <div id='task-user'>
                <label for='task-user'>Assigned to:</label>
                <select name='task-user'>
                    <option value=''></option>";
            foreach ($task['users_list'] as $user) {
                $selected = ($task['user_id'] == $user['user_id']) ? "selected='selected'" : "";
                echo "
                    <option value='${user['user_id']}' $selected>${user['login_name']}</option>";
            }
            echo "
                </select>
            </div>";
        }

        if (array_key_exists('timebox_list', $task)) {
            echo "
            <div id='task-timebox'>
                <label for='task-timebox'>Timebox:</label>
                <select name='task-timebox'>
                    <option value=''></option>";
            foreach ($task['timebox_list'] as $timebox) {
                $selected = ($task['timebox_id'] == $timebox['timebox_id']) ? "selected='selected'" : "";
                echo "
                    <option value='${timebox['timebox_id']}' $selected>
                        ${timebox['timebox_name']} (${timebox['timebox_end_date']})
                    </option>";
            }
            echo "
                </select>
            </div>";
        }
    }
            
    echo "
            <div id='task-created-date'>
                <label>Created:</label>
                ${task['task_created_date']}
            </div>
            
            <div id='task-modified-date'>
                <label>Modified:</label>
                ${task['task_modified_date']}
            </div>
                
            <div id='form-controls'>
                <input type='submit' name='update-button' value='Update'></input>
            </div> <!-- /form-controls -->
        </form>
        ";


                
    if (array_key_exists('subtask_list', $task)) {
        echo "
            <div id='tasks-header'>
                <h4>Subtasks</h4>
            </div>
            <div class='task-list'>";
        foreach ($task['subtask_list'] as $subtask_id => $subtask) {
            echo "
                <div id='task-$subtask_id' class='task'>
                    <div class='task-info task-${subtask['task_status']}'>
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
}



include_once ('template.inc');

?>
