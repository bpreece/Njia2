<?php

function show_remove_user_from_project_form($project_id, $user_id, $user_name) 
{
    echo "
        <form id='remove-user-$user_id-form' method='post'>
            <input type='hidden' name='project-id' value='$project_id'></input>
            <input type='hidden' name='user-id' value='$user_id'></input>
            <input type='hidden' name='user-name' value='$user_name'></input>
            <input type='submit' class='remove-user' name='remove-user-button' title='Remove this user' value=''></input>
        </form>";
}

function process_remove_user_from_project_form()
{
    if (! isset($_POST['remove-user-button'])) {
        return FALSE;
    }
    
    if (connect_to_database_session()) {
        $user_id = db_escape($_POST['user-id']);
        $project_id = db_escape($_POST['project-id']);
        
        $query = "DELETE FROM `access_table` 
            WHERE `project_id` = '$project_id' AND `user_id` = '$user_id'";

        if (db_execute($query)) {
            set_user_message("User ${_POST['user-name']} has been removed from project $project_id", 'success');
        }
    }

    return TRUE;
}

?>
