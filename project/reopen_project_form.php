<?php

function show_reopen_project_form($project_id) {
    echo "
        <form id='reopen-project-form' method='POST'>
            <div class='group'>
                <input type='hidden' name='project-id' value='$project_id'>
            </div>
            <input type='submit' name='reopen-project-button' value='Reopen this project'></input>
        </form>";
}

function process_reopen_project_form()
{
    if (! isset($_POST['reopen-project-button'])) {
        return FALSE;
    }
    
    if (connect_to_database_session()) {
        $project_id = db_escape($_POST['project-id']);
        
        if (authorize_project($project_id)) {
            $query = "UPDATE `project_table` 
                SET `project_status` = 'open' 
                WHERE `project_id` = '$project_id'";

            if (db_execute($query)) {
                set_user_message("Project $project_id has been re-opened", 'success');
            }    
        } else {
            set_user_message("Project $project_id was not found.", 'warning');
            return FALSE;
        }
    }
    return TRUE;
}

?>
