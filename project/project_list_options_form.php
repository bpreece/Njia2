<?php

/**
 * 
 * @param boolean $show_closed_tasks
 * @param boolean $show_closed_projects
 */
function show_project_list_options_form($user_list, $user, $show_closed_tasks, $show_closed_projects, $is_admin = FALSE)
{
    $tx_checked = $show_closed_tasks ? 'checked' : '';
    $px_checked = $show_closed_projects ? 'checked' : '';
    
    echo "
        <form id='list-options-form' method='GET'>
            <div id='user-field' class='group'>
                <label>Show log for:</label>";
    if ($is_admin) {
        echo "
                <input type='text' style='width:100%' name='n' value='${user['login_name']}'/>";
    } else {
        echo "
                <select name='id' style='width:100%'>";
        foreach ($user_list as $user_id => $user_name) {
            $selected = ($user_id == $user['user_id']) ? "selected" : "";
            echo "
                <option value='$user_id' $selected>$user_name</option>";
        }    
        echo "
                </select>";
    }
    echo "
            </div>
            <div id='list-options' class='group'>
                <input type='checkbox' name='tx' $tx_checked> Show closed tasks</br>
                <input type='checkbox' name='px' $px_checked> Show closed projects</br>
            </div>
            <input type='submit' value='Apply these options'></input>
        </form>";
}

?>
