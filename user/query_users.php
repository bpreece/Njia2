<?php

function query_user($connection, $user_id) 
                {
    $user_query = "SELECT U.`user_id` , U.`login_name` , U.`user_creation_date` , U.`account_closed_date` 
                FROM `user_table` AS U 
                WHERE U.`user_id` = '$user_id'";
    $user_result = mysqli_query($connection, $user_query);
    if (! $user_result) {
        set_user_message(mysqli_error($connection), 'failure');
        return FALSE;
    }
    $num_users = mysqli_num_rows($user_result);
    if ($num_users == 0) {
        return FALSE;
    } else {
        return mysqli_fetch_array($user_result);
    }
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
