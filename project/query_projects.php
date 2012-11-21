<?php

function query_projects($connection, $show_empty_projects, $show_closed_projects, $show_closed_tasks)
{
    $user_id = get_session_user_id();
    $task_join = $show_empty_projects ? "LEFT JOIN" : "INNER JOIN";
    $projects_query = "SELECT T.`task_id` , T.`task_summary` , T.`task_status` , T.`parent_task_id` , 
        P.`project_id` , P.`project_name` , P.`project_status` , 
        X.`timebox_id` , X.`timebox_name` , X.`timebox_end_date` , 
        U.`user_id` , U.`login_name`
        FROM `access_table` AS A 
        INNER JOIN `project_table` AS P ON A.`project_id` = P.`project_id` 
        $task_join `task_table` AS T ON T.`project_id` = P.`project_id` 
        LEFT JOIN `timebox_table` AS X ON T.`timebox_id` = X.`timebox_id` 
        LEFT JOIN `user_table` AS U ON T.`user_id` = U.`user_id`
        LEFT JOIN `task_table` AS PT ON T.`parent_task_id` = PT.`task_id`
        WHERE A.`user_id` = '$user_id' ";
    if (! $show_closed_projects) {
        $projects_query .= "
            AND P.`project_status` <> 'closed' ";
    }
    if (! $show_closed_tasks) {
        if ($show_empty_projects) {
            $projects_query .= "
            AND ( T.`task_id` IS NULL OR T.`task_status` <> 'closed' ) ";
        } else {
            $projects_query .= "
            AND T.`task_status` <> 'closed' ";
        }
    }
    $projects_query .= "
        ORDER BY P.`project_id` , T.`task_id`";

    $projects = array();
    $tasks = array();
    
    $projects_result = mysqli_query($connection, $projects_query);
    if (! $projects_result) {
        set_user_message(mysqli_error($connection), 'failure');
    } else {    
        while ($result = mysqli_fetch_array($projects_result)) {
            $project_id = $result['project_id'];
            if (!array_key_exists($project_id, $projects)) {
                $projects[$project_id] = array(
                    'project-id' => $result['project_id'],
                    'project-name' => $result['project_name'],
                    'project-status' => $result['project_status'],
                    'task-list' => array()
                );
            }
            $task_id = $result['task_id'];
            if ($task_id) {
                $tasks[$task_id] = array(
                    'task-id' => $task_id, 
                    'task-summary' => $result['task_summary'], 
                    'timebox-id' => $result['timebox_id'], 
                    'timebox-name' => $result['timebox_name'], 
                    'timebox-end-date' => $result['timebox_end_date'], 
                    'task-status' => $result['task_status'],  
                    'user-id' => $result['user_id'], 
                    'user-name' => $result['login_name'], 
                    'subtask-list' => array()
                );
                if ($result['parent_task_id']) {
                    $tasks[$result['parent_task_id']]['subtask-list'][$task_id] = &$tasks[$task_id];
                } else {
                    $projects[$result['project_id']]['task-list'][$task_id] = &$tasks[$task_id];
                }
            }
        }
    }
    
    return $projects;
}

?>
