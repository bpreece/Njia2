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
    
    $connection = connect_to_database_session();
    if (!$connection) {
        return TRUE;
    }

    $project_id = mysqli_real_escape_string($connection, $_POST['project-id']);
    $timebox_name = mysqli_real_escape_string($connection, $_POST['timebox-name']);
    $timebox_end_date = mysqli_real_escape_string($connection, $_POST['timebox-end-date']);
    
    $query = "INSERT INTO `timebox_table` (
        `timebox_name` , `project_id` , `timebox_end_date` 
        ) VALUES ( 
        '$timebox_name' , '$project_id' , '$timebox_end_date')";

    $results = mysqli_query($connection, $query);
    if (! $results) {
        set_user_message(mysqli_error($connection), "warning");
        return TRUE;
    }    
    $new_timebox_id = mysqli_insert_id($connection);
    
    header("Location:timebox.php?id=$new_timebox_id");
    return TRUE;
}

?>
