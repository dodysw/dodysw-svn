<?
// Copyright 2004 - Dody Suria Wijaya <dodysw@gmail.com>
# ------- build info ----------- #
$__build['name'] = 'PHP/MySQL Application Framework';
$__build['description'] = 'One file simple framework (under 500 lines) to deploy, check, manage, and run php/mysql code.';
$__build['version'] = '1.0';
$__build['author'] = 'Dody Suria Wijaya <dodysw@gmail.com>';
$__build['license'] = 'GPL';
$__build['copyright'] = 'Copyright 2004 - <a href="http://dsw.gesit.com">dsw software house</a>. Code owned by dsw software house, but you may use it for non-commercial and commercial purpose.';


$__config['password'] = '1233';
$__config['default_module'] = 'mainmenu';

# ------- internal variables ----------- #
$__css = '<style>body{background:#fff;} dl,p,ul,li,blockquote,address,dt,dd,th,td,div{font-family:verdana,tahoma,arial,sans-serif;color:#000;} p,address,dt,dd,dl{margin-left:1em;} h1,h2,h3,h4,h5,h6{font-family:arial,sans-serif;color:#005a9c} small{font-family:Tahoma,arial;} h3{margin-left:1em;} h4{margin-left:2em;} h5{margin-left:3em;} th{font-size:80%;font-weight:bold;} td{font-size:75%;}</style>';
$__phpself = $_SERVER['PHP_SELF'];
$__phpself_abs = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
__modulehandler__();

#~ if(!file_exists("config.inc.php")) exit("<html><head><title>Error</title></head>$__css<body> <p>Please <a href='$__phpself?__build__=setup'>run setup</a> first</p> <hr><small>{$__build['copyright']}</small></body></html>");

#~ include 'config.inc.php';

/*
problem with 1-file php: state hirarchie
main
    - module A (admin)
        - page A1 (manage database)
            - sub-page A11 (manage table)
                - sub-subpage A111 (manage table A)
    - module B
        - page B1
            - sub-page B11
I need to keep all of those state variable both in URL and in form, and can be damn long the deeper the hirarchie.
Let's make an abstraction of this often occured nuisance.
index.php?m=managedb,tblprop,addcol&

Ie:
1. index.php?m=managedb     User pick Database Metro
2. index.php?m=managedb&dbname=Metro        which show Database Metro tables, use click table survey
3. index.php?m=managedb,tblprop&dbname=Metro&tblname=survey     user want to edit a column
4. index.php?m=managedb,tblprop,colprop&dbname=Metro&tblname=survey&colname=description&act=edit     user commit edit by post/submit
5. index.php? + Post Vars: m=managedb,tblprop,editcol&dbname=Metro&tblname=survey&colname=description&name=desc&datatype=varchar(200)

How about this:
- index.php?m=2,12,4&p0=dbname=Metro&p1=tblname^survey,co
*/

# ------- functions definition ----------- #

function __modulehandler__() {  // module handler
    global $__config;
    // enable like index.php?m=mymodule&therestofmymoduleparameters
    $module = $_REQUEST['m'];
    $GLOBALS['module'] = $module;
    if ($module == '')
        $module = $__config['default_module'];
    if (!function_exists($module.'_module')) {
        die('Sorry, module '.$module.' has no handler');
    }
    call_user_func($module.'_module');
}

function mainmenu_module() {    //display our main menu
    global $__build;
    echo '<h1>'.$__build['name'].'</h1><i>'.$__build['description'].'</i>';
    echo '<hr>If you have not done so, please edit this file, and change the password from the default';
    echo '<ol>';
    echo '<li><a href="'.get_current_url().'?m=setupdb">setup database connection</a>';
    echo '<li><a href="'.get_current_url().'?m=managedb">manage database</a>';
    echo '<li><a href="'.get_current_url().'?m=setupmodule">setup module</a>';
    echo '<li><a href="'.get_current_url().'?m=build_info&type=copyright">show build information</a>';
    echo '<li><a href="'.get_current_url().'?m=build_info&type=phpinfo">show php information</a>';
    echo '</ol>';
    echo '<hr>';
}

