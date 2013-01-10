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
        </div>";
    echo "<div class='sidebar-block'>
            <ul class='menu'>
                <li><a href='index.php'>NJIA &beta;</a></li>
                <li>QUICK START</li>
                <li><a href='faq.php'>FAQ</a></li>
            </ul>
        </div>";
}

function show_content() {
    echo "<h1>Njia &beta;</h1>
                <h2>Project Tracking for the Ultra-Agile</h2>
                <p class='dictionary-definition'>
                    <strong>Njia</strong> /n&#805;.&#712;&#676;&#618;.&#601;/ 
                    Noun. The absolute best online project task-tracking 
                    application ever created&mdash;designed especially for 
                    small, agile development teams like yours&mdash;you're 
                    guarantee of success. Because, you know, hyperbole!
                    (from Swahili <cite>njia</cite> way, path)
                </p>
                <p>
                    You and us, we're the same. We're small. We're agile. We 
                    get things done.  
                </p>
                <p>
                    We know that process keeps us on track, keeps us
                    honest&mdash;but we follow process mindfully, not slavishly. 
                    We don't let process get in our way. We use just as much 
                    as we need and no more. We always remember that people and 
                    results are more important than process.
                </p>
                <p>
                    That's why we built <strong>Njia!</strong>
                </p>
                <h2>Section title</h2>
                <p>
                    Ego enim de meo sensu iudico. Nam ita mihi salva re publica 
                    vobiscum perfrui liceat, ut ego, quod in hac causa vehementior 
                    sum, non atrocitate animi moveor—quis est enim me mitior?
                    sed singulari quadam humanitate et misericordia. Videor enim 
                    mihi videre hanc urbem, lucem orbis terrarum atque arcem 
                    omnium gentium, subito uno incendio concidentem; cerno 
                    animo sepulta in patria miseros atque insepultos acervos 
                    civium; versatur mihi ante oculos aspectus Cethegi et furor 
                    in vestra caede bacchantis. 
                </p>
                <h2>Section title</h2>
                <p>
                    Cum vero mihi proposui regnantem Lentulum, sicut ipse se 
                    sperasse ex fatis confessus est, purpuratum esse huic Gabinium, 
                    cum exercitu venisse Catilinam, tum lamentationem matrum 
                    familias, tum fugam virginum atque puerorum ac vexationem 
                    Vestalium perhorresco, et quia mihi vehementer haec videntur 
                    misera atque miseranda, idcirco in eos, qui ea perficere voluerunt, 
                    me severum vehementemque praebeo. Etenim quaero, si quis pater 
                    familias, liberis suis a servo interfectis, uxore occisa, 
                    incensa domo, supplicium de servo non quam acerbissimum 
                    sumpserit, utrum is clemens ac misericors an inhumanissimus 
                    et crudelissimus esse videatur? Mihi vero importunus ac 
                    ferreus, qui non dolore et cruciatu nocentis suum dolorem 
                    cruciatumque lenierit. Sic nos in his hominibus, qui nos, 
                    qui coniuges, qui liberos nostros trucidare voluerunt, 
                    qui singulas unius cuiusque nostrum domos et hoc universum 
                    rei publicae domicilium delere conati sunt, qui id egerunt, 
                    ut gentem Allobrogum in vestigiis huius urbis atque in 
                    cinere deflagrati imperii conlocarent, si vehementissimi 
                    fuerimus, misericordes habebimur: sin remissiores esse 
                    voluerimus, summae nobis crudelitatis in patriae civiumque 
                    pernicie fama subeunda est. 
                </p>
                <p>
                    Nisi vero cuipiam L. Caesar, vir fortissimus et amantissimus 
                    rei publicae, crudelior nudius tertius visus est, cum 
                    sororis suae, feminae lectissimae, virum praesentem et 
                    audientem vita privandum esse dixit, cum avum suum iussu 
                    consulis interfectum filiumque eius impuberem, legatum a 
                    patre missum, in carcere necatum esse dixit. 
                    Quorum quod simile factum? quod initum delendae rei publicae consilium? 
                </p>
                <h3>Disclaimer</h3>
                <p>
                    This site is currently in first beta stage.  You're welcome
                    to use it, and feedback will be welcome, but consider
                    yourself warned.
                </p>";
}

include_once ('template.inc');

?>
