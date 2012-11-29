<?php

function show_reopen_account_form($user_id) {
    echo "
        <form id='reopen-account-$user_id' method='post'>
            <input type='hidden' name='user-id' value='$user_id'>
            <input type='submit' name='reopen-account-button' value='Re-open this account'></input>
        </form>";
}

function process_reopen_account_form()
{
    if (! isset($_POST['reopen-account-button'])) {
        return FALSE;
    }
    
    if (connect_to_database_session()) {
        $user_id = db_escape($_POST['user-id']);
        
        if (!is_admin_session()) {
            header('Location: user.php');
            return TRUE;
        }

        $query = "UPDATE `user_table` 
            SET `account_closed_date` = NULL
            WHERE `user_id` = '$user_id'";
        
        if (db_execute($query)) {
            set_user_message("This account has been re-opened.", 'debug');
        }
    }
    
    return TRUE;
}

?>
