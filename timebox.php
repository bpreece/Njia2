<?php

include_once('common.inc');

global $timebox_id, $timebox;
global $show_closed_tasks;
$show_closed_tasks = '';

function get_stylesheets() {
    $stylesheets = array('timebox.css');
    return $stylesheets;
}

function get_page_id() {
    return 'timebox-page';
}

function get_page_class() {
    global $timebox;
    if (! $timebox) {
        return "";
    }
    $page_class = "timebox-${timebox['timebox_id']}";
    return $page_class;
}

function process_query_string() {
    global $timebox_id, $timebox;
    if (isset($_GET['id'])) {
        $timebox_id = $_GET['id'];
    }
}

function process_form_data() {
    if (isset($_POST['update-button'])) {
        process_timebox_form();
    } else if (isset($_POST['apply-list-options-button'])) {
        process_apply_list_options();
    }
}

function process_apply_list_options() {
    global $show_closed_tasks;

    if (isset($_POST['closed-tasks-option'])) {
        $show_closed_tasks = "checked";
    }
}

function process_timebox_form() {
    global $timebox;
    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    $timebox_id = mysqli_real_escape_string($connection, $_POST['timebox-id']);
    $timebox_name = mysqli_real_escape_string($connection, $_POST['timebox-name']);
    $timebox_end_date= mysqli_real_escape_string($connection, $_POST['timebox-end-date']);
    
    $query = "UPDATE `timebox_table` SET
        `timebox_name` = '$timebox_name' , 
        `timebox_end_date` = '$timebox_end_date' 
        WHERE `timebox_id` = '$timebox_id'";

    $results = mysqli_query($connection, $query);
    if (! $results) {
        set_user_message(mysqli_error($connection), "warning");
        return null;
    }

    header("Location:timebox.php?id=${_POST['timebox-id']}");
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
        <div class='sidebar-block'>
            <form id='list-options-form' method='post'>
                <div id='list-options' class='group'>
                    <input type='checkbox' name='closed-tasks-option' value='show-closed-tasks' $show_closed_tasks> Show closed tasks</br>
                </div>
                <input type='submit' name='apply-list-options-button' value='Apply these options'></input>
            </form>
        </div>";
}

function show_content() 
{
    global $timebox, $timebox_id;
    if (! $timebox) {
        show_user_message("There was an error retrieving the project information", 'warning');
        return;
    }
    
    echo "                
        <h3>Timebox $timebox_id</h3>
        <form id='timebox-form' class='main-form' method='post'>
            <input type='hidden' name='timebox-id' value='$timebox_id'>
            
            <div id='project_name'>
                <label>Project:</label>
                <a class='object-ref' href='project.php?id=${timebox['project_id']}'>${timebox['project_name']}</a>
            </div>

            <div id='timebox-name'>
                <label for='timebox-name'>Name:</label>
                <input style='width:50%' type='text' name='timebox-name' value='${timebox['timebox_name']}'></input>
            </div>
            
            <div id='timebox-discussion'>
                <label for='timebox-discussion'>Discussion:</label>
                <textarea name='timebox-discussion' rows='10' style='width:50%'>${timebox['timebox_discussion']}</textarea>
            </div>

            <div id='timebox-end-date'>
                <label>End date:</label>
                <input style='width:50%' type='text' name='timebox-end-date' value='${timebox['timebox_end_date']}'></input>
            </div>
                
            <div id='form-controls'>
                <input type='submit' name='update-button' value='Update'></input>
            </div> <!-- /form-controls -->
        </form>
            ";
                
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
