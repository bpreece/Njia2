<?php

function show_reopen_task_form($task_id)
{
    echo "
        <form id='close-task-form' method='post'>
            <input type='hidden' name='task-id' value='$task_id'>
            <input type='submit' name='reopen-task-button' value='Reopen this task'></input>
        </form>";
}

function process_reopen_task_form()
{
    if (! isset($_POST['reopen-task-button'])) {
        return FALSE;
    }
    
    $connection = connect_to_database_session();
    if (!$connection) {
        return TRUE;
    }

    $task_id = mysqli_real_escape_string($connection, $_POST['task-id']);
    $query = "UPDATE `task_table` 
        SET `task_status` = 'open' 
        WHERE `task_id` = '$task_id'";
    $results = mysqli_query($connection, $query);
    if (! $results) {
        set_user_message(mysqli_error($connection), "warning");
        return TRUE;
    }    

    header("Location: task.php?id=$task_id");
    return TRUE;
}

?>
