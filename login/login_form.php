<?php

include_once 'common.inc';

global $login_form_name_field;

/**
 * 
 * @param boolean $new_login TRUE if this function should show a new login account
 *             form; FALSE if this function should show a regular login form
 * @param text $login_name an optional value to pre-populate the name field
 */
function show_login_form($new_login) 
{
    global $login_form_name_field;

    echo "
        <form id='login_form' name='login_form' method='POST'>
            <label for='name_field'>Sign-on name:</label>
            <input type='text' name='name_field' value='$login_form_name_field'></input>
            <label for='password-field'>Password:</label>
            <input type='password' name='password-field'></input>";
    if ($new_login) {
        echo "
            <label for='repeat-password_field'>Repeat password:</label>
            <input type='password' name='repeat-password-field'></input>
            <br/>
            <input type='submit' name='new-login-button' value='Create login'></input>";
    } else {
        echo "
            <br/>
            <input type='submit' name='login-button' value='Login'></input>";
    }
    echo "
        </form>";
}

/**
 * Handle input from a login form
 * @return boolean TRUE if we handled a login form, even if there were errors;
 *                 FALSE if there was no login form data to handle
 */
function process_login_form() 
{
    global $login_form_name_field;

    if (! isset($_POST['login-button'])) {
        return FALSE;  // we did not handle a login form
    }
    
    if (!$_POST['name_field'] || !$_POST['password-field']) {
        return TRUE;  // we did handle a login form
    }
    
    if (db_connect()) {
        $login_form_name_field = db_escape($_POST['name_field']);
        $password = db_escape($_POST['password-field']);

        $query = "SELECT `user_id`, `login_name` 
            FROM `user_table` 
            WHERE `login_name` = '$login_form_name_field' AND 
                `password` = MD5( CONCAT( `password_salt`, '$password' ) ) AND 
                `account_closed_date` IS NULL";
    
        $user = db_fetch($query);
        if ($user) {
            $cookie = set_session_id($user['user_id']);
            header("Location: todo.php");
        } else {
            set_user_message("Login failed.  Either this user account does not exist, or the password was incorrect.", 'warning'); 
        }
    }
    
    return TRUE;
}

/**
 * Handle input from a new login account form
 * @return boolean TRUE if we handled a new login account form, even if there 
 *                 were errors; FALSE if there was no new login account form 
 *                 data to handle
 */
function process_new_login_form() 
{
    global $login_form_name_field;

    if (! isset($_POST['new-login-button'])) {
        return FALSE;
    }

    if (!$_POST['name_field'] || !$_POST['password-field']) {
        set_user_message("You must provide a login name and password", "warning");
        return TRUE;
    }

    if (!$_POST['repeat-password-field'] || $_POST['repeat-password-field'] != $_POST['password-field']) {
        set_user_message("The passwords do not match.", "warning");
        return TRUE;
    }
    
    if (db_connect()) {
        $login_form_name_field = db_escape($_POST['name_field']);
        $password = db_escape($_POST['password-field']);

        $query = "INSERT INTO `user_table` (
                `login_name` , `password_salt` 
            ) VALUES (
                '$login_form_name_field' , MD5( CONCAT( '$login_form_name_field' , NOW() ) )
            )";
        
        if (db_execute($query)) {
            $user_id = db_last_index();

            $password_query = "UPDATE `user_table`
                SET `password` = MD5( CONCAT( `password_salt`, '$password' ) )
                WHERE `user_id` = '$user_id'";
            
            if (db_execute($password_query)) {
                $cookie = set_session_id($user_id);
                header("Location: todo.php");
            }
        }
    }
    
    return TRUE;
}

?>
