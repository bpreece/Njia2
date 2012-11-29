<?php


/**
 * 
 * @param number $task_id the ID of the task to close
 */
function show_close_account_form($user_id, $show_title = TRUE)
{
    $title = $show_title ? 'Close this account' : '';
    echo "
        <form id='close-account-$user_id' method='POST'>
            <input type='hidden' name='user-id' value='$user_id'></input>
            <input type='submit' class='close-button' name='close-account-button' title='Close this accouint' value='$title'></input>
        </form>";
}

/**
 * Handle input from a close task form
 * @return boolean TRUE if we handled a close task form, even if there 
 *                 were errors; FALSE if there was no close task form 
 *                 data to handle
 */
function process_close_account_form()
{
    if (! isset($_POST['close-account-button'])) {
        return FALSE;
    }
    
    if (connect_to_database_session()) {
        $user_id = db_escape($_POST['user-id']);
        if ($user_id == 1) {
            set_user_message("User account 1 cannot be closed", 'warning');
            return TRUE;
        }

        if (! is_admin_session() && $user_id != get_session_user_id()) {
            header('Location: user.php');
            return TRUE;
        }

        $query = "UPDATE `user_table`
            SET `account_closed_date` = NOW()
            WHERE `user_id` = '$user_id'";
        
        if (db_execute($query)) {
            if ($user_id == get_session_user_id()) {
                header("Location: login.php");
            }
        }
    }
    
    return TRUE;
}

?>
