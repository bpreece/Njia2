<?php

/**
 * Queries the database for all timeboxes for all projects to which the
 * session user has access.
 * @param type $show_closed_tasks
 * @param type $timebox_end_date
 * @return array
 */
function query_timeboxes($user_id, $show_closed_tasks, $timebox_end_date, $session_user_id)
{
    $timebox_query = "SELECT X.`timebox_id` , X.`timebox_name` , X.`timebox_end_date` , 
            ( X.`timebox_end_date` < CURRENT_DATE() ) AS `timebox_expired` , 
            P.`project_id` , P.`project_name` , P.`project_status` 
        FROM `timebox_table` AS X 
        INNER JOIN `project_table` AS P ON X.`project_id` = P.`project_id` 
        INNER JOIN `access_table` AS A1 ON A1.`project_id` = P.`project_id` 
        INNER JOIN `access_table` AS A2 ON A2.`project_id` = A1.`project_id` 
            AND A2.`user_id` = '$session_user_id' 
        WHERE A1.`user_id` = '$user_id' 
            AND P.`project_status` <> 'closed' ";
    if ($timebox_end_date) {
        $timebox_query .= "
            AND X.`timebox_end_date` >= '$timebox_end_date'";
    } else {
        $timebox_query .= "
            AND X.`timebox_end_date` >= CURRENT_DATE()";
    }
    $timebox_query .= "
        ORDER BY X.`timebox_end_date` , P.`project_id` ";
 
    $timeboxes = db_fetch_list('timebox_id', $timebox_query);
    foreach ($timeboxes as $timebox_id => $timebox) {
        $timeboxes[$timebox_id]['task-list'] = query_timebox_tasks($timebox_id, $show_closed_tasks);
    }
    
    return $timeboxes;
}

function query_user_timeboxes($timebox_id)
{
    $session_id = get_session_id();
    
    $timebox_query = "SELECT X.* , P.`project_name` 
        FROM `session_table` AS S
        INNER JOIN `access_table` AS A ON S.`user_id` = A.`user_id` 
        INNER JOIN `timebox_table` AS X ON A.`project_id` = X.`project_id` 
        INNER JOIN `project_table` AS P ON X.`project_id` = P.`project_id`
        WHERE S.`session_id` = '$session_id' and X.`timebox_id` = '$timebox_id'";
    
    return db_fetch($timebox_query);
}

/**
 * Queries the database for all tasks included in this timebox.  This function
 * presumes the session user has already been authenticated and has authority
 * to view this timebox.
 * @param type $timebox_id
 * @param type $show_closed_tasks
 * @param type $show_subtasks
 * @return array
 */
function query_timebox_tasks($timebox_id, $show_closed_tasks, $show_subtasks = TRUE)
{
    $tasks_query = "SELECT T.`task_id` , T.`task_summary` , T.`task_status` , T.`parent_task_id` , 
            X.`timebox_id` , X.`timebox_name` , X.`timebox_end_date` , 
            U.`user_id` , U.`login_name` AS `user_name` 
        FROM `task_table` AS T 
        INNER JOIN `timebox_table` AS X ON X.`timebox_id` = T.`timebox_id` 
        LEFT JOIN `user_table` AS U ON T.`user_id` = U.`user_id`
        WHERE T.`timebox_id` = '$timebox_id' ";
    if (! $show_closed_tasks) {
        $tasks_query .= "
            AND T.`task_status` <> 'closed' ";
    }
    if (! $show_subtasks) {
        $tasks_query .= "
            AND T.`parent_task_id` IS NULL ";
    }
    $tasks_query .= "
        ORDER BY T.`task_id` ";
    
    $results = db_fetch_list('task_id', $tasks_query);
    return $results;
}

?>
