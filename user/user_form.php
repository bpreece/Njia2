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
                <input type='submit' name='user-form-button' value='Update'></input>
            </div> <!-- /form-controls -->

        </form>";
}


function process_user_form() 
{
    if (! isset($_POST['user-form-button'])) {
        return FALSE;
    }
    
    if (isset($_POST['new-password']) && $_POST['new-password'] != $_POST['repeat-password']) {
        set_user_message("The new passwords do not match.  Please try again.", 'warning');
        return TRUE;
    }
    
    if (connect_to_database_session()) {
        $user_id = db_escape($_POST['user-id']);
        $login_name = db_escape($_POST['login-name']);
        $old_password = db_escape($_POST['old-password']);

        if ($user_id == get_session_user_id() || is_admin_session()) {
            if ($_POST['new-password']) {
                $new_password = db_escape($_POST['new-password']);
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

            if (db_execute($query)) {
                set_user_message("The changes have been applied", 'success');
            }
        } else {
            header("Location:todo.php?id=$user_id");
            return FALSE;
        }
    }
    
    return TRUE;
}

?>
