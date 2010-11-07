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

//'keyword' => 'translation'
$phpdig_mess = array (
'upd_sites'    =>'Update sites',
'upd2'         =>'Update Done',
'links_per'    =>'Links per',
'yes'          =>'oui',
'no'           =>'non',
'delete'       =>'supprimer',
'reindex'      =>'réindexer',
'back'         =>'Retour',
'files'        =>'fichiers',
'admin'        =>'Administration',
'warning'      =>'Attention !',
'index_uri'    =>'Quelle URI voulez-vous indexer ?',
'spider_depth' =>'Profondeur de recherche',
'spider_warn'  =>"Assurez-vous que personne ne soit en train d'indexer
des pages du même site avant de lancer votre propre indexation.
Un mécanisme de lock sera inclu dans une version ultérieure",
'site_update'  =>"Mise à jour d'un site (ou d'une de ses branches)",
'clean'        =>'Nettoyer',
't_index'      =>"l'index",
't_dic'        =>'le dictionnaire',
't_stopw'      =>'les mots courants',
't_dash'       =>'dashes',

'update'       =>'Mise à jour',
'exclude'      =>'Effacer et exclure la branche',
'excludes'     =>'Chemins exclus',
'tree_found'   =>'Arborescence trouvée',
'update_mess'  =>'Réindexer ou supprimer une arborescence ',
'update_warn'  =>"L'exclusion efface les indexations",
'update_help'  =>'Cliquez sur la croix pour exclure une branche
Cliquez sur le plus pour mettre à jour la branche
Cliquez sur le sens interdit pour effacer et exclure une branche des indexations futures',
'branch_start' =>'Sélectionnez le répertoire à afficher sur le volet de gauche',
'branch_help1' =>'Vous pouvez sélectionner ici individuellement
les index des pages à mettre à jour',
'branch_help2' =>'Cliquez sur la croix supprimer la page
Cliquez sur le plus pour une réindexation',
'redepth'      =>'niveaux',
'branch_warn'  =>"L'effacement est définitif",
'to_admin'     =>"à l'interface d'administration",
'to_update'    =>"à l'interface de mise à jour",

'search'       =>'Rechercher',
'results'      =>'résultats',
'display'      =>'afficher',
'w_begin'      =>'Début de mot',
'w_whole'      =>'Mot entier',
'w_part'       =>'Partie de mot',
'alt_try'      =>'Did you mean',

'limit_to'     =>'limiter à',
'this_path'    =>'ce chemin',
'total'        =>'au total',
'seconds'      =>'secondes',
'w_common_sing'     =>'sont des mots courants et ont été ignorés.',
'w_short_sing'      =>'sont des mots trop courts et ont été ignorés.',
'w_common_plur'     =>'sont des mots courants et ont été ignorés.',
'w_short_plur'      =>'sont des mots trop courts et ont été ignorés.',
's_results'    =>'Résultats de la recherche',
'previous'     =>'Précédents',
'next'         =>'Suivants',
'on'           =>'pour',

'id_start'     =>'Indexation du site',
'id_end'       =>'Indexation terminée !',
'id_recent'    =>'A été indexé récemment',
'num_words'    =>'Nombre de mots',
'time'         =>'temps',
'error'        =>'Erreur',
'no_spider'    =>'Spider non lancé',
'no_site'      =>"Ce site n'existe pas dans la base de données",
'no_temp'      =>'Pas de liens dans la table temporaire',
'no_toindex'   =>'Rien à indexer',
'double'       =>'Doublon avec un document existant',

'spidering'    =>'Exploration des liens en cours...',
'links_more'   =>'liens en plus',
'level'        =>'niveau',
'links_found'  =>'liens trouvés',
'define_ex'    =>'Définir des exclusions',
'index_all'    =>'Tout indexer',

'end'          =>'fin',
'no_query'     =>'Veuillez renseigner le formulaire de recherche',
'pwait'        =>'Veuillez patienter',
'statistics'   =>'Statistiques',

// INSTALL
'slogan'   =>'Le plus petit moteur de recherche de l\'univers : version',
'installation'   =>'Installation',
'instructions' =>'Veuillez entrer vos paramètres MYSQL. '
                 .'Spécifiez un utilisateur autorisé à créer une base de données '
                 .'si vous choisissez de créer ou de mettre à jour la base.',
'hostname'   =>'Hôte :',
'port'   =>'Port (none = default) :',
'sock'   =>'Sock (none = default) :',
'user'   =>'Utilisateur :',
'password'   =>'Mot de passe :',
'phpdigdatabase'   =>'Base de PhpDig :',
'tablesprefix'   =>'Préfixe des tables :',
'instructions2'   =>'* facultatif. Utilisez des minuscules, 16 caractères max.',
'installdatabase'   =>'Installer la base de phpdig',
'error1'   =>'Can\'t find connexion template. ',
'error2'   =>'Can\'t write connexion template. ',
'error3'   =>'Impossible de trouver le fichier init_db.sql. ',
'error4'   =>'Impossible de créer les tables. ',
'error5'   =>'Impossible de trouver tous les fichiers de configuration de la base. ',
'error6'   =>'Impossible de créer la base de données.<br />Veuillez vérifier que vous disposez des droits suffisants. ',
'error7'   =>'Impossible de se connecter à la base de données.<br />Veuillez vérifier les informations de connexion à la base. ',
'createdb' =>'Créer la base',
'createtables' =>'Créer les tables uniquement',
'updatedb' =>'Mettre à jour la base',
'existingdb' =>'Paramètres de connexion uniquement',
// CLEANUP_ENGINE
'cleaningindex'   =>'Nettoyage de l\'index',
'enginenotok'   =>' référence(s) dans l\'index ne correspondai(en)t plus à un mot clé existant.',
'engineok'   =>'Le moteur est cohérent.',
// CLEANUP_KEYWORDS
'cleaningdictionnary'   =>'Nettoyage du dictionnaire',
'keywordsok'   =>'Tous les mots clés sont présents dans au moins une page.',
'keywordsnotok'   =>' mot(s) clé(s) n\'étai(en)t plus présent(s) dans aucune page.',
// CLEANUP_COMMON
'cleanupcommon' =>'Nettoyage des mots courants',
'cleanuptotal' =>'Un total de ',
'cleaned' =>' mot(s) courant(s) a (ont) été effacé.',
'deletedfor' =>' suppression(s) pour ',
// INDEX ADMIN
'digthis' =>'Indexer !',
'databasestatus' =>'Stats de la base de données PhpDig',
'entries' =>' enregistrements ',
'updateform' =>'Mettre à jour',
'deletesite' =>'Effacer le site',
// SPIDER
'spiderresults' =>'Les résultats de l\'indexation',
// STATISTICS
'mostkeywords' =>'Mots clés les plus nombreux',
'richestpages' =>'Pages avec le plus de mots clés',
'mostterms'    =>'Termes les plus recherchés',
'largestresults'=>'Les plus grands résultats',
'mostempty'     =>'Les recherches infructueuses les plus courantes',
'lastqueries'   =>'Les dernières recherches',
'responsebyhour'=>'Temps de réponse horaire',
// UPDATE
'userpasschanged' =>'Nom d\'utilisateur / mot de passe modifié(s) !',
'uri' =>'URI : ',
'change' =>'Modifier',
'root' =>'Racine',
'pages' =>' pages',
'locked' => 'Verrouillé',
'unlock' => 'Déverrouiller le site',
'onelock' => 'Un site est verrouillé car en cours d\'indexation.
vous ne pouvez effectuer cette action',
// PHPDIG_FORM
'go' =>'Chercher',
// SEARCH_FUNCTION
'noresults' =>'Aucun résultat'
);
?>