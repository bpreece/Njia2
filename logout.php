<?php

include_once('common.inc');

if (connect_to_database_session()) {
    $session_id = get_session_id();
    
    $query = "UPDATE `session_table` 
        SET `session_expiration_date` = NOW()
        WHERE `session_id` = '$session_id'";
    
    db_execute($query);
}

header('Location: login.php');

?>
