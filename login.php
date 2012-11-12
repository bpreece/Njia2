<?php
include_once('common.inc');

global $new_login, $login_name;
$login_name = '';
$new_login = FALSE;

function process_query_string() {
    global $new_login;
    if (isset($_GET['new'])) {
        $new_login = $_GET['new'];
    }
}

function process_form_data() {
    if (isset($_POST['login-button'])) {
        process_login();
    } else if (isset($_POST['new-login-button'])) {
        process_new_login();
    } else if (isset($_POST['forgot-button'])) {
        process_forgot();
    }
}

function process_login() {
    global $login_name, $cookie;
    
    if (!$_POST['name-field'] || !$_POST['password-field']) {
        set_user_message("Missing login name or password", "failure");
        return;
    }

    if (!($connection = connect_to_database())) {
        set_user_message("Failed accessing database", "failure");
        return;
    }

    $login_name = mysqli_real_escape_string($connection, $_POST['name-field']);
    $password = mysqli_real_escape_string($connection, $_POST['password-field']);

    $query = "SELECT `user_id`, `login_name`
                FROM `user_table` 
                WHERE `login_name` = '$login_name' AND 
                    `password` = MD5( CONCAT( `password_salt`, '$password' ) ) AND 
                    `account_closed_date` IS NULL";
    $results = mysqli_query($connection, $query);
    if (!$results) {
        set_user_message(mysqli_error($connection), "failure");
        return;
    }
    $result = mysqli_fetch_array($results);
    if (! $result) {
        set_user_message("Login name not found, or password doesn't match.", "warning");
        return;
    }

    $cookie = set_session_id($result['user_id'], $connection);
    header("location: todo.php");
}

function process_new_login() {
    global $login_name, $cookie;
    
    if (!($connection = connect_to_database())) {
        set_user_message("Failed accessing database", "failure");
        return;
    }
    
    $login_name = mysqli_real_escape_string($connection, $_POST['name-field']);
    $password = mysqli_real_escape_string($connection, $_POST['password-field']);

    if (!$_POST['name-field'] || !$_POST['password-field']) {
        set_user_message("You must provide a login name and password", "warning");
        return;
    }
    
    if (!$_POST['repeat-password-field'] || $_POST['repeat-password-field'] != $_POST['password-field']) {
        set_user_message("The passwords do not match.", "warning");
        return;
    }
    
    $user_query = "INSERT INTO `user_table` (
            `login_name` , `password_salt` 
        ) VALUES (
            '$login_name' , MD5( CONCAT( '$login_name' , NOW() ) )
        )";
    $user_results = mysqli_query($connection, $user_query);
    if (!$user_results) {
        set_user_message(mysqli_error($connection), "failure");
        return;
    }
    $user_id = mysqli_insert_id($connection);
    
    $password_query = "UPDATE `user_table`
        SET `password` = MD5( CONCAT( `password_salt`, '$password' ) )
        WHERE `user_id` = '$user_id'";
    $password_results = mysqli_query($connection, $password_query);
    if (! $password_results) {
        set_user_message(mysqli_error($connection), "failure");
        return;
    }

    $cookie = set_session_id($user_id, $connection);
    header("location: todo.php");
}

function process_forgot() {
    
}

function show_main_login_form() {
    global $login_name, $new_login;

    echo "
                <div id='login-main'>
                    <form id='login_form' name='login_form' method='POST'>
                        <label for='name-field'>Sign-on name:</label>
                        <input type='text' name='name-field' value='$login_name'></input>
                        <label for='password-field'>Password:</label>
                        <input type='password' name='password-field'></input>";
    if ($new_login) {
        echo "
                        <label for='repeat-password_field'>Repeat password:</label>
                        <input type='password' name='repeat-password-field'></input>
                        <br/>
                        <input type='submit' name='new-login-button' value='Create login'></input>";
    } else {
        echo "
                        <br/>
                        <input type='submit' name='login-button' value='Login'></input>";
    }
    echo "
                    </form>";
    if ($new_login) {
        echo "
                    <div>
                        or <a href='login.php'>Log in as an existing user</a>
                    </div>";
    } else {
        echo "
                    <div>
                        or <a href='login.php?new=1'>Create a new login</a>
                    </div>";
    }
    echo "
                </div>";
}

?>

<?php process_query_string(); ?>
<?php process_form_data(); ?>

<!DOCTYPE xhtml>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" href="layout.css" type="text/css" media="all" />
        <link rel="stylesheet" href="style.css" type="text/css" media="all" />
        <link rel="stylesheet" href="login.css" type="text/css" media="all" />
        <title><?php echo get_global_title() ?></title>
    </head>
    <body id="login-page" OnLoad="document.login_form.name_field.focus();">
        <div id="logo-and-title">
            <div id="logo"><img src="<?php echo get_logo_image(); ?>" /></div>
            <!--
            <div id="title"><?php echo get_global_title(); ?></div>
            -->
        </div> <!-- /logo-and-title -->
        <div id="page">
            <div id="header">
                <div id="logo-and-title">
                    <div id="logo"><img src="<?php get_logo_image(); ?>" /></div>
                    <div id="title"><?php echo get_global_title(); ?></div>
                </div> <!-- /logo-and-title -->
                <div id="main-menu">
                </div> <!-- /main-menu -->
            </div> <!-- /header -->
            <?php show_user_messages(); ?>
            <div id="content">
                <!--
                                <div id="login-controls">
                                    <form id='new-user-form' method='post' action='new-user.php'>
                                        <b>New User</b>
                                        <br/>
                                        <label for='name_field'>Sign-on name:</label>
                                        <br/>
                                        <input type='text' name='name_field'></input>
                                        <br/>
                                        <label for='password_field'>Password:</label>
                                        <br/>
                                        <input type='password' name='password_field'></input>
                                        <br/>
                                        <input type='submit' name='new_user_button' value='Create Login'></input>
                                    </form>
                                    <form method='post'>
                                        <b>Forgot password</b>
                                        <br/>
                                        <label for='name_field'>Sign-on name:</label>
                                        <br/>
                                        <input type='text' name='name_field'></input>
                                        <br/>
                                        <input type='submit' name='forgot_button' value='Forgot Password'></input>
                                    </form>
                                </div>
                -->
                <h1>Sign on</h1>

                <?php show_main_login_form(); ?>

            </div> <!-- /content -->
            <div id="footer">
                <?php show_footer(); ?>
            </div>
        </div> <!-- /page -->
    </body>
</html>
