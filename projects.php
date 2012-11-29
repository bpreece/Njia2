<?php

include_once 'common.inc';
include_once 'project/new_project_form.php';
include_once 'project/project_list_options_form.php';
include_once 'project/query_projects.php';
include_once 'task/tasks_list.php';

global $show_closed_tasks;
global $show_closed_projects;
$show_closed_tasks = "";
$show_closed_projects = "";

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

    if (isset($_GET['tx'])) {
        $show_closed_tasks = TRUE;
    }
    if (isset($_GET['px'])) {
        $show_closed_projects = TRUE;
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
    $projects = query_projects($show_closed_projects, $show_closed_tasks);
}
    
function show_sidebar() {
    global $show_closed_tasks;
    global $show_closed_projects;
    
    echo "
        <div class='sidebar-block'>";
    show_project_list_options_form($show_closed_tasks, $show_closed_projects);
    echo "
        </div>";
    echo "
        <div class='sidebar-block'>";
    show_new_project_form();
    echo "
        </div>";

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
                    <div class='project-header object-header object-${project['project_status']}'>
                        <div class='project-id'>$project_id</div>
                        <div class='project-name'>
                            <a class='object-ref' href='project.php?id=$project_id'>${project['project_name']}</a>
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
