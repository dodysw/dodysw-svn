<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class proyek extends TableManager {
    var $db_table, $properties;
    function proyek() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Proyek';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'proyek_tab';
        $this->properties['kode_proyek'] = new Prop(array('label'=>'Proyek','length'=>6,'colname'=>'kode_proyek', 'required'=>True, 'is_key'=>True));
		$this->properties['kode_cabang'] = new Prop(array('label'=>'Cabang','length'=>4,'colname'=>'kode_cabang', 'required'=>True, 'is_key'=>false,'inputtype'=>'combobox','enumerate'=>'cabang'));
		$this->properties['nama'] = new Prop(array('label'=>'Nama','colname'=>'nama','updatable'=>True,'insertable'=>True,'length'=>35));
        //$this->properties['ruas_id'] = new Prop(array('label'=>'Ruas','colname'=>'ruas_id','required'=>True,'inputtype'=>'combobox','enumerate'=>'ruasjalan'));
        $this->properties['deskripsi'] = new Prop(array('label'=>'Deskripsi','colname'=>'deskripsi', 'required'=>True,'inputtype'=>'textarea','rows'=>5,'browse_maxchar'=>30,'hidden'=>true));
        $this->properties['kode_kerjasama'] = new Prop(array('label'=>'Tipe Kerjasama','colname'=>'kode_kerjasama','inputtype'=>'combobox','enumerate'=>'tipe_kerjasama','length'=>6));
		$this->properties['keterangan_seksi'] = new Prop(array('label'=>'Seksi','colname'=>'keterangan_seksi','inputtype'=>'textarea','rows'=>10,'browse_maxchar'=>30));
		$this->properties['panjang'] = new Prop(array('label'=>'Panjang(KM)','colname'=>'panjang','updatable'=>True,'insertable'=>True,'length'=>10));

		$this->properties['investor'] = new Prop(array('label'=>'Investor','colname'=>'investor','updatable'=>True,'insertable'=>True,'length'=>70));
		$this->properties['masa_konsesi'] = new Prop(array('label'=>'Masa Konsensi','colname'=>'masa_konsesi','updatable'=>True,'insertable'=>True,'length'=>25,'hidden'=>true));
		$this->properties['masa_konstruksi'] = new Prop(array('label'=>'Masa Konstruksi(tahun)','colname'=>'masa_konstruksi','updatable'=>True,'insertable'=>True,'length'=>25,'hidden'=>true));
		$this->properties['biaya_investigasi'] = new Prop(array('hidden'=>true,'label'=>'Biaya Investasi(Rp)','colname'=>'biaya_investigasi','updatable'=>True,'insertable'=>True,'length'=>25));
		$this->properties['saham_disetor'] = new Prop(array('label'=>'Saham Disetor','colname'=>'saham_disetor','updatable'=>True,'insertable'=>True,'inputtype'=>'textarea', 'rows'=>5));
		$this->properties['personil'] = new Prop(array('hidden'=>true,'label'=>'Personil','colname'=>'personil','updatable'=>True,'insertable'=>True,'length'=>45));

		$this->properties['permasalahan'] = new Prop(array('hidden'=>true,'label'=>'Permasalahan','colname'=>'permasalahan','inputtype'=>'textarea','rows'=>5,'browse_maxchar'=>30,'hidden'=>true));
		$this->properties['tindak_lanjut'] = new Prop(array('hidden'=>true,'label'=>'Tindak Lanjut','colname'=>'tindak_lanjut','inputtype'=>'textarea','rows'=>5,'browse_maxchar'=>30,'hidden'=>true));
		$this->properties['pm_jabatan'] = new Prop(array('hidden'=>true,'label'=>'PM Jabatan','colname'=>'pm_jabatan','updatable'=>True,'insertable'=>True,'length'=>45));
		$this->properties['pm_nama'] = new Prop(array('hidden'=>true,'label'=>'PM Nama','colname'=>'pm_nama','updatable'=>True,'insertable'=>True,'length'=>45));
		$this->properties['pengelola'] = new Prop(array('label'=>'Pengelola','colname'=>'pengelola','updatable'=>True,'insertable'=>True,'length'=>45));
		#~ $this->properties['status_tahap'] = new Prop(array('label'=>'Status Tahap','colname'=>'status_tahap','updatable'=>True,'insertable'=>True,'length'=>45,'inputtype'=>'combobox','enumerate'=>'status_tahapan'));
		$this->properties['status'] = new Prop(array('label'=>'Status','colname'=>'status','required'=>False,'is_key'=>false,'length'=>25, 'inputtype'=>'combobox', 'enumerate'=>'status_tahapan'));
        $this->properties['tarif'] = new Prop(array('label'=>'Tarif','colname'=>'tarif','required'=>False, 'inputtype'=>'textarea', 'rows'=>5));

        $this->properties['jumlah_lajur'] = new Prop(array('colname'=>'jumlah_lajur','required'=>False, 'inputtype'=>'textarea', 'rows'=>5, 'length'=>2000));
        $this->properties['sumber_pendanaan'] = new Prop(array('colname'=>'sumber_pendanaan','required'=>False, 'inputtype'=>'textarea', 'rows'=>5, 'length'=>2000));
        $this->properties['bunga'] = new Prop(array('colname'=>'bunga','required'=>False, 'datatype'=>'double'));
        $this->properties['volume_lalulintas'] = new Prop(array('colname'=>'volume_lalulintas','required'=>False, 'datatype'=>'double'));
        $this->properties['penyesuaian_tarif'] = new Prop(array('colname'=>'penyesuaian_tarif','required'=>False));
        #~ $this->properties['kelayakan_proyek'] = new Prop(array('label'=>'Kelayakan IRR(%)','colname'=>'kelayakan_proyek','required'=>False, 'inputtype'=>'textarea', 'rows'=>10, 'length'=>2000));
        $this->properties['kelayakan_proyek_irr'] = new Prop(array('label'=>'Kelayakan IRR(%)','colname'=>'kelayakan_proyek_irr','required'=>False, 'length'=>2000));
        $this->properties['kelayakan_proyek_npv'] = new Prop(array('label'=>'Kelayakan NPV(Rp)','colname'=>'kelayakan_proyek_npv','required'=>False, 'length'=>2000));

        $this->enum_keyval = array('kode_proyek','nama');

        $this->grid_command[] = array('attach','Tambah Tahapan Pada Proyek');

//		$this->childds[] = 'tahapan_kerjasama';
		$this->childds[] = 'proyek_tahapan_kerjasama';
		//$this->childds[] = 'saham_disetor';
		//$this->childds[] = 'nilai_proyek';
		$this->childds[]='investasi_balsheet';
		//$this->childds[] = 'progres_kerjasama';

        $prog->must_authenticated = True;
        $this->browse_mode = 'form';

    }

    function go() { // called inside main content
        $this->basic_handler();
    }

      function act_attach ($post) {
       $this->properties['kode_kategori'] = new Prop(array('label'=>'Kategori','colname'=>'kode_kategori','required'=>True,'inputtype'=>'combobox','enumerate'=>'kategori'));
       $this->import2ds(); # properties is modified, re-import to datasource
       $kategori = new proyek_model();


        if ($post) {

            if (!$this->_save) return;

            if (!$this->validate_rows()) {
                return False;
            }

            $model = new EntityManager();
            $proyek = new proyek_model();

            # start insertion
            //sebenernya gak usah looping soalnya cuma satu isinya he he he
            foreach ($this->_rowid as $rowid) {
                //$sql = "insert into {$GLOBALS['dbpre']}newsletter_article_tab (newsletter_id,article_id) values ('{$this->ds->newsletter_id[0]}','$rowid')";
                //$res = mysql_query($sql) or die(mysql_error()); #do to database
                $proyek= $model->findProyekById($rowid);


            }
            $tahapan = new tahapan_kerjasama_model();


            $tahapans = $model->getTahapanByTipe($proyek->getKodeKerjasama());

            $i=0;
            foreach ($tahapans as $tahap)
            {
            	$tahapan = $tahap;

            	$sql = "insert into " . an_proyek_tahapan_kerjasama_tab . " ";
            	$sql .="(kode_proyek,kode_tahapan,kode_kerjasama,kategori1,kategori2,rencana,selesai) values ";
            	$sql .="('" . $proyek->getKodeProyek() . "','" . $tahapan->getKodeTahapan() . "','" ;
            	$sql .= $tahapan->getKodeKerjasama() . "','" . $tahapan->getKategori1() . "','";
            	$sql .= $tahapan->getKategori2() . "','" . $_REQUEST['rencana'][$i] . "','
            	" . $_REQUEST['selesai'][$i] . "');";

            	//echo $sql . "<br>";
            	$i++;

            	$res = mysql_query($sql) or die(mysql_error()); #do to database
            }



				$kategori =$proyek;
				//echo '<p>ini adalah proyek ' . $kategori->getNama() . " = " . $kategori->getRowid() . '</p>';


           return;
        }

        if (!$this->showerror() and $this->_save) {   # this is a successful posted result

            echo '<p> Tahapan Sudah DiPasangkan</p>';

            echo '<p><b><a href="'.$this->_go.'">Continue</a></b></p>';
            return;
        }

        //echo '<p>Attach selected article(s) to this newsletter:</p>';
        echo '<form method=post action="'.$_SERVER['PHP_SELF'].'">';
        echo '<input type=hidden name=m value="'.$this->module.'">';   # this module
        echo '<input type=hidden name=act value="'.$this->action.'">';   # contains the action (edit/new)
        echo '<input type=hidden name=save value="1">';         # marker to indicate form submitation
        echo '<input type=hidden name=go value="'.htmlentities($this->_go).'">';         # url to go after successful submitation

          $model = new EntityManager();

            # start insertion






        foreach ($this->_rowid as $rowid)
        {    echo '<input type=hidden name="rowid[]" value="'.htmlentities($rowid).'">';         # url to go after successful submitation
	        $proyek= $model->findProyekById($rowid);
        }


				$kategori =$proyek;
				//echo '<p>ini adalah proyek ' . $kategori->getNama() . " = " . $kategori->getRowid() . '</p>';



		$tahapans= $model->getTahapanByTipe($kategori->getKodeKerjasama());
		echo "Daftar Tahapan Yang Akan Digabung Ke Proyek " . $kategori->getNama() . "<p>";
		echo "<table>
		         <tr class='greyformtitle'><th>Tahapan</th>
		             <th>Tipe Kerjasama</th>
					 <th>Kategori Utama</th>
					 <th>Sub Kategori</th>
		             <th>Urutan</th>
					 <th>Rencana</th>
					 <th>Selesai</th>
		         </tr>";
		//$tahapan = new tahapan_kerjasama_model();
		//$tipe = new tipe_kerjasama_model();
		//$kategori1 = new kategori_model();
		//$kategori2 = new kategori_model();
		for($i=0;$i<count($tahapans);$i++)
		{

				$tahapan= $tahapans[$i];
				$tipe = $tahapan->getTipeKerjasama();
				$kategori1 = $tahapan->getObjekKategori1();
				$kategori2 = $tahapan->getObjekKategori2();

				//echo '<p>ini adalah proyek ' . $kategori->getNama() . " = " . $kategori->getRowid() . '</p>';
				echo "<tr  class='greyformlight'><td> ". $tahapan->getTahapan() . "</td>
				<td>" . $tipe->getKerjasama() . "</td>
				<td>" . $kategori1->getKategori() . "</td>
				<td>" .  $kategori2->getKategori() . "</td>
				<td>" . $tahapan->getUrutan() . "</td>
				<td><input type='text' name='rencana[]' ></td>
				<td><input type='text' name='selesai[]' ></td></tr>";

		}
		echo "</table>";





        echo '<p>';
        //$this->input_widget("field[kode_kategori][$i]", $this->ds->kode_kategori[$i], 'kode_kategori');
        echo '<p><input type=submit value=" OK "> | ';
        echo '<b><a href="" onclick="window.history.back();return false;">Cancel</a></b></p>';
        echo '</form>';

    }

}

?>