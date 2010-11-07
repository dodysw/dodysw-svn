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

//------------------------------------------------------------------------------
// Czech language file from Dan Barta (Enemy) and Jan Kincl
// encoding:          ----------> iso-8859-2 <----------
//------------------------------------------------------------------------------
//'keyword' => 'translation'
$phpdig_mess = array (
'upd_sites'    =>'Update sites',
'upd2'         =>'Update Done',
'links_per'    =>'Links per',
'yes'          =>'ano',
'no'           =>'ne',
'delete'       =>'smazat',
'reindex'      =>'pøeindexovat',
'back'         =>'zpìt',
'files'        =>'soubory',
'admin'        =>'administrace',
'warning'      =>'Pozor!',
'index_uri'    =>'Které URI chcete indexovat?',
'spider_depth' =>'Hloubka vyhledávání',
'spider_warn'  =>'Prosím ovìøte, zda adresa nebyla ji¾ indexována.
Uzamykací mechanismus bude k dispozici pozdìji.',
'site_update'  =>'aktualizace stránky nebo vìtve webu',
'clean'        =>'èistit',
't_index'      =>'Index',
't_dic'        =>'slovník',
't_stopw'      =>'bì¾ná slova',
't_dash'       =>'dashes',

'update'       =>'aktualizace',
'exclude'      =>'vymazaná a vyøazená vìtev',
'excludes'     =>'vyøazené cesty',
'tree_found'   =>'Vyhledávání stromu',
'update_mess'  =>'pøeindexování nebo smazání stromu?',
'update_warn'  =>'výmaz je trvalý',
'update_help'  =>'Kliknutím na køí¾ek vyma¾ete vìtev
Kliknutím na zelenou teèku povolíte aktualizaci
Kliknutím na jednosmìrku vylouèíte z budoucích aktualizací',
'branch_start' =>'Vyberte slo¾ku kliknutím na modrou ¹ipku v levé èásti obrazovky.',
'branch_help1' =>'Vyberte dokumentu k jednotlivé aktualizaci',
'branch_help2' =>'Kliknutím na køí¾ek vyma¾ete dokumenty,
kliknutím na zelenou teèku aktualizujete dokument',
'redepth'      =>'úroveò',
'branch_warn'  =>'vymazání je trvalé, bez návratu',
'to_admin'     =>'k administratorskému rozhraní',
'to_update'    =>'k aktualizaènímu rozhraní',

'search'       =>'Klíèová slova',
'results'      =>'výsledkù',
'display'      =>'zobraz',
'w_begin'      =>'slovo zaèíná na',
'w_whole'      =>'pøesné znìní slova',
'w_part'       =>'jakákoliv èást slov',
'alt_try'      =>'Did you mean',

'limit_to'     =>'limit po',
'this_path'    =>'tato cesta',
'total'        =>'celkem',
'seconds'      =>'sekundy',
'w_common_sing'     =>'pøíli¹ velké mno¾ství klíèových slov.',
'w_short_sing'      =>'pøíli¹ krátká slova byla ignorována.',
'w_common_plur'     =>'pøíli¹ velké mno¾ství klíèových slov.',
'w_short_plur'      =>'pøíli¹ krátká slova byla ignorována.',
's_results'    =>'výsledky hledání',
'previous'     =>'pøedchozí',
'next'         =>'dal¹í',
'on'           =>'hledaná slova:',

'id_start'     =>'Stránka indexována',
'id_end'       =>'Indexace kompletní!',
'id_recent'    =>'Právì bylo indexováno',
'num_words'    =>'Poèet slov',
'time'         =>'èas',
'error'        =>'chyba',
'no_spider'    =>'Spider nebyl spu¹tìn',
'no_site'      =>'tato strana nenalezena v databázi',
'no_temp'      =>'¾ádné odkazy v doèasné tabulce',
'no_toindex'   =>'¾ádná data k indexaci',
'double'       =>'duplicitní odkaz na ji¾ existující dokument',

'spidering'    =>'Spider právì pracuje...',
'links_more'   =>'dal¹í nové odkazy',
'level'        =>'úroveò',
'links_found'  =>'nalezeny odkazy',
'define_ex'    =>'Definice výjimek',
'index_all'    =>'V¹echno zaindexováno',

'end'          =>'Konec',
'no_query'     =>'Prosím vyplòte vyhledávací pole',
'pwait'        =>'prosím èekejte',
'statistics'   =>'statistika',

// INSTALL
'slogan'   =>'Nejmen¹í vyhledávací nástroj na svìtì. : verze',
'installation'   =>'Instalace',
'instructions' =>'Zde napi¹te parametry MySql. Urèete platného u¾ivatele, který mù¾e vytváøet databáze, pokud se rozhodnete je tvoøit nebo mìnit.',
'hostname'   =>'Hostname  :',
'port'   =>'Port (prázdné = default) :',
'sock'   =>'Sock (prázdné = default) :',
'user'   =>'U¾ivatel :',
'password'   =>'Heslo :',
'phpdigdatabase'   =>'PhpDig databáze :',
'tablesprefix'   =>'Pøedpona tabulek :',
'instructions2'   =>'* volitelné. Pou¾ijte malá písmena, 16 písmen maximálnì',
'installdatabase'   =>'Instalovat phpdig databáze',
'error1'   =>'Nemohu najít pøipojovací ¹ablonu. ',
'error2'   =>'Nemohu zapsat pøipojovací ¹ablonu. ',
'error3'   =>'Nemohu najít soubor init_db.sql. ',
'error4'   =>'Nemohu vytvoøit tabulky. ',
'error5'   =>'Nemohu najít v¹echny konfiguraèní soubory databázez. ',
'error6'   =>'Nemohu vytvoøit databázi.<br />Ovìøte práva u¾ivatele. ',
'error7'   =>'Nemohu se spojit s databází.<br />Ovìøte pøihla¹ovací údaje. ',
'createdb' =>'Vytvoøit databázi',
'createtables' =>'Vytvoøit pouze tabulky',
'updatedb' =>'Zmìnit existující databázi',
'existingdb' =>'Pouze vypsat parametry pøipojení',
// CLEANUP_ENGINE
'cleaningindex'   =>'Èistím seznam',
'enginenotok'   =>' seznam odkazù ukazuje na neexistující klíèový výraz.',
'engineok'   =>'Engine je koherentní.',
// CLEANUP_KEYWORDS
'cleaningdictionnary'   =>'Èistím slovníky',
'keywordsok'   =>'V¹echny klíèové výrazy jsou na jedné nebo více stránkách.',
'keywordsnotok'   =>' klíèové výrazy nebyly ani na jédné stránce.',
// CLEANUP_COMMON
'cleanupcommon' =>'Vyèistit bì¾ná slova',
'cleanuptotal' =>'Celkem ',
'cleaned' =>' vyèi¹tìno.',
'deletedfor' =>' smazáno za ',
// INDEX ADMIN
'digthis' =>'Indexuj !',
'databasestatus' =>'Stav databáze',
'entries' =>' Polo¾ek ',
'updateform' =>'Aktualizaèní formuláø',
'deletesite' =>'Smazat stránku',
// SPIDER
'spiderresults' =>'Výsledky spideringu',
// STATISTICS
'mostkeywords' =>'Nejhledanej¹í klíèová slova',
'richestpages' =>'Nejbohat¹í stránky',
'mostterms'    =>'Nejpou¾ívanej¹í podmínky',
'largestresults'=>'Nejvìt¹í výsledky',
'mostempty'     =>'Nejvíc hledání bez výsledku',
'lastqueries'   =>'Poslední hledané dotazy',
'responsebyhour'=>'Response time by hour',
// UPDATE
'userpasschanged' =>'U¾ivatel/Heslo zmìnìno !',
'uri' =>'URI : ',
'change' =>'Zmìnit',
'root' =>'Koøen',
'pages' =>' stránek',
'locked' => 'Zamknuto',
'unlock' => 'Odemkout stránku',
'onelock' => 'Stránka je zamknuta, probíhá spidering. Akci nelze nyní provést',
// PHPDIG_FORM
'go' =>'Hledej',
// SEARCH_FUNCTION
'noresults' =>'BEZ VÝSLEDKU'
);
?>