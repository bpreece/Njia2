<?php

/**
 * 
 * @param boolean $show_closed_tasks
 * @param boolean $show_closed_projects
 */
function show_project_list_options_form($show_closed_tasks, $show_closed_projects)
{
    $tx_checked = $show_closed_tasks ? 'checked' : '';
    $px_checked = $show_closed_projects ? 'checked' : '';
    
    echo "
        <form id='list-options-form' method='GET'>
            <div id='list-options' class='group'>
                <input type='checkbox' name='tx' $tx_checked> Show closed tasks</br>
                <input type='checkbox' name='px' $px_checked> Show closed projects</br>
            </div>
            <input type='submit' value='Apply these options'></input>
        </form>";
}

?>
