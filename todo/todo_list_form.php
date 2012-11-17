<?php

/**
 * @param array $user_list array of user_id to user login name
 * @param integer $selected_user_id id of user to be selected in options field
 */
function show_todo_list_form($user_list, $selected_user_id = '')
{
    if ($selected_user_id == '') {
        $selected_user_id = get_session_user_id();
    }
    echo "
        <form id='show-todo-form' method='GET' action='todo.php'>
            <div id='user-id-field' class='group'>
                <label for='user-id'>Show to-do list for:</label>
                <select name='id' style='width:100%'>";
    
    foreach ($user_list as $user_id => $user_name) {
        $selected = ($user_id == $selected_user_id) ? "selected" : "";
        echo "
                <option value='$user_id' $selected>$user_name</option>";
    }
    
    echo "
                </select>
            </div>
            <input type='submit' value='Show to-do list'></input>
        </form>";
}

?>
