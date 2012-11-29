<?php

function query_task($task_id)
{
    $session_id = get_session_id();
    $task_id = db_escape($task_id);
    
    $task_query = "SELECT T . * , P.`project_name` , 
                X.`timebox_name` , X.`timebox_end_date` , 
                PT.`task_summary` AS `parent_task_summary` , 
                PT.`task_status` AS `parent_task_status` ,
                U.`login_name`
                FROM `session_table` AS S
                INNER JOIN `access_table` AS A ON S.`user_id` = A.`user_id` 
                INNER JOIN `task_table` AS T ON A.`project_id` = T.`project_id` 
                INNER JOIN `project_table` AS P ON T.`project_id` = P.`project_id` 
                LEFT JOIN `timebox_table` AS X ON T.`timebox_id` = X.`timebox_id` 
                LEFT JOIN `task_table` AS PT ON t.`parent_task_id` = PT.`task_id`
                LEFT JOIN `user_table` AS U ON T.`user_id` = U.`user_id`
                WHERE S.`session_id` = '$session_id' and T.`task_id` = '$task_id'";
    
    $task = db_fetch($task_query);
    $task['task_summary'] = htmlspecialchars($task['task_summary'], ENT_QUOTES);
    $task['task_discussion'] = htmlspecialchars($task['task_discussion'], ENT_QUOTES);
    if ($task['task_status'] == 'closed') {
        set_user_message('This task has been closed', 'warning');
    } else {
        $task['can-close'] = TRUE;
    }
    
    return $task;
}

function query_subtasks($task_id, &$show_closed_tasks)
{
    $subtask_query = "SELECT T.`task_id` , T.`task_summary` , T.`task_status` , T.`parent_task_id` , 
            X.`timebox_id` , X.`timebox_name` , X.`timebox_end_date` , 
            U.`user_id` , U.`login_name` AS `user_name` 
        FROM `task_table` AS T 
        LEFT JOIN `timebox_table` AS X ON T.`timebox_id` = X.`timebox_id` 
        LEFT JOIN `user_table` AS U ON T.`user_id` = U.`user_id`
        WHERE T.`parent_task_id` = '$task_id' ";
    if (! $show_closed_tasks) {
        $subtask_query .= "
            AND T.`task_status` <> 'closed' ";
    }
    $subtask_query .= "
        ORDER BY T.`task_id`";
    
    return db_fetch_list('task_id', $subtask_query);
}

function query_task_log($task_id, &$total_hours)
{
    $log_query = "SELECT L.`log_id` , L.`work_hours` , L.`description` , 
            L.`user_id` , L.`log_time` , 
            U.`login_name` AS `user_name`
        FROM `log_table` AS L
        LEFT JOIN `user_table` AS U ON U.`user_id` = L.`user_id` 
        WHERE L.`task_id` = '$task_id'
        ORDER BY L.`log_id` DESC";
    
    $results = db_fetch_array($log_query);
    $log_list = array();
    if ($results) {
        foreach ($results as &$log) {
            $log_list[$log['log_id']] = $log;
            $total_hours += $log['work_hours'];
        }
    }
    
    return $log_list;
}

?>
