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

//Norske ord for PhpDig
//Oversatt av Martin Kristiansen - Nettmedia (martin@nettmedia.no)
//'keyword' => 'translation'
$phpdig_mess = array (
'upd_sites'    =>'Update sites',
'upd2'         =>'Update Done',
'links_per'    =>'Links per',
'yes'          =>'ja',
'no'           =>'nei',
'delete'       =>'Slett',
'reindex'      =>'Oppdater indeks',
'back'         =>'Tilbake',
'files'        =>'filer',
'admin'        =>'Administrasjon',
'warning'      =>'Advarsel!',
'index_uri'    =>'Hvilken nettadresse vil du indeksere?',
'spider_depth' =>'Søkedybde',
'spider_warn'  =>"Forsikre deg om at ingen andre prøver å oppdatere det samme nettstedet nŒ.",
'site_update'  =>"Oppdatere et nettsted eller en katalog i nettstedet",
'clean'        =>'Rydd',
't_index'      =>"Indeks",
't_dic'        =>'Ordbok',
't_stopw'      =>'Vanlige ord',
't_dash'       =>'dashes',

'update'       =>'Oppdater',
'exclude'      =>'Sletter og ekskluderer kataloger',
'excludes'     =>'Analyserer filstier',
'tree_found'   =>'Grunntre',
'update_mess'  =>'Reindekser eller slett et tre ',
'update_warn'  =>'Ekskludering og sletting fører til permanente endringer i indeksen',
'update_help'  =>'Klikk i krysset for å slette en katalog. Klikk på det grønne merket for å oppdatere den. Klikk på «Stoppskiltet» for å ekskludere den for all fremtidig indeksering',
'branch_start' =>'Bruk menyen i venstre side for å merke katalogen du vil undersøke. vise Velg katalogen som skal vises på venstre side',
'branch_help1' =>'Velg der dokumenter som skal oppdateres individiuelt',
'branch_help2' =>'Klikk i krysset for å slette et dokument. Klikk på det grønne merket for å oppdatere dokumentets indeks',
'redepth'      =>'nivådybde',
'branch_warn'  =>"Endringene er permanente",
'to_admin'     =>"Til kontrollpanelet",
'to_update'    =>"Til indeksen",

'search'       =>'Søk',
'results'      =>'funn per side',
'display'      =>'vis',
'w_begin'      =>'ord starter med',
'w_whole'      =>'eksakt uttrykk',
'w_part'       =>'deler av et ord',
'alt_try'      =>'Did you mean',

'limit_to'     =>'begrens til',
'this_path'    =>'denne filstien',
'total'        =>'totalt',
'seconds'      =>'sekunder',
'w_common_sing'     =>'er veldig vanlige ord og blir ignorert.',
'w_short_sing'      =>'er for korte ord og blir ignorert.',
'w_common_plur'     =>'er veldig vanlige ord og blir ignorert.',
'w_short_plur'      =>'er for korte ord og blir ignorert.',
's_results'    =>'Resultat av søket',
'previous'     =>'Forrige',
'next'         =>'Neste',
'on'           =>'på',

'id_start'     =>'Indekserer nettsted',
'id_end'       =>'Indekseringen er ferdig!',
'id_recent'    =>'Er nettopp indeksert',
'num_words'    =>'Antall ord',
'time'         =>'tid',
'error'        =>'Feil',
'no_spider'    =>'Søkemotoren er ikke sparket i gang',
'no_site'      =>'Finner ikke dette nettstedet i databasen',
'no_temp'      =>'Ingen lenke i mellomlagret',
'no_toindex'   =>'Innholdet ble ikke indeksert',
'double'       =>'Dokumentet er funnet flere ganger',

'spidering'    =>'Indekseringen er i gang...',
'links_more'   =>'flere nye lenker',
'level'        =>'nivå',
'links_found'  =>'lenker funnet',
'define_ex'    =>'Definer utestenginger',
'index_all'    =>'indekser alt',

'end'          =>'slutt',
'no_query'     =>'Vennligst fyll ut s¿keskjemaet',
'pwait'        =>'Vennligst vent',
'statistics'   =>'Statistikk',

// INSTALL
'slogan'   =>'Universets minste søkemotor, versjon',
'installation'   =>'Innstallasjon',
'instructions' =>'Skriv inn MySql-oppsettet. Velg en eksisterende bruker, som har tillatelse til å opprette databaser, dersom du velger Opprett eller Oppdater.',
'hostname'   =>'Vertsnavn:',
'port'   =>'Port (ingenting = default):',
'sock'   =>'Sock (ingenting = default):',
'user'   =>'Bruker:',
'password'   =>'Passord:',
'phpdigdatabase'   =>'PhpDig database:',
'tablesprefix'   =>'Prefiks for databasetabeller:',
'instructions2'   =>'* valgfritt. Bruk små bokstaver. Ikke mer enn 16 tegn.',
'installdatabase'   =>'Installer phpdig database',
'error1'   =>'Finner ikke malen (template) for tilkobling. ',
'error2'   =>'Klarer ikke å skrive til connexion template. ',
'error3'   =>'Finner ikke filen init_db.sql. ',
'error4'   =>'Klarer ikke å opprette tabeller. ',
'error5'   =>'Finner ikke konfigurasjonsfilene til databasen. ',
'error6'   =>'Klarer ikke å opprette databasen.<br />Vennligst kontroller brukerens rettigheter. ',
'error7'   =>'Klarer ikke å koble til databasen.<br />Vennligst kontroller mySql-oppsettet. ',
'createdb' =>'Opprett database',
'createtables' =>'Opprett kun databasens tabeller',
'updatedb' =>'Oppdater en eksisterende database',
'existingdb' =>'Kun lagre tilkoblingsdataene',
// CLEANUP_ENGINE
'cleaningindex'   =>'Rydder opp i indeksen',
'enginenotok'   =>' Fant et nøkkelord som ikke passet i referanseindeksen.',
'engineok'   =>'Søkemotoren er nå oppdatert.',
// CLEANUP_KEYWORDS
'cleaningdictionnary'   =>'Rydder opp i ordboka',
'keywordsok'   =>'Alle nøkkelordene finnes i en eller flere sider.',
'keywordsnotok'   =>' av nøkkelordene mangler i minst en side.',
// CLEANUP_COMMON
'cleanupcommon' =>'Rydd opp i vanlig ord',
'cleanuptotal' =>'Totalt ',
'cleaned' =>' ryddet.',
'deletedfor' =>' slettet for ',
// INDEX ADMIN
'digthis' =>'Søk',
'databasestatus' =>'Status for database',
'entries' =>' Oppføringer ',
'updateform' =>'Oppdater skjema',
'deletesite' =>'Slett nettsted',
// SPIDER
'spiderresults' =>'Resultat av indekseringen',
// STATISTICS
'mostkeywords' =>'Vanligste nøkkelordene',
'richestpages' =>'Fyldigste sidene',
'mostterms'    =>'Vanligste søkeordene',
'largestresults'=>'Søkeord som finnes på flest sider',
'mostempty'     =>'Søkeord som finnes på færrest sider',
'lastqueries'   =>'De siste søkene',
'responsebyhour'=>'Response time by hour',
// UPDATE
'userpasschanged' =>'Brukernavn/Passord endret!',
'uri' =>'URI: ',
'change' =>'Endre',
'root' =>'Rot',
'pages' =>' sider',
'locked' => 'Låst',
'unlock' => 'Lås opp indeks',
'onelock' => 'Et nettsted er låst fordi det indekseres nå. Du kan derfor ikke gjøre dette nå',
// PHPDIG_FORM
'go' =>'Start ...',
// SEARCH_FUNCTION
'noresults' =>'Fant ikke noe som passet for søket.'
);
?>
