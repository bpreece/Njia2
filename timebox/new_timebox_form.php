<?php

function show_new_timebox_form($project_id) 
{
    echo "
        <form id='add-timebox-form' method='post'>
            <input type='hidden' name='project-id' value='$project_id'>
            <div id='timebox-name'>
                <label for='timebox-name'>Timebox name:</label>
                <input style='width:100%' type='text' name='timebox-name'></input>
            </div>
            <div id='timebox-end-date'>
                <label for='timebox-end-date'>Timebox end date:</label>
                <input style='width:100%' type='text' name='timebox-end-date'></input>
            </div>
            <input type='submit' name='new-timebox-button' value='Add timebox'></input>
        </form>";
}

function process_new_timebox_form()
{
    if (! isset($_POST['new-timebox-button'])) {
        return FALSE;
    }
    
    if (connect_to_database_session()) {
        $project_id = db_escape($_POST['project-id']);
        $timebox_name = db_escape($_POST['timebox-name']);
        $timebox_end_date = db_escape($_POST['timebox-end-date']);

        if (authorize_project($project_id)) {
            $query = "INSERT INTO `timebox_table` (
                    `timebox_name` , `project_id` , `timebox_end_date` 
                ) VALUES ( 
                    '$timebox_name' , '$project_id' , '$timebox_end_date')";

            if (db_execute($query)) {
                $new_timebox_id = db_last_index();
                header("Location:timebox.php?id=$new_timebox_id");
            }    
        } else {
            set_user_message("Project $project_id was not found.", 'warning');
            return FALSE;
        }
    }
    
    return TRUE;
}

?>
