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
                <li>NJIA &beta;</li>
                <li><a href='site-design.php'>SITE DESIGN</a></li>
                <li><a href='test-cases.php'>TEST CASES</a></li>
            </ul>
        </div>";
}

function show_content() {
    echo "<h1>Njia &beta;</h1>
                <h2>Project Tracking for the Ultra-Agile</h2>
                <p>
                    Quam ob rem sive hoc statueritis, dederitis mihi comitem ad 
                    contionem populo carum atque iucundum, sive Silani sententiam 
                    sequi malueritis, facile me atque vos crudelitatis vituperatione 
                    populus Romanus exsolvet, atque obtinebo eam multo leniorem fuisse. 
                </p>
                <p>
                    Quamquam, patres conscripti, quae potest esse in tanti 
                    sceleris immanitate punienda crudelitas? 
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
                </p>";
}

include_once ('template.inc');

?>
