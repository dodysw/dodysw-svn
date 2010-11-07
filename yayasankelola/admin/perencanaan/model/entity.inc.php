<?php

 class tahapan_kerjasama_model{ 

 	var $rowid;
 	var $kode_tahapan;
 	var $tahapan;
 	var $kode_kerjasama;
 	var $kategori1;
 	var $kategori2;
 	var $urutan;
 	var $isSave=false;
 
	function tahapan_kerjasama_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getKodeTahapan(){
 		 return  $this->kode_tahapan;
	}
 
 	function setKodeTahapan( $kode_tahapan){
 		$this->kode_tahapan=$kode_tahapan;
	}
 
 	function getTahapan(){
 		 return  $this->tahapan;
	}
 
 	function setTahapan( $tahapan){
 		$this->tahapan=$tahapan;
	}
 
 	function getKodeKerjasama(){
 		 return  $this->kode_kerjasama;
	}
 
 	function setKodeKerjasama( $kode_kerjasama){
 		$this->kode_kerjasama=$kode_kerjasama;
	}
 
 	function getKategori1(){
 		 return  $this->kategori1;
	}
 
 	function setKategori1( $kategori1){
 		$this->kategori1=$kategori1;
	}
 
 	function getKategori2(){
 		 return  $this->kategori2;
	}
 
 	function setKategori2( $kategori2){
 		$this->kategori2=$kategori2;
	}
 
 	function getUrutan(){
 		 return  $this->urutan;
	}
 
 	function setUrutan( $urutan){
 		$this->index=$urutan;
	}
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','kode_tahapan','tahapan','kode_kerjasama','kategori1','kategori2','urutan');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setKodeTahapan','setTahapan','setKodeKerjasama','setKategori1','setKategori2','setUrutan');
 
	 return $fields;

	}
	
}


 class propinsi_model{ 

 	var $rowid;
 	var $kode_propinsi;
 	var $nama_propinsi;
 	var $luas_wilayah;
 	var $jumlah_penduduk;
 	var $isSave=false;
 
	function propinsi_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getKodePropinsi(){
 		 return  $this->kode_propinsi;
	}
 
 	function setKodePropinsi( $kode_propinsi){
 		$this->kode_propinsi=$kode_propinsi;
	}
 
 	function getNamaPropinsi(){
 		 return  $this->nama_propinsi;
	}
 
 	function setNamaPropinsi( $nama_propinsi){
 		$this->nama_propinsi=$nama_propinsi;
	}
 
 	function getLuasWilayah(){
 		 return  $this->luas_wilayah;
	}
 
 	function setLuasWilayah( $luas_wilayah){
 		$this->luas_wilayah=$luas_wilayah;
	}
 
 	function getJumlahPenduduk(){
 		 return  $this->jumlah_penduduk;
	}
 
 	function setJumlahPenduduk( $jumlah_penduduk){
 		$this->jumlah_penduduk=$jumlah_penduduk;
	}
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','kode_propinsi','nama_propinsi','luas_wilayah','jumlah_penduduk');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setKodePropinsi','setNamaPropinsi','setLuasWilayah','setJumlahPenduduk');
 
	 return $fields;

	}

}

class periode_op_model{ 

 	var $rowid;
 	var $tahun;
 	var $bulan;
 	var $deskripsi;
 	var $isSave=false;
 
	function periode_op_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getTahun(){
 		 return  $this->tahun;
	}
 
 	function setTahun( $tahun){
 		$this->tahun=$tahun;
	}
 
 	function getBulan(){
 		 return  $this->bulan;
	}
 
 	function setBulan( $bulan){
 		$this->bulan=$bulan;
	}
 
 	function getDeskripsi(){
 		 return  $this->deskripsi;
	}
 
