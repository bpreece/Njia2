<?php

/**
 * 
 */
function show_new_project_form()
{
    echo "
        <form id='add-project-form' method='post'>
            <div id='subtask-summary' class='group'>
                <label for='project-name'>Project name:</label>
                <input style='width:100%' type='text' name='project-name'></input>
            </div>
            <input type='submit' name='new-project-button' value='Create a new project'></input>
        </form>";
}

/**
 * Handle input from a new project form
 * @return boolean TRUE if we handled a new project form, even if there 
 *                 were errors; FALSE if there was no new project form 
 *                 data to handle
 */
function process_new_project_form()
{
    if (! isset($_POST['new-project-button'])) {
        return FALSE;
    }

    $connection = connect_to_database_session();
    if (!$connection) {
        return TRUE;
    }
    
    $session_user_id = get_session_user_id();
    $project_name = mysqli_real_escape_string($connection, $_POST['project-name']);

    $project_query = "INSERT INTO `project_table` (
            `project_name` , `project_owner` 
        ) VALUES ( 
            '$project_name' , '$session_user_id' 
        )";
    $project_results = mysqli_query($connection, $project_query);
    if (! $project_results) {
        set_user_message(mysqli_error($connection), 'failure');
        return TRUE;
    }
    $new_project_id = mysqli_insert_id($connection);
    
    $access_query = "INSERT INTO `access_table` (
            `user_id` , `project_id` 
        ) VALUES ( 
            '$session_user_id' , '$new_project_id' 
        )";
    $access_results = mysqli_query($connection, $access_query);
    if (! $access_results) {
        set_user_message(mysqli_error($connection), 'failure');
        return TRUE;
    }
    
    header ("Location: project.php?id=$new_project_id");
    return TRUE;
}

?>
