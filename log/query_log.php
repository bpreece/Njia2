<?php

function query_user_log($user_id, $start_date, $end_date, &$total_work_hours, $session_user_id)
{
    $log_query = "SELECT L.`log_id` , L.`description` , L.`work_hours` , 
            DATE( L.`log_time` ) AS `log_date` , 
            T.`task_id` , T.`task_summary` 
        FROM `log_table` AS L
        INNER JOIN `task_table` AS T ON L.`task_id` = T.`task_id` 
        INNER JOIN `access_table` AS A1 ON A1.`project_id` = T.`project_id` 
        INNER JOIN `access_table` AS A2 ON A2.`project_id` = A1.`project_id` 
            AND A2.`user_id` = '$session_user_id'
        WHERE L.`user_id` = '$user_id' ";
    if ($end_date) {
        $log_query .= "
            AND DATE( L.`log_time` ) <= '$end_date' ";
    } else {
        $log_query .= "
            AND DATE( L.'log_time` ) <= DATE( NOW() ) ";
    }
    if ($start_date) {
        $log_query .= "
            AND DATE( L.`log_time` ) >= '$start_date' ";
    } else {
        if ($end_date) {
            $log_query .= "
                AND DATE( L.`log_time` ) >= DATE( DATE_SUB( '$end_date' , INTERVAL 13 DAY ) ) ";
        } else {
            $log_query .= "
                AND DATE( L.`log_time` ) >= DATE( DATE_SUB( NOW() , INTERVAL 13 DAY ) ) ";
        }
    }
    $log_query .= "
        ORDER BY  L.`log_time` ASC , T.`task_id` ";

    $log_results = db_fetch_array($log_query);
    $total_work_hours = 0;
    $log_date_list = array();
    if ($log_results) {
        foreach($log_results as $log) {
            $date = $log['log_date'];
            $log_id = $log['log_id'];
            $task_id = $log['task_id'];
            $work_hours = $log['work_hours'];
            $total_work_hours += $work_hours;
            if (!array_key_exists($date, $log_date_list)) {
                $log_date_list[$date] = array();
                $log_date_list[$date]['work-hours'] = 0;
                $log_date_list[$date]['task-list'] = array();
            }

            $log_date_list[$date]['work-hours'] += $work_hours;
            if (!array_key_exists($task_id, $log_date_list[$date]['task-list'])) {
                $log_date_list[$date]['task-list'][$task_id] = array();
                $log_date_list[$date]['task-list'][$task_id]['work-hours'] = 0;
                $log_date_list[$date]['task-list'][$task_id]['task-summary'] = $log['task_summary'];
                $log_date_list[$date]['task-list'][$task_id]['log-list'] = array();
            }
            $log_date_list[$date]['task-list'][$task_id]['work-hours'] += $work_hours;
            $log_date_list[$date]['task-list'][$task_id]['log-list'][$log_id] = array(
                'work-hours'=> $log['work_hours'], 
                'description' => $log['description'],
            );
        }
    }
    
    return $log_date_list;
}

?>