 	function setDeskripsi( $deskripsi){
 		$this->deskripsi=$deskripsi;
	}
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','tahun','bulan','deskripsi');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setTahun','setBulan','setDeskripsi');
 
	 return $fields;

	}

}

 class periode_in_model{ 

 	var $rowid;
 	var $tahun;
 	var $bulan;
 	var $deskripsi;
 	var $isSave=false;
 
	function periode_in_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getTahun(){
 		 return  $this->tahun;
	}
 
 	function setTahun( $tahun){
 		$this->tahun=$tahun;
	}
 
 	function getBulan(){
 		 return  $this->bulan;
	}
 
 	function setBulan( $bulan){
 		$this->bulan=$bulan;
	}
 
 	function getDeskripsi(){
 		 return  $this->deskripsi;
	}
 
 	function setDeskripsi( $deskripsi){
 		$this->deskripsi=$deskripsi;
	}
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','tahun','bulan','deskripsi');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setTahun','setBulan','setDeskripsi');
 
	 return $fields;

	}

}

 class penyebab_kecelakaan_model{ 

 	var $rowid;
 	var $penyebab_kecelakaan;
 	var $deskripsi;
 	var $isSave=false;
 
	function penyebab_kecelakaan_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getPenyebabKecelakaan(){
 		 return  $this->penyebab_kecelakaan;
	}
 
 	function setPenyebabKecelakaan( $penyebab_kecelakaan){
 		$this->penyebab_kecelakaan=$penyebab_kecelakaan;
	}
 
 	function getDeskripsi(){
 		 return  $this->deskripsi;
	}
 
 	function setDeskripsi( $deskripsi){
 		$this->deskripsi=$deskripsi;
	}
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','penyebab_kecelakaan','deskripsi');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setPenyebabKecelakaan','setDeskripsi');
 
	 return $fields;

	}

}

 class operasional_balsheet_model{ 

 	var $rowid;
 	var $kode_cabang;
 	var $tahun_op;
 	var $bulan_op;
 	var $amount;
 	var $isSave=false;
 
	function operasional_balsheet_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getKodeCabang(){
 		 return  $this->kode_cabang;
	}
 
 	function setKodeCabang( $kode_cabang){
 		$this->kode_cabang=$kode_cabang;
	}
 
 	function getTahunOp(){
 		 return  $this->tahun_op;
	}
 
 	function setTahunOp( $tahun_op){
 		$this->tahun_op=$tahun_op;
	}
 
 	function getBulanOp(){
 		 return  $this->bulan_op;
	}
 
 	function setBulanOp( $bulan_op){
 		$this->bulan_op=$bulan_op;
	}
 
 	function getAmount(){
 		 return  $this->amount;
	}
 
 	function setAmount( $amount){
 		$this->amount=$amount;
	}
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','kode_cabang','tahun_op','bulan_op','amount');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setKodeCabang','setTahunOp','setBulanOp','setAmount');
 
	 return $fields;

	}

}

 class kecelakaan_model{ 

 	var $rowid;
 	var $kode_cabang;
 	var $kode_jalan;
 	var $kode_ruas;
 	var $tahun_op;
 	var $bulan_op;
 	var $arah_ruas;
 	var $jumlah;
 	var $tingkat_kecelakaan;
 	var $isSave=false;
 
	function kecelakaan_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getKodeCabang(){
 		 return  $this->kode_cabang;
	}
 
 	function setKodeCabang( $kode_cabang){
 		$this->kode_cabang=$kode_cabang;
	}
 
 	function getKodeJalan(){
 		 return  $this->kode_jalan;
	}
 
 	function setKodeJalan( $kode_jalan){
 		$this->kode_jalan=$kode_jalan;
	}
 
 	function getKodeRuas(){
 		 return  $this->kode_ruas;
	}
 
 	function setKodeRuas( $kode_ruas){
 		$this->kode_ruas=$kode_ruas;
	}
 
 	function getTahunOp(){
 		 return  $this->tahun_op;
	}
 
 	function setTahunOp( $tahun_op){
 		$this->tahun_op=$tahun_op;
	}
 
 	function getBulanOp(){
 		 return  $this->bulan_op;
	}
 
 	function setBulanOp( $bulan_op){
 		$this->bulan_op=$bulan_op;
	}
 
 	function getArahRuas(){
 		 return  $this->arah_ruas;
	}
 
 	function setArahRuas( $arah_ruas){
 		$this->arah_ruas=$arah_ruas;
	}
 
 	function getJumlah(){
 		 return  $this->jumlah;
	}
 
 	function setJumlah( $jumlah){
 		$this->jumlah=$jumlah;
	}
 
 	function getTingkatKecelakaan(){
 		 return  $this->tingkat_kecelakaan;
	}
 
 	function setTingkatKecelakaan( $tingkat_kecelakaan){
 		$this->tingkat_kecelakaan=$tingkat_kecelakaan;
	}
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','kode_cabang','kode_jalan','kode_ruas','tahun_op','bulan_op','arah_ruas','jumlah','tingkat_kecelakaan');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setKodeCabang','setKodeJalan','setKodeRuas','setTahunOp','setBulanOp','setArahRuas','setJumlah','setTingkatKecelakaan');
 
	 return $fields;

	}

}

 class kecelakaan_penyebab_model{ 

 	var $rowid;
 	var $kode_cabang;
 	var $kode_jalan;
 	var $kode_ruas;
 	var $tahun_op;
 	var $bulan_op;
 	var $arah_ruas;
 	var $jumlah;
 	var $kode_penyebab_kecelakaan;

 	var $isSave=false;
 
	function kecelakaan_penyebab_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getKodeCabang(){
 		 return  $this->kode_cabang;
	}
 
 	function setKodeCabang( $kode_cabang){
 		$this->kode_cabang=$kode_cabang;
	}
 
 	function getKodeJalan(){
 		 return  $this->kode_jalan;
	}
 
 	function setKodeJalan( $kode_jalan){
 		$this->kode_jalan=$kode_jalan;
	}
 
 	function getKodeRuas(){
 		 return  $this->kode_ruas;
	}
 
 	function setKodeRuas( $kode_ruas){
 		$this->kode_ruas=$kode_ruas;
	}
 
 	function getTahunOp(){
 		 return  $this->tahun_op;
	}
 
 	function setTahunOp( $tahun_op){
 		$this->tahun_op=$tahun_op;
	}
 
 	function getBulanOp(){
 		 return  $this->bulan_op;
	}
 
 	function setBulanOp( $bulan_op){
 		$this->bulan_op=$bulan_op;
	}
 
 	function getArahRuas(){
 		 return  $this->arah_ruas;
	}
 
 	function setArahRuas( $arah_ruas){
 		$this->arah_ruas=$arah_ruas;
	}
 
 	function getJumlah(){
 		 return  $this->jumlah;
	}
 
 	function setJumlah( $jumlah){
 		$this->jumlah=$jumlah;
	}
 
 	function getKodeKecelakaan(){
 		 return  $this->kode_penyebab_kecelakaan;
	}
 
 	function setKodeKecelakaan( $kode_penyebab_kecelakaan){
 		$this->kode_penyebab_kecelakaan=$kode_penyebab_kecelakaan;
	}
 
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','kode_cabang','kode_jalan','kode_ruas','tahun_op','bulan_op','arah_ruas','jumlah','kode_penyebab_kecelakaan');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setKodeCabang','setKodeJalan','setKodeRuas','setTahunOp','setBulanOp','setArahRuas','setJumlah','setKodeKecelakaan');
 
	 return $fields;

	}

}

