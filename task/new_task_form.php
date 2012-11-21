<?php

function show_new_task_form($project_id, $parent_task_id = NULL) 
{
    $label_title = $parent_task_id ? 'Subtask Summary' : 'Task Summary';
    $submit_title = $parent_task_id ? 'Add subtask' : 'Add task';
    echo "
        <form id='add-task-form' method='post'>
            <input type='hidden' name='project-id' value='$project_id'>";
    if ($parent_task_id) {
        echo "
            <input type='hidden' name='parent_task-id' value='$parent_task_id'>";
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
    
    $connection = connect_to_database_session();
    if (!$connection) {
        return TRUE;
    }

    $project_id = mysqli_real_escape_string($connection, $_POST['project-id']);
    if (isset($_POST['parent-task-id'])) {
        $parent_task_id = mysqli_real_escape_string($connection, $_POST['parent-task-id']);
    }
    $task_summary = mysqli_real_escape_string($connection, $_POST['task-summary']);
    
    $task_query = "INSERT INTO `task_table` (
            `task_summary` , `project_id` , `task_created_date` , ";
    if (isset($parent_task_id)) {
        $task_query .= "`parent_task_id`, ";
    }
    $task_query .= "`user_id` 
        ) VALUES ( 
            '$task_summary' , '$project_id' , CURRENT_TIMESTAMP() , ";
    if ($parent_task_id) {
        $task_query .= "'$parent_task_id', ";
    }
    $task_query .= "
            ( SELECT `project_owner` FROM `project_table` WHERE `project_id` = '$project_id' )
        )";

    $task_results = mysqli_query($connection, $task_query);
    if (! $task_results) {
        set_user_message(mysqli_error($connection), "warning");
        return TRUE;
    }    
    $new_task_id = mysqli_insert_id($connection);
    
    header("Location:task.php?id=$new_task_id");
    return TRUE;
}

?>
