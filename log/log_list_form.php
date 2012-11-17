<?php

/**
 * 
 * @param array $user_list array of user id to array of user attributes
 * @param int $selected_user_id
 * @param string $start_date
 * @param string $end_date
 */
function show_log_list_form($user_list, $selected_user_id, $start_date = '', $end_date = '') 
{
    echo "
        <form id='log-list-form' method='GET'>
            <div id='user-field' class='group'>
                <label for='u'>Show log for:</label>
                <select name='u' style='width:100%'>";
    
    foreach ($user_list as $user_id => $user) {
        $selected = ($user_id == $selected_user_id) ? 'selected' : '';
        echo "
                    <option value='$user_id' $selected>${user['login_name']}</option>";
    }
    
    echo "
                </select>
            </div>
            <div id='start-date-field' class='group'>
                <label for='s'>From:</label>
                <input type='text' name='s' style='width:100%' value='$start_date' />
            </div>
            <div id='end-date-field' class='group'>
                <label for='e'>To:</label>
                <input type='text' name='e' style='width:100%' value='$end_date' />
            </div>
            <input type='submit' value='Show log'></input>
        </form>";
}

?>
