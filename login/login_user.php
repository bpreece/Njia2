<?php

function login_user($user_name, $user_password) 
{
    $user_query = "SELECT `user_id`, `login_name` 
            FROM `user_table` 
            WHERE `login_name` = '$user_name' AND 
                `password` = MD5( CONCAT( `password_salt`, '$user_password' ) ) AND 
                `account_closed_date` IS NULL";

    $user = db_fetch($user_query);
    if ($user) {
        $login_query = "UPDATE  `user_table` 
            SET `last_login_date` = NOW() 
            WHERE `user_id` = '${user['user_id']}' ";
        db_execute($login_query);
        
        $cookie = set_session_id($user['user_id']);
        header("Location: todo.php");
    } else {
        set_user_message("Login failed.  Either this user account does not exist, or the password was incorrect.", 'warning');
    }
}

?>
