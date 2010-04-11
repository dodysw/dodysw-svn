<?
//CSS file is considered static
    #~ httpcache_by_lastupdate();
echo <<<__E__
body{margin:0;padding:0;font-family:"Bitstream Vera Sans",Verdana,Arial,sans-serif;color:#333;}
a:hover{background:#cec;}
#header {background:#ded;margin:0;padding:0;}
#header h1 {margin:0;padding:0;}
#header p {margin:0;padding:0;}
#header .date {color:#666;font-size:90%;float:right;font-weight:bold;margin-right:0.5em;}
#header h1 a {text-decoration:none;}
#header .detik {color:#911;}
#header .usable {color:#191;}
#header .usable:after { content: " {$app['version']}"; font-weight:bold;color:#aaa;}

#nav {}
#nav ul {padding: 0;margin: 0 0 0 1em;}
#nav li {display: inline;list-style: none;}
#nav li:first-child:before { content: ""; }
#nav li:before { content: "| "; }

#main{float:left;width:50%;border-right:solid 1px #aaa;}
#content-headlines {margin:0.5em;}
#content-headlines .block {padding-bottom:0.2em;}
#content-headlines h3 {margin:0;padding:0;font-size:110%;display:inline;}
#content-headlines h3 a {text-decoration:none;}
#content-headlines h3:after { content: " = ";}
#content-headlines p {margin:0;padding:0;display:inline;}
//#content-headlines .date {color:#333;font-size:80%;float:left;margin-right:0.5em;margin-top:0.5 em;background:#ffc;}
//#content-headlines .date {color:#333;font-size:80%;float:right;margin-right:0.5em;margin-top:0.5 em;background:#ffc;}
//#content-headlines .date {color:#333;font-size:80%;background:#ff0;}
#content-headlines .date {margin-left:0.5em;color:#777;font-size:80%;}

#content-oldernews {margin:0 0.4em;float:left;}
#content-oldernews h3 {margin:0;padding:0;font-size:95%;display:inline;}
#content-oldernews h3 a {text-decoration:none;}
#content-oldernews p {margin:0;padding:0;display:inline;}
//#content-oldernews .date {margin:0;padding:0;font-size:80%;float:left;margin-right:0.5em;background:#ffc;}
//#content-oldernews .date {margin:0;padding:0;font-size:80%;float:right;margin-right:0.5em;background:#ffc;}
#content-oldernews .date {margin-left:0.5em;color:#777;font-size:80%;}

//#secondary {width:48%; float:right;margin-right:1%;}
#secondary {width:48%; float:right;margin-right:0.5em;}

#content-channels {}
#content-channels h2 {margin:0;padding:0;font-size:95%;color:#999;}
#content-channels ul {padding: 0;margin: 0 0 0 0em;}
#content-channels li {list-style: none;}
#content-channels .block {margin-top:0.5em;}
//#content-channels li:first-child:before { content: ""; }
#content-channels li:after { content: ".."; font-weight:bold;}
#content-channels a {text-decoration:none;}

#ads {margin-top:1em; border:solid 1px #aab;background:#eef;padding:0.5em;}
#ads a {text-decoration:none;}
#ads h2 {margin:0 0 0.2em 0;padding:0;font-size:95%;color:#999;text-align:center;float:right;}
#ads ul {padding: 0;margin: 0 0 0 0em;}
#ads li {display: inline;list-style: none;}
#ads li:first-child:before { content: ""; }
//#ads li:before { content: " ... "; font-weight:bold;}

#content-detail {margin:1em;}
#content-detail h2 {margin:0;padding:0;font-size:120%;}
#content-detail .date {margin:0;color:#777;font-size:80%;}
#content-detail .body {}
#content-detail ul {padding: 0;margin: 0 0 0 0em;}
#content-detail li {display: inline;list-style: none;}
#content-detail li:first-child:before { content: ""; }
#content-detail li:before { content: "| "; }

#footer {clear:both;}
#footer p {padding:0.5em;margin-top:2em;padding:0;background:#ded;}

#info {border:solid 1px #aca;background:#efe;padding:0.5em;width:50%;margin:1em;}
#info h1 {margin:0 0 0.2em 0;padding:0;font-size:110%;color:#363;text-align:center;}

__E__;

?>