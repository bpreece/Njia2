<?php

function show_close_project_form($project_id) {
    echo "
        <form id='close-project-form' method='post'>
            <div class='group'>
                <input type='hidden' name='project-id' value='$project_id'>
            </div>
            <input type='submit' name='close-project-button' value='Close this project'></input>
        </form>";
}

function process_close_project_form()
{
    if (! isset($_POST['close-project-button'])) {
        return FALSE;
    }
    
    if (connect_to_database_session()) {
        $project_id = db_escape($_POST['project-id']);
        
        if (authorize_project($project_id)) {
            $query = "UPDATE `project_table` 
                SET `project_status` = 'closed' 
                WHERE `project_id` = '$project_id'";

            if (db_execute($query)) {
                set_user_message("Project $project_id has been closed", 'success');
            }    
        } else {
            set_user_message("Project $project_id was not found.", 'warning');
            return FALSE;
        }
    }
    
    return TRUE;
}

?>
