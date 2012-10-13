<?php

include_once('common.inc');


function query_tasks() {
    $project_array = array();

    $connection = connect_to_database_session();
    if (!$connection) {
        set_user_message("Failed accessing database", "failure");
        return;
    }

    $session_id = get_session_id();
    $query = "SELECT P.`project_id` , P.`project_name` , T.`task_id` , T.`task_summary` , T.`parent_task_id` , B.`timebox_id` , B.`timebox_name` , B.`timebox_end_date` 
                FROM  `session_table` AS S
                INNER JOIN  `access_table` AS A ON S.`user_id` = A.`user_id` 
                INNER JOIN  `project_table` AS P ON A.`project_id` = P.`project_id` 
                INNER JOIN  `task_table` AS T ON P.`project_id` = T.`project_id` 
                LEFT JOIN  `timebox_table` AS B ON T.`timebox_id` = B.`timebox_id` 
                WHERE S.`session_id` = '$session_id'
                ORDER BY T.`task_id`";

    $results = mysqli_query($connection, $query);
    
    $num_rows = mysqli_num_rows($results);
    if ($num_rows == 0) {
        return;
    }

    $projects = array();
    for ($i = 0; $i < $num_rows; $i++) {
        $result = mysqli_fetch_array($results);

        $project_id = $result['project_id'];
        if (array_key_exists($project_id, $projects)) {
            $project = &$projects[$project_id];
        } else {
            $project = array();
            $project['project-id'] = $project_id;
            $project['project-name'] = $result['project_name'];
            $project['project-tasks'] = array();
            $projects[$project_id] = $project;
        }
        $tasks = &$project['project-tasks'];
        
        $task_id = $result['task_id'];
        if (array_key_exists($task_id, $tasks)) {
            $task = $tasks[$task_id];
        } else {
            $task = array();
            $task['task-id'] = $task_id;
            $task['parent-task-id'] = $result['parent_task_id'];
            $task['task-summary'] = $result['task_summary'];
            $task['timebox-id'] = $result['timebox_id'];
            $task['timebox-name'] = $result['timebox_name'];
            $task['timebox-end-date'] = $result['timebox_end_date'];
            $tasks[$task_id] = $task;
        }
    }

    return $projects;
}


function show_sidebar() {
    echo "
        <h3>Sidebar block</h3>
        <div class='sidebar-block'>
            <p>
                Video adhuc duas esse sententias, unam D. Silani, qui 
                censet eos, qui haec delere conati sunt, morte esse 
                multandos, alteram C. Caesaris, qui mortis poenam removet, 
                ceterorum suppliciorum omnes acerbitates amplectitur. 
            </p>
            <p>
                Uterque et pro sua dignitate et pro rerum magnitudine in 
                summa severitate versatur. Alter eos, qui nos omnes 
                vita privare conati sunt, qui delere imperium, qui 
                populi Romani nomen exstinguere, punctum temporis frui 
                vita et hoc communi spiritu non putat oportere, atque 
                hoc genus poenae saepe in improbos cives in hac re 
                publica esse usurpatum recordatur. 
            </p>
        </div>
        <div class='sidebar-block'>
            <h3>Sidebar block</h3>
            <p>
                Alter intellegit mortem ab dis immortalibus non esse 
                supplicii causa constitutam, sed aut necessitatem 
                naturae aut laborum ac miseriarum quietem. 
            </p>
        </div>";
}

function show_content() 
{
    $projects = query_tasks();
    if (! $projects) {
        show_user_message("You must sign on to view this page.", 'warning');
        return;
    }
    
    echo "<div id=todo-projects-list>";
    foreach ($projects as $pid => &$pobj) {
        echo "<div id='project-$pid' class='project-item'>";
        echo "    <div class='project-info'>";
        echo "        <div class='project-id'>$pid</div>";
        echo "        <div class='project-name'>{$pobj['project-name']}</div>";
        echo "    </div> <!-- /project-info -->";
        $tasks = &$pobj['project-tasks'];
        echo "    <div class='project-tasks-list'>";
        foreach ($tasks as $tid => &$tobj) {
            echo "        <div id='task-$tid' class='task-item'>";
            echo "            <div class='task-info'>";
            echo "                <div class='task-id'>$tid</div>";
            echo "                <div class='task-summary'><a href='task.php?id=$tid'>{$tobj['task-summary']}</a></div>";
            echo "                <div class='task-timebox-id'>{$tobj['timebox-id']}</div>";
            echo "                <div class='task-timebox-name'>{$tobj['timebox-name']}</div>";
            echo "                <div class='task-timebox-end-date'>{$tobj['timebox-end-date']}</div>";
            echo "            </div> <!-- /task-info -->";
            echo "        </div> <!-- /task-$tid -->";
        }
        echo "    </div> <!-- /project-tasks-list -->";
        echo "</div> <!-- /project-$pid -->";
    }
    echo "</div> <!-- /todo-projects-list -->";
}

include_once ('template.inc');
?>
