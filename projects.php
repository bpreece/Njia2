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

global $projects;

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

    global $projects;
    global $show_closed_tasks;
    global $show_closed_projects;
    global $show_empty_projects;
    $projects = query_projects($connection, $show_empty_projects, $show_closed_projects, $show_closed_tasks);
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
    foreach ($tasks_list as $task_id => $task) {
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
