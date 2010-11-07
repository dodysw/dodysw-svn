<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class dir_entry extends TableManager {
    var $db_table, $properties;
    function dir_entry() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = lang('Directory entry');
        $html_title = $this->title;
        $this->browse_mode = 'form';

        $this->db_table = $GLOBALS['dbpre'].'dir_entry_tab';
        $this->properties['entry_id'] = new Prop(array('label'=>'Id','colname'=>'entry_id','required'=>True,'length'=>4,'insertable'=>False));

        $this->properties['org_nama'] = new Prop(array('label'=>'Nama', 'colname'=>'org_nama','required'=>True,'length'=>50, 'box_start'=>'Organisasi'));
        $this->properties['org_jenis'] = new Prop(array('label'=>'Jenis', 'colname'=>'org_jenis', 'datatype'=>'int', 'inputtype'=>'combobox', 'enumerate'=>'jenis_organisasi'));
        $this->properties['org_alamat_1'] = new Prop(array('label'=>'Alamat 1', 'colname'=>'org_alamat_1', 'length'=>255));
        $this->properties['org_alamat_2'] = new Prop(array('label'=>'Alamat 2', 'colname'=>'org_alamat_2', 'length'=>255));
        $this->properties['org_kota'] = new Prop(array('label'=>'Kota', 'colname'=>'org_kota','length'=>20));
        $this->properties['org_propinsi'] = new Prop(array('label'=>'Propinsi', 'colname'=>'org_propinsi', 'length'=>50));
        $this->properties['org_kode_pos'] = new Prop(array('label'=>'Kode pos', 'colname'=>'org_kode_pos', 'length'=>6));
        $this->properties['org_telepon_1'] = new Prop(array('label'=>'Telepon 1', 'colname'=>'org_telepon_1', 'length'=>15));
        $this->properties['org_telepon_2'] = new Prop(array('label'=>'Telepon 2', 'colname'=>'org_telepon_2', 'length'=>15));
        $this->properties['org_fax'] = new Prop(array('label'=>'Fax', 'colname'=>'org_fax', 'length'=>15));
        $this->properties['org_hp'] = new Prop(array('label'=>'HP', 'colname'=>'org_hp', 'length'=>15));
        $this->properties['org_email'] = new Prop(array('label'=>'Email', 'colname'=>'org_email', 'length'=>30));
        $this->properties['org_website'] = new Prop(array('label'=>'Website', 'colname'=>'org_website', 'length'=>30, 'box_end'=>True));

        $this->properties['pimpinan_nama'] = new Prop(array('label'=>'Nama Pimpinan', 'colname'=>'pimpinan_nama', 'length'=>30));
        $this->properties['pimpinan_jabatan'] = new Prop(array('label'=>'Jabatan', 'colname'=>'pimpinan_jabatan', 'length'=>30));
        $this->properties['direktur_artistik'] = new Prop(array('label'=>'Direktur Artistik', 'colname'=>'direktur_artistik', 'length'=>30));

        $this->properties['cp_nama'] = new Prop(array('label'=>'Nama', 'colname'=>'cp_nama', 'length'=>30, 'box_start'=>'Contact Person'));
        $this->properties['cp_jabatan'] = new Prop(array('label'=>'Jabatan', 'colname'=>'cp_jabatan', 'length'=>30));
        $this->properties['cp_telepon_1'] = new Prop(array('label'=>'Telepon 1', 'colname'=>'cp_telepon_1', 'length'=>15));
        $this->properties['cp_telepon_2'] = new Prop(array('label'=>'Telepon 2', 'colname'=>'cp_telepon_2', 'length'=>15));
        $this->properties['cp_fax'] = new Prop(array('label'=>'Fax', 'colname'=>'cp_fax', 'length'=>15));
        $this->properties['cp_hp'] = new Prop(array('label'=>'HP', 'colname'=>'cp_hp', 'length'=>15));
        $this->properties['cp_email'] = new Prop(array('label'=>'Email', 'colname'=>'cp_email', 'length'=>30, 'box_end'=>True));

        $this->properties['cat_venue'] = new Prop(array('label'=>'Venue', 'colname'=>'cat_venue', 'inputtype'=>'checkbox', 'datatype'=>'bool', 'box_start'=>'Venue'));
        $this->properties['cat_taman_budaya'] = new Prop(array('label'=>'Taman Budaya', 'colname'=>'cat_taman_budaya', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_ruang_pertunjukan'] = new Prop(array('label'=>'Ruang Pertunjukan', 'colname'=>'cat_ruang_pertunjukan', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_galeri'] = new Prop(array('label'=>'Galeri', 'colname'=>'cat_galeri', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_ruang_tunjuk_visual'] = new Prop(array('label'=>'Ruang Tunjuk Visual', 'colname'=>'cat_ruang_tunjuk_visual', 'inputtype'=>'checkbox', 'datatype'=>'bool', 'box_end'=>True));

        $this->properties['cat_instansi'] = new Prop(array('label'=>'Instansi', 'colname'=>'cat_instansi', 'inputtype'=>'checkbox', 'datatype'=>'bool', 'box_start'=>'Instansi'));
        $this->properties['cat_dewan_kesenian'] = new Prop(array('label'=>'Dewan Kesenian', 'colname'=>'cat_dewan_kesenian', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_pusat_budasing'] = new Prop(array('label'=>'Pusat Budaya Asing', 'colname'=>'cat_pusat_budasing', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_organisasi_pendukung'] = new Prop(array('label'=>'Organisasi Pendukung', 'colname'=>'cat_organisasi_pendukung', 'inputtype'=>'checkbox', 'datatype'=>'bool', 'box_end'=>True));

        $this->properties['cat_kesenian'] = new Prop(array('label'=>'Kesenian', 'colname'=>'cat_kesenian', 'inputtype'=>'checkbox', 'datatype'=>'bool', 'box_start'=>'Kesenian'));
        $this->properties['cat_pertunjukan'] = new Prop(array('label'=>'Pertunjukan', 'colname'=>'cat_pertunjukan', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_musik'] = new Prop(array('label'=>'Musik', 'colname'=>'cat_musik', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_musik_tradisional'] = new Prop(array('label'=>'Musik Tradisional', 'colname'=>'cat_musik_tradisional', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_tari_tradisional'] = new Prop(array('label'=>'Tari Tradisional', 'colname'=>'cat_tari_tradisional', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_musik_kontemporer'] = new Prop(array('label'=>'Musik Kontemporer', 'colname'=>'cat_musik_kontemporer', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_tari_kontemporer'] = new Prop(array('label'=>'Tari Kontemporer', 'colname'=>'cat_tari_kontemporer', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_teater'] = new Prop(array('label'=>'Teater', 'colname'=>'cat_teater', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_teater_tradisional'] = new Prop(array('label'=>'Teater Tradisional', 'colname'=>'cat_teater_tradisional', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_teater_kontemporer'] = new Prop(array('label'=>'Teater Kontemporer', 'colname'=>'cat_teater_kontemporer', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_tari'] = new Prop(array('label'=>'Tari', 'colname'=>'cat_tari', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_tari_boneka_kontemporer'] = new Prop(array('label'=>'Tari Boneka Kontemporer', 'colname'=>'cat_tari_boneka_kontemporer', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_pertunjukan_lain'] = new Prop(array('label'=>'Pertunjukan Lain', 'colname'=>'cat_pertunjukan_lain', 'length'=>200, 'box_end'=>True));

        $this->properties['cat_visual'] = new Prop(array('label'=>'Visual', 'colname'=>'cat_visual', 'inputtype'=>'checkbox', 'datatype'=>'bool', 'box_start'=>'Visual'));
        $this->properties['cat_visual_tradisional'] = new Prop(array('label'=>'Visual Tradisional', 'colname'=>'cat_visual_tradisional', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_visual_modern'] = new Prop(array('label'=>'Visual Modern', 'colname'=>'cat_visual_modern', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_seni_lukis_tradisional'] = new Prop(array('label'=>'Seni Lukis Tradisional', 'colname'=>'cat_seni_lukis_tradisional', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_seni_patung_tradisional'] = new Prop(array('label'=>'Seni Patung Tradisional', 'colname'=>'cat_seni_patung_tradisional', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_seni_lukis_kontemporer'] = new Prop(array('label'=>'Seni Lukis Kontemporer', 'colname'=>'cat_seni_lukis_kontemporer', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_seni_patung_kontemporer'] = new Prop(array('label'=>'Seni Patung Kontemporer', 'colname'=>'cat_seni_patung_kontemporer', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_fotografi'] = new Prop(array('label'=>'Fotografi', 'colname'=>'cat_fotografi', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_visual_lain'] = new Prop(array('label'=>'Lainnya', 'colname'=>'cat_visual_lain', 'length'=>200));

        $this->properties['cat_pendidikan_seni'] = new Prop(array('label'=>'Pendidikan Seni', 'colname'=>'cat_pendidikan_seni', 'inputtype'=>'checkbox', 'datatype'=>'bool', 'box_start'=>'Pendidikan Seni'));
        $this->properties['cat_sek_menengah'] = new Prop(array('label'=>'Sekolah Menengah', 'colname'=>'cat_sek_menengah', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['cat_sek_tinggi'] = new Prop(array('label'=>'Sekolah Tinggi', 'colname'=>'cat_sek_tinggi', 'inputtype'=>'checkbox', 'datatype'=>'bool', 'box_end'=>True));

        $this->properties['tanggal_berdiri'] = new Prop(array('colname'=>'tanggal_berdiri', 'box_start'=>'Latar Belakang Organisasi'));
        $this->properties['kegiatan_tujuan'] = new Prop(array('colname'=>'kegiatan_tujuan', 'length'=>4000, 'inputtype'=>'textarea'));
        $this->properties['prestasi'] = new Prop(array('colname'=>'prestasi', 'length'=>4000, 'inputtype'=>'textarea', 'box_end'=>True));

        $this->properties['punya_fasilitas'] = new Prop(array('label'=>'Punya Fasilitas Publik', 'colname'=>'punya_fasilitas', 'inputtype'=>'checkbox', 'datatype'=>'bool', 'box_start'=>'Fasilitas'));

        $this->properties['fas_ruang_pertunjukan'] = new Prop(array('label'=>'Ruang Pertunjukan', 'colname'=>'fas_ruang_pertunjukan', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['fas_ruang_pertunjukan_1_panjang'] = new Prop(array('label'=>'1 - Panjang/Lebar/Kapasitas', 'suffix'=>'m x ', 'colname'=>'fas_ruang_pertunjukan_1_panjang', 'datatype'=>'double', 'colspan'=>3));
        $this->properties['fas_ruang_pertunjukan_1_lebar'] = new Prop(array('label'=>'Lebar', 'suffix'=>'m', 'colname'=>'fas_ruang_pertunjukan_1_lebar', 'datatype'=>'double'));
        $this->properties['fas_ruang_pertunjukan_1_kapasitas'] = new Prop(array('label'=>'Kapasitas','suffix'=>'orang', 'colname'=>'fas_ruang_pertunjukan_1_kapasitas'));
        $this->properties['fas_ruang_pertunjukan_2_panjang'] = new Prop(array('label'=>'2 - Panjang/Lebar/Kapasitas', 'suffix'=>'m x ', 'colname'=>'fas_ruang_pertunjukan_2_panjang', 'datatype'=>'double', 'colspan'=>3));
        $this->properties['fas_ruang_pertunjukan_2_lebar'] = new Prop(array('label'=>'Lebar', 'suffix'=>'m','colname'=>'fas_ruang_pertunjukan_2_lebar', 'datatype'=>'double'));
        $this->properties['fas_ruang_pertunjukan_2_kapasitas'] = new Prop(array('label'=>'Kapasitas','suffix'=>'orang', 'colname'=>'fas_ruang_pertunjukan_2_kapasitas'));
        $this->properties['fas_ruang_pertunjukan_3_panjang'] = new Prop(array('label'=>'3 - Panjang/Lebar/Kapasitas', 'suffix'=>'m x ', 'colname'=>'fas_ruang_pertunjukan_3_panjang', 'datatype'=>'double', 'colspan'=>3));
        $this->properties['fas_ruang_pertunjukan_3_lebar'] = new Prop(array('label'=>'Lebar', 'suffix'=>'m','colname'=>'fas_ruang_pertunjukan_3_lebar', 'datatype'=>'double'));
        $this->properties['fas_ruang_pertunjukan_3_kapasitas'] = new Prop(array('label'=>'Kapasitas','suffix'=>'orang', 'colname'=>'fas_ruang_pertunjukan_3_kapasitas'));

        $this->properties['fas_ruang_pameran'] = new Prop(array('label'=>'Ruang Pameran', 'colname'=>'fas_ruang_pameran', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['fas_ruang_pameran_1_panjang'] = new Prop(array('label'=>'1 - Panjang/Lebar/Kapasitas', 'suffix'=>'m x ', 'colname'=>'fas_ruang_pameran_1_panjang', 'datatype'=>'double', 'colspan'=>3));
        $this->properties['fas_ruang_pameran_1_lebar'] = new Prop(array('label'=>'Lebar', 'suffix'=>'m', 'colname'=>'fas_ruang_pameran_1_lebar', 'datatype'=>'double'));
        $this->properties['fas_ruang_pameran_1_kapasitas'] = new Prop(array('label'=>'Kapasitas','suffix'=>'orang', 'colname'=>'fas_ruang_pameran_1_kapasitas'));
        $this->properties['fas_ruang_pameran_2_panjang'] = new Prop(array('label'=>'2 - Panjang/Lebar/Kapasitas', 'suffix'=>'m x ', 'colname'=>'fas_ruang_pameran_2_panjang', 'datatype'=>'double', 'colspan'=>3));
        $this->properties['fas_ruang_pameran_2_lebar'] = new Prop(array('label'=>'Lebar', 'suffix'=>'m','colname'=>'fas_ruang_pameran_2_lebar', 'datatype'=>'double'));
        $this->properties['fas_ruang_pameran_2_kapasitas'] = new Prop(array('label'=>'Kapasitas','suffix'=>'orang', 'colname'=>'fas_ruang_pameran_2_kapasitas'));
        $this->properties['fas_ruang_pameran_3_panjang'] = new Prop(array('label'=>'3 - Panjang/Lebar/Kapasitas', 'suffix'=>'m x ', 'colname'=>'fas_ruang_pameran_3_panjang', 'datatype'=>'double', 'colspan'=>3));
        $this->properties['fas_ruang_pameran_3_lebar'] = new Prop(array('label'=>'Lebar', 'suffix'=>'m','colname'=>'fas_ruang_pameran_3_lebar', 'datatype'=>'double'));
        $this->properties['fas_ruang_pameran_3_kapasitas'] = new Prop(array('label'=>'Kapasitas','suffix'=>'orang', 'colname'=>'fas_ruang_pameran_3_kapasitas'));


        $this->properties['punya_fas_pendukung_ruang'] = new Prop(array('label'=>'Fasilitas Pendukung Ruang', 'colname'=>'punya_fas_pendukung_ruang', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['fas_pendukung_ruang'] = new Prop(array('label'=>'Sebutkan', 'inputtype'=>'textarea', 'colname'=>'fas_pendukung_ruang', 'length'=>4000));
        $this->properties['punya_fas_pendukung_lainnya'] = new Prop(array('label'=>'Fasilitas Lainnya', 'colname'=>'punya_fas_pendukung_lainnya', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['fas_pendukung_lainnya'] = new Prop(array('label'=>'Sebutkan', 'inputtype'=>'textarea', 'colname'=>'fas_pendukung_lainnya', 'length'=>4000));

        $this->properties['menghasilkan_produk'] = new Prop(array('colname'=>'menghasilkan_produk', 'inputtype'=>'checkbox', 'datatype'=>'bool', 'box_start'=>'Kesediaan dan Produk'));
        $this->properties['prod_buku_tersedia'] = new Prop(array('label'=>'Buku Tersedia','colspan'=>2, 'colname'=>'prod_buku_tersedia', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['prod_buku_dijual'] = new Prop(array('prefix'=>'Dijual umum?', 'colname'=>'prod_buku_dijual', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['prod_newsletter_tersedia'] = new Prop(array('label'=>'Newsletter Tersedia','colspan'=>2,'colname'=>'prod_newsletter_tersedia', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['prod_newsletter_dijual'] = new Prop(array('prefix'=>'Dijual umum?','colname'=>'prod_newsletter_dijual', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['prod_jurnal_tersedia'] = new Prop(array('label'=>'Jurnal Tersedia','colspan'=>2,'colname'=>'prod_jurnal_tersedia', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['prod_jurnal_dijual'] = new Prop(array('prefix'=>'Dijual umum?','colname'=>'prod_jurnal_dijual', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['prod_rekaman_audio_tersedia'] = new Prop(array('label'=>'Rekaman Audio Tersedia','colspan'=>2,'colname'=>'prod_rekaman_audio_tersedia', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['prod_rekaman_audio_dijual'] = new Prop(array('prefix'=>'Dijual umum?','colname'=>'prod_rekaman_audio_dijual', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['prod_rekaman_video_tersedia'] = new Prop(array('label'=>'Rekaman Video Tersedia','colspan'=>2,'colname'=>'prod_rekaman_video_tersedia', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['prod_rekaman_video_dijual'] = new Prop(array('prefix'=>'Dijual umum?','colname'=>'prod_rekaman_video_dijual', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['prod_barang_kerajinan_tersedia'] = new Prop(array('label'=>'Barang Kerajinan Tersedia','colspan'=>2,'colname'=>'prod_barang_kerajinan_tersedia', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['prod_barang_kerajinan_dijual'] = new Prop(array('prefix'=>'Dijual umum?','colname'=>'prod_barang_kerajinan_dijual', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['prod_benda_seni_tersedia'] = new Prop(array('label'=>'Benda Seni Tersedia','colspan'=>2,'colname'=>'prod_benda_seni_tersedia', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['prod_benda_seni_dijual'] = new Prop(array('prefix'=>'Dijual umum?','colname'=>'prod_benda_seni_dijual', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['prod_lainlain_tersedia'] = new Prop(array('label'=>'Lain-lain tersedia','colspan'=>2,'colname'=>'prod_lainlain_tersedia', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['prod_lainlain_dijual'] = new Prop(array('prefix'=>'Dijual umum?','colname'=>'prod_lainlain_dijual', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['prod_hasil_lain'] = new Prop(array('label'=>'Hasil Lain', 'colname'=>'prod_hasil_lain', 'inputtype'=>'textarea', 'length'=>4000));


        $this->properties['tersedia_brosur'] = new Prop(array('colname'=>'tersedia_brosur', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['bersedia_diikutkan'] = new Prop(array('label'=>'Bersedia diikutkan dalam direktori lain', 'colname'=>'bersedia_diikutkan', 'inputtype'=>'checkbox', 'datatype'=>'bool'));
        $this->properties['nama_file_input'] = new Prop(array('colname'=>'nama_file_input', 'length'=>255));

        //TODO!
        $this->properties['parent_dir'] = new Prop(array('label'=>lang('Directory'),'colname'=>'parent_dir', 'inputtype'=>'combobox','required'=>False, 'enumerate'=>'dirstruct'));

        $this->enum_keyval = array('entry_id','org_nama');

        $this->unit = 'entry';

    }

    function go() { # called inside main content
        $this->basic_handler();
    }

    function prepare_insert($i) {
        $seq = instantiate_module('seq_gen');
        $this->ds->dir_id[$i] = $seq->simulate_next_number('direntry_id');
        return True;
    }

    function insert($i) {
        $seq = instantiate_module('seq_gen');
        $this->ds->dir_id[$i] = $seq->get_next_number('direntry_id');
        parent::insert($i);
    }

    function front_list($cat_id = '') {
        if ($cat_id == '') $cat_id = $_REQUEST['pa'];
        if ($cat_id == '') $cat_id = '*';
        $this->db_where = "parent_dir = '".myaddslashes($cat_id)."'";
        $this->populate();
        if ($this->db_count == 0) {
            echo '<p>'.lang($this->unit).' '.lang('not available').'</p>';
            return;
        }
        $child = new dirstruct();
        for ($i = 0; $i < $this->db_count; $i++) {
            echo '<p><b><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('pa'=>$this->ds->dir_id[$i])).'">'.$this->ds->name[$i].':'.$this->ds->description[$i].'</a></b>';
            # check if this category has direct children
            $child->db_where = "parent_dir = '".myaddslashes($this->ds->dir_id[$i])."'";
            $child->populate();
            #~ print_r($child);
            for ($j = 0; $j < $child->db_count; $j++) {
                echo '<br><a href="'.get_fullpath().'">'.$child->ds->name[$j].' '.$child->ds->description[$j].'</a>';
            }
            unset($child->ds);
        }
    }

    function front_view($id) {
        return $this->get_row(array('entry_id'=>$id));
    }

    function front_trail($id) {
        $parent_field = 'parent_dir';
        $url_key = 'pa';
        #~ if ($cat_id == '') $cat_id = $_REQUEST[$url_key];
        #~ if ($cat_id == '') $cat_id = '*';
        # strategy: trace BACK to *
        $trace = array();

        $row = $this->get_row(array('entry_id'=>$id));
        #~ $trace[] = array($row[$parent_field],$row['name']);   # put first element
        $trace[] = array('',$row['name']);   # put first element
        $cid = $row[$parent_field];
        do {
            $sql = "select id,name,$parent_field from {$GLOBALS['dbpre']}dirstruct_tab where id='{$cid}'";
            $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());
            if (!mysql_num_rows($res)) break;
            list($id,$name,$parent) = mysql_fetch_row($res);
            $cid = $parent;
            $trace[] = array($id,$name);
        } while ($cid != '*');
        $trace[] = array('*','Home');   # put first element
        $trace = array_reverse($trace);
        foreach ($trace as $t) {
            if ($t[0] == '') { # no anchor
                echo '<b>'.$t[1].'</b>';
                continue;
            }
            echo '<a href="directory.php?'.merge_query(array($url_key=>$t[0])).'">'.$t[1].'</a>  &gt; ';
        }
    }

    function show_search_result() {
        $kw = $_REQUEST['query'];
        $row_per_page = $_REQUEST['row_per_page'];
        $page_var_name = 'page';
        $rowstart = 0;
        if ($_REQUEST[$page_var_name]) $rowstart = $_REQUEST[$page_var_name];

        $wheres = array();
        $wheres[] = "(org_nama like '%$kw%' or org_alamat_1 like '%$kw%' or org_alamat_2 like '%$kw%' or pimpinan_nama like '%$kw%' or direktur_artistik like '%$kw%' or cp_nama like '%$kw%' or kegiatan_tujuan like '%$kw%' or prestasi like '%$kw%' )";
        $where_str = join(' and ', $wheres);

        # first get howmuch total rows
        $sql = 'select 1 from '.$this->db_table." where ".$where_str;
        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
        $max_rownum = mysql_num_rows($res);

        # normal sql
        $sql = 'select * from '.$this->db_table." where ".$where_str.' limit '.$rowstart.','.$row_per_page;
        #~ die($sql);

        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
        if (!mysql_num_rows($res)) {
            echo '<p align="center" ><b>No result returned from search</b></p>';
        }
        else {
            $rows = array();
            $i = $_REQUEST[$page_var_name] + 1;
            echo '<table><tr><th>No</th><th>Nama Organisasi</th><th>Alamat</th><th>Propinsi</th></tr>';
            while ($row = mysql_fetch_array($res)) {
                echo '<tr><td>'.$i.'</td><td><a href="dir_entry.php?eid='.$row['entry_id'].'">'.$row['org_nama'].'</a></td><td>'.$row['org_alamat_1'].' '.$row['org_alamat_2'].'</td><td>'.$row['org_propinsi'].'</td></tr>';
                $i++;
            }
            echo '</table>';
        }

        # show paging
        if ($row_per_page and $max_rownum and $max_rownum > $row_per_page) {
            echo lang('Pages').': ';
            $pages = array();
            for ($rowidx = 0, $pg = 1; $rowidx < $max_rownum; $rowidx += $row_per_page, $pg += 1) {
                if ($rowstart == $rowidx)
                    $pages[] = "<b>$pg</b>";
                else {
                    $pages[] = '<a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array($page_var_name=>$rowidx)).'">'.$pg.'</a>';
                }
            }
            echo join(' | ',$pages);
        }

    }

}


?>