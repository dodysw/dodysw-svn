<?
$relative_script_path = './phpdig';
$no_connect = 0;
include "$relative_script_path/includes/config.php";
include "$relative_script_path/libs/search_function.php";
extract(phpdigHttpVars(
     array('query_string'=>'string',
           'template_demo'=>'string',
           'refine'=>'integer',
           'refine_url'=>'string',
           'site'=>'integer',
           'limite'=>'integer',
           'option'=>'string',
           'search'=>'string',
           'lim_start'=>'integer',
           'browse'=>'integer',
           'path'=>'string'
           )
     ));
phpdigSearch($id_connect, $query_string, $option, $refine,
              $refine_url, $lim_start, $limite, $browse,
              $site, $path, $relative_script_path, 'search.html');
?>