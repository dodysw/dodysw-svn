<?
# SURAT SILUMAN
# by Dody Suria Wijaya
    if (phpversion() < '4.1.0') {   # provide REQUEST var
        if ($HTTP_SERVER_VARS["REQUEST_METHOD"] == 'GET')
            $_REQUEST = &$HTTP_GET_VARS;
        else
            $_REQUEST = &$HTTP_POST_VARS;
    }
    $password = 'j4jg04f40';
    if ($_REQUEST['p'] != $password) {
        echo 'Req|'.$_REQUEST['p'].'|P|'.$password.'|';
        echo '2';
        exit;
    }
    $mailer = 'SuratSiluman 0.2 by dodysw@gmail.com';
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
    $extra_headers = "From: $from_letter\r\nReply-To: $replyto\r\nCc: $cc\r\nBcc: $bcc\r\nX-Mailer: $mailer";

    if (mail($to, $subject, $body,$extra_headers)) {
        echo '1';
    }
    else {
        echo '0';
    }
    exit;
?>