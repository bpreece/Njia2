<?php

function query_user_vitals($connection, $user_id) 
{
    $user_query = "SELECT U.`user_id` , U.`login_name` , U.`user_creation_date` , U.`account_closed_date` 
                FROM `user_table` AS U 
                WHERE U.`user_id` = '$user_id'";
    $user_result = mysqli_query($connection, $user_query);
    if (! $user_result) {
        set_user_message(mysqli_error($connection), 'failure');
        return NULL;
    }

    return mysqli_fetch_array($user_result);
}

function query_user($connection, $user_id) 
{
    $session_user_id = get_session_user_id();
    $user_id = mysqli_real_escape_string($connection, $user_id);
    if (is_admin_session()) {
        $user_query = "SELECT U.`user_id` , U.`login_name` AS  `user_name` , 
                U.`account_closed_date` 
            FROM `user_table` AS U
            WHERE `user_id` = '$user_id'";
    } else {
        $user_query = "SELECT U.`user_id` , U.`login_name` AS  `user_name` , 
                U.`account_closed_date` 
            FROM  `project_table` AS P
            INNER JOIN  `access_table` AS A1 ON P.`project_id` = A1.`project_id` 
            INNER JOIN  `access_table` AS A2 ON P.`project_id` = A2.`project_id` 
            INNER JOIN  `user_table` AS U ON A1.`user_id` = U.`user_id` 
            WHERE A1.`user_id` =  '$user_id'
                AND A2.`user_id` =  '$session_user_id'";
    }
    $query_result = mysqli_query($connection, $user_query);
    if (! $query_result) {
        set_user_message(mysqli_error($connection), 'failure');
        return NULL;
    }
    return mysqli_fetch_array($query_result);
}

function query_user_owned_projects($connection, $user_id, $show_closed_projects)
{
    $session_user_id = get_session_user_id();
    $owner_query = "SELECT P.`project_id` , P.`project_name` , P.`project_status` 
        FROM `access_table` AS A 
        INNER JOIN `project_table` AS P ON A.`project_id` = P.`project_id` 
        WHERE P.`project_owner` = '$user_id' AND A.`user_id` = '$session_user_id' ";
    if (! $show_closed_projects) {
        $owner_query .= "
            AND P.`project_status` <> 'closed'";
    }
    $owner_query .= "
        ORDER BY P.`project_id`";
    
    $projects_list = array();
    $owner_results = mysqli_query($connection, $owner_query);
    if (! $owner_results) {
        set_user_message(mysqli_error($connection), 'failure');
    } else {
        while ($project = mysqli_fetch_array($owner_results)) {
            $projects_list[$project['project_id']] = $project;
        }
    }
    return $projects_list;
}

function query_user_member_functions($connection, $user_id, $show_closed_projects)
{
    $member_query = "SELECT P.`project_id` , P.`project_name` , P.`project_status` 
        FROM `access_table` AS A 
        INNER JOIN `project_table` AS P ON A.`project_id` = P.`project_id` 
        WHERE P.`project_owner` <> '$user_id' AND A.`user_id` = '$user_id' ";
    if (! $show_closed_projects) {
        $member_query .= "
            AND P.`project_status` <> 'closed'";
    }
    
    $projects_list = array();
    $member_query .= "
        ORDER BY P.`project_id`";
    $member_results = mysqli_query($connection, $member_query);
    if (! $member_results) {
        set_user_message(mysqli_error($connection), 'failure');
    } else {
        while ($project = mysqli_fetch_array($member_results)) {
            $projects_list[$project['project_id']] = $project;
        }
    }
    
    return $projects_list;
}

function query_user_work_log($connection, $user_id, $work_log_start_date, $work_log_end_date)
{
    $session_user_id = get_session_user_id();
    $log_query = "SELECT P.`project_id` , P.`project_name` , 
            T.`task_id` , T.`task_summary` , 
            L.`log_id` , L.`description` , L.`work_hours` , L.`log_time` 
        FROM `access_table` AS A 
        INNER JOIN `project_table` AS P ON A.`project_id` = P.`project_id` 
        INNER JOIN `task_table` AS T ON P.`project_id` = T.`project_id` 
        INNER JOIN `log_table` AS L ON L.`task_id` = T.`task_id` 
        WHERE A.`user_id`= '$session_user_id' 
            AND L.`user_id` = $user_id ";
    if ($work_log_end_date) {
        $log_query .= "
            AND DATE( L.`log_time` ) <= '$work_log_end_date' ";
    }
    if ($work_log_start_date) {
        $log_query .= "
            AND DATE( L.`log_time` ) >= '$work_log_start_date' ";
    } else {
        if ($work_log_end_date) {
            $log_query .= "
            AND DATE( L.`log_time` ) >= DATE_SUB( '$work_log_end_date', INTERVAL 13 DAY ) ";
        } else {
            $log_query .= "
            AND DATE( L.`log_time` ) >= DATE_SUB( DATE( NOW() ), INTERVAL 13 DAY ) ";
        }
    }
    $log_query .= "
        ORDER BY P.`project_id` , T.`task_id` , L.`log_time` ";
    
    $log_list = array();
    $log_results = mysqli_query($connection, $log_query);
    if (! $log_results) {
        set_user_message(mysqli_error($connection), 'failure');
    } else {
        while ($log = mysqli_fetch_array($log_results)) {
            $log_list[$log['log_id']] = $log;
        }
    }
    
    return $log_list;
}

