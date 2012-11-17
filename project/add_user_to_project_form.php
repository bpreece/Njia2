<?php

function show_add_user_to_project_form($project_id)
{
    echo "
        <form id='add-user-form' method='post'>
            <input type='hidden' name='project-id' value='$project_id'>
            <div id='user-field'>
                <label for='user-name'>User login name:</label>
                <input style='width:100%' type='text' name='user-name'></input>
            </div>
            <input type='submit' name='add-user-button' value='Add user to project'></input>
        </form>";
}

function process_add_user_to_project_form()
{
    if (!isset($_POST['add-user-button'])) {
        return FALSE;
    }
    
    $connection = connect_to_database_session();
    if (!$connection) {
        return TRUE;
    }
    
    $project_id = mysqli_real_escape_string($connection, $_POST['project-id']);
    $user_name = mysqli_real_escape_string($connection, $_POST['user-name']);
    $access_query = "INSERT INTO `access_table` ( 
            `project_id` , `user_id` 
        ) VALUES ( 
            '$project_id' , (SELECT `user_id` FROM `user_table` WHERE `login_name` = '$user_name' )
        )";
    $access_results = mysqli_query($connection, $access_query);
    if (! $access_results) {
        set_user_message(mysqli_error($connection), "warning");
        return TRUE;
    }    

    set_user_message("User $user_name has been added to project $project_id", 'success');
    return TRUE;
}

?>
