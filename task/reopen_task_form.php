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
    
    if (connect_to_database_session()) {
        $task_id = db_escape($_POST['task-id']);

        if (authorize_task($task_id)) {
            $query = "UPDATE `task_table` 
                SET `task_status` = 'open' 
                WHERE `task_id` = '$task_id'";

            if (db_execute($query)) {
                header("Location: task.php?id=$task_id");
            }    
        } else {
            set_user_message("Task $task_id was not found.", 'warning');
            return FALSE;
        }
    }
    
    return TRUE;
}

?>
