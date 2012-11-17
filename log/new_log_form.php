<?php

function show_new_log_form($task_id)
{
    echo "
        <form id='add-task-log-form' method='POST'>
            <input type='hidden' name='task-id' value='$task_id' />
            <div id='log-description'>
                <label for='description'>Description:</label>
                <textarea style='width:100%' rows='10' name='description'></textarea>
            </div>
            <div id='work-hours'>
                <label for='work-hours'>Hours worked:</label>
                <input type='text' size='5' name='work-hours'></input>
            </div>
            <input type='submit' name='new-log-button' value='Add log entry'></input>
        </form>";
}

function process_new_log_form()
{
    if (! isset($_POST['new-log-button'])) {
        return FALSE;
    }
    
    $connection = connect_to_database_session();
    if (!$connection) {
        return TRUE;
    }

    $user_id = get_session_user_id();
    $task_id = mysqli_real_escape_string($connection, $_POST['task-id']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $work_hours = mysqli_real_escape_string($connection, $_POST['work-hours']);
    $query = "INSERT INTO `log_table` (
            `user_id` , `task_id` , `description` ";
    if ($_POST['work-hours']) {
        $query .=  ", `work_hours` ";
    }
    $query .= "
        ) VALUES (
            '$user_id' , '$task_id' , '$description' ";
    if ($_POST['work-hours']) {
        $query .=  ", '$work_hours' ";
    }
    $query .= "
        )";
    $results = mysqli_query($connection, $query);
    if (! $results) {
        set_user_message(mysqli_error($connection), "warning");
        return TRUE;
    }    
    
    set_user_message("The log entry has been created.", 'success');
    return TRUE;
}

?>
