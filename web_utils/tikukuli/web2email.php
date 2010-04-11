<?

function mystripslashes($val) {
    return get_magic_quotes_gpc()? stripslashes($val) : $val;
}


if ($_REQUEST['url'] == '') {
    echo '<html><head><title>Download to email</title></head><body>';
    echo '<h3>Tikikuli PHP-Web Interface</h3>';
    echo '<p>Simple form to use tikikuli via web. Warning! do not let non-authorized user to access this page, since no checking is done to avoid tikikuli from sending arbitrary files in your local shell account.';
    echo '<form method="POST">';
    echo '<p>Email: <input type=text name="email" value="'.$_COOKIE['w2e_email'].'" size="30">';
    echo '<p>URL: (separate with newline)<br><textarea name="url" rows="10" cols="80"></textarea>';
    echo '<br>Send notification: <input type=checkbox name="notify" value="1">';
    echo '<br>Send compressed: <input type=checkbox name="compressed" value="1"> pack in tar.bz2 (for smaller email)';
    echo '<br>Fake extension: <input type=checkbox name="fake_ext" value="1" checked> fake extension to .jpg (some mail anti-spam reject compressed files)';
    echo '<p><input type=submit>';
    echo '</form>';

    $result = `python tikikuli.py -v 2>&1`;
    echo "<p><i>Using tikikuli $result";
    echo '&copy; 2005 - Dody Suria Wijaya';

    echo '</body></html>';
    exit();
}
else {
    if ($_REQUEST['email'] != '')
        setcookie('w2e_email',$_REQUEST['email'], time()+31000000);
}
$urls = array();
foreach (explode("\n",mystripslashes($_REQUEST['url'])) as $url) {
    $url = trim($url);
    if ($url == '') continue;
    //remove invalid characters in URL
    $url = str_replace('"', '',$url);
    #~ $url = str_replace('\'', '',$url);
    $url = str_replace(' ', '',$url);
    $urls[] = '"'.$url.'"';
}
$urls_string = join(' ',$urls);
$email = $_REQUEST['email'];
$parameters = array();
$parameters[] = escapeshellarg($email);
if ($_REQUEST['compressed']) $parameters[] = '-c';
if ($_REQUEST['notify']) $parameters[] = '-n';
if ($_REQUEST['fake_ext']) $parameters[] = '-k';
$parameters[] = $urls_string;
$path = 'python tikikuli.py '.join($parameters, ' ');

$result = `$path 2>&1`;

echo '<p>I have submitted '.count($urls).' URL(s) to Python downloader. Please DO NOT RELOAD, press F5, or re-submit this page. Wait and monitor your mail inbox at '.$email.'. Thank you.';
echo '<p>Command:<br><pre>'.$path.'</pre>';
echo '<p>Result:<br><pre>'.$result.'</pre>';


?>