class kategori_model{ 

 	var $rowid;
 	var $kode_kategori;
 	var $kategori;
 	var $isSave=false;
 
	function kategori_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getKodeKategori(){
 		 return  $this->kode_kategori;
	}
 
 	function setKodeKategori( $kode_kategori){
 		$this->kode_kategori=$kode_kategori;
	}
 
 	function getKategori(){
 		 return  $this->kategori;
	}
 
 	function setKategori( $kategori){
 		$this->kategori=$kategori;
	}
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','kode_kategori','kategori');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setKodeKategori','setKategori');
 
	 return $fields;

	}

}

class jalan_model{ 

 	var $rowid;
 	var $kode_cabang;
 	var $kode_jalan;
 	var $jalan;
 	var $isSave=false;
 
	function jalan_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getKodeCabang(){
 		 return  $this->kode_cabang;
	}
 
 	function setKodeCabang( $kode_cabang){
 		$this->kode_cabang=$kode_cabang;
	}
 
 	function getKodeJalan(){
 		 return  $this->kode_jalan;
	}
 
 	function setKodeJalan( $kode_jalan){
 		$this->kode_jalan=$kode_jalan;
	}
 
 	function getJalan(){
 		 return  $this->jalan;
	}
 
 	function setJalan( $jalan){
 		$this->jalan=$jalan;
	}
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','kode_cabang','kode_jalan','jalan');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setKodeCabang','setKodeJalan','setJalan');
 
	 return $fields;

	}

}

