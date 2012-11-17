<?php

function show_user_form($user_id, $user_name)
{
    echo "
        <form id='user-form' class='main-form' method='post'>
            <input type='hidden' name='user-id' value='$user_id'>

            <div id='login-name'>
                <label for='login-name'>Login name:</label>
                <input style='width:15em' type='text' name='login-name' value='$user_name'></input>
            </div>

            <div id='old-password'>
                <label for='old-password'>Old password:</label>
                <input style='width:15em' type='password' name='old-password'></input>
            </div>

            <div id='new-password'>
                <label for='new-password'>New password:</label>
                <input style='width:15em' type='password' name='new-password'></input>
            </div>

            <div id='repeat-password'>
                <label for='repeat-password'>Repeat password:</label>
                <input style='width:15em' type='password' name='repeat-password'></input>
            </div>
                
            <div id='form-controls'>
                <input type='submit' name='user_form-button' value='Update'></input>
            </div> <!-- /form-controls -->

        </form>";
}


function process_user_form() 
{
    if (! isset($POST['user_form_button'])) {
        return FALSE;
    }
    
    $connection = connect_to_database_session();
    if (!$connection) {
        return TRUE;
    }
    
    $user_id = get_session_user_id();
    if ($user_id != $_POST['user-id']) {
        header ("Location: user.php");
        return TRUE;
    }
    
    if (isset($_POST['new-password']) && $_POST['new-password'] != $_POST['repeat-password']) {
        set_user_message("The new passwords do not match.  Please try again.", 'warning');
        return TRUE;
    }

    $login_name = mysqli_real_escape_string($connection, $_POST['login-name']);
    $old_password = mysqli_real_escape_string($connection, $_POST['old-password']);
    
    if (isset($_POST['new-password'])) {
        $new_password = mysqli_real_escape_string($connection, $_POST['new-password']);
        $query = "UPDATE `user_table` SET 
            `login_name` = '$login_name' , 
            `password` =  MD5(CONCAT(`password_salt`,'$new_password'))
            WHERE `user_id` = '$user_id' AND 
                `password` = MD5(CONCAT(`password_salt`,'$old_password')) ";
    } else {
        $query = "UPDATE `user_table` SET 
            `login_name` = '$login_name' 
            WHERE `user_id` = '$user_id' AND 
                `password` = MD5(CONCAT(`password_salt`,'$old_password')) ";
    }
    $result = mysqli_query($connection, $query);
    if (! $result) {
        set_user_message(mysqli_error($connection), 'failure');
        return TRUE;
    }
    if (mysqli_affected_rows($connection) == 0) {
        set_user_message("The changes could not be applied.  Please check your password and try again.", 'warning');
        return TRUE;
    }
    
    set_user_message("The changes have been applied", 'success');
    return TRUE;
}

?>