function build_info_module($type='') {
    global $__build;
    if ($type == '' and $_REQUEST['type'] != '')
        $type = $_REQUEST['type'];
    switch ($type) {
        case '':
        case 'copyright':
            echo '<h1>'.$__build['name'].'</h1><i>'.$__build['description'].'</i>';
            echo '<p>Version: '.$__build['version'].'<br>Author: '.$__build['author'].'<br>License: '.$__build['license'];
            echo '<p><small>'.$__build['copyright'].'</small></p>';
            break;
        case 'phpinfo':
            phpinfo();
            break;
        default:
            die('Build command "'.$type.'" not known. Try "copyright"');
    }
}

function install_module() {
    global $__build;
    authenticate_user();
    //get all modules in current folder
    $files = array();
    $fh = opendir('.');
    while (($filename = readdir($fh)) != false) {
        if (($pos = strpos($filename,'module.php')) != false) {
            $filename = substr($filename,0,$pos-1);
            array_push($files, $filename);
        }
    }
    closedir($fh);
    echo '<h1>Pick module</h1><ul>';
    foreach ($files as $f) {
        echo "<li>$f = install";
    }
    echo '</ul>';
}

function managedb_module() {
    $v = load_var();
    echo '<p>Hostname='.$v['db_host'].', Username='.$v['db_username'].', Database='.$v['db_name'].' - <a href="'.get_current_url().'?m=setupdb">change connection</a>';
    echo '<h1>Manage Database</h1>';
    echo '<form action="'.get_current_url().'" method="post">'.make_state_var('form');
    echo '<h3>Execute SQL</h3><textarea name="sql" rows="5" cols="40">'.$_REQUEST['dbm_sql'].'</textarea>';
    echo '<br><input type=submit name="action" value="Go"></form>';
    // show tables
    echo '<h3>Tables</h3>';
    $conn = mysql_connect($v['db_host'], $v['db_username'], $v['db_password']);
    mysql_select_db($v['db_name'],$conn);
    $res = mysql_query('show tables',$conn);
    while ($row = mysql_fetch_row($res)) {
        #~ echo '<li>'.$row[0],' = <a href="'.get_current_url().'?'.make_state_var($state_vars).'&m2=property">property</a>, <a href="'.get_current_url().'?'.make_state_var($state_vars).'&m2=drop">drop</a>, <a href="'.get_current_url().'?'.make_state_var($state_vars).'&m2=purge">purge</a>';
        echo '<li>'.$row[0],' = '.htmlhref_state(array('sql_query'=>'property'),'property').','.htmlhref_state(array('sql_query'=>'property'),'property').','.htmlhref_state(array('sql_query'=>'property'),'property');
        #~ // show field names
        #~ echo '<ul>';
        #~ $res2 = mysql_query('show columns from '.$row[0],$conn);
        #~ while ($col = mysql_fetch_array($res2)) {
            #~ echo '<li>'.$col['Field'];
        #~ }
        #~ echo '</ul>';
    }
    echo '</ul>';
    echo '<hr><a href="'.get_current_url().'">main menu</a>';

}

function setupdb_module() {
    global $__build;
    authenticate_user();
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        switch ($_REQUEST['action']) {
            case 'Test':
                if (!($res = mysql_connect($_REQUEST['db_host'], $_REQUEST['db_username'], $_REQUEST['db_password']))) { //test connection
                    echo '<p>Connection failed.';
                }
                if (!mysql_select_db($_REQUEST['db_name'],$res)) {  //test database existance
                    echo '<p>Select Db failed. Either database does not exist or this user does not have USAGE grant to this database.';
                }
                else {
                    echo '<p>Connection and Select Db OK.';
                }
                break;
            case 'Load':
                $cg = load_var();
                $_REQUEST['db_host'] = $cg['db_host'];
                $_REQUEST['db_username'] = $cg['db_username'];
                $_REQUEST['db_password'] = $cg['db_password'];
                $_REQUEST['db_name'] = $cg['db_name'];
                break;
            case 'Save':
                $cg = array();
                $cg['db_host'] = $_REQUEST['db_host'];
                $cg['db_username'] = $_REQUEST['db_username'];
                $cg['db_password'] = $_REQUEST['db_password'];
                $cg['db_name'] = $_REQUEST['db_name'];
                save_var($cg);
                break;
        }
    }
    $state_vars['m'] = $_REQUEST['m'];
    echo '<h1>Setup Database</h1><p>Please provide all of fields:';
    echo '<form action="'.get_current_url().'" method="post">'.make_state_var($state_vars,'form');
    echo '<p>Hostname: <input type="text" name="db_host" value="'.$_REQUEST['db_host'].'">';
    echo '<p>Username: <input type="text" name="db_username" value="'.$_REQUEST['db_username'].'">';
    echo '<p>Password: <input type="password" name="db_password" value="'.$_REQUEST['db_password'].'">';
    echo '<p>Database: <input type="text" name="db_name" value="'.$_REQUEST['db_name'].'">';
    echo '<p><input type=submit name="action" value="Test"> <input type=submit name="action" value="Save"> <input type=submit name="action" value="Load"></form>';
    echo '<hr><a href="'.get_current_url().'">main menu</a>';
}

