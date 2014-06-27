<?php

include_once 'common.inc';
include_once 'login_user.php';

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
            <label for='email-field'>E-mail:</label>
            <input type='text' name='email-field' value=''></input>
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

?>
