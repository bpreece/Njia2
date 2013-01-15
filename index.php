<?php

include_once 'common.inc';

function get_stylesheets() {
    return array('index.css');
}

function get_page_id() {
    return 'front-page';
}

function get_page_class() {
    return 'no-header';
}

/*
function process_query_string() {
}
 */

/*
function process_form_data() {
}
 */

function show_sidebar() {
    echo "
        <div class='sidebar-block'>
            <form method='GET' action='login.php'>
                <input type='submit' value='Log in to Njia'></input>
            </form>
            or
            <form method='GET' action='login.php?new'>
                <input type='submit' value='Create an account'></input>
            </form>
        </div>";
    echo "<div class='sidebar-block'>
            <ul class='menu'>
                <li>NJIA &beta;</li>
                <li><a href='overview.php'>READ ME</a></li>
                <li><a href='quickstart.php'>QUICK START</a></li>
            </ul>
        </div>";
}

function show_content() {
    echo "
        <h1>Njia &beta;</h1>
        <p class='dictionary-definition'>
            <strong>Njia</strong> /n&#805;.&#712;&#676;&#618;.&#601;/ 
            Noun. The absolute best online project task-tracking 
            application ever created&mdash;designed especially for 
            small, agile development teams like yours&mdash;your 
            guarantee of success. Because <em>hyperbole</em>!
            <br>
            [Swahili <cite>njia</cite> way, path]
        </p>
        <h2>Project Tracking for the Ultra-Agile</h2>
        <p>
            You're small. You're agile. You get things done.  
        </p>
        <p>
            You follow process mindfully, not slavishly. You know that 
            process keeps you on track, keeps you focused, but you know that 
            process is a tool, not a goal, and you don't let process get 
            in your way. You always remember that people and results are more 
            important than process.
        </p>
        <p>
            That's why <strong>Njia!</strong>
        </p>
        <p>
            Njia is project task tracking distilled to its purest essence; 
            designed for doers, not watchers, for programmers, not program 
            managers, for people who get things done.
        </p>
        <p>
            Get started now. 
        </p>
        <p>
            <a href='http://localhost/~benpreece/Njia2/login.php'>Log in</a> or
            <a href='http://localhost/~benpreece/Njia2/login.php?new='>create 
            an account</a>.
        </p>
        <h2>Ceterum Censeo</h2>
        <p>
            Ego enim de meo sensu iudico. Nam ita mihi salva re publica 
            vobiscum perfrui liceat, ut ego, quod in hac causa vehementior 
            sum, non atrocitate animi moveor—quis est enim me mitior?
            sed singulari quadam humanitate et misericordia. Seriously? Are
            you reading this?  Just create an account already. Videor enim 
            mihi videre hanc urbem, lucem orbis terrarum atque arcem 
            omnium gentium, subito uno incendio concidentem; cerno 
            animo sepulta in patria miseros atque insepultos acervos 
            civium; versatur mihi ante oculos aspectus Cethegi et furor 
            in vestra caede bacchantis. 
        </p>
        <h2>Disclaimer</h2>
        <p>
            This site is currently in first beta stage.  You're welcome
            to use it, and comments and feedback will be duly drooled over, 
            but consider yourself warned.
        </p>
        ";
}

include_once ('template.inc');

?>
