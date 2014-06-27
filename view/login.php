<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* 
 * File: login.php
 * Created on: Jun 24, 2014, 9:16:04 PM
 * Author: ben
 * Description:
 * 
 */

?>

<h3>Sign on</h3>
<div id='login-main'>";
    <form id='login_form' name='login_form' method='POST'>
        <label for='name_field'>Sign-on name:</label>
        <input type='text' name='name_field' value='$login_form_name_field'></input>
        <label for='password-field'>Password:</label>
        <input type='password' name='password-field'></input>
        <?php if (isset($page['new-login'])) : ?>
            <label for='repeat-password_field'>Repeat password:</label>
            <input type='password' name='repeat-password-field'></input>
            <label for='email-field'>E-mail:</label>
            <input type='text' name='email-field' value=''></input>
            <br/>
            <input type='submit' name='new-login-button' value='Create login'></input>";
        <?php else : ?>
            <br/>
            <input type='submit' name='login-button' value='Login'></input>";
        <?php endif; ?>
    </form>";
</div>
