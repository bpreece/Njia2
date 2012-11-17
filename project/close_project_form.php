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
    
    $connection = connect_to_database_session();
    if (!$connection) {
        return TRUE;
    }

    $project_id = mysqli_real_escape_string($connection, $_POST['project-id']);
    $query = "UPDATE `project_table` 
        SET `project_status` = 'closed' 
        WHERE `project_id` = '$project_id'";
    $results = mysqli_query($connection, $query);
    if (! $results) {
        set_user_message(mysqli_error($connection), "warning");
        return TRUE;
    }    
    
    set_user_message("Project $project_id has been closed", 'success');
    return TRUE;
}

?>