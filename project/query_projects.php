<?php

function query_projects($show_closed_projects, $show_closed_tasks)
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

    $projects = db_fetch_list('project_id', $projects_query);
    foreach($projects as $project_id => $project) {
        $projects[$project_id]['task-list'] = query_project_tasks($project_id, $show_closed_tasks);
    }
    
    return $projects;
}

function query_project($project_id, $user_id, $show_closed_tasks, $show_subtasks, $timebox_end_date = NULL)
{
    $project_id = db_escape($project_id);
    $show_closed_tasks = db_escape($show_closed_tasks);
    $timebox_end_date = db_escape($timebox_end_date);

    $project_query = "SELECT P.* , 
            O.`user_id` AS `owner_id` , O.`login_name` AS `owner_name` 
        FROM `access_table` AS A 
        INNER JOIN `project_table` AS P ON A.`project_id` = P.`project_id` 
        INNER JOIN `user_table` AS O ON P.`project_owner` = O.`user_id`
        WHERE P.`project_id` = '$project_id' AND A.`user_id` = $user_id";
    $project = db_fetch($project_query);
    if (! $project) {
        return NULL;
    }

    $project['can-close'] = ($project['project_status'] != 'closed');
    
    $project['task-list'] = query_project_tasks($project_id, $show_closed_tasks, $show_subtasks);
    $project['timebox-list'] = query_project_timeboxes($project_id, $timebox_end_date);    
    $project['user-list'] = query_project_users($project_id);

    return $project;
}

function query_project_tasks($project_id, $show_closed_tasks, $show_subtasks = TRUE)
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
    
    $root_task_list = array();
    $task_list = db_fetch_list('task_id', $tasks_query);
    if (! $task_list) {
        return $root_task_list;
    }
    
    foreach($task_list as $task_id => $task) {
        $task_id = $task['task_id'];
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
    
    return $root_task_list;
}

function query_project_users($project_id)
{
    $user_query = "SELECT U.`user_id` , U.`login_name` AS `user_name` 
                 FROM `access_table` AS A 
                 INNER JOIN `user_table` AS U on U.`user_id` = A.`user_id`
                 WHERE A.`project_id` = '$project_id'
                 ORDER BY U.`login_name`";
    return db_fetch_list('user_id', $user_query);
}

function query_project_timeboxes( $project_id, $timebox_end_date = NULL)
{
    $timebox_query = "SELECT X.`timebox_id` , X.`timebox_name` , X.`timebox_end_date` 
                 FROM `timebox_table` AS  X
                 WHERE X.`project_id` = '$project_id' ";
    if ($timebox_end_date) {
        $timebox_query .= "
            AND X.`timebox_end_date` >= DATE( '$timebox_end_date' )";
    } else {
        $timebox_query .= "
            AND X.`timebox_end_date` >= NOW()";
    }
    $timebox_query .= "
                 ORDER BY X.`timebox_end_date`, X.`timebox_id`";
    return db_fetch_list('timebox_id', $timebox_query);
}

?>
