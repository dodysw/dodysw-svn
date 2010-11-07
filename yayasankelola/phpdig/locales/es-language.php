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

//Spanish messages for PhpDig
//by Geffrey Velásquez
//'keyword' => 'translation'
$phpdig_mess = array (
'upd_sites'    =>'Update sites',
'upd2'         =>'Update Done',
'links_per'    =>'Links per',
'yes'          =>'sí',
'no'           =>'no',
'delete'       =>'eliminar',
'reindex'      =>'Reindexar',
'back'         =>'Atrás',
'files'        =>'archivos',
'admin'        =>'Administración',
'warning'      =>'¡Advertencia!',
'index_uri'    =>'¿Qué URI desea indexar?',
'spider_depth' =>'Profundidad de búsqueda',
'spider_warn'  =>"Por favor, asegúrese de que nadie más esté actualizando este mismo sitio.
Un mecanismo de bloqueo será incluido en versiones posteriores",
'site_update'  =>"Actualizar un sitio o una de sus ramificaciones",
'clean'        =>'Limpiar',
't_index'      =>"índice",
't_dic'        =>'diccionario',
't_stopw'      =>'palabras comunes',
't_dash'       =>'dashes',

'update'       =>'Actualizar',
'tree_found'   =>'Árbol encontrado',
'update_mess'  =>'Reindexar o borrar un árbol ',
'update_warn'  =>"La exclusión es permanente",
'update_help'  =>'Haga clic en el aspa para borrar la ramificación
Haga clic en el botón verde para actualizar',
'branch_start' =>'Seleccione la carpeta para mostrarla en el lado izquierdo',
'branch_help1' =>'Seleccione los documentos para actualizarlos',
'branch_help2' =>'Haga clic en el aspa para eliminar un documento
Haga clic en el botón verde para reindexar
La flecha inicia un spidering',
'redepth'      =>'Grados de profundidad',
'branch_warn'  =>"La eliminación es permanente",
'to_admin'     =>"vaya a la página de administración",

'search'       =>'Búsqueda',
'results'      =>'resultados',
'display'      =>'Mostrar',
'w_begin'      =>'Al inicio',
'w_whole'      =>'Palabras exactas',
'w_part'       =>'En cualquier lugar',
'alt_try'      =>'Did you mean',

'limit_to'     =>'Limitar a',
'this_path'    =>'esta ruta',
'total'        =>'total',
'seconds'      =>'segundos',
'w_common_sing'     =>'las palabras comunes fueron obviadas.',
'w_short_sing'      =>'las palabras cortas fueron obviadas.',
'w_common_plur'     =>'las palabras comunes fueron obviadas.',
'w_short_plur'      =>'las palabras cortas fueron obviadas.',
's_results'    =>'resultados de la búsqueda',
'previous'     =>'Anterior',
'next'         =>'Siguiente',
'on'           =>'en',

'id_start'     =>'Indexación del sito',
'id_end'       =>'¡Indexación completa!',
'id_recent'    =>'Fue recientemente indexado',
'num_words'    =>'Número de palabras',
'time'         =>'tiempo',
'error'        =>'Error',
'no_spider'    =>'Spider no iniciado',
'no_site'      =>'No se encontró el sitio en la base de datos',
'no_temp'      =>'No existe el enlace en la tabla temporal',
'no_toindex'   =>'Nada para indexar',
'double'       =>'Duplicado de un documento existente',

'spidering'    =>'Spidering en progreso',
'links_more'   =>'más enlaces nuevos',
'level'        =>'nivel',
'links_found'  =>'enlaces encontrados',
'define_ex'    =>'Definir exclusiones',
'index_all'    =>'indexar todo',

'end'          =>'fin',
'no_query'     =>'Escriba en el recuadro la palabra o la frase que desea buscar',
'pwait'        =>'Por favor, espere',
'statistics'   =>'Estadísticas',

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
'go' =>'Ir...',
// SEARCH_FUNCTION
'noresults' =>'No hay resultados'
);
?>