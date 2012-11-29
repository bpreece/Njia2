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
    
    if (connect_to_database_session()) {
        $user_id = get_session_user_id();
        $task_id = db_escape($_POST['task-id']);
        $description = db_escape($_POST['description']);
        $work_hours = db_escape($_POST['work-hours']);

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
        
        if (db_execute($query)) {
            set_user_message("The log entry has been created.", 'success');
        }
    }
    return TRUE;
}

?>
