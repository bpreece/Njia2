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

    if (connect_to_database_session()) {
        $session_user_id = get_session_user_id();
        $project_name = db_escape($_POST['project-name']);

        $project_query = "INSERT INTO `project_table` (
                `project_name` , `project_owner` 
            ) VALUES ( 
                '$project_name' , '$session_user_id' 
            )";
        if (! db_execute($connection, $project_query)) {
            return TRUE;
        }
        $new_project_id = db_last_index();

        $access_query = "INSERT INTO `access_table` (
                `user_id` , `project_id` 
            ) VALUES ( 
                '$session_user_id' , '$new_project_id' 
            )";
        if (db_execute($access_query)) {
            header ("Location: project.php?id=$new_project_id");
        }
    }
        
    return TRUE;
}

?>
