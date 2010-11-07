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

//English messages for PhpDig
//Some corrections by Brien Louque
//'keyword' => 'translation'
$phpdig_mess = array (
'upd_sites'    =>'Update sites',
'upd2'         =>'Update Done',
'links_per'    =>'Links per',
'yes'          =>'yes',
'no'           =>'no',
'delete'       =>'delete',
'reindex'      =>'Re-index',
'back'         =>'Back',
'files'        =>'files',
'admin'        =>'Administration',
'warning'      =>'Warning !',
'index_uri'    =>'Which URI would you index?',
'spider_depth' =>'Search depth',
'spider_warn'  =>"Please ensure that no one else is updating the same site.
A locking mechanism will be included in a later version.",
'site_update'  =>"Update a site or one of its branch",
'clean'        =>'Clean',
't_index'      =>"index",
't_dic'        =>'dictionary',
't_stopw'      =>'common words',
't_dash'       =>'dashes',

'update'       =>'Update',
'exclude'      =>'Delete and exclude branch',
'excludes'     =>'Exclude paths',
'tree_found'   =>'Found tree',
'update_mess'  =>'Re-index or delete a tree ',
'update_warn'  =>"Exclude will delete indexed entries",
'update_help'  =>'Click on the cross to delete the branch
Click on the green sign to update it
Click on the noway sign to exclude from future indexings',
'branch_start' =>'Select the folder to display on the left side',
'branch_help1' =>'Select there documents to update individually',
'branch_help2' =>'Click on the cross to delete a document
Click on the green sign to reindex it',
'redepth'      =>'levels depth',
'branch_warn'  =>"Erase is permanent",
'to_admin'     =>"to admin interface",
'to_update'    =>"to update interface",

'search'       =>'Search',
'results'      =>'results',
'display'      =>'display',
'w_begin'      =>'and operator',
'w_whole'      =>'exact phrase',
'w_part'       =>'or operator',
'alt_try'      =>'Did you mean',

'limit_to'     =>'limit to',
'this_path'    =>'this path',
'total'        =>'total',
'seconds'      =>'seconds',
'w_common_sing'     =>'is a very common word and was ignored.',
'w_short_sing'      =>'is too short a word and was ignored.',
'w_common_plur'     =>'are very common words and were ignored.',
'w_short_plur'      =>'are too short of words and were ignored.',
's_results'    =>'search results',
'previous'     =>'Previous',
'next'         =>'Next',
'on'           =>'on',

'id_start'     =>'Site indexing',
'id_end'       =>'Indexing complete !',
'id_recent'    =>'Was recently indexed',
'num_words'    =>'Num words',
'time'         =>'time',
'error'        =>'Error',
'no_spider'    =>'Spider not launched',
'no_site'      =>'No such site in database',
'no_temp'      =>'No link in temporary table',
'no_toindex'   =>'No content indexed',
'double'       =>'Duplicate of an existing document',

'spidering'    =>'Spidering in progress...',
'links_more'   =>'more new links',
'level'        =>'level',
'links_found'  =>'links found',
'define_ex'    =>'Define exclusions',
'index_all'    =>'index all',

'end'          =>'end',
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
'cleaningdictionnary'   =>'Cleaning dictionary',
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
'lastclicks'   =>'Last search clicks',
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