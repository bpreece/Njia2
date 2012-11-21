<?php

function query_timeboxes($connection, $show_closed_tasks, $timebox_end_date)
{
    $user_id = get_session_user_id();
    $timebox_query = "SELECT X.`timebox_id` , X.`timebox_name` , X.`timebox_end_date` , 
        ( X.`timebox_end_date` < DATE( NOW() ) ) as `timebox_expired` , 
        P.`project_id` , P.`project_name` , P.`project_status` , 
        T.`task_id` , T.`task_summary` , T.`task_status` , 
        U.`user_id` , U.`login_name` 
        FROM `access_table` AS A 
        INNER JOIN `timebox_table` AS X ON A.`project_id` = X.`project_id` 
        INNER JOIN `project_table` AS P ON X.`project_id` = P.`project_id` 
        INNER JOIN `task_table` AS T ON T.`timebox_id` = X.`timebox_id` 
        LEFT OUTER JOIN `user_table` AS U ON U.`user_id` = T.`user_id`
        WHERE A.`user_id` = '$user_id' 
            AND P.`project_status` <> 'closed'";
    if (! $show_closed_tasks) {
        $timebox_query .= "
            AND T.`task_status` <> 'closed'";
    }
    if ($timebox_end_date) {
        $timebox_query .= "
            AND X.`timebox_end_date` >= '$timebox_end_date'";
    } else {
        $timebox_query .= "
            AND X.`timebox_end_date` >= NOW()";
    }
    $timebox_query .= "
        ORDER BY X.`timebox_end_date` , P.`project_id` , T.`task_id`";

    $timeboxes = array();
    
    $timebox_result = mysqli_query($connection, $timebox_query);
    if (! $timebox_result) {
        set_user_message(mysqli_error($connection), 'failure');
    } else {
        while ($result = mysqli_fetch_array($timebox_result)) {
            $timebox_id = $result['timebox_id'];
            if (!array_key_exists($timebox_id, $timeboxes)) {
                $timeboxes[$timebox_id] =  array(
                    'timebox-id' => $timebox_id, 
                    'timebox-name' => $result['timebox_name'], 
                    'timebox-end-date' => $result['timebox_end_date'], 
                    'timebox-expired' => $result['timebox_expired'],
                    'project-id' => $result['project_id'], 
                    'project-name' => $result['project_name'], 
                    'project-status' => $result['project_status'], 
                    'task-list' => array(), 
                );
            }
            $task_id = $result['task_id'];
            $timeboxes[$timebox_id]['task-list'][$task_id] =  array(
                'task-id' => $task_id, 
                'task-summary' => $result['task_summary'], 
                'task-status' => $result['task_status'], 
                'user-id' => $result['user_id'], 
                'user-name' => $result['login_name'], 
            );
        }
    }
    
    return $timeboxes;
}

?>
