<?php

include_once 'common.inc';
include_once 'project/new_project_form.php';
include_once 'project/project_list_options_form.php';
include_once 'project/query_projects.php';

global $show_closed_tasks;
global $show_closed_projects;
global $show_empty_projects;
$show_closed_tasks = "";
$show_closed_projects = "";
$show_empty_projects = "";

function get_stylesheets() {
    $stylesheets = array('projects.css');
    return $stylesheets;
}

function get_page_class() {
    return 'projects-page';
}

global $projects, $tasks;

function process_query_string() {
    global $show_closed_tasks;
    global $show_closed_projects;
    global $show_empty_projects;

    if (isset($_GET['tx'])) {
        $show_closed_tasks = TRUE;
    }
    if (isset($_GET['px'])) {
        $show_closed_projects = TRUE;
    }
    if (isset($_GET['pe'])) {
        $show_empty_projects = TRUE;
    }
}

function process_form_data() {
    process_new_project_form();
}

function prepare_page_data() {
    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    global $show_closed_tasks;
    global $show_closed_projects;
    global $show_empty_projects;

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
    
    $projects_result = mysqli_query($connection, $projects_query);
    if (! $projects_result) {
        set_user_message("There are no results to display", 'warning');
        return null;
    }

    global $projects, $tasks;
    $projects = array();
    $tasks = array();
    
    while ($result = mysqli_fetch_array($projects_result)) {
        $project_id = $result['project_id'];
        if (!array_key_exists($project_id, $projects)) {
            $projects[$project_id] = array();
            $projects[$project_id]['project-id'] = $result['project_id'];
            $projects[$project_id]['project-name'] = $result['project_name'];
            $projects[$project_id]['project-status'] = $result['project_status'];
            $projects[$project_id]['task-list'] = array();
        }
        $task_id = $result['task_id'];
        if ($task_id && ! array_key_exists($task_id, $tasks)) {
            $tasks[$task_id] = array();
            $tasks[$task_id]['task-id'] = $task_id;
            $tasks[$task_id]['task-summary'] = $result['task_summary'];
            $tasks[$task_id]['timebox-id'] = $result['timebox_id'];
            $tasks[$task_id]['timebox-name'] = $result['timebox_name'];
            $tasks[$task_id]['timebox-end-date'] = $result['timebox_end_date'];
            $tasks[$task_id]['task-status'] = $result['task_status'];;
            $tasks[$task_id]['user-id'] = $result['user_id'];
            $tasks[$task_id]['user-name'] = $result['login_name'];
            $tasks[$task_id]['subtask-list'] = array();
            if ($result['parent_task_id']) {
                $tasks[$result['parent_task_id']]['subtask-list'][] = $task_id;
            } else {
                $projects[$result['project_id']]['task-list'][] = $task_id;
            }
        }
    }
    
    return $projects;
}
    
function show_sidebar() {
    global $show_closed_tasks;
    global $show_closed_projects;
    global $show_empty_projects;
    
    echo "
        <div class='sidebar-block'>";
    show_project_list_options_form($show_closed_tasks, $show_closed_projects, $show_empty_projects);
    echo "
        </div>";
    echo "
        <div class='sidebar-block'>";
    show_new_project_form();
    echo "
        </div>";

}

function show_tasks_list($tasks_list) {
    global $tasks;
        foreach ($tasks_list as $task_id) {
            $task = $tasks[$task_id];
            $task_header_css = "task-header object-header object-${task['task-status']}";
            if (count($task['subtask-list']) == 0 && $task['task-status'] != 'closed') { 
                if (! $task['timebox-id']) {
                    $task_header_css .= " object-unscheduled";
                } else {
                    $task_header_css .= " object-scheduled";
                }
            }
            echo "
            <div id='task-$task_id' class='task'>
                <div class='task-header $task_header_css'>
                    <div class='task-details'>
                        <div class='task-user'>
                            <a class='object-ref' href='user.php?id=${task['user-id']}'>${task['user-name']}</a>
                        </div>
                        <div class='task-timebox'>
                            <a class='object-ref' href='timebox.php?id=${task['timebox-id']}'>${task['timebox-end-date']}</a>
                        </div>
                    </div> <!-- /task-details -->
                    <div class='task-id'>$task_id</div>
                    <div class='task-summary'>
                        <a class='object-ref' href='task.php?id=$task_id'>${task['task-summary']}</a>
                    </div>
                </div> <!-- /task-info -->";
            if (count($task['subtask-list']) > 0) {
                echo "
                <div class='task-list'>";
                show_tasks_list($task['subtask-list']);
                echo "
                </div>";
            }
            echo "
            </div> <!-- /task-$task_id -->";
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
    foreach ($projects as $project_id => &$project) {
        echo "
                <div id='project-$project_id' class='project'>
                    <div class='project-header object-header object-${project['project-status']}'>
                        <div class='project-id'>$project_id</div>
                        <div class='project-name'>
                            <a class='object-ref' href='project.php?id=$project_id'>${project['project-name']}</a>
                        </div>
                    </div> <!-- /project-info -->";
        if (count($project['task-list']) > 0) {
            echo "
                    <div class='task-list object-list'>";
            show_tasks_list($project['task-list']);
            echo "
                    </div>";
        }
        echo "
                </div> <!-- /project$project_id -->";
    }
    echo "
            </div> <!-- /projects-list -->";
}

include_once ('template.inc');

?>
