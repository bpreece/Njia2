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
        return FALSE;  // we did not handled a login form
    }

    if (!$_POST['name_field'] || !$_POST['password-field']) {
        set_user_message("Missing login name or password", "failure");
        return TRUE;  // we did handle a login form
    }

    if (!($connection = connect_to_database())) {
        set_user_message("Failed accessing database", "failure");
        return TRUE;
    }

    $login_form_name_field = mysqli_real_escape_string($connection, $_POST['name_field']);
    $password = mysqli_real_escape_string($connection, $_POST['password-field']);

    $query = "SELECT `user_id`, `login_name` 
                FROM `user_table` 
                WHERE `login_name` = '$login_form_name_field' AND 
                    `password` = MD5( CONCAT( `password_salt`, '$password' ) ) AND 
                    `account_closed_date` IS NULL";
    $results = mysqli_query($connection, $query);
    if (!$results) {
        set_user_message(mysqli_error($connection), "failure");
        return TRUE;
    }
    $result = mysqli_fetch_array($results);
    if (!$result) {
        set_user_message("Login name not found, or password doesn't match.", "warning");
        return TRUE;
    }

    $cookie = set_session_id($result['user_id'], $connection);
    header("Location: todo.php");
    
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
    
    if (!($connection = connect_to_database())) {
        set_user_message("Failed accessing database", "failure");
        return TRUE;
    }

    $login_form_name_field = mysqli_real_escape_string($connection, $_POST['name_field']);
    $password = mysqli_real_escape_string($connection, $_POST['password-field']);

    if (!$_POST['name_field'] || !$_POST['password-field']) {
        set_user_message("You must provide a login name and password", "warning");
        return TRUE;
    }

    if (!$_POST['repeat-password-field'] || $_POST['repeat-password-field'] != $_POST['password-field']) {
        set_user_message("The passwords do not match.", "warning");
        return TRUE;
    }

    $user_query = "INSERT INTO `user_table` (
            `login_name` , `password_salt` 
        ) VALUES (
            '$login_form_name_field' , MD5( CONCAT( '$login_form_name_field' , NOW() ) )
        )";
    $user_results = mysqli_query($connection, $user_query);
    if (!$user_results) {
        set_user_message(mysqli_error($connection), "failure");
        return TRUE;
    }
    $user_id = mysqli_insert_id($connection);

    $password_query = "UPDATE `user_table`
        SET `password` = MD5( CONCAT( `password_salt`, '$password' ) )
        WHERE `user_id` = '$user_id'";
    $password_results = mysqli_query($connection, $password_query);
    if (!$password_results) {
        set_user_message(mysqli_error($connection), "failure");
        return TRUE;
    }

    $cookie = set_session_id($user_id, $connection);
    header("Location: user.php?id=$user_id");
    
    return TRUE;
}

?>
