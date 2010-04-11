<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');
require_once( LOG4PHP_DIR . '/LoggerManager.php' ); //log4php

class kecelakaan extends TableManager {
    var $db_table, $properties;

    function kecelakaan() {
        parent::TableManager(); # must call base class
		$this->_logger =& LoggerManager::getLogger('Kecelakaan');

        global $html_title;
        $this->title = 'Kecelakaan';
        $html_title = $this->title;
        $this->db_table = $GLOBALS['dbpre'].'kecelakaan_tab';

		$this->properties['kode_cabang'] = new Prop(array('label'=>'Cabang','length'=>5,'colname'=>'kode_cabang', 'required'=>True, 'is_key'=>True,'inputtype'=>'combobox','enumerate'=>'cabang'));
		#~ $this->properties['kode_jalan'] = new Prop(array('label'=>'Kode jalan','length'=>4,'colname'=>'kode_jalan', 'required'=>True, 'is_key'=>True,'inputtype'=>'combobox','enumerate'=>'jalan'));
		#~ $this->properties['kode_ruas'] = new Prop(array('label'=>'Kode Ruas','length'=>4,'colname'=>'kode_ruas', 'required'=>True, 'is_key'=>True));
		$this->properties['tahun_op'] = new Prop(array('label'=>'Tahun','colname'=>'tahun_op','required'=>True,'datatype'=>'int', 'is_key'=>True));
        $this->properties['bulan_op'] = new Prop(array('label'=>'Bulan','colname'=>'bulan_op','required'=>True, 'is_key'=>True,'length'=>20));
		//o$this->properties['sta_awal'] = new Prop(array('label'=>'STA Awal','colname'=>'sta_awal','length'=>17,'required'=>False,'is_key'=>true));
		#~ $this->properties['arah_ruas'] = new Prop(array('label'=>'Arah Ruas','colname'=>'arah_ruas','datatype'=>'int','required'=>False,'is_key'=>true));

        $this->properties['panjang_jalan'] = new Prop(array('label'=>'Panjang jalan (km)', 'colname'=>'panjang_jalan','datatype'=>'double','required'=>False,'is_key'=>false));

        $this->properties['volume_lhr_avg'] = new Prop(array('label'=>'Volume Lalu lintas harian rata-rata (kendaraan)','colname'=>'volume_lhr_avg','datatype'=>'double','required'=>False,'is_key'=>false));
        $this->properties['avg_perjalanan_kendaraan_harian'] = new Prop(array('label'=>'Rata-rata perjalanan kendaraan per hari (km)','colname'=>'avg_perjalanan_kendaraan_harian','datatype'=>'double','required'=>False,'is_key'=>false));
        $this->properties['jml_kendaraan_km_perjalanan'] = new Prop(array('label'=>'Jumlah kendaraan perjalanan km per hari','colname'=>'jml_kendaraan_km_perjalanan','datatype'=>'double','required'=>False,'is_key'=>false));

        $this->properties['jumlah_kecelakaan'] = new Prop(array('label'=>'','colname'=>'jumlah_kecelakaan','datatype'=>'int','required'=>False,'is_key'=>false));
        $this->properties['tingkat_kecelakaan'] = new Prop(array('label'=>'Tingkat Kecelakaan per 100 juta kendaraan km','colname'=>'tingkat_kecelakaan','datatype'=>'double','required'=>False,'is_key'=>false));

        $this->properties['jumkcl_tidakadakorban'] = new Prop(array('label'=>'Jumlah Kecelakaan Tidak ada Korban','colname'=>'jumkcl_tidakadakorban','datatype'=>'int','required'=>False,'is_key'=>false));
        $this->properties['jumkcl_lukaringan'] = new Prop(array('label'=>'Jumlah Kecelakaan Menyebabkan luka ringan','colname'=>'jumkcl_lukaringan','datatype'=>'int','required'=>False,'is_key'=>false));
        $this->properties['jumkcl_lukaberat'] = new Prop(array('label'=>'Jumlah Kecelakaan Menyebabkan luka berat','colname'=>'jumkcl_lukaberat','datatype'=>'int','required'=>False,'is_key'=>false));
        $this->properties['jumkcl_kematian'] = new Prop(array('label'=>'Jumlah Kecelakaan Menyebabkan kematian','colname'=>'jumkcl_kematian','datatype'=>'int','required'=>False,'is_key'=>false));

        $this->properties['jumkbn_lukaringan'] = new Prop(array('label'=>'Jumlah korban luka ringan','colname'=>'jumkbn_lukaringan','datatype'=>'int','required'=>False,'is_key'=>false));
        $this->properties['jumkbn_lukaberat'] = new Prop(array('label'=>'Jumlah korban luka berat','colname'=>'jumkbn_lukaberat','datatype'=>'int','required'=>False,'is_key'=>false));
        $this->properties['jumkbn_lukameninggal'] = new Prop(array('label'=>'Jumlah korban Meninggal Dunia','colname'=>'jumkbn_lukameninggal','datatype'=>'int','required'=>False,'is_key'=>false));

        $this->properties['tingkat_kcl_fatal'] = new Prop(array('label'=>'Tingkat Kecelakaan fatal per 100 juta kend km','colname'=>'tingkat_kcl_fatal','datatype'=>'double','required'=>False,'is_key'=>false));
        $this->properties['tingkat_fatalitas'] = new Prop(array('label'=>'Tingkat Fatalitas per 100 juta kend km','colname'=>'tingkat_fatalitas','datatype'=>'double','required'=>False,'is_key'=>false));

        $this->properties['jumkdr_terlibatkcl'] = new Prop(array('label'=>'Jumlah kendaraan terlibat kecelakaan','colname'=>'jumkdr_terlibatkcl','datatype'=>'int','required'=>False,'is_key'=>false));
        $this->properties['jumkdr_tidakrusak'] = new Prop(array('label'=>'Jumlah kendaraan tidak rusak','colname'=>'jumkdr_tidakrusak','datatype'=>'int','required'=>False,'is_key'=>false));
        $this->properties['jumkdr_rusakringan'] = new Prop(array('label'=>'Jumlah kendaraan rusak ringan','colname'=>'jumkdr_rusakringan','datatype'=>'int','required'=>False,'is_key'=>false));
        $this->properties['jumkdr_rusakberat'] = new Prop(array('label'=>'Jumlah kendaraan rusak berat','colname'=>'jumkdr_rusakberat','datatype'=>'int','required'=>False,'is_key'=>false));

        $this->properties['kcl_tunggal'] = new Prop(array('label'=>'Kecelakaan tunggal (satu kendaraan)','colname'=>'kcl_tunggal','datatype'=>'int','required'=>False,'is_key'=>false));
        $this->properties['kcl_ganda'] = new Prop(array('label'=>'Kecelakaan ganda (dua kendaraan)','colname'=>'kcl_ganda','datatype'=>'int','required'=>False,'is_key'=>false));
        $this->properties['kcl_beruntun'] = new Prop(array('label'=>'Kecelakaan beruntun (tiga kendaraan atau lebih)','colname'=>'kcl_beruntun','datatype'=>'int','required'=>False,'is_key'=>false));

		#~ $this->childds[]="kecelakaan_penyebab";

        $prog->must_authenticated = True;

        $this->browse_mode = 'form';


        $this->grid_command[] = array('ingen_csv','Generate CSV for input');
        $this->grid_command[] = array('enter_ingen_csv','Enter CSV for input');
        $this->grid_command[] = array('','___________');

    }

