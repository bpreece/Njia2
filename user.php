<?php


include_once 'common.inc';
include_once 'user/user_form.php';
include_once 'user/close_account_form.php';
include_once 'user/reopen_account_form.php';
include_once 'user/query_users.php';

global $show_closed_member_projects;
global $show_closed_owned_projects;
global $work_log_start_date;
global $work_log_end_date;
global $account_closed;

function get_stylesheets() {
    $stylesheets = array('user.css');
    return $stylesheets;
}

function get_page_id() {
    global $query_user;
    $page_class = "user-${query_user['user_id']}";
    return $page_class;
}

function get_page_class() {
    return 'user-page';
}

global $user, $user_id, $user_name;

function process_query_string() {
    global $user, $user_id, $user_name;
    global $show_closed_member_projects, $show_closed_owned_projects;
    global $work_log_start_date, $work_log_end_date;

    if (isset($_GET['po'])) {
        $show_closed_owned_projects = 'checked';
    }
    if (isset($_GET['pm'])) {
        $show_closed_member_projects = 'checked';
    }
    if (isset($_GET['ls'])) {
        $work_log_start_date = $_GET['ls'];
    }
    if (isset($_GET['le'])) {
        $work_log_end_date = $_GET['le'];
    }

    if (isset($_GET['id'])) {
        $user_id = $_GET['id'];
    } else if (isset($_GET['n'])) {
        $user_name = $_GET['n'];
    }
}

function process_form_data() {
    process_user_form()
    || process_close_account_form()
    || process_reopen_account_form();
}

function prepare_page_data() {
    global $user, $user_list, $user_id, $user_name;
    global $account_closed;
    global $show_closed_member_projects, $show_closed_owned_projects;
    global $work_log_start_date, $work_log_end_date;

    if (connect_to_database_session()) {
        $user = find_user($user_list, $user_id, $user_name);
        if ($user) {
            $session_user_id = is_admin_session() ? $user['user_id'] : get_session_user_id();
            $user['log-list'] = query_user_work_log($user_id, $work_log_start_date, $work_log_end_date, $session_user_id);
            if (isset($user['account_closed_date'])) {
                $account_closed = TRUE;
                set_user_message('This account has been closed', 'warning');
            }
            $user['owned-project-list'] = query_user_owned_projects($user_id, $show_closed_owned_projects, $session_user_id);
            $user['project-member-list'] = query_user_member_functions($user_id, $show_closed_member_projects, $session_user_id);
        }
    }
}

function show_sidebar() {
    global $user_id, $account_closed;
    
    if (! $account_closed && $user_id != 1 && ($user_id == get_session_user_id() || is_admin_session())) {
        echo "
            <div class='sidebar-block'>";
        show_close_account_form($user_id);
        echo "
            </div>";
    }
    
    if ($account_closed && is_admin_session()) {
        echo "
            <div class='sidebar-block'>";
        show_reopen_account_form($user_id);
        echo "
            </div>";
    }        
}

