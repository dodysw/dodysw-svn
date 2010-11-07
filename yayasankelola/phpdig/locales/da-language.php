<?php
/*
--------------------------------------------------------------------------------
PhpDig Version 1.8.x
This program is provided under the GNU/GPL license.
See the LICENSE file for more information.
All contributors are listed in the CREDITS file provided with this package.
PhpDig Website : http://www.phpdig.net/
--------------------------------------------------------------------------------
*/

//Danish messages for PhpDig
//Translated by Søren Thing Pedersen for v. 1.4.8
//Please note that in 1.4.8 a text-string "No results" is included in search_function.php.
//You have to edit search_function.php directly to translate it.
//'keyword' => 'translation'
$phpdig_mess = array (
'upd_sites'    =>'Update sites',
'upd2'         =>'Update Done',
'links_per'    =>'Links per',
'yes'          =>'ja',
'no'           =>'nej',
'delete'       =>'slet',
'reindex'      =>'Opdater indeks',
'back'         =>'Tilbage',
'files'        =>'filer',
'admin'        =>'Administration',
'warning'      =>'Advarsel !',
'index_uri'    =>'hvilken URI vil du indeksere ?',
'spider_depth' =>'Søge dybde',
'spider_warn'  =>"Vær venligst sikker på at ingen udover dig opdaterer det samme websted.
En låsemekanisme vil blive udarbejdet til næste version.",
'site_update'  =>"Opdater et websted eller eller et af dets kataloger.",
'clean'        =>'Ryd',
't_index'      =>"indeks",
't_dic'        =>'ordbog',
't_stopw'      =>'almindelige ord',
't_dash'       =>'dashes',

'update'       =>'Opdater',
'exclude'      =>'Slet eller ekskluder et katalog',
'excludes'     =>'Ekskluder stier',
'tree_found'   =>'Træ fundet',
'update_mess'  =>'Opdater indeks eller slet et træ ',
'update_warn'  =>"Ekskluder vil slette indekserede forekomster",
'update_help'  =>'Klik på korset for at slette kataloget
Klik på det grønne ikon for at opdatere det
Klik på adgang-forbudt skiltet for at ekskludere det fra fremtidige indekseringer',
'branch_start' =>'Vælg folderen, der skal vises, til venstre',
'branch_help1' =>'Vælg der dokumenter der skal opdateres individuelt',
'branch_help2' =>'Klik på korset for at slette dokumentet
Klik på det grønne ikon for at indeksere det igen',
'redepth'      =>'niveau dybde',
'branch_warn'  =>"Slet er permanent",
'to_admin'     =>"til administrations grænsefladen",
'to_update'    =>"til opdaterings grænsefladen",

'search'       =>'Søg',
'results'      =>'resultater',
'display'      =>'vis',
'w_begin'      =>'ord begynder med',
'w_whole'      =>'eksacte ord',
'w_part'       =>'del af ord',
'alt_try'      =>'Did you mean',

'limit_to'     =>'begræns til',
'this_path'    =>'denne sti',
'total'        =>'total',
'seconds'      =>'sekunder',
'w_common_sing'     =>'er almindelige ord og blev derfor ignoreret.',
'w_short_sing'      =>'er for korte ord og blev derfor ignoreret.',
'w_common_plur'     =>'er almindelige ord og blev derfor ignoreret.',
'w_short_plur'      =>'er for korte ord og blev derfor ignoreret.',
's_results'    =>'søge resultater',
'previous'     =>'Forrige',
'next'         =>'Næste',
'on'           =>'på',

'id_start'     =>'Websted indeksering',
'id_end'       =>'Indekseringen er fuldført !',
'id_recent'    =>'Var indeksteret for nyligt',
'num_words'    =>'Antal ord',
'time'         =>'tid',
'error'        =>'Fejl',
'no_spider'    =>'Søgerobotten blev ikke startet',
'no_site'      =>'Intet sådant websted i databasen',
'no_temp'      =>'Intet link i midlertid tabel',
'no_toindex'   =>'Intet indhold er indekseret',
'double'       =>'Duplikat af eksisterende dokument',

'spidering'    =>'Søgerobotten arbejder...',
'links_more'   =>'flere nye link',
'level'        =>'niveau',
'links_found'  =>'link fundet',
'define_ex'    =>'Definer eksklusioner',
'index_all'    =>'indekser alt',

'end'          =>'slut',
'no_query'     =>'Skriv venligst et søgeord i søgefeltet',
'pwait'        =>'Vent venligst',
'statistics'   =>'Statistik',

// INSTALL
'slogan'   =>'The smallest search engine in the universe : version',
'installation'   =>'Installation',
'instructions' =>'Type here the MySql parameters. Specify a valid existing user who can create databases if you choose create or update.',
'hostname'   =>'Hostname  :',
'port'   =>'Port (none = default) :',
'sock'   =>'Sock (none = default) :',
'user'   =>'User :',
'password'   =>'Password :',
'phpdigdatabase'   =>'PhpDig database :',
'tablesprefix'   =>'Tables prefix :',
'instructions2'   =>'* optional. Use lowercase characters, 16 characters max.',
'installdatabase'   =>'Install phpdig database',
'error1'   =>'Can\'t find connexion template. ',
'error2'   =>'Can\'t write connexion template. ',
'error3'   =>'Can\'t find init_db.sql file. ',
'error4'   =>'Can\'t create tables. ',
'error5'   =>'Can\'t find all config database files. ',
'error6'   =>'Can\'t create database.<br />Verify user\'s rights. ',
'error7'   =>'Can\'t connect to database<br />Verify connection datas. ',
'createdb' =>'Create database',
'createtables' =>'Create tables only',
'updatedb' =>'Update existing database',
'existingdb' =>'Write only connection parameters',
// CLEANUP_ENGINE
'cleaningindex'   =>'Cleaning index',
'enginenotok'   =>' index references targeted an inexistent keyword.',
'engineok'   =>'Engine is coherent.',
// CLEANUP_KEYWORDS
'cleaningdictionnary'   =>'Cleaning dictionnary',
'keywordsok'   =>'All keywords are in one or more page.',
'keywordsnotok'   =>' keywords where not in one page at least.',
// CLEANUP_COMMON
'cleanupcommon' =>'Cleanup common words',
'cleanuptotal' =>'Total ',
'cleaned' =>' cleaned.',
'deletedfor' =>' deleted for ',
// INDEX ADMIN
'digthis' =>'Dig this !',
'databasestatus' =>'DataBase status',
'entries' =>' Entries ',
'updateform' =>'Update form',
'deletesite' =>'Delete site',
// SPIDER
'spiderresults' =>'Spider results',
// STATISTICS
'mostkeywords' =>'Most keywords',
'richestpages' =>'Richest pages',
'mostterms'    =>'Most search terms',
'largestresults'=>'Largest results',
'mostempty'     =>'Most searchs giving empty results',
'lastqueries'   =>'Last search queries',
'responsebyhour'=>'Response time by hour',
// UPDATE
'userpasschanged' =>'User/Password changed !',
'uri' =>'URI : ',
'change' =>'Change',
'root' =>'Root',
'pages' =>' pages',
'locked' => 'Locked',
'unlock' => 'Unlock site',
'onelock' => 'A site is locked, because of spidering. You can\'t do this for now',
// PHPDIG_FORM
'go' =>'Go ...',
// SEARCH_FUNCTION
'noresults' =>'No results'
);
?>