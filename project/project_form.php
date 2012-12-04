<?php

function show_project_form($project_id, $project)
{
    echo "
        <form id='project-form' class='main-form' method='post'>
            <input type='hidden' name='project-id' value='$project_id'>
                
            <div id='project-name'>
                <label for='project-name'>Name:</label>
                <input style='width:50%' type='text' name='project-name' value='${project['project_name']}'></input>
            </div>
            
            <div id='project-discussion'>
                <label for='project-discussion' style='vertical-align:top'>Discussion:</label>
                <textarea name='project-discussion' rows='10' style='width:50%'>${project['project_discussion']}</textarea>
            </div>
                
            <div id='project-owner'>
                <label>Owner:</label>
                <a class='object-ref' href='user.php?id=${project['owner_id']}'>${project['owner_name']}</a>
            </div>

            <div id='project-created-date'>
                <label>Created:</label>
                ${project['project_created_date']}
            </div>
                
            <div id='form-controls'>
                <input type='submit' name='project-form-button' value='Update'></input>
            </div> <!-- /form-controls -->
        </form>";
}


function process_project_form() 
{
    if (! isset($_POST['project-form-button'])) {
        return FALSE;
    }
    
    if (connect_to_database_session()) {
        $project_id = db_escape($_POST['project-id']);
        $project_name = db_escape($_POST['project-name']);
        $project_discussion = db_escape($_POST['project-discussion']);

        if (authorize_project($project_id)) {
            $query = "UPDATE `project_table` SET
                    `project_name` = '$project_name' , 
                    `project_discussion` = '$project_discussion' 
                WHERE `project_id` = '$project_id'";

            if (db_execute($query)) {
                set_user_message("The changes have been applied", 'success');
            }
        } else {
            set_user_message("Project $project_id was not found.", 'warning');
            return FALSE;
        }
    }

    return TRUE;
}

?>
