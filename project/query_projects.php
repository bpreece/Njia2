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

function query_project($connection, $project_id, $user_id, $show_closed_tasks, $timebox_end_date)
{
    $project_id = mysqli_real_escape_string($connection, $project_id);
    $show_closed_tasks = mysqli_real_escape_string($connection, $show_closed_tasks);
    $timebox_end_date = mysqli_real_escape_string($connection, $timebox_end_date);

    $project_query = "SELECT P.* , 
            O.`user_id` AS `owner_id` , O.`login_name` AS `owner_name` 
        FROM `access_table` AS A 
        INNER JOIN `project_table` AS P ON A.`project_id` = P.`project_id` 
        INNER JOIN `user_table` AS O ON P.`project_owner` = O.`user_id`
        WHERE P.`project_id` = '$project_id' AND A.`user_id` = $user_id";
    
    $project_result = mysqli_query($connection, $project_query);
    if (! $project_result) {
        set_user_message(mysqli_error($connection), 'failure');
        return NULL;
    }
    
    $project = mysqli_fetch_array($project_result);
    if (! $project) {
        return NULL;
    }

    $project['can-close'] = ($project['project_status'] != 'closed');
    
    $task_query = "SELECT T.`task_id` , T.`task_summary` , `task_status` ,  `timebox_id` 
        FROM `task_table` AS T
        WHERE T.`project_id` = '$project_id' AND T.`parent_task_id` IS NULL";
    if (! $show_closed_tasks) {
        $task_query .= "
            AND T.`task_status` <> 'closed' ";
    }
    $task_query .= "
        ORDER BY T.`task_id`";

    $project['task_list'] = array();
    $task_result = mysqli_query($connection, $task_query);
    if (! $task_result) {
        set_user_message(mysqli_error($connection), 'failure');
    } else {
        while ($task = mysqli_fetch_array($task_result)) {
            $project['task_list'][$task['task_id']] = $task;
            if ($task['task_status'] != 'closed') {
                $project['can-close'] = FALSE;
            }
        }
    }
    
    $timebox_query = "SELECT X.* FROM `timebox_table` AS X
        WHERE X.`project_id` = '$project_id'";
    if ($timebox_end_date) {
        $timebox_query .= "
            AND X.`timebox_end_date` >= '$timebox_end_date'";
    } else {
        $timebox_query .= "
            AND X.`timebox_end_date` >= NOW()";
    }
    $timebox_query .= "
        ORDER BY X.`timebox_end_date`";

    $project['timebox_list'] = array();
    $timebox_result = mysqli_query($connection, $timebox_query);
    if (! $timebox_result) {
        set_user_message(mysqli_error($connection), 'failure');
    } else {
        while ($timebox = mysqli_fetch_array($timebox_result)) {
            $project['timebox_list'][$timebox['timebox_id']] = $timebox;
        }
    }
    
    $project['user_list'] = array();
    $user_query = "SELECT U.`user_id` , U.`login_name` 
        FROM `access_table` AS A
        INNER JOIN `user_table` AS U ON A.`user_id` = U.`user_id`
        WHERE A.`project_id` = '$project_id'
        ORDER BY U.`login_name`";
    $user_result = mysqli_query($connection, $user_query);
    if (! $user_result) {
        set_user_message(mysqli_error($connection), 'failure');
    } else {
        while ($user = mysqli_fetch_array($user_result)) {
            $project['user_list'][$user['user_id']] = $user['login_name'];
        }
    }

    return $project;
}

?>
