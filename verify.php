<?php

include_once 'common.inc';

global $id, $key;

function process_query_string() {
    global $id, $key;
    set_user_message(var_export($_GET, TRUE), 'debug');

    if (isset($_GET['id'])) {
        $id = $_GET['id'];
    }
    if (isset($_GET['key'])) {
        $key = $_GET['key'];
    }
}

function prepare_page_data() {
    global $id, $key;

    if (db_connect()) {
        $user_query = "SELECT `login_name` 
            FROM `user_table` 
            WHERE `user_id` = '$id' ";
        $user = db_fetch($user_query);
        
        if ($user) {
            $query = "UPDATE `user_table` 
                SET `expiration_date` = NULL 
                WHERE `user_id` = '$id'
                    AND MD5( CONCAT( `password_salt`, `expiration_date` ) ) = '$key' ";
            if (db_execute($query)) {
                set_user_message("The expiration data has been removed from this account: 
                        ${user['login_name']}.", 'success');
            } else {
                set_user_message("There was an unexpected error while trying to remove 
                    the expiration date from this account: ${user['login_name']}.", 'warning');
            }
        } else {
            set_user_message("The expiration date was not be removed from this account because 
                the account was not found: User #$id", 'warning');
        }
    } else {
        header ("Location: login.php");
    }
}

function show_content() {
    echo "
        <ul>
            <li><a href='login.php'>Go to login page</a>
        </ul>
        ";
}

include_once ('template.inc');
?>