class investasi_balsheet_model{ 

 	var $rowid;
 	var $kode_proyek;
 	var $account_no;
 	var $tahun_in;
 	var $bulan_in;
 	var $amount;
 	var $isSave=false;
 
	function investasi_balsheet_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getKodeProyek(){
 		 return  $this->kode_proyek;
	}
 
 	function setKodeProyek( $kode_proyek){
 		$this->kode_proyek=$kode_proyek;
	}
 
 	function getAccountNo(){
 		 return  $this->account_no;
	}
 
 	function setAccountNo( $account_no){
 		$this->account_no=$account_no;
	}
 
 	function getTahunIn(){
 		 return  $this->tahun_in;
	}
 
 	function setTahunIn( $tahun_in){
 		$this->tahun_in=$tahun_in;
	}
 
 	function getBulanIn(){
 		 return  $this->bulan_in;
	}
 
 	function setBulanIn( $bulan_in){
 		$this->bulan_in=$bulan_in;
	}
 
 	function getAmount(){
 		 return  $this->amount;
	}
 
 	function setAmount( $amount){
 		$this->amount=$amount;
	}
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','kode_proyek','account_no','tahun_in','bulan_in','amount');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setKodeProyek','setAccountNo','setTahunIn','setBulanIn','setAmount');
 
	 return $fields;

	}

}

class gerbang_tol_model{ 

 	var $rowid;
 	var $kode_cabang;
 	var $kode_jalan;
 	var $kode_ruas;
 	var $kode_gerbang;
 	var $gerbang;
 	var $isSave=false;
 
	function gerbang_tol_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getKodeCabang(){
 		 return  $this->kode_cabang;
	}
 
 	function setKodeCabang( $kode_cabang){
 		$this->kode_cabang=$kode_cabang;
	}
 
 	function getKodeJalan(){
 		 return  $this->kode_jalan;
	}
 
 	function setKodeJalan( $kode_jalan){
 		$this->kode_jalan=$kode_jalan;
	}
 
 	function getKodeRuas(){
 		 return  $this->kode_ruas;
	}
 
 	function setKodeRuas( $kode_ruas){
 		$this->kode_ruas=$kode_ruas;
	}
 
 	function getKodeGerbang(){
 		 return  $this->kode_gerbang;
	}
 
 	function setKodeGerbang( $kode_gerbang){
 		$this->kode_gerbang=$kode_gerbang;
	}
 
 	function getGerbang(){
 		 return  $this->gerbang;
	}
 