    function go() { // called inside main content
        $this->basic_handler();
    }

    function csv_separa ($str, $delim=';', $qual="\"")
    {
           // Largo de la línea
           $largo=strlen($str);
           // Dentro
           $dentro=false;
           // Palabra
           $palabra="";
           // Bucle
           for ( $i=0; $i<$largo; $i++)
           {
                   if ( $str[$i] == $delim && !$dentro )
                   {
                           $salida[] = $palabra;
                           $palabra="";
                   }
                   else if ( $str[$i] == $qual && ( $i<$largo && $str[$i+1] == $qual ) )
                   {
                           $palabra .= $qual;
                           $i++;
                   }
                   else if ( $str[$i] == $qual )
                   {
                           $dentro = !$dentro;
                   }
                   else
                   {
                           $palabra .= $str[$i];
                   }
           }
           // Devolvemos la matriz
           $salida[]=$palabra;
           return $salida;
    }

    function act_enter_ingen_csv($post) {
        if ($post) {
            # parse csv input
            $body = $_REQUEST['body'];
            echo 'You enter';
            echo '<pre>';
            echo $body;
            echo '</pre>';
            $rows = explode("\r\n",$body);

            if (count($rows) < 3) {
                $this->__message = '<p>row < 3';
                return;
            }

            # check first row for consistency
            $dedupes = array();
            $row = $this->csv_separa($rows[0],"\t");
            for ($i = 0; $i < count($row); $i++) {
                if ($i == count($row)-1 and $row[$i] == '') continue;
                if (array_key_exists($row[$i],$dedupes)) {
                    $this->__message = 'Colvar "'.$row[$i].'" defined more than one time in csv.';
                    return;
                }
                $dedupes[$row[$i]] = 1;
                if (!array_key_exists($row[$i], $this->properties)) {
                    $this->__message = 'Colvar "'.$row[$i].'" does not exist in program.';
                    return;
                }
            }
            $colvars = $row;

            # skip second row

            # parse third row and so on
            for ($i = 2; $i < count($rows); $i++) {
                $row = $this->csv_separa($rows[$i],"\t");
                for ($i2 = 0; $i2 < count($row); $i2++) {
                    #~ $this->ds->{$colvars[$i2]}[$i] = $row[$i2];
                    $_REQUEST['field'][$colvars[$i2]][$i-2] = $row[$i2];
                }
                $_REQUEST['rowid'][$i-2] = '';
            }
            $_REQUEST['num_row'] = count($rows)-3;
            # $this->db_count = count($rows)-2;
            # $this->_save = 1;
            $_REQUEST['save'] = '';
            $this->_save == '';
            $this->action = 'new';
            $this->import2ds();
            return;
        }
        echo $this->__message;
        echo '<p>Paste CSV input from Excel:';
        echo '<form method=post action="'.$_SERVER['PHP_SELF'].'">';
        echo '<input type=hidden name=m value="'.$this->module.'">';   # this module
        echo '<input type=hidden name=act value="enter_ingen_csv">';   # contains the action (edit/new)
        echo '<input type=hidden name=save value="1">';         # marker to indicate form submitation
        echo '<textarea rows=10 cols=80 name=body>'.htmlentities($_REQUEST['body']).'</textarea>';
        echo '<input type=hidden name=go value="'.$_REQUEST['go'].'">';         # url to go after successful submitation
        echo '<p><input type=submit value=" OK ">';
        echo '</form>';

    }

