<?php

function show_timebox_form($timebox_id, $timebox) 
{
    echo "
        <form id='timebox-form' class='main-form' method='post'>
            <input type='hidden' name='timebox-id' value='$timebox_id'>
            
            <div id='project_name'>
                <label>Project:</label>
                <a class='object-ref' href='project.php?id=${timebox['project_id']}'>${timebox['project_name']}</a>
            </div>

            <div id='timebox-name'>
                <label for='timebox-name'>Name:</label>
                <input style='width:50%' type='text' name='timebox-name' value='${timebox['timebox_name']}'></input>
            </div>
            
            <div id='timebox-discussion'>
                <label for='timebox-discussion' style='vertical-align:top'>Discussion:</label>
                <textarea name='timebox-discussion' rows='10' style='width:50%'>${timebox['timebox_discussion']}</textarea>
            </div>

            <div id='timebox-end-date'>
                <label>End date:</label>
                <input style='width:50%' type='text' name='timebox-end-date' value='${timebox['timebox_end_date']}'></input>
            </div>
                
            <div id='form-controls'>
                <input type='submit' name='update-button' value='Update'></input>
            </div> <!-- /form-controls -->
        </form>";
    
}

function process_timebox_form()
{
    if (! isset($POST['timebox_form_button'])) {
        return FALSE;
    }

    if (connect_to_database_session()) {
        $timebox_id = db_escape($_POST['timebox-id']);
        $timebox_name = db_escape($_POST['timebox-name']);
        $timebox_end_date= db_escape($_POST['timebox-end-date']);

        $query = "UPDATE `timebox_table` SET
            `timebox_name` = '$timebox_name' , 
            `timebox_end_date` = '$timebox_end_date' 
            WHERE `timebox_id` = '$timebox_id'";

        if (db_execute($query)) {
            set_user_message('The changes have been applied', 'success');
        }
    }
    
    return TRUE;
}

?>
