
<?php 

include_once 'common.inc';

/**
 * Returns a list of users who share a common project with the session user
 * @param type $connection
 * @return type array()
 */
function fetch_user_list($connection) {
    $session_user_id = get_session_user_id();

    $user_query = "SELECT DISTINCT U.`user_id` , U.`login_name` 
        FROM  `access_table` AS A1
        INNER JOIN  `access_table` AS A2 ON A1.`project_id` = A2.`project_id` 
        INNER JOIN  `user_table` AS U ON A1.`user_id` = U.`user_id` 
        WHERE A2.`user_id` =  '$session_user_id'
        ORDER BY U.`login_name`";
    $user_list = db_fetch_list('user_id', $user_query);
    
    // in case the user doesn't have any projects, still return the session
    // user in the list
    if (!array_key_exists($session_user_id, $user_list)) {
        $user_list[$session_user_id] = get_session_user();
    }
    
    return $user_list;
}

?>