 	function setGerbang( $gerbang){
 		$this->gerbang=$gerbang;
	}
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','kode_cabang','kode_jalan','kode_ruas','kode_gerbang','gerbang');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setKodeCabang','setKodeJalan','setKodeRuas','setKodeGerbang','setGerbang');
 
	 return $fields;

	}

}

 class cabang_model{ 

 	var $rowid;
 	var $kode_cabang;
 	var $nama_cabang;
 	var $keterangan;
 	var $kode_propinsi;
 	var $main;
 	var $akses;
 	var $status_pengelola;
 	var $status;
 	var $isSave=false;
 
	function cabang_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getKodeCabang(){
 		 return  $this->kode_cabang;
	}
 
 	function setKodeCabang( $kode_cabang){
 		$this->kode_cabang=$kode_cabang;
	}
 
 	function getNamaCabang(){
 		 return  $this->nama_cabang;
	}
 
 	function setNamaCabang( $nama_cabang){
 		$this->nama_cabang=$nama_cabang;
	}
 
 	function getKeterangan(){
 		 return  $this->keterangan;
	}
 
 	function setKeterangan( $keterangan){
 		$this->keterangan=$keterangan;
	}
 
 	function getKodePropinsi(){
 		 return  $this->kode_propinsi;
	}
 
 	function setKodePropinsi( $kode_propinsi){
 		$this->kode_propinsi=$kode_propinsi;
	}
 
 	function getMain(){
 		 return  $this->main;
	}
 
 	function setMain( $main){
 		$this->main=$main;
	}
 
 	function getAkses(){
 		 return  $this->akses;
	}
 
 	function setAkses( $akses){
 		$this->akses=$akses;
	}
 
 	function getStatusPengelola(){
 		 return  $this->status_pengelola;
	}
 
 	function setStatusPengelola( $status_pengelola){
 		$this->status_pengelola=$status_pengelola;
	}
 
 	function getStatus(){
 		 return  $this->status;
	}
 
 	function setStatus( $status){
 		$this->status=$status;
	}
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','kode_cabang','nama_cabang','keterangan','kode_propinsi','main','akses','status_pengelola','status');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setKodeCabang','setNamaCabang','setKeterangan','setKodePropinsi','setMain','setAkses','setStatusPengelola','setStatus');
 
	 return $fields;

	}

}

 class progres_tahapan_model{ 

 	var $rowid;
 	var $kode_proyek;
 	var $kode_tahapan;
 	var $kode_kerjasama;
 	var $kategori1;
 	var $kategori2;
 	var $tahun;
 	var $bulan;
 	var $nilai;
 	var $isSave=false;
 
	function progres_tahapan_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getKodeProyek(){
 		 return  $this->kode_proyek;
	}
 
 	function setKodeProyek( $kode_proyek){
 		$this->kode_proyek=$kode_proyek;
	}
 
 	function getKodeTahapan(){
 		 return  $this->kode_tahapan;
	}
 
 	function setKodeTahapan( $kode_tahapan){
 		$this->kode_tahapan=$kode_tahapan;
	}
 
 	function getKodeKerjasama(){
 		 return  $this->kode_kerjasama;
	}
 
 	function setKodeKerjasama( $kode_kerjasama){
 		$this->kode_kerjasama=$kode_kerjasama;
	}
 
 	function getKategori1(){
 		 return  $this->kategori1;
	}
 
 	function setKategori1( $kategori1){
 		$this->kategori1=$kategori1;
	}
 
 	function getKategori2(){
 		 return  $this->kategori2;
	}
 
 	function setKategori2( $kategori2){
 		$this->kategori2=$kategori2;
	}
 
 	function getTahun(){
 		 return  $this->tahun;
	}
 
 	function setTahun( $tahun){
 		$this->tahun=$tahun;
	}
 
 	function getBulan(){
 		 return  $this->bulan;
	}
 
 	function setBulan( $bulan){
 		$this->bulan=$bulan;
	}
 
 	function getNilai(){
 		 return  $this->nilai;
	}
 
 	function setNilai( $nilai){
 		$this->nilai=$nilai;
	}
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','kode_proyek','kode_tahapan','kode_kerjasama','kategori1','kategori2','tahun','bulan','nilai');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setKodeProyek','setKodeTahapan','setKodeKerjasama','setKategori1','setKategori2','setTahun','setBulan','setNilai');
 
	 return $fields;

	}

}

 class proyek_tahapan_model{ 

 	var $rowid;
 	var $kode_proyek;
 	var $kode_tahapan;
 	var $kode_kerjasama;
 	var $kategori1;
 	var $kategori2;
 	var $rencana;
 	var $selesai;
 	var $isSave=false;
 
	function proyek_tahapan_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getKodeProyek(){
 		 return  $this->kode_proyek;
	}
 
 	function setKodeProyek( $kode_proyek){
 		$this->kode_proyek=$kode_proyek;
	}
 
 	function getKodeTahapan(){
 		 return  $this->kode_tahapan;
	}
 
 	function setKodeTahapan( $kode_tahapan){
 		$this->kode_tahapan=$kode_tahapan;
	}
 
 	function getKodeKerjasama(){
 		 return  $this->kode_kerjasama;
	}
 
 	function setKodeKerjasama( $kode_kerjasama){
 		$this->kode_kerjasama=$kode_kerjasama;
	}
 
 	function getKategori1(){
 		 return  $this->kategori1;
	}
 
 	function setKategori1( $kategori1){
 		$this->kategori1=$kategori1;
	}
 
 	function getKategori2(){
 		 return  $this->kategori2;
	}
 
 	function setKategori2( $kategori2){
 		$this->kategori2=$kategori2;
	}
 
 	function getRencana(){
 		 return  $this->rencana;
	}
 
 	function setRencana( $rencana){
 		$this->rencana=$rencana;
	}
 
 	function getSelesai(){
 		 return  $this->selesai;
	}
 
 	function setSelesai( $selesai){
 		$this->selesai=$selesai;
	}
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','kode_proyek','kode_tahapan','kode_kerjasama','kategori1','kategori2','rencana','selesai');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setKodeProyek','setKodeTahapan','setKodeKerjasama','setKategori1','setKategori2','setRencana','setSelesai');
 
	 return $fields;

	}

}

