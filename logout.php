<?php

include_once('common.inc');

$connection = connect_to_database_session();
if ($connection) {    
    $session_id = get_session_id();
    $query = "UPDATE `session_table` 
        SET `session_expiration_date` = NOW()
        WHERE `session_id` = '$session_id'";
    mysqli_query($connection, $query);
}

header('Location: login.php');

?>