function authenticate_module() {
    global $__config;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if ($_REQUEST['password'] == $__config['password']) {
            $_SESSION['auth'] = $__config['password'];
            header('Location: '.$_REQUEST['go']);
            exit;
        }
        else {
            echo('<p>Invalid password!<br>');
            unset($_SESSION['auth']);
        }
    }
    if ($_SESSION['auth'] != $__config['password']) {
        $go = get_current_url('abs','uri'); // save current url
        if ($_REQUEST['go'] != '') $go = $_REQUEST['go'];
        $state_vars['m'] = $_REQUEST['m'];
        die('<form action="'.get_current_url().'" method="post">'.make_state_var($state_vars,'form').'<input type="hidden" name="go" value="'.$go.'"><input type=password name=password><input type=submit></form>');
    }
}
function authenticate_user() {  //authenticate user
    session_start();
    global $__config;
    if ($_SESSION['auth'] != $__config['password']) {

    }
}

function get_current_url($param1='abs',$param2='self') {
    $part1 = ''; $part2 = '';
    if ($param1 == 'abs')   $part1 = 'http://'.$_SERVER['HTTP_HOST'];
    switch ($param2) {
        case 'self':    # http://localhost/path/file/index.php
            return $part1.$_SERVER['PHP_SELF'];
        case 'host':    # http://localhost
            return $part1;
        case 'uri': # http://localhost/path/file/index.php?a=1&b=2
            return $part1.$_SERVER['REQUEST_URI'];
        case 'path': # http://localhost/path/file/
            return $part1.dirname($_SERVER['PHP_SELF']);
        default:
            trigger_error('invalid parameter');
    }
}

function make_state_var($type='url') {
    // this function create dictionary into (1) url param or (2) input type hidden (form)
    $lst = array();
    $vars = $GLOBALS[''];
    if ($type == 'url') {
        foreach ($vars as $key=>$value) {
            $lst[] = urlencode($key).'='.urlencode($value);
        }
        return implode('&',$lst);
    }
    elseif ($type == 'form') {
        foreach ($vars as $key=>$value) {
            $lst[] = '<input type="hidden" name="'.htmlentities($key).'" value="'.htmlentities($value).'">';
        }
        return implode('\n',$lst);
    }
}

function load_var($filename='config.generic.php') {
    if (!is_readable($filename))
        return array();
    $fh = fopen($filename,'rb');
    $buffer = fread($fh,filesize($filename));   //note buffer is in xxx to avoid being displayed
    fclose($fh);
    $buffer = substr($buffer,4,-4);
    return unserialize($buffer);
}

function save_var($vars, $filename='config.generic.php') {
    $oldvars = load_var($filename);
    $fh = fopen($filename,'wb');
    $buffer = array_merge($oldvars, $vars); //merge with new vars
    fwrite($fh,"<?/*".serialize($buffer)."*/?>");
    fclose($fh);
}

function htmlhref_state($params, $text) {
    //create <a href="http://localhost/path/to/index.php?m=mymodule&paramskey=paramsvalue>TEXT</a>
    $GLOBALS['module']
    return '<a href="'..'>'.$text.'</a>';
}

?>