class proyek_model{ 

 	var $rowid;
 	var $kode_proyek;
 	var $kode_cabang;
 	var $nama;
 	var $deskripsi;
 	var $kode_kerjasama;
 	var $keterangan_seksi;
 	var $panjang;
 	var $investor;
 	var $masa_konsesi;
 	var $masa_konstruksi;
 	var $biaya_investigasi;
 	var $personil;
 	var $permasalahan;
 	var $tindak_lanjut;
 	var $pm_jabatan;
 	var $pm_nama;
 	var $pengelola;
 	var $status_tahap;
 	var $status;
 	var $isSave=false;
 
	function proyek_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getKodeProyek(){
 		 return  $this->kode_proyek;
	}
 
 	function setKodeProyek( $kode_proyek){
 		$this->kode_proyek=$kode_proyek;
	}
 
 	function getKodeCabang(){
 		 return  $this->kode_cabang;
	}
 
 	function setKodeCabang( $kode_cabang){
 		$this->kode_cabang=$kode_cabang;
	}
 
 	function getNama(){
 		 return  $this->nama;
	}
 
 	function setNama( $nama){
 		$this->nama=$nama;
	}
 
 	function getDeskripsi(){
 		 return  $this->deskripsi;
	}
 
 	function setDeskripsi( $deskripsi){
 		$this->deskripsi=$deskripsi;
	}
 
 	function getKodeKerjasama(){
 		 return  $this->kode_kerjasama;
	}
 
 	function setKodeKerjasama( $kode_kerjasama){
 		$this->kode_kerjasama=$kode_kerjasama;
	}
 
 	function getKeteranganSeksi(){
 		 return  $this->keterangan_seksi;
	}
 
 	function setKeteranganSeksi( $keterangan_seksi){
 		$this->keterangan_seksi=$keterangan_seksi;
	}
 
 	function getPanjang(){
 		 return  $this->panjang;
	}
 
 	function setPanjang( $panjang){
 		$this->panjang=$panjang;
	}
 
 	function getInvestor(){
 		 return  $this->investor;
	}
 
 	function setInvestor( $investor){
 		$this->investor=$investor;
	}
 
 	function getMasaKonsesi(){
 		 return  $this->masa_konsesi;
	}
 
 	function setMasaKonsesi( $masa_konsesi){
 		$this->masa_konsesi=$masa_konsesi;
	}
 
