<?php

include_once('common.inc');

function get_stylesheets() {
    $stylesheets = array('projects.css');
    return $stylesheets;
}

function get_page_id() {
    return 'task-page';
}

function get_page_class() {
    global $task;
    if (! $task) {
        return "";
    }
    $page_class = "task-${task['task_id']}";
    if ($task['task_status'] == 'closed') {
        $page_class .= " task-closed";
    }
    return $page_class;
}

global $projects;

function process_form_data() {
    
}

function process_query_string() {
    $projects = query_projects();
}

function query_projects() {
    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    $user_id = get_session_user_id();
    $projects_query = "SELECT T.* , 
                P.`project_id` , P.`project_name` , P.`project_status` , 
                X.`timebox_id` , X.`timebox_name` , X.`timebox_end_date` , 
                U.`user_id` , U.`login_name`
                FROM `access_table` AS A 
                INNER JOIN `project_table` AS P ON A.`project_id` = P.`project_id` 
                INNER JOIN `task_table` AS T ON P.`project_id` = T.`project_id` 
                LEFT JOIN `timebox_table` AS X ON T.`timebox_id` = X.`timebox_id` 
                LEFT JOIN `user_table` AS U ON T.`user_id` = U.`user_id`
                LEFT JOIN `task_table` AS PT ON T.`parent_task_id` = PT.`task_id`
                WHERE A.`user_id` = '$user_id' AND
                    P.`project_status` <> 'closed' AND
                    T.`task_status` <> 'closed' 
                    ORDER BY T.`task_id`";
    
    $projects_result = mysqli_query($connection, $projects_query);
    $num_rows = mysqli_num_rows($projects_result);
    if ($num_rows == 0) {
        set_user_message("There are no open projects with open tasks", 'warning');
        return null;
    }

    global $projects;
    $projects = array();
    $tasks = array();
    
    while ($result = mysqli_fetch_array($projects_result)) {
        $project_id = $result['project_id'];
        if (!array_key_exists($project_id, $projects)) {
            $project = array();
            $result['task-list'] = array();
            $projects[$project_id] = $result;
        }
        $task_id = $result['task_id'];
        if (!array_key_exists($task_id, $tasks)) {
            $result['subtask-list'] = array();
            $tasks[$task_id] = $result;
            if ($result['parent_task_id']) {
                $tasks[$result['parent_task_id']]['subtask-list'][$task_id] = $result;
            } else {
                $projects[$result['project_id']]['task-list'][$task_id] = $result;
            }
        }
    }
    
    return $projects;
}
    
function show_sidebar() {
    global $projects;
    echo "
        <h3>Projects Options</h3>";
    if (! $projects) {
        return;
    }

}

function show_content() 
{
    global $projects, $user;
    
    echo "
        <h3>Projects</h3>";
    if (! $projects) {
        echo "
            <div>You currently have no open projects.</div>";
        return;
    }
    
    echo "
            <div id='projects-list'>";
    foreach ($projects as $project) {
        echo "
                <div class='project project-${project['project_id']}'>
                    <div class='project-id'>${project['project_id']}</div>
                    <div class='project-name'>
                        <a href='project.php?id=${project['project_id']}'>${project['project_name']}</a>
                    </div>";
        foreach ($project['task-list'] as $task) {
            echo "
                    <div class='task-list'>
                        <div class='task task-${task['task_id']}'>
                            <div class='task-id'>${task['task_id']}</div>
                            <div class='task-summary'>
                                <a href='task.php?id=${task['task_id']}'>${task['task_summary']}</a>
                            </div>
                        </div> <!-- /task${task['task_id']} -->
                    </div> <!-- /task-list -->";
        }
        echo "
                </div> <!-- /project${project['project_id']} -->";
    }
    echo "
            </div> <!-- /projects-list -->";
}

include_once ('template.inc');

?>
