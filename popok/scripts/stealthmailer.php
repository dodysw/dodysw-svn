<?
# SURAT SILUMAN
# by Dody Suria Wijaya

# configuration
$password = 'mystealthmailerpassword';    # synch this with popok.ini password line
$mailer = 'SuratSiluman/0.7/2004/dodysw@gmail.com';

if (phpversion() < '4.1.0') {   # provide REQUEST var
    if ($HTTP_SERVER_VARS["REQUEST_METHOD"] == 'GET')
        $_REQUEST = &$HTTP_GET_VARS;
    else
        $_REQUEST = &$HTTP_POST_VARS;
}

if(get_magic_quotes_gpc()) {    # handle magic quote problem (default is on which escapes everything)
    $_REQUEST = array_map("stripslashes", $_REQUEST);
}

if ($_REQUEST['p'] != $password) {  # check password
    echo '2';
    exit;
}

$to = $_REQUEST['to'];
$subject = $_REQUEST['subject'];
# there are 2 kind of from, envelope from and letter from.
$from = $_REQUEST['from'];
$from_letter = $_REQUEST['froml'];
if ($from_letter == '') $from_letter = $from;
$cc = $_REQUEST['cc'];
$bcc = $_REQUEST['bcc'];
$body = $_REQUEST['body'];
$replyto = $_REQUEST['replyto'];
$usemyheader = $_REQUEST['usemyheader'];
$header = $_REQUEST['header']; # additional headers
$extra_headers = "From: $from_letter\r\nReply-To: $replyto\r\nCc: $cc\r\nBcc: $bcc\r\nX-Mailer: $mailer";
# if usemyheader, then force to use header param
# if not use my header, and
if ($usemyheader == '1') {    # if usemyheader, then use header to replace default header
    # do nothing. use header.
    $header .= "\r\nX-Mailer: $mailer";
}
elseif ($header != '') {
    $header .= "\r\n".$extra_headers;
}
else {
    $header = $extra_headers;
}

#~ echo "\r\n--------Header------------\r\n".$header;
#~ echo "\r\n--------Body--------\r\n".$body;
#~ echo "\r\n--------END--------\r\n";
# Note: special for usemyheader
# if To address specified in both $to param and header, Qmail by default copy the evelope address to header letter too,
# and even they are both the same, the email will get duplicated
# the solution is to strip To line in header, and blindly put the value into $to
$header = preg_replace('/^To:.*\r\n/m','',$header);
# this also happen for subject
$header = preg_replace('/^Subject:.*\r\n/m','',$header);

# Some MTA confuse \r\n and translate it to \n\n, causing double lines. This will makes them happy:
$header = str_replace("\r\n", "\n", $header);
$body = str_replace("\r\n", "\n", $body);


if (mail($to, $subject,$body,$header)) {
    echo '1';
}
else {
    echo '0';
}
exit;
?>