    function act_ingen_csv($post) {
        /* generate CSV representation of loaded datasource
        http://www.creativyst.com/Doc/Articles/CSV/CSV01.htm
        */
        $this->populate();
        $rows = array();

        # colvar row
        $fields = array();
        foreach ($this->properties as $colvar=>$col) {
            $vtemp = $colvar;
            $vtemp = str_replace('"','""',$vtemp);
            $vtemp = (strpos($vtemp,',') === false and strpos($vtemp,'"') === false and strpos($vtemp,"\n") === false)? $vtemp: '"'.$vtemp.'"';
            $fields[] = $vtemp;
        }
        $rows[] = join(',',$fields);

        # col label row
        $fields = array();
        foreach ($this->properties as $colvar=>$col) {
            $vtemp = $col->label;
            $vtemp = str_replace('"','""',$vtemp);
            $vtemp = (strpos($vtemp,',') === false and strpos($vtemp,'"') === false and strpos($vtemp,"\n") === false)? $vtemp: '"'.$vtemp.'"';
            $fields[] = $vtemp;
        }
        $rows[] = join(',',$fields);

        # col content row
        for ($i = 0; $i < $this->db_count; $i++) {
            $fields = array();
            foreach ($this->properties as $colvar=>$col) {
                $vtemp = $this->ds->{$colvar}[$i];
                $vtemp = str_replace('"','""',$vtemp);
                $vtemp = (strpos($vtemp,',') === false and strpos($vtemp,'"') === false and strpos($vtemp,"\n") === false)? $vtemp: '"'.$vtemp.'"';
                $fields[] = $vtemp;
            }
            $rows[] = join(',',$fields);
        }
        #~ header('Content-type: application/vnd.ms-excel');
        header('Content-type: text/comma-separated-values');
        header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Content-Disposition: inline; filename="dump.csv"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header("Expires: 0");

        echo join("\r\n",$rows);
        exit();
    }

}

?>