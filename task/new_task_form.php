<?php

function show_new_task_form($project_id, $parent_task_id = NULL) 
{
    $label_title = $parent_task_id ? 'Subtask Summary' : 'Task Summary';
    $submit_title = $parent_task_id ? 'Add subtask' : 'Add task';
    echo "
        <form id='add-task-form' method='POST'>
            <input type='hidden' name='project-id' value='$project_id'>";
    if ($parent_task_id) {
        echo "
            <input type='hidden' name='parent-task-id' value='$parent_task_id'>";
    }
    echo "
            <div id='task-summary'>
                <label for='task-summary'>$label_title:</label>
                <input style='width:100%' type='text' name='task-summary'></input>
            </div>
            <input type='submit' name='new-task-button' value='$submit_title'></input>
        </form>";
}

function process_new_task_form()
{
    if (! isset($_POST['new-task-button'])) {
        return FALSE;
    }
    
    if (isset($_POST['parent-task-id'])) {
        return process_add_subtask_form();
    }
    
    if (connect_to_database_session()) {
        $project_id = db_escape($_POST['project-id']);
        $task_summary = db_escape($_POST['task-summary']);
        
        $task_query = "INSERT INTO `task_table` (
                `task_summary` , `project_id` , `task_created_date` , `user_id` 
            ) VALUES ( 
                '$task_summary' , '$project_id' , CURRENT_TIMESTAMP() , 
                ( SELECT `project_owner` FROM `project_table` WHERE `project_id` = '$project_id' )
            )";

        if (db_execute($task_query)) {
            $new_task_id = db_last_index();    
            header("Location:task.php?id=$new_task_id");
        }    
    }
    
    return TRUE;
}

function process_add_subtask_form() 
{    
    if (! isset($_POST['new-task-button'])) {
        return FALSE;
    }
    
    if (! isset($_POST['parent-task-id'])) {
        return process_new_task_form();
    }
    
    if (connect_to_database_session()) {
        $parent_task_id = db_escape($_POST['parent-task-id']);
        $project_id = db_escape($_POST['project-id']);
        $task_summary = db_escape($_POST['task-summary']);

        $user_id = get_session_user_id();
        $task_query = "INSERT INTO `task_table` 
            ( `task_summary` , `project_id` , `parent_task_id` , `user_id` , 
                `timebox_id` , `task_created_date` )
            ( SELECT '$task_summary' , '$project_id' , '$parent_task_id' , 
                '$user_id' , `timebox_id` , CURRENT_TIMESTAMP()
              FROM `task_table` WHERE `task_id` = '$parent_task_id' )";

        if (db_execute($task_query)) {
            $new_task_id = db_last_index();

            $parent_task_query = "UPDATE `task_table` SET 
                `user_id` = '0' , `timebox_id` = NULL
                WHERE `task_id` = '$parent_task_id'";
            
            if (db_execute($parent_task_query)) {
                header("Location:task.php?id=$new_task_id");
            }
        }    
    }

    return TRUE;
}


?>
