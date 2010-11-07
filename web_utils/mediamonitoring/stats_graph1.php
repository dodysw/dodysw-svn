<?
    include 'admin/config.inc.php';
    include 'admin/include/func.inc.php';
    if (!extension_loaded('gd')) {
        dl('gd.so');
    }

    include (APP_JPGRAPH_ROOT.'/jpgraph.php');
    include (APP_JPGRAPH_ROOT.'/jpgraph_bar.php');


    // Some data
    #~ $datay=array(3,1,7,5,12,11,9,4,17);
    #~ echo '<br>--'.$_REQUEST['y_val'];
    $datay = unserialize(mystripslashes($_REQUEST['y_val']));
    #~ echo $datay;
    #~ exit();

    // Create the graph and setup the basic parameters
    $graph = new Graph(600,360,'auto');
    $graph->img->SetMargin(40,30,40,100);
    $graph->SetScale("textint");
    $graph->SetFrame(true,'blue',1);
    $graph->SetColor('lightblue');
    $graph->SetMarginColor('lightblue');

    // Add some grace to the top so that the scale doesn't
    // end exactly at the max value.
    $graph->yaxis->scale->SetGrace(20);

    // Setup X-axis labels
    #~ $a = $gDateLocale->GetShortMonth();
    $graph->xaxis->SetTickLabels(unserialize(mystripslashes($_REQUEST['x_label'])));
    #~ $graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,10);
    $graph->title->SetFont(FF_FONT1,FS_BOLD);
    $graph->xaxis->SetColor('darkblue','black');
    $graph->xaxis->SetLabelAngle(90);

    // Stup "hidden" y-axis by given it the same color
    // as the background
    $graph->yaxis->SetColor('lightblue','darkblue');
    $graph->ygrid->SetColor('white');

    // Setup graph title ands fonts
    #~ $graph->title->Set('Example of integer Y-scale');
    #~ $graph->subtitle->Set('(With "hidden" y-axis)');

    $graph->title->SetFont(FF_FONT2,FS_BOLD);
    #~ $graph->xaxis->title->Set("Year 2002");
    $graph->xaxis->title->SetFont(FF_FONT2,FS_BOLD);

    // Create a bar pot
    $bplot = new BarPlot($datay);
    $bplot->SetFillColor('darkblue');
    $bplot->SetColor('darkblue');
    $bplot->SetWidth(0.5);
    $bplot->SetShadow('darkgray');

    // Setup the values that are displayed on top of each bar
    $bplot->value->Show();
    // Must use TTF fonts if we want text at an arbitrary angle
    #~ $bplot->value->SetFont(FF_ARIAL,FS_NORMAL,8);
    $bplot->value->SetFormat('%d');
    // Black color for positive values and darkred for negative values
    $bplot->value->SetColor("black","darkred");
    $graph->Add($bplot);

    // Finally stroke the graph
    $graph->Stroke();
?>