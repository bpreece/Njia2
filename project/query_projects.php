<?php

function validate_project_owner($project_id)
{
    $owner_id = get_session_user_id();
    $query = "SELECT P.`project_owner` 
        FROM `project_table` AS P 
        WHERE P.`project_id` = '$project_id' 
            AND P.`project_owner` = '$owner_id' ";
    $owner = db_fetch($query);
    if (! $owner) {
        return FALSE;
    } else {
        return TRUE;
    }
}

function query_projects($user_id, $show_closed_projects, $show_closed_tasks, $session_user_id)
{
    $projects_query = "SELECT P.`project_id` , P.`project_name` , P.`project_status` 
        FROM `project_table` AS P
        INNER JOIN `access_table` AS A1 ON A1.`project_id` = P.`project_id` 
        INNER JOIN `access_table` AS A2 ON A2.`project_id` = A1.`project_id` 
            AND A2.`user_id` = '$session_user_id'
        WHERE A1.`user_id` = '$user_id' ";
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

function query_project($project_id)
{
    $project_id = db_escape($project_id);

    $project_query = "SELECT P.* , 
            U.`user_id` AS `owner_id` , U.`login_name` AS `owner_name` 
        FROM `project_table` AS P 
        INNER JOIN `user_table` AS U ON P.`project_owner` = U.`user_id`
        WHERE P.`project_id` = '$project_id' ";

    return db_fetch($project_query);
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

/**
 * Returns an array of timeboxes for the given project
 * @param type $project_id
 * @param type $timebox_end_date
 * @return type
 */
function query_project_timeboxes($project_id, $timebox_end_date = NULL)
{
    $timebox_query = "SELECT X.`timebox_id` , X.`timebox_name` , X.`timebox_end_date` 
                 FROM `timebox_table` AS  X
                 WHERE X.`project_id` = '$project_id' ";
    if ($timebox_end_date) {
        $timebox_query .= "
            AND X.`timebox_end_date` >= DATE( '$timebox_end_date' )";
    } else {
        $timebox_query .= "
            AND X.`timebox_end_date` >= CURRENT_DATE()";
    }
    $timebox_query .= "
                 ORDER BY X.`timebox_end_date`, X.`timebox_id`";
    
    return db_fetch_list('timebox_id', $timebox_query);
}

?>
