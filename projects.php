<?php

include_once 'common.inc';
include_once 'project/new_project_form.php';
include_once 'project/project_list_options_form.php';
include_once 'project/query_projects.php';
include_once 'task/tasks_list.php';
include_once 'user/query_users.php';

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

global $projects, $user, $user_id, $user_name, $user_list, 
        $show_closed_tasks, $show_closed_projects;

function process_query_string() {
    global $user_id, $user_name;
    global $show_closed_tasks, $show_closed_projects;

    if (isset($_GET['id'])) {
        $user_id = $_GET['id'];
    } else if (isset($_GET['n'])) {
        $user_name = $_GET['n'];
    }

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
    global $projects, $user_list, $user, $user_id, $user_name;
    global $show_closed_tasks, $show_closed_projects;
    if (connect_to_database_session()) {
        $user = find_user($user_list, $user_id, $user_name);
        if ($user) {
            $session_user_id = is_admin_session() ? $user['user_id'] : get_session_user_id();
            $projects = query_projects($user['user_id'], $show_closed_projects, $show_closed_tasks, $session_user_id);
        }
    }
}
    
function show_sidebar() {
    global $user_list, $user;
    global $show_closed_tasks;
    global $show_closed_projects;
    
    echo "
        <div class='sidebar-block'>";
    show_project_list_options_form($user_list, $user, $show_closed_tasks, $show_closed_projects, is_admin_session());
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
    
    if (! $user) {
        return;
    }

    echo "
        <h3><a class='object-ref' href='user.php?id=${user['user_id']}'>${user['login_name']}</a></h3>";
    if (! $projects) {
        echo "
            <div>There are no projects to show for ${user['login_name']}.</div>";
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
