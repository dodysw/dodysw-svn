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

//Portuguese (Portugal) messages for PhpDig
//by Carlos Serrão (carlos.serrao@iscte.pt)
//'keyword' => 'translation'
$phpdig_mess = array (
'upd_sites'    =>'Update sites',
'upd2'         =>'Update Done',
'links_per'    =>'Links per',
'yes'          =>'sim',
'no'           =>'não',
'delete'       =>'eliminar',
'reindex'      =>'Re-indexar',
'back'         =>'Atrás',
'files'        =>'ficheiros',
'admin'        =>'Administração',
'warning'      =>'Cuidado !',
'index_uri'    =>'Qual a URI que deseja indexar?',
'spider_depth' =>'Profundidade da busca',
'spider_warn'  =>"Por favor assegure-se que mais ninguém está a actualizar este site.
Um mecanismo de bloqueio será incluído em versões posteriores",
'site_update'  =>"Actualizar um site ou um dos seus ramos",
'clean'        =>'Limpar',
't_index'      =>"índice",
't_dic'        =>'dicionário',
't_stopw'      =>'palavras comuns',
't_dash'       =>'dashes',

'update'       =>'Actualizar',
'tree_found'   =>'Árvore encontrada',
'update_mess'  =>'Re-indexar ou apagar uma árvore ',
'update_warn'  =>"A eliminação é permanente",
'update_help'  =>'Carregue na cruz para apagar o ramo.
Carregue no sinal verde para actualizar',
'branch_start' =>'Seleccione o directório para mostrar no lado esquerdo',
'branch_help1' =>'Seleccione os documentos para actualização',
'branch_help2' =>'Carregue na cruz para apagar um documento.
Carregue no sinal verde para re-indexar
A seta inicia o spidering',
'redepth'      =>'Níveis de Profundidade',
'branch_warn'  =>"A eliminação é permanente",
'to_admin'     =>"ir para a página de administração",

'search'       =>'Procurar',
'results'      =>'Resultados',
'display'      =>'Mostrar',
'w_begin'      =>'Do inicio',
'w_whole'      =>'palavras exactas',
'w_part'       =>'em qualquer lugar',
'alt_try'      =>'Did you mean',

'limit_to'     =>'limitar a',
'this_path'    =>'este caminho',
'total'        =>'total',
'seconds'      =>'segundos',
'w_common_sing'     =>'as palavras comuns foram omitidas.',
'w_short_sing'      =>'as palavras curtas foram omitidas.',
'w_common_plur'     =>'as palavras comuns foram omitidas.',
'w_short_plur'      =>'as palavras curtas foram omitidas.',
's_results'    =>'resultados da busca',
'previous'     =>'Anterior',
'next'         =>'Seguinte',
'on'           =>'em',

'id_start'     =>'Indexação do site',
'id_end'       =>'Indexação completa !',
'id_recent'    =>'Foi indexado recentemente',
'num_words'    =>'Número de palavras',
'time'         =>'tempo',
'error'        =>'Erro',
'no_spider'    =>'Spider não iniciado',
'no_site'      =>'Não se encontrou o sítio na base de dados',
'no_temp'      =>'Não existe o link na tabela temporal',
'no_toindex'   =>'Nada para indexar',
'double'       =>'Duplicado de um documento existente',

'spidering'    =>'Spidering em progresso',
'links_more'   =>'mais links novos',
'level'        =>'nível',
'links_found'  =>'links encontrados',
'define_ex'    =>'Definir exclusões',
'index_all'    =>'indexar tudo',

'end'          =>'fim',
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