function query_users($connection, $show_closed_accounts, $starting_index, $max_row_count) 
{
    $users_query = "SELECT U.`user_id` , U. `login_name`, U.`user_permissions` , 
        U.`user_creation_date` , U.`account_closed_date`
        FROM `user_table` AS U ";
    if (! $show_closed_accounts) {
        $users_query .= "
            WHERE U.`account_closed_date` IS NULL ";
    }
    $users_query .= "
        LIMIT $starting_index , $max_row_count";

    $users_list = array();
    $users_result = mysqli_query($connection, $users_query);
    if (! $users_result) {
        set_user_message(mysqli_error($connection), 'failure');
    } else {
        while ($user = mysqli_fetch_array($users_result)) {
            $users_list[$user['user_id']] = $user;
        }
    }
    
    return $users_list;
}

function query_user_tasks($connection, $user_id)
{
    $task_query = "SELECT P.`project_id` , P.`project_name` , 
                T.`task_id` , T.`task_summary` , T.`parent_task_id` , 
                X.`timebox_id` , X.`timebox_name` , X.`timebox_end_date` 
                FROM  `access_table` AS A 
                INNER JOIN `project_table` AS P ON A.`project_id` = P.`project_id` 
                INNER JOIN `task_table` AS T ON P.`project_id` = T.`project_id` 
                INNER JOIN `timebox_table` AS X ON T.`timebox_id` = X.`timebox_id` 
                WHERE A.`user_id` = '$user_id' AND
                    T.`user_id` = '$user_id' AND
                    T.`task_status` <> 'closed' AND X.`timebox_end_date` >= CURRENT_DATE()
                ORDER BY X.`timebox_end_date` , P.`project_id` , T.`task_id`";

    $task_results = mysqli_query($connection, $task_query);
    $projects = array();
    if (! $task_results) {
        set_user_message(mysqli_error($connection), 'failure');
        return $projects;
    }
    while ($result = mysqli_fetch_array($task_results)) {
        $project_id = $result['project_id'];
        if (! array_key_exists($project_id, $projects)) {
            $projects[$project_id] = array(
                'project-id' => $project_id,
                'project-name' => $result['project_name'],
                'timebox-list' => array(),
            );
        }

        $timebox_id = $result['timebox_id'];
        if (! array_key_exists($timebox_id, $projects[$project_id]['timebox-list'])) {
            $projects[$project_id]['timebox-list'][$timebox_id] = array(
                'timebox-id' => $result['timebox_id'],
                'timebox-name' => $result['timebox_name'],
                'timebox-end-date' => $result['timebox_end_date'],
                'task-list' => array(),
            );
        }

        $task_id = $result['task_id'];
        if (! array_key_exists($task_id, $projects[$project_id]['timebox-list'][$timebox_id]['task-list'])) {
            $projects[$project_id]['timebox-list'][$timebox_id]['task-list'][$task_id] = array(
                'task-id' => $task_id,
                'parent-task-id' => $result['parent_task_id'],
                'task-summary' => $result['task_summary'],
            );
        }
    }
    
    return $projects;
}

function query_known_users($connection, $user_id)
{
    $users_query = "SELECT DISTINCT U.`user_id` , U.`login_name` 
                FROM  `access_table` AS A1 
                INNER JOIN `project_table` AS P ON A1.`project_id` = P.`project_id` 
                INNER JOIN `access_table` AS A2 ON P.`project_id` = A2.`project_id`
                INNER JOIN `user_table` as U ON A2.`user_id` = U.`user_id` 
                WHERE A1.`user_id` = '$user_id'
                ORDER BY U.`login_name`";
    $users_result = mysqli_query($connection, $users_query);
    $user_list = array();
    if (! $users_result) {
        set_user_message(mysqli_error($connection), 'failure');
        return $user_list;
    }
    while ($project_user = mysqli_fetch_array($users_result)) {
        $user_list[$project_user['user_id']] = $project_user['login_name'];
    }
    return $user_list;
}

?>