 	function getMasaKonstruksi(){
 		 return  $this->masa_konstruksi;
	}
 
 	function setMasaKonstruksi( $masa_konstruksi){
 		$this->masa_konstruksi=$masa_konstruksi;
	}
 
 	function getBiayaInvestigasi(){
 		 return  $this->biaya_investigasi;
	}
 
 	function setBiayaInvestigasi( $biaya_investigasi){
 		$this->biaya_investigasi=$biaya_investigasi;
	}
 
 	function getPersonil(){
 		 return  $this->personil;
	}
 
 	function setPersonil( $personil){
 		$this->personil=$personil;
	}
 
 	function getPermasalahan(){
 		 return  $this->permasalahan;
	}
 
 	function setPermasalahan( $permasalahan){
 		$this->permasalahan=$permasalahan;
	}
 
 	function getTindakLanjut(){
 		 return  $this->tindak_lanjut;
	}
 
 	function setTindakLanjut( $tindak_lanjut){
 		$this->tindak_lanjut=$tindak_lanjut;
	}
 
 	function getPmJabatan(){
 		 return  $this->pm_jabatan;
	}
 
 	function setPmJabatan( $pm_jabatan){
 		$this->pm_jabatan=$pm_jabatan;
	}
 
 	function getPmNama(){
 		 return  $this->pm_nama;
	}
 
 	function setPmNama( $pm_nama){
 		$this->pm_nama=$pm_nama;
	}
 
 	function getPengelola(){
 		 return  $this->pengelola;
	}
 
 	function setPengelola( $pengelola){
 		$this->pengelola=$pengelola;
	}
 
 	function getStatusTahap(){
 		 return  $this->status_tahap;
	}
 
 	function setStatusTahap( $status_tahap){
 		$this->status_tahap=$status_tahap;
	}
 
 	function getStatus(){
 		 return  $this->status;
	}
 
 	function setStatus( $status){
 		$this->status=$status;
	}
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','kode_proyek','kode_cabang','nama','deskripsi','kode_kerjasama','keterangan_seksi','panjang','investor','masa_konsesi','masa_konstruksi','biaya_investigasi','personil','permasalahan','tindak_lanjut','pm_jabatan','pm_nama','pengelola','status_tahap','status');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setKodeProyek','setKodeCabang','setNama','setDeskripsi','setKodeKerjasama','setKeteranganSeksi','setPanjang','setInvestor','setMasaKonsesi','setMasaKonstruksi','setBiayaInvestigasi','setPersonil','setPermasalahan','setTindakLanjut','setPmJabatan','setPmNama','setPengelola','setStatusTahap','setStatus');
 
	 return $fields;

	}

}

class volume_lalulintas_rencana_model{ 

 	var $rowid;
 	var $kode_cabang;
 	var $tahun_op;
 	var $bulan_op;
 	var $total;
 	var $isSave=false;
 
	function volume_lalulintas_rencana_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getKodeCabang(){
 		 return  $this->kode_cabang;
	}
 
 	function setKodeCabang( $kode_cabang){
 		$this->kode_cabang=$kode_cabang;
	}
 
 	function getTahunOp(){
 		 return  $this->tahun_op;
	}
 
 	function setTahunOp( $tahun_op){
 		$this->tahun_op=$tahun_op;
	}
 
 	function getBulanOp(){
 		 return  $this->bulan_op;
	}
 
 	function setBulanOp( $bulan_op){
 		$this->bulan_op=$bulan_op;
	}
 
 	function getTotal(){
 		 return  $this->total;
	}
 
