<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* 
 * File: login.php
 * Created on: Jun 24, 2014, 9:27:43 PM
 * Author: ben
 * Description:
 * 
 */

/**
 * The $user_name and $user_password will be db_escaped when writing the SQL command.
 * 
 * @param type $user_name
 * @param type $user_password
 * @return type
 */
function login_user($user_name, $user_password) 
{
    $_name = db_escape($user_name);
    $_password = db_escape($user_password);
    $user_query = "SELECT `user_id`, `login_name` 
            FROM `user_table` 
            WHERE `login_name` = '$_name' AND 
                `password` = MD5( CONCAT( `password_salt`, '$_password' ) ) AND 
                `account_closed_date` IS NULL AND
                ( `expiration_date` IS NULL OR `expiration_date` >= DATE(NOW()) )";
    
    $user = db_fetch($user_query);
    if ($user) {
        // The login should not fail now
        $login_query = "UPDATE  `user_table` 
            SET `last_login_date` = NOW() 
            WHERE `user_id` = '${user['user_id']}' ";
        if (db_execute($login_query)) {        
            $cookie = set_session_id($user['user_id']);
            header("Location: todo.php");
        }
    }
    
    return $user;
}
