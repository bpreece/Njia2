<?php
/*
 * NOTE: the form field 'name_field' is deliberately named with an underbar, 
 * not a hyphen, so that set_focus() can be called on it.
 */

include_once 'common.inc';
include_once 'data/login.php';

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
        $password_field = db_escape($_POST['password-field']);
        login_user($login_form_name_field, $password_field);
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
    global $njia_url, $admin_email;
    global $login_form_name_field;

    if (! isset($_POST['new-login-button'])) {
        return FALSE;
    }

    if (!$_POST['name_field'] || !$_POST['password-field'] || !$_POST['email-field']) {
        set_user_message("You must provide a login name, password, and e-mail address", "warning");
        return TRUE;
    }

    if (!$_POST['repeat-password-field'] || $_POST['repeat-password-field'] != $_POST['password-field']) {
        set_user_message("The passwords do not match.", "warning");
        return TRUE;
    }
    
    if (db_connect()) {
        $login_form_name_field = db_escape($_POST['name_field']);
        $password_field = db_escape($_POST['password-field']);
        $email_field = db_escape($_POST['email-field']);

        $query = "INSERT INTO `user_table` (
                `login_name` , `password_salt` , `password`, `email` , `expiration_date` 
            ) VALUES (
                '$login_form_name_field' , 
                MD5( CONCAT( '$login_form_name_field' , NOW() ) ) , 
                MD5( CONCAT( `password_salt`, '$password_field' ) ), 
                '$email_field' , 
                DATE_ADD( NOW(), INTERVAL 2 DAY)
            )";
        
        if (db_execute($query)) {
            $user_id = db_last_index();

            $user_query = "SELECT `password_salt` , `expiration_date` , 
                MD5( CONCAT( `password_salt`, `expiration_date` ) ) AS `key` 
                FROM `user_table` WHERE `user_id` = '$user_id'";
            $user = db_fetch($user_query);
            if (! $user) {
                return;
            }
            
            $subject = 'Your new Njia account';
            $message = <<<EOM
An account has been created for you at Njia with the login name ${_POST['name_field']}.  
This is a temporary account which will expire in two days.  To remove the 
expiration from this account, please follow this link:

    http://$njia_url/verify.php?id=$user_id&key=${user['key']}

If you did not create this account, please notify the administrator at Njia
by replying to this email.
EOM;
            $headers = "From: $admin_email";
            mail($email_field, $subject, $message, $headers);
            
            login_user($login_form_name_field, $password_field);
        }
    }
    
    return TRUE;
}

global $new_login;
$new_login = FALSE;

if (isset($_POST)) {
    
} else if (isset($_GET)) {    
    if (isset($_GET['new'])) {
        $new_login = TRUE;
    }
    if (isset($_GET['x'])) {
        set_user_message('You must log in to view these pages.', 'warning');
    }
}

function process_form_data() {
    process_login_form()
    || process_new_login_form();
}

function show_sidebar() {
    global $new_login;
    if ($new_login) {
        echo "
        <div class='sidebar-block'>
            <form method='GET'>
                <input type='submit' value='Use existing account'></input>
            </form>
        </div>";
    } else {
        echo "
        <div class='sidebar-block'>
            <form method='GET'>
                <input type='hidden' name='new' />
                <input type='submit' value='Create an account'></input>
            </form>
        </div>";
    }
}

$page = array(
    'view' => 'view/login.php',
    'styles' => array( 'css/login.css', ),
    'page-id' => 'login-page',
    'page-class' => 'no-header',
);

include_once 'template.inc';
