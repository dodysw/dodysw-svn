<?php
if (!defined('PHPDIG_ENCODING')) {
    die("Cannot display htmlmetas.php file.\n");
}
$phpdig_language_array = array('cs', 'da', 'de', 'en', 'es', 'fr', 'gr', 'it', 'nl', 'no', 'pt');
if (!in_array($phpdig_language,$phpdig_language_array)) {
    $phpdig_language = "en";
}
if (is_file("$relative_script_path/includes/style.css")) {
    $my_css_link = "$relative_script_path/includes/style.css";
}
else {
    die("Cannot find style.css file.\n");
}
?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php print PHPDIG_ENCODING; ?>" />
<meta http-equiv="Content-Language" content="<?php print $phpdig_language; ?>">
<link href="<?php print $my_css_link ?>" rel="stylesheet" type="text/css" />