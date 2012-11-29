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

global $query_user, $user_id;

function process_query_string() {
    global $show_closed_member_projects;
    global $show_closed_owned_projects;
    global $work_log_start_date;
    global $work_log_end_date;
    global $query_user, $user_id;

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
    } else {
        $user_id = get_session_user_id();
    }
}

function process_form_data() {
    process_user_form()
    || process_close_account_form()
    || process_reopen_account_form();
}

function prepare_page_data() {
    global $query_user, $user_id, $account_closed;
    
    if (connect_to_database_session()) {
        if ( ($query_user = query_user($user_id)) == NULL) {
            set_user_message("User $user_id was not found", 'warning');
            return;
        }
        if (isset($query_user['account_closed_date'])) {
            $account_closed = TRUE;
            set_user_message('This account has been closed', 'warning');
        }

        global $show_closed_member_projects;
        global $show_closed_owned_projects;

        // projects which are owned by $user_id, and which are accessible to
        // get_session_user_id();
        $query_user['owned-project-list'] = query_user_owned_projects($user_id, $show_closed_owned_projects);

        // projects which are accessible to both $user_id and get_session_user_id();
        $query_user['project-member-list'] = query_user_member_functions($user_id, $show_closed_member_projects);

        global $work_log_start_date;
        global $work_log_end_date;

        $query_user['log-list'] = query_user_work_log($user_id, $work_log_start_date, $work_log_end_date);
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
    global $show_closed_member_projects;
    global $show_closed_owned_projects;
    global $work_log_start_date;
    global $work_log_end_date;
    global $query_user;
    if (!$query_user) {
        return;
    }
    
    $query_id = $query_user['user_id'];
    echo "
        <h3>User $query_id</h3>";

    show_user_form($query_id, $query_user['user_name']);
    
    echo "
        <div id='owned-projects-header'>
            <div class='header-controls' style='float:right'>
                <form method='GET'>
                    <input type='hidden' name='id' value='$query_id' />";
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
    foreach ($query_user['owned-project-list'] as $project_id => $project) {
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
                    <input type='hidden' name='id' value='$query_id' />";
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
    foreach ($query_user['project-member-list'] as $project_id => $project) {
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
                    <input type='hidden' name='id' value='$query_id' />";
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
    foreach($query_user['log-list'] as $log_id => $log) {
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
