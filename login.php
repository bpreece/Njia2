<?php
include_once('common.inc');

global $user_id;
global $login_name;

function process_login() {
    global $login_name, $cookie;
    
    if (!$_POST['name_field'] || !$_POST['password_field']) {
        set_user_message("Missing login name or password", "failure");
        return;
    }

    if (!($connection = connect_to_database())) {
        set_user_message("Failed accessing database", "failure");
        return;
    }

    $login_name = mysqli_real_escape_string($connection, $_POST['name_field']);
    $password = mysqli_real_escape_string($connection, $_POST['password_field']);

    $query = "SELECT `user_id`, `login_name`
                FROM `user_table` 
                WHERE `login_name` = '$login_name' AND 
                    `password` = MD5(CONCAT(`password_salt`, '$password')) AND 
                    `account_closed_date` IS NULL";
    $results = mysqli_query($connection, $query);
    if (!$results) {
        set_user_message(mysqli_error($connection), "failure");
        return;
    }

    if (mysqli_num_rows($results) == 0) {
        set_user_message("Login name not found, or password doesn't match.", "warning");
        return;
    }

    $result = mysqli_fetch_assoc($results);
    if (!$result) {
        set_user_message(mysqli_error($connection), "failure");
        return;
    }

    $login_name = $result['login_name'];
    $user_id = $result['user_id'];
    $cookie = set_session_id($user_id, $connection);
    
    header("location: todo.php");
}

function process_new() {
    
}

function process_forgot() {
    
}

if (isset($_POST['login_button'])) {
    process_login();
} else if (isset($_POST['new_user_button'])) {
    process_new();
} else if (isset($_POST['forgot_button'])) {
    process_forgot();
}
?>


<!DOCTYPE xhtml>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" href="layout.css" type="text/css" media="all" />
        <link rel="stylesheet" href="style.css" type="text/css" media="all" />
        <link rel="stylesheet" href="login.css" type="text/css" media="all" />
        <title><?php echo get_global_title() ?></title>
    </head>
    <body id="login-page">
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

                <div id="login-main">
                    <form id="login-form" method='post'>
                        <label for='name_field'>Sign-on name:</label>
                        <input type='text' name='name_field'></input>
                        <label for='password_field'>Password:</label>
                        <input type='password' name='password_field'></input>
                        <br/>
                        <input type='submit' name='login_button' value='Login'></input>
                    </form>
                </div>

            </div> <!-- /content -->
            <div id="footer">
                <?php show_footer(); ?>
            </div>
        </div> <!-- /page -->
    </body>
</html>
