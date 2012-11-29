
<?php 

include_once 'common.inc';

function get_stylesheets() {
    return array('index.css', 'site-design.css');
}

function get_page_id() {
    return 'site-design';
}

function get_page_class() {
    return 'no-header';
}

function show_sidebar() {
    echo "
        <div class='sidebar-block'>
            <form method='GET' action='login.php'>
                <input type='submit' value='Log in to Njia'></input>
            </form>
        </div>";
    echo "<div class='sidebar-block'>
            <ul class='menu'>
                <li><a href='index.php'>NJIA 2</a></li>
                <li>SITE DESIGN</li>
                <li><a href='test-cases.php'>TEST CASES</a></li>
            </ul>
        </div>";
}

function show_content() { 
    echo "
        <h2>Data Model</h2>
        
        <h3>Task</h3>
        
        <h4>Attributes</h4>
        <div class='definition'>
            <div class='label'>task id</div>
            <div class='data'>
                A unique identifier for this task.  The task identifier is 
                permanent and cannot be changed.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>project id</div>
            <div class='data'>
                The unique identifier for the project to which this task belongs.  
                Every task must belong to a project.  If a task is created as a 
                subtask of another task, it automatically inherits the project 
                of its parent task.  The project identifier cannot change - a 
                task cannot be moved from one task to another.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>parent task ID</div>
            <div class='data'>
                The identifier of the parent task of this task, if it has one.  
                The parent task is assigned when thte task is created, and cannot 
                be changed.  A task which is created without a parent cannot be 
                given one later.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>task summary</div>
            <div class='data'>
                Summarium breve huis laboris.  Quod ad summam bytes 255 continet,
                aliter tamen non est circumscriptum. Exempli gratia, id idem 
                esse alio labori potest. Animus tamen nomen esse unicum.  
                Hoc summarium emendabile est.
                A brief summary of this task.  This summary cannot be more than 
                255 bytes, but otherwise it is not constrained - for example, it 
                can potentially be duplicated in another task.  However, its 
                intent is to uniquely identify the task to the user.  The task 
                summary can be edited later.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>task discussion</div>
            <div class='data'>
                A detailed description and discussion of the task.  There is no 
                significant limit to the length of the discussion.
            </div>
        </div>	
        <div class='definition'>
            <div class='label'>task status</div>
            <div class='data'>
                Either 'open' or 'closed'.  Other task status is defined by 
                other fields:  a task is scheduled if it has a timebox ID;  a 
                task is assigned if it has a user ID.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>timebox id</div>
            <div class='data'>
                The identifier for the timebox to which this task is assigned, 
                if any.  The timebox can be assigned or unassigned or modified 
                at any later time, but the task is never assigned to more than 
                one timebox at once.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>user id</div>
            <div class='data'>
                The identifier for the user to which this task is assigned, if 
                any.  The user can be assigned or unassigned or modified at any 
                later time, but the task is never assigned to more than one user 
                at once.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>task creation date</div>
            <div class='data'>
                A timestamp for the date/time when this task was created.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>task modified date</div>
            <div class='data'>
                A timestamp for the date/time when the task was last modified.
            </div>
        </div>
        <div class='definition'>
            <div class='label'></div>
            <div class='data'>
                
            </div>
        </div>
        
        <h4>Operations</h4>
        <div class='definition'>
            <div class='label'>new</div>
            <div class='data'>
                A task can only be created as new task for a specific project, 
                or as a subtask of another task - at the time of creation, 
                exactly one of these must be provided.  The task must also have 
                a summary at the time of creation.  The task ID and task creation 
                date are automatically provided.  All other fields are optional.  
                The default value for the task status is 'open'.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>edit</div>
            <div class='data'>
                The task ID, project ID, parent task ID, and task creation date 
                are not modifiable.  Once the object is created, these are 
                constant.  The task modified date is automatically updated on 
                every edit to the instance.  All other fields can be edited by 
                the user.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>delete</div>
            <div class='data'>
                A &quot;deleted&quot; task is not actually removed from the database table.  
                Instead, the task status is set to 'closed'.
            </div>
        </div>

        <h3>Project</h3>

        <h4>Attributes</h4>
        <div class='definition'>
            <div class='label'>project id</div>
            <div class='data'>
                A unique identifier for this project.  The project identifier is permanent and cannot be changed.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>project name</div>
            <div class='data'>
                A short string identifying this project.  This does not need to be unique.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>project discussion</div>
            <div class='data'>
                A detailed description and discussion of the project.  There is no significant limit on the length of the discussion.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>project status</div>
            <div class='data'>
                Either 'open' or 'closed'.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>project creation date</div>
            <div class='data'>
                A timestamp for the date/time when this project was created.
           </div>
        </div>
         
        <h4>Operations</h4>
        <div class='definition'>
            <div class='label'>new</div>
            <div class='data'>
                A project must have a name assigned at the time of creation.  The project ID and project creation date are automatically provided.  Other fields are optional.  The default value for the project status is 'open'.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>edit</div>
            <div class='data'>
                The project ID and project creation date are not modifiable.  Once the object is created, these are constant.  All other fields can be edited by the user.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>delete</div>
            <div class='data'>
                A &quot;deleted&quot; project is not actually removed from the database table.  Instead, the project status is set to 'closed'.
            </div>
        </div>

        <h3>Timebox</h3>

        <h4>Attributes</h4>
        <div class='definition'>
            <div class='label'>timebox id</div>
            <div class='data'>
                A unique identifier for this timebox.  The timebox identifier is permanent and cannot be changed.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>timebox name</div>
            <div class='data'>
                A short string identifying this timebox.  This does not need to be unique.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>timebox discussion</div>
            <div class='data'>
                A detailed description and discussion of the timebox.  There is no significant limit on the length of the discussion.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>timebox end date</div>
            <div class='data'>
                A timestamp for the date/time when this timebox is scheduled to end.
            </div>
        </div>
        
        <h4>Operations</h4>
        <div class='definition'>
            <div class='label'>new</div>
            <div class='data'>
                A timebox must have a name assigned at the time of creation.  The timebox ID is automatically provided.  Other fields are optional.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>edit</div>
            <div class='data'>
                The project ID is not modifiable.  Once the object is created, this are constant.  All other fields can be edited by the user.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>delete</div>
            <div class='data'>
                A &quot;deleted&quot; timebox is not actually removed from the database table.  Instead, the timebox end date can be set to NULL.
            </div>
        </div>

        <h3>User</h3>

        <h4>Attributes</h4>
        <div class='definition'>
            <div class='label'>user id</div>
            <div class='data'>
                A unique identifier for this user.  The user identifier is permanent and cannot be changed.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>user login name</div>
            <div class='data'>
                A short string identifying this user.  This must be unique.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>user password</div>
            <div class='data'>
                The password which the user must provide to log in.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>user password salt</div>
            <div class='data'>
                An arbitrary salt which is added to the user's login for password validation.  This must be provided at the time the account is created and cannot be changed.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>user creation date</div>
            <div class='data'>
                A timestamp for the date/time when this user's account was created.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>user last login date</div>
            <div class='data'>
                A timestamp for the last date/time when this user logged in.
            </div>
        </div>
        
        <h4>Operations</h4>
        <div class='definition'>
            <div class='label'>new</div>
            <div class='data'>
                A user must have a name, password, and password salt assigned at the time of creation.  The user ID, user creation date, and last login date are automatically provided.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>edit</div>
            <div class='data'>
                The user ID, password salt, and user creation date are not modifiable.  Once the object is created, they are constant.  The last login date must be set to CURRENT_TIMESTAMP.   The login name and password may be changed by the user, but the login name must be unique in the database.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>delete</div>
            <div class='data'>
                A &quot;deleted&quot; user is not actually removed from the database table.  Instead, the password is set to NULL.
            </div>
        </div>

        <h3>Session</h3>

        <h4>Attributes</h4>
        <div class='definition'>
            <div class='label'>session id</div>
            <div class='data'>
                A unique identifier for this session.  The session identifier is permanent and cannot be changed.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>user id</div>
            <div class='data'>
                The identifier of the user who set up this session.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>session creation date</div>
            <div class='data'>
                A timestamp for the date/time when this session was created.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>session expiration date</div>
            <div class='data'>
                A timestamp for the date/time when this session will expire.
            </div>
        </div>

        <h4>Operations</h4>
        <div class='definition'>
            <div class='label'>new</div>
            <div class='data'>
                A session must have a user id assigned at the time of creation.  The session ID and session creation date are automatically provided.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>edit</div>
            <div class='data'>
                There are no editable fields in the session table.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>delete</div>
            <div class='data'>
                A &quot;deleted&quot; session is not actually removed from the database table.  Instead, the session expiration date can be set to '0000-00-00 00:00:00'.
            </div>
        </div>

        <h3>Access</h3>

        <h4>Attributes</h4>
        <div class='definition'>
            <div class='label'>user id</div>
            <div class='data'>
                The identifier of the user who is granted access.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>project id</div>
            <div class='data'>
                The identifier of the project for which the user is granted access.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>access creation date</div>
            <div class='data'>
                A timestamp for the date/time when this access was granted.
            </div>
        </div>

        <h4>Operations</h4>
        <div class='definition'>
            <div class='label'>new</div>
            <div class='data'>
                A session must have a user id and project id assigned at the time of creation.  The access creation date is automatically provided.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>edit</div>
            <div class='data'>
                There are no editable fields in the access table.
            </div>
        </div>
        <div class='definition'>
            <div class='label'>delete</div>
            <div class='data'>
                A &quot;deleted&quot; session is not actually removed from the database table.  Instead, the session expiration date can be set to 0000-00-00 00:00:00.
            </div>
        </div>

        <h2>Use Cases</h2>

        <h2>User Views</h2>

        <h3>To-do list</h3>
        ";
}

include_once 'template.inc';

?>