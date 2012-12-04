<?php

/**
 * 
 * @param number $task_id the ID of the task to close
 */
function show_close_task_form($task_id, $show_title = TRUE)
{
    $title = $show_title ? 'Close this task' : '';
    echo "
        <form id='close-task-$task_id' method='POST'>
            <input type='hidden' name='task-id' value='$task_id'></input>
            <input type='submit' class='close-button' name='close-task-button' title='Close this task' value='$title'></input>
        </form>";
}

/**
 * Handle input from a close task form
 * @return boolean TRUE if we handled a close task form, even if there 
 *                 were errors; FALSE if there was no close task form 
 *                 data to handle
 */
function process_close_task_form()
{
    if (! isset($_POST['close-task-button'])) {
        return FALSE;
    }
    
    if (connect_to_database_session()) {
        $task_id = db_escape($_POST['task-id']);
        
        if (authorize_task($task_id)) {
            $query = "UPDATE `task_table` 
                SET `task_status` = 'closed' 
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
