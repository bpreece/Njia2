<?php

include_once('common.inc');

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

global $timebox;

function process_form_data() {
    if (isset($_POST['update-button'])) {
        process_timebox_form();
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

function process_query_string() {
    global $timebox_id, $timebox;
    if (isset($_GET['id'])) {
        $timebox_id = $_GET['id'];
        $timebox = query_timebox($timebox_id);
    }
}

function query_timebox($timebox_id) {
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
    $num_rows = mysqli_num_rows($timebox_result);
    if ($num_rows == 0) {
        set_user_message("Timebox ID $timebox_id not recognized", 'warning');
        return null;
    }
    $timebox = mysqli_fetch_array($timebox_result);
    
    $task_query = "SELECT T.`task_id` , T.`task_summary` , T.`task_status` 
                FROM `task_table` AS T
                WHERE T.`timebox_id` = '$timebox_id'";
    $task_result = mysqli_query($connection, $task_query);
    $num_rows = mysqli_num_rows($task_result);
    if ($num_rows > 0) {
        $tasks = array();
        while ($task = mysqli_fetch_array($task_result)) {
            $tasks[$task['task_id']] = $task;
        }
        $timebox['task_list'] = $tasks;
    }
    
    return $timebox;
}

function show_sidebar() {
    global $timebox;
    echo "
        <h3>Timebox Options</h3>";
    if (! $timebox) {
        return;
    }
}

function show_content() 
{
    global $timebox;
    if (! $timebox) {
        set_user_message("There was an error retrieving the project information", 'warning');
        return;
    }
    
    echo "
        <form id='timebox-form' class='main-form' method='post'>
            <input type='hidden' name='timebox-id' value='${timebox['timebox_id']}'>
                
            <div id='timebox-id'>Timebox ${timebox['timebox_id']}</div>
            
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
                <textarea name='timebox-discussion' rows='4' style='width:50%'>${timebox['timebox_discussion']}</textarea>
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
            Tasks:
            <ul>";
        foreach ($timebox['task_list'] as $task) {
            echo "
                <li>
                    <a class='object-ref' href='task.php?id=${task['task_id']}'>${task['task_summary']}</a>";
            if ($task['task_status'] == 'closed') {
                echo " <span class='subtask-closed'>&mdash; Closed</span>";
            }
            echo "
                </li>";
        }
        echo "
            </ul>";
    }
}

include_once ('template.inc');

?>
