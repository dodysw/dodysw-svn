<?
/*
--------------------------------------------------------------------------------
PhpDig Version 1.8.x
This program is provided under the GNU/GPL license.
See the LICENSE file for more information.
All contributors are listed in the CREDITS file provided with this package.
PhpDig Website : http://www.phpdig.net/
--------------------------------------------------------------------------------
*/

//German language file from Gregor Mucha
//Version 2 from Matthias Strohmaier
//--------------------------------------
//'keyword' => 'translation'
$phpdig_mess = array (
'upd_sites'    =>'Update sites',
'upd2'         =>'Update Done',
'links_per'    =>'Links per',
'yes'          =>'ja',
'no'           =>'nein',
'delete'       =>'l&ouml;schen',
'reindex'      =>'Erneut indizieren',
'back'         =>'Zur&uuml;ck',
'files'        =>'Dateien',
'admin'        =>'Administration',
'warning'      =>'Achtung!',
'index_uri'    =>'Welche URI soll indiziert werden?',
'spider_depth' =>'Suchtiefe',
'spider_warn'  =>"Bitte sicherstellen, dass niemand sonst diese Seite indiziert.
Ein Sperr-Mechanismus wird in einer späteren Version realisiert werden.",
'site_update'  =>"Update der Seite oder des Unterordners",
'clean'        =>'s&auml;ubern',
't_index'      =>"Index",
't_dic'        =>'W&ouml;rterbuch',
't_stopw'      =>'gebr&auml;uchliche W&ouml;rter',
't_dash'       =>'dashes',

'update'       =>'Update',
'exclude'      =>'Zweig ausschlie&szlig;en und l&ouml;schen',
'excludes'     =>'Ausgeschlossene Pfade',
'tree_found'   =>'Zweig gefunden',
'update_mess'  =>'Erneut indizieren oder Zweig l&ouml;schen?',
'update_warn'  =>"\"Ausschliessen\" löscht alle indizierten Einträge.",
'update_help'  =>'Bitte auf das Kreuz klicken um den Zweig zu l&ouml;schen,
auf das gr&uuml;ne Häkchen um den Zweig zu aktualisieren
oder auf das Minus-Zeichen um den Zweig auszuschlie&szlig;en.',
'branch_start' =>'Bitte den Ordner ausw&auml;hlen, der rechts angezeigt werden soll.',
'branch_help1' =>'Bitte Ordner f&uuml;r individuelles Update ausw&auml;hlen',
'branch_help2' =>'Bitte auf das Kreuz klicken um das Dokument zu l&ouml;schen,
aauf das gr&uuml;ne Häkchen um es erneut zu indizieren.',
'redepth'      =>'levels depth',
'branch_warn'  =>"Das L&ouml;schen ist endg&uuml;ltig",
'to_admin'     =>"zum Admin-Interface",
'to_update'    =>"zum Update-Interface",

'search'       =>'Suche',
'results'      =>'Ergebnisse',
'display'      =>'Anzeige',
'w_begin'      =>'W&ouml;rter beginnen mit',
'w_whole'      =>'Exakter Wortlaut',
'w_part'       =>'Teil eines Ausdrucks',
'alt_try'      =>'Did you mean',

'limit_to'     =>'Suche begrenzen auf',
'this_path'    =>'oder nur diesen Pfad',
'total'        =>'Gesamtergebnisse',
'seconds'      =>'sekunden',
'w_common_sing'     =>'sind sehr gebr&auml;uchliche Worte und werden ignoriert.',
'w_short_sing'      =>'sind zu kurz und werden ignoriert.',
'w_common_plur'     =>'sind sehr gebr&auml;uchliche Worte und werden ignoriert.',
'w_short_plur'      =>'sind zu kurz und werden ignoriert.',
's_results'    =>'Ergebnisse',
'previous'     =>'Vorherige',
'next'         =>'N&auml;chste',
'on'           =>'f&uuml;r den Suchbegriff:',

'id_start'     =>'Seite indizieren',
'id_end'       =>'Indizierung abgeschlossen!',
'id_recent'    =>'Wurde gerade indiziert',
'num_words'    =>'Anzahl der Worte',
'time'         =>'Zeit',
'error'        =>'Fehler',
'no_spider'    =>'Spider nicht gestartet',
'no_site'      =>"Diese Seite ist nicht in der Datenbank",
'no_temp'      =>'Kein Link in der tempor&auml;reren Tabelle',
'no_toindex'   =>'Es ist nichts vorhanden, was indiziert werden könnte',
'double'       =>'Duplikat eines existierenden Dokuments',

'spidering'    =>'Der Spider arbeitet gerade...',
'links_more'   =>'mehr Links',
'level'        =>'Level',
'links_found'  =>'Links gefunden',
'define_ex'    =>'Ausgrenzungen definieren',
'index_all'    =>'Alles indizieren',

'end'          =>'Ende',
'no_query'     =>'Bitte Suchfeld ausf&uuml;llen',
'pwait'        =>'Bitte warten',
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