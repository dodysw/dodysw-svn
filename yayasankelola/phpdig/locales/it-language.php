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

// Messaggi in italiano per PhpDig (28-mag-2001)
// Traduzione di Mirko Maischberger <mirko@lilik.ing.unifi.it>
// Sito preferito: http://lilik.ing.unifi.it
//'keyword' => 'translation'
$phpdig_mess = array (
'upd_sites'    =>'Update sites',
'upd2'         =>'Update Done',
'links_per'    =>'Links per',
'yes'          =>'si',
'no'           =>'no',
'delete'       =>'cancella',
'reindex'      =>'rigenera indice',
'back'         =>'Indietro',
'files'        =>'files',
'admin'        =>'Amministrazione',
'warning'      =>'Attenzione!',
'index_uri'    =>'Quale URI vuoi indicizzare?',
'spider_depth' =>'Profondit&agrave; della ricerca',
'spider_warn'  =>"Assicurati che nessun altro stia aggiornando lo stesso sito prima di procedere.
La prossima versione implementer&agrave; un meccanismo di locking.",
'site_update'  =>"Aggiorna un sito o uno dei suoi rami",
'clean'        =>'Pulisci',
't_index'      =>"indice",
't_dic'        =>'dizionario',
't_stopw'      =>'parole di uso comune',
't_dash'       =>'dashes',

'update'       =>'Aggiorna',
'tree_found'   =>'Found tree',
'update_mess'  =>'Re-index or delete a tree ',
'update_warn'  =>"L'esclusione &egrave; permanente",
'update_help'  =>'Clicca sulla croce per cancellare questo ramo
Clicca sul simbolo verde per aggiornarlo',
'branch_start' =>'Seleziona la cartella da mostrare sul lato sinistro',
'branch_help1' =>'Seleziona i documenti da aggiornare manualmente',
'branch_help2' =>'Clicca sulla croce per cancellare un documento
Clicca sul simbolo verde per reindicizzare
La freccia lancia lo spider',
'redepth'      =>'livelli di profondit&agrave;',
'branch_warn'  =>"La cancellazione &egrave; permanente",
'to_admin'     =>"all'interfaccia di amministrazione",

'search'       =>'Cerca',
'results'      =>'risultati',
'display'      =>'mostra',
'w_begin'      =>'solo inizio parole',
'w_whole'      =>'parole esatte',
'w_part'       =>'qualsiasi parte delle parole',
'alt_try'      =>'Did you mean',

'limit_to'     =>'limita a',
'this_path'    =>'questa cartella',
'total'        =>'totali',
'seconds'      =>'secondi',
'w_common_sing'     =>'ignora le parole di uso comune.',
'w_short_sing'      =>'ignora le parole troppo corte.',
'w_common_plur'     =>'ignora le parole di uso comune.',
'w_short_plur'      =>'ignora le parole troppo corte.',
's_results'    =>'risultati della ricerca',
'previous'     =>'Precedente',
'next'         =>'Successivo',
'on'           =>'per',

'id_start'     =>'Indicizzazione del sito',
'id_end'       =>'Indicizzazione completata!',
'id_recent'    =>'&Egrave; stato indicizzato recentemente',
'num_words'    =>'Numero di parole',
'time'         =>'data',
'error'        =>'Errore',
'no_spider'    =>'Lo spider non &egrave; stato lanciato',
'no_site'      =>'Questo sito non esiste nel database',
'no_temp'      =>'Nessun link nella tabella temporanea',
'no_toindex'   =>'Nulla da indicizzare',
'double'       =>'Duplicato di un documento esistente',

'spidering'    =>'Lo spider sta lavorando...',
'links_more'   =>'ulteriori link',
'level'        =>'livello',
'links_found'  =>'trovati nuovi link',
'define_ex'    =>'Definisci le esclusioni',
'index_all'    =>'indicizza tutto',

'end'          =>'fine',
'no_query'     =>'Please fill the search form field',
'pwait'        =>'Please wait',
'statistics'   =>'Statistics',

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