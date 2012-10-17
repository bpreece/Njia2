<?php

include_once('common.inc');

function get_stylesheets() {
    $stylesheets = array('schedules.css');
    return $stylesheets;
}

function get_page_id() {
    return 'schedules-page';
}

global $timeboxes, $tasks;

function process_form_data() {
    
}

function process_query_string() {
    $timeboxes = query_schedules();
}

function query_schedules() {
    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    $user_id = get_session_user_id();
    $timebox_query = "SELECT X.`timebox_id` , X.`timebox_name` , X.`timebox_end_date` , 
        P.`project_id` , P.`project_name` , 
        T.`task_id` , T.`task_summary` , 
        U.`user_id` , U.`login_name` 
        FROM `access_table` AS A 
        INNER JOIN `timebox_table` AS X ON A.`project_id` = X.`project_id` 
        INNER JOIN `project_table` AS P ON X.`project_id` = P.`project_id` 
        INNER JOIN `task_table` AS T ON T.`timebox_id` = X.`timebox_id` 
        LEFT OUTER JOIN `user_table` AS U ON U.`user_id` = T.`user_id`
        WHERE A.`user_id` = '$user_id' AND 
            P.`project_status` <> 'closed' AND T.`task_status` <> 'closed' AND 
            X.`timebox_end_date` >= CURRENT_DATE() 
        ORDER BY X.`timebox_end_date` , P.`project_id` , T.`task_id`";
    
    $timebox_result = mysqli_query($connection, $timebox_query);
    $num_rows = mysqli_num_rows($timebox_result);
    if ($num_rows == 0) {
        set_user_message("There are no open projects with open tasks", 'warning');
        return null;
    }

    global $timeboxes;
    $timeboxes = array();
    
    while ($result = mysqli_fetch_array($timebox_result)) {
        $timebox_id = $result['timebox_id'];
        if (!array_key_exists($timebox_id, $timeboxes)) {
            $timeboxes[$timebox_id] =  array(
                'timebox-id' => $timebox_id, 
                'timebox-name' => $result['timebox_name'], 
                'timebox-end-date' => $result['timebox_end_date'], 
                'project-id' => $result['project_id'], 
                'project-name' => $result['project_name'], 
                'task-list' => array(), 
            );
        }
        $task_id = $result['task_id'];
        $timeboxes[$timebox_id]['task-list'][$task_id] =  array(
            'task-id' => $task_id, 
            'task-summary' => $result['task_summary'], 
            'user-id' => $result['user_id'], 
            'user-name' => $result['login_name'], 
        );
    }
    
    return $timeboxes;
}

function show_sidebar() {
    global $timeboxes;
    echo "
        <h3>Options</h3>";
    if (! $timeboxes) {
        return;
    }

}

function show_content() {
    global $timeboxes, $user;
    
    echo "
        <h3>Schedules</h3>";
    if (! $timeboxes) {
        echo "
            <div>You currently have no scheduled tasks.</div>";
        return;
    }
    
    echo "
            <div id='schedules-list'>";
    foreach ($timeboxes as $timebox_id => $timebox) {
        $project_id = $timebox['project-id'];
        echo "
                <div class='timebox timebox-$timebox_id'>
                    <div class='timebox-info'>
                        <div class='timebox-end-date'>${timebox['timebox-end-date']}</div>
                    </div>
                    <div class='timebox-id'>$timebox_id</div>
                    <div class='timebox-name'>
                        <a class='object-ref' href='timebox.php?id=$timebox_id'>${timebox['timebox-name']}</a>
                    </div>
                    <div class='project-info'>
                        <div class='project-id'>$project_id</div>
                        <div class='project-name'>
                            <a class='object-ref' href='project.php?id=$project_id'>${timebox['project-name']}</a>
                        </div>
                    </div> <!-- /project-info -->
                    <div class='task-list'>";
        foreach ($timebox['task-list'] as $task_id => $task) {
            echo "
                        <div class='task task-$task_id'>
                            <div class='task-info'>
                                 <div class='task-user'>
                                     <a class='object-ref' href='user.php?id=${task['user-id']}'>${task['user-name']}</a>
                                 </div>
                             </div> <!-- /task-info -->
                             <div class='task-id'>$task_id</div>
                             <div class='task-summary'>
                                 <a class='object-ref' href='task.php?id=$task_id'>${task['task-summary']}</a>
                             </div>
                        </div> <!-- /task-$task_id -->";
        }
        echo "
                    </div> <!-- /task-list -->
                </div> <!-- /timebox-$timebox_id -->";
    }
    echo "
            </div> <!-- /schedules-list -->";
}

include_once ('template.inc');

?>
