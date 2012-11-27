<?php

function query_task($connection, $task_id)
{
    $session_id = get_session_id();
    $task_id = mysqli_real_escape_string($connection, $task_id);
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
    
    $task_result = mysqli_query($connection, $task_query);
    if (! $task_result) {
        set_user_message(mysqli_error($connection), 'warning');
        return;
    } else if (mysqli_num_rows($task_result) == 0) {
        set_user_message("Task ID $task_id is not recognized", 'warning');
        return;
    }
    $task = mysqli_fetch_array($task_result);
    $task['task_summary'] = htmlspecialchars($task['task_summary'], ENT_QUOTES);
    $task['task_discussion'] = htmlspecialchars($task['task_discussion'], ENT_QUOTES);
    if ($task['task_status'] == 'closed') {
        set_user_message('This task has been closed', 'warning');
    } else {
        $task['can-close'] = TRUE;
    }
    
    return $task;
}

function query_subtasks($connection, $task_id, &$open_tasks)
{
    $open_tasks = FALSE;
    $subtask_query = "SELECT T.`task_id` , T.`task_summary` , T.`task_status` , T.`parent_task_id` , 
            X.`timebox_id` , X.`timebox_name` , X.`timebox_end_date` , 
            U.`user_id` , U.`login_name` AS `user_name` 
        FROM `task_table` AS T 
        LEFT JOIN `timebox_table` AS X ON T.`timebox_id` = X.`timebox_id` 
        LEFT JOIN `user_table` AS U ON T.`user_id` = U.`user_id`
        WHERE T.`parent_task_id` = '$task_id'
        ORDER BY T.`task_id`";
    $subtask_result = mysqli_query($connection, $subtask_query);
    
    $subtask_list = array();
    if (! $subtask_result) {
        set_user_message(mysqli_error($connection), 'warning');
    } else if (mysqli_num_rows($subtask_result) > 0) { 
        while ($subtask = mysqli_fetch_array($subtask_result)) {
            $subtask_list[$subtask['task_id']] = $subtask;
            if ($subtask['task_status'] != 'closed') {
                $open_tasks = TRUE;
            }
        }
    }
    
    return $subtask_list;
}

function query_task_log($connection, $task_id, &$total_hours)
{
    $log_list = array();
    $log_query = "SELECT L.`log_id` , L.`work_hours` , L.`description` , 
            L.`user_id` , L.`log_time` , 
            U.`login_name` AS `user_name`
        FROM `log_table` AS L
        LEFT JOIN `user_table` AS U ON U.`user_id` = L.`user_id` 
        WHERE L.`task_id` = '$task_id'
        ORDER BY L.`log_id` DESC";
    $log_result = mysqli_query($connection, $log_query);
    if (! $log_result) {
        set_user_message(mysqli_errno($connection), 'failure');
    } else {
        while ($log = mysqli_fetch_array($log_result)) {
            $log_list[$log['log_id']] = $log;
            $total_hours += $log['work_hours'];
        }
    }
    return $log_list;
}

?>
