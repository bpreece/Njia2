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

function query_user_timeboxes($connection, $timebox_id)
{
    $session_id = get_session_id();
    $timebox_query = "SELECT X.* , P.`project_name` 
                FROM `session_table` AS S
                INNER JOIN `access_table` AS A ON S.`user_id` = A.`user_id` 
                INNER JOIN `timebox_table` AS X ON A.`project_id` = X.`project_id` 
                INNER JOIN `project_table` AS P ON X.`project_id` = P.`project_id`
                WHERE S.`session_id` = '$session_id' and X.`timebox_id` = '$timebox_id'";
    
    $timebox_result = mysqli_query($connection, $timebox_query);
    if (! $timebox_result) {
        set_user_message(mysqli_error($connection), "warning");
        return NULL;
    }
    
    return mysqli_fetch_array($timebox_result);
}

function query_timebox_tasks($connection, $timebox_id, $show_closed_tasks)
{
    $task_query = "SELECT T.`task_id` , T.`task_summary` , T.`task_status` 
                FROM `task_table` AS T
                WHERE T.`timebox_id` = '$timebox_id' ";
    if (! $show_closed_tasks) {
        $task_query .= "
                    AND T.`task_status` <> 'closed' ";
    }
    $task_result = mysqli_query($connection, $task_query);
    
    $tasks_list = array();
    if (! $task_result) {
        set_user_message(mysqli_error($connection), "warning");
    } else {
        while ($task = mysqli_fetch_array($task_result)) {
            $tasks_list[$task['task_id']] = $task;
        }
    }
    
    return array();
}

?>