 	function setTotal( $total){
 		$this->total=$total;
	}
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','kode_cabang','tahun_op','bulan_op','total');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setKodeCabang','setTahunOp','setBulanOp','setTotal');
 
	 return $fields;

	}

}

 class volume_lalulintas_realisasi_model{ 

 	var $rowid;
 	var $kode_cabang;
 	var $kode_jalan;
 	var $kode_ruas;
 	var $tahun_op;
 	var $bulan_op;
 	var $kode_gerbang_asal;
 	var $kode_gerbang_tujuan;
 	var $gol_I;
 	var $gol_IIA;
 	var $gol_IIB;
 	var $isSave=false;
 
	function volume_lalulintas_realisasi_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getKodeCabang(){
 		 return  $this->kode_cabang;
	}
 
 	function setKodeCabang( $kode_cabang){
 		$this->kode_cabang=$kode_cabang;
	}
 
 	function getKodeJalan(){
 		 return  $this->kode_jalan;
	}
 
 	function setKodeJalan( $kode_jalan){
 		$this->kode_jalan=$kode_jalan;
	}
 
 	function getKodeRuas(){
 		 return  $this->kode_ruas;
	}
 
 	function setKodeRuas( $kode_ruas){
 		$this->kode_ruas=$kode_ruas;
	}
 
 	function getTahunOp(){
 		 return  $this->tahun_op;
	}
 
 	function setTahunOp( $tahun_op){
 		$this->tahun_op=$tahun_op;
	}
 
 	function getBulanOp(){
 		 return  $this->bulan_op;
	}
 
 	function setBulanOp( $bulan_op){
 		$this->bulan_op=$bulan_op;
	}
 
 	function getKodeAsal(){
 		 return  $this->kode_gerbang_asal;
	}
 
 	function setKodeAsal( $kode_gerbang_asal){
 		$this->kode_gerbang_asal=$kode_gerbang_asal;
	}
 
 	function getKodeTujuan(){
 		 return  $this->kode_gerbang_tujuan;
	}
 
 	function setKodeTujuan( $kode_gerbang_tujuan){
 		$this->kode_gerbang_tujuan=$kode_gerbang_tujuan;
	}
 
 	function getGolI(){
 		 return  $this->gol_I;
	}
 
 	function setGolI( $gol_I){
 		$this->gol_I=$gol_I;
	}
 
 	function getGolIIA(){
 		 return  $this->gol_IIA;
	}
 
 	function setGolIIA( $gol_IIA){
 		$this->gol_IIA=$gol_IIA;
	}
 
 	function getGolIIB(){
 		 return  $this->gol_IIB;
	}
 
 	function setGolIIB( $gol_IIB){
 		$this->gol_IIB=$gol_IIB;
	}
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','kode_cabang','kode_jalan','kode_ruas','tahun_op','bulan_op','kode_gerbang_asal','kode_gerbang_tujuan','gol_I','gol_IIA','gol_IIB');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setKodeCabang','setKodeJalan','setKodeRuas','setTahunOp','setBulanOp','setKodeAsal','setKodeTujuan','setGolI','setGolIIA','setGolIIB');
 
	 return $fields;

	}

}

 class tipe_kerjasama_model{ 

 	var $rowid;
 	var $kode_kerjasama;
 	var $kerjasama;
 	var $isSave=false;
 
	function tipe_kerjasama_model(){ 
 
 	}
 
 	function getRowid(){
 		 return  $this->rowid;
	}
 
 	function setRowid( $rowid){
 		$this->rowid=$rowid;
	}
 
 	function getKodeKerjasama(){
 		 return  $this->kode_kerjasama;
	}
 
 	function setKodeKerjasama( $kode_kerjasama){
 		$this->kode_kerjasama=$kode_kerjasama;
	}
 
 	function getKerjasama(){
 		 return  $this->kerjasama;
	}
 
 	function setKerjasama( $kerjasama){
 		$this->kerjasama=$kerjasama;
	}
 
 	function getIsSave(){
 		 return  $this->isSave;
	}
 
 	function setIsSave( $isSave){
 		$this->isSave=$isSave;
	}
 
 
 	function getFields(){
 		 $fields=array('rowid','kode_kerjasama','kerjasama');
 
	 return $fields;

	}
 
 	function getFieldsMethod(){
 		 $fields=array('setRowid','setKodeKerjasama','setKerjasama');
 
	 return $fields;

	}

}

?>