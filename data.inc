
<?php 

/**
 * Returns a list of users who share a common project with the session user
 * @param type $connection
 * @return type array()
 */
function fetch_user_list($connection) {
    $session_user_id = get_session_user_id();
    $user_list = array();

    $user_query = "SELECT DISTINCT U.`user_id` , U.`login_name` 
        FROM  `access_table` AS A1
        INNER JOIN  `access_table` AS A2 ON A1.`project_id` = A2.`project_id` 
        INNER JOIN  `user_table` AS U ON A1.`user_id` = U.`user_id` 
        WHERE A2.`user_id` =  '$session_user_id'
        ORDER BY U.`login_name`";
    
    $user_result = mysqli_query($connection, $user_query);
    if (! $user_result) {
        set_user_message(mysqli_error($connection), 'failure');
    } else {
        while ($user = mysqli_fetch_array($user_result)) {
            $user_list[$user['user_id']] = $user;
        }
    }
    
    return $user_list;
}

?>