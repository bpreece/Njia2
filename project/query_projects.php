<?php

function query_projects($connection, $show_closed_projects, $show_closed_tasks)
{
    $user_id = get_session_user_id();
    $projects_query = "SELECT P.`project_id` , P.`project_name` , P.`project_status` 
        FROM `access_table` AS A 
        INNER JOIN `project_table` AS P ON A.`project_id` = P.`project_id` 
        WHERE A.`user_id` = '$user_id' ";
    if (! $show_closed_projects) {
        $projects_query .= "
            AND P.`project_status` <> 'closed' ";
    }
    $projects_query .= "
        ORDER BY P.`project_id` ";

    $projects = array();
    
    $projects_result = mysqli_query($connection, $projects_query);
    if (! $projects_result) {
        set_user_message(mysqli_error($connection), 'failure');
    } else {    
        while ($result = mysqli_fetch_array($projects_result)) {
            $project_id = $result['project_id'];
            if (!array_key_exists($project_id, $projects)) {
                $projects[$project_id] = $result;
            }
            $projects[$project_id]['task-list'] = query_project_tasks($connection, $project_id, $show_closed_tasks);
        }
    }
    
    return $projects;
}

function query_project($connection, $project_id, $user_id, $show_closed_tasks, $show_subtasks, $timebox_end_date)
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

    $project['task-list'] = query_project_tasks($connection, $project_id, $show_closed_tasks, $show_subtasks);
    $project['timebox-list'] = query_project_timeboxes($connection, $project_id, $timebox_end_date);    
    $project['user-list'] = query_project_users($connection, $project_id);

    return $project;
}

function query_project_tasks($connection, $project_id, $show_closed_tasks, $show_subtasks = TRUE)
{
    $tasks_query = "SELECT T.`task_id` , T.`task_summary` , T.`task_status` , T.`parent_task_id` , 
            X.`timebox_id` , X.`timebox_name` , X.`timebox_end_date` , 
            U.`user_id` , U.`login_name` AS `user_name` 
        FROM `task_table` AS T 
        LEFT JOIN `timebox_table` AS X ON T.`timebox_id` = X.`timebox_id` 
        LEFT JOIN `user_table` AS U ON T.`user_id` = U.`user_id`
        WHERE T.`project_id` = '$project_id'";
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
    
    $task_list = array();
    $root_task_list = array();
    $tasks_result = mysqli_query($connection, $tasks_query);
    if (! $tasks_result) {
        set_user_message(mysqli_error($connection), 'failure');
    } else {
        while ($task = mysqli_fetch_array($tasks_result)) {
            $task_id = $task['task_id'];
            $task_list[$task_id] = $task;
            if (! $task['parent_task_id']) {
                $root_task_list[$task_id] = &$task_list[$task_id];
            }
            if ($show_subtasks) {
                $task_list[$task_id]['subtask-list'] = array();
                if ($task['parent_task_id']) {
                    $task_list[$task['parent_task_id']]['subtask-list'][$task_id] = &$task_list[$task_id];
                }
            }
        }
    }
    
    return $root_task_list;
}

function query_project_users($connection, $project_id)
{
    $users_list = array();
    $user_query = "SELECT U.`user_id` , U.`login_name` AS `user_name` 
                 FROM `access_table` AS A 
                 INNER JOIN `user_table` AS U on U.`user_id` = A.`user_id`
                 WHERE A.`project_id` = '$project_id'
                 ORDER BY U.`login_name`";
    $user_result = mysqli_query($connection, $user_query);
    if (! $user_result) {
        set_user_message(mysqli_error($connection), 'warning');
    } else {
        while ($user = mysqli_fetch_array($user_result)) {
            $users_list[$user['user_id']] = $user;
        }
    }
    return $users_list;
}

function query_project_timeboxes($connection, $project_id, $timebox_end_date)
{
    $timeboxes_list = array();
    $timebox_query = "SELECT X.`timebox_id` , X.`timebox_name` , X.`timebox_end_date` 
                 FROM `timebox_table` AS  X
                 WHERE X.`project_id` = '$project_id' ";
    if ($timebox_end_date) {
        $timebox_query .= "
            AND X.`timebox_end_date` >= '$timebox_end_date'";
    } else {
        $timebox_query .= "
            AND X.`timebox_end_date` >= NOW()";
    }
    $timebox_query .= "
                 ORDER BY X.`timebox_end_date`, X.`timebox_id`";
    $timebox_result = mysqli_query($connection, $timebox_query);
    if (! $timebox_result) {
        set_user_message(mysqli_error($connection), 'warning');
    } else {
        while ($timebox = mysqli_fetch_array($timebox_result)) {
            $timeboxes_list[$timebox['timebox_id']] = $timebox;
        }
    }
    return $timeboxes_list;
}

?>