function show_content() 
{    
    global $show_closed_member_projects, $show_closed_owned_projects;
    global $work_log_start_date, $work_log_end_date;
    global $user;
    
    if (!$user) {
        return;
    }

    echo "
        <h3>User ${user['user_id']}</h3>";

    if ($user['user_id'] == get_session_user_id() || is_admin_session()) {
        show_user_form($user['user_id'], $user['login_name']);
    } else {
        echo "
        <form class='main-form'>
            <div id='login-name'>
                <label for='login-name'>Login name:</label>
                ${user['login_name']}
            </div>
        </form>";
    }
    
    echo "
        <div id='owned-projects-header'>
            <div class='header-controls' style='float:right'>
                <form method='GET'>
                    <input type='hidden' name='id' value='${user['user_id']}' />";
    if ($show_closed_member_projects) {
        echo "
                    <input type='hidden' name='pm' value='$show_closed_member_projects' />";
    }
    echo "
                    <input type='hidden' name='ls' value='$work_log_start_date' />
                    <input type='hidden' name='le' value='$work_log_end_date' />
                    <input type='checkbox' name='po' value='YES' $show_closed_owned_projects /><label>Show closed projects</label>
                    <input type='submit' style='font-size:85%' value='&#x2713;' title='Apply options' />
                </form>
            </div>
            <h4>Project owner</h4>
        </div>
        <div class='project-list'>";
    foreach ($user['owned-project-list'] as $project_id => $project) {
        echo "
                <div id='project-$project_id' class='project object-element'>";
        if ($project['project_status'] != 'open') {
            echo "
                    <div class='project-details'>${project['project_status']}</div>";
        }
        echo "
                    <div class='project-header object-header object-${project['project_status']}'>
                        <div class='project-id'>$project_id</div>
                        <div class='project-name'>
                            <a class='object-ref' href='project.php?id=$project_id'>${project['project_name']}</a>
                        </div> <!-- /project-name -->
                    </div> <!-- /project-info -->
                </div> <!-- /project-$project_id -->";
    }
    echo "
        </div> <!-- /user-list -->
        ";
    
    echo "
        <div id='project-member-header'>
            <div class='header-controls' style='float:right'>
                <form method='GET'>
                    <input type='hidden' name='id' value='${user['user_id']}' />";
    if ($show_closed_owned_projects) {
        echo"
                    <input type='hidden' name='po' value='$show_closed_owned_projects' />";
    }
    echo "
                    <input type='hidden' name='ls' value='$work_log_start_date' />
                    <input type='hidden' name='le' value='$work_log_end_date' />
                    <input type='checkbox' name='pm' value='YES' $show_closed_member_projects /><label>Show closed projects</label>
                    <input type='submit' style='font-size:85%' value='&#x2713;' title='Apply options' />
                </form>
            </div>
            <h4>Project member</h4>
        </div>
        <div class='project-list'>";
    foreach ($user['project-member-list'] as $project_id => $project) {
        echo "
                <div id='project-$project_id' class='project object-element'>";
        if ($project['project_status'] != 'open') {
            echo "
                    <div class='project-details'>${project['project_status']}</div>";
        }
        echo "
                    <div class='project-header object-header object-${project['project_status']}'>
                        <div class='project-id'>$project_id</div>
                        <div class='project-name'>
                            <a class='object-ref' href='project.php?id=$project_id'>${project['project_name']}</a>
                        </div> <!-- /project-name -->
                    </div> <!-- /project-info -->
                </div> <!-- /project-$project_id -->";
    }
    echo "
        </div> <!-- /user-list -->
        ";
    
    echo "
        <div id='work-log-header'>
            <div class='header-controls' style='float:right'>
                <form method='GET'>
                    <input type='hidden' name='id' value='${user['user_id']}' />";
    if ($show_closed_member_projects) {
        echo"
                    <input type='hidden' name='pm' value='$show_closed_member_projects' />";
    }
    if ($show_closed_owned_projects) {
        echo"
                    <input type='hidden' name='po' value='$show_closed_owned_projects' />";
    }
    echo "
                    <label for='work-log-start-date'>Show from</label>
                    <input type='text' size='12' value='$work_log_start_date' />
                    <label for='work-log-end-date'>to</label>
                    <input type='text' size='12' value='$work_log_end_date' />
                    <input type='submit' style='font-size:85%' value='&#x2713;' title='Apply options' />
                </form>
            </div>
            <h4>Work log</h4>
        </div>
        <div class='work-log-list'>";
    foreach($user['log-list'] as $log_id => $log) {
        echo "
            <div id='log-$log_id' class='log-entry'>
                <div class='log-time'>${log['log_time']}</div>
                <div class='log-description'>
                    ${log['description']}
                </div> <!-- /log-description -->
                <div class='log-details'>";
            if ($log['work_hours']) {
                echo "${log['work_hours']} hours";
            }
            echo "
                </div>
            </div> <!-- /log-$log_id -->";
    }
    echo "
        </div> <!-- /work-log-list -->";
}

include_once ('template.inc');

?>
