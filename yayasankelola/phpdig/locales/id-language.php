<?php
/*
--------------------------------------------------------------------------------
PhpDig Version 1.8.x
This program is provided under the GNU/GPL license.
See the LICENSE file for more information.
All contributors are listed in the CREDITS file provided with this package.
PhpDig Website : http://www.phpdig.net/
--------------------------------------------------------------------------------
*/

//Indonesian messages for PhpDig
//by Dody Suria Wijaya <dodysw@gmail.com>
//'keyword' => 'translation'
$phpdig_mess = array (
'upd_sites'    =>'Perbarui situs',
'upd2'         =>'Pembaruan Selesai',
'links_per'    =>'Link setiap',
'yes'          =>'ya',
'no'           =>'tidak',
'delete'       =>'hapus',
'reindex'      =>'Indeks ulang',
'back'         =>'Mundur',
'files'        =>'arsip',
'admin'        =>'Administrasi',
'warning'      =>'Awas !',
'index_uri'    =>'URI mana yang akan anda indeks?',
'spider_depth' =>'Kedalaman pencarian',
'spider_warn'  =>"Mohon pastikan tidak ada orang lain yang meng-update situs yang sama.
Mekanisme penguncian akan dimasukkan pada versi lanjut.",
'site_update'  =>"Perbarui satu situs atau salah satu cabangnya",
'clean'        =>'Bersihkan',
't_index'      =>"indeks",
't_dic'        =>'kamus',
't_stopw'      =>'kata-kata umum',
't_dash'       =>'strip',

'update'       =>'Perbarui',
'exclude'      =>'Hapus dan kecualikan cabangnya',
'excludes'     =>'Kecualikan path',
'tree_found'   =>'Pohon ditemukan',
'update_mess'  =>'Indeks ulang atau hapus sebuah pohon ',
'update_warn'  =>"Pengecualian akan menghapus entri yang telah diindeks",
'update_help'  =>'Klik pada X untuk menghapus cabangnya
Klik pada tanda hijau untuk membaruinya
Klik pada tanda dilarang masuk untuk mengkecualikannya pada pengindeksan selanjutnya',
'branch_start' =>'Pilih folder untuk ditampilkan di bagian kiri',
'branch_help1' =>'Pilih dokument untuk diperbarui secara individual',
'branch_help2' =>'Klik pada X untuk menghapus dokumen
Klik pada tanda hijau untuk mengindeks ulang',
'redepth'      =>'level kedalaman',
'branch_warn'  =>"Penghapusan bersifat permanen",
'to_admin'     =>"ke antar muka admin",
'to_update'    =>"ke antar muka pembaruan",

'search'       =>'Pencarian',
'results'      =>'hasil',
'display'      =>'tampilkan',
'w_begin'      =>'operator dan',
'w_whole'      =>'frase persis',
'w_part'       =>'operator atau',
'alt_try'      =>'Maksud anda',

'limit_to'     =>'batasi pada',
'this_path'    =>'path ini',
'total'        =>'total',
'seconds'      =>'detik',
'w_common_sing'     =>'adalah kata yang sangat umum dan kami abaikan.',
'w_short_sing'      =>'adalah kata yang telalu pendek dan kami abaikan.',
'w_common_plur'     =>'adalah kata-kata yang sangat umum dan kami abaikan.',
'w_short_plur'      =>'adalah kata-kata yang terlalu pendek dan kami abaikan.',
's_results'    =>'hasil pencarian',
'previous'     =>'Sebelumnya',
'next'         =>'Berikutnya',
'on'           =>'untuk',

'id_start'     =>'Pengindeksan situs',
'id_end'       =>'Pengindeksan selesai !',
'id_recent'    =>'Telah baru saja diindeks',
'num_words'    =>'Num words',
'time'         =>'waktu',
'error'        =>'Kesalahan',
'no_spider'    =>'Spider tidak dijalankan',
'no_site'      =>'Tidak ada situs tersebut di database',
'no_temp'      =>'Tidak ada link di tabel sementara',
'no_toindex'   =>'Tidak ada konten yang diindeks',
'double'       =>'Duplikat dari dokument yang telah ada',

'spidering'    =>'Spidering sedang berjalan...',
'links_more'   =>'more new links',
'level'        =>'tingkatan',
'links_found'  =>'link ditemukan',
'define_ex'    =>'Definisikan pengecualian',
'index_all'    =>'index semua',

'end'          =>'akhir',
'no_query'     =>'Silakan isi field pencariannya',
'pwait'        =>'Silakan tunggu',
'statistics'   =>'Statistik',

// INSTALL
'slogan'   =>'Search engine terkecil di dunia : versi',
'installation'   =>'Instalasi',
'instructions' =>'Ketik parameter MySQL di sini. Sebutkan user yang telah ada yang mampu membuat database apabila anda memimilih untuk membuat atau memperbarui.',
'hostname'   =>'Hostname  :',
'port'   =>'Port (none = default) :',
'sock'   =>'Sock (none = default) :',
'user'   =>'User :',
'password'   =>'Password :',
'phpdigdatabase'   =>'PhpDig database :',
'tablesprefix'   =>'Prefik tabel :',
'instructions2'   =>'* optional. Gunakan huruf kecil, maksimum 16 karakter.',
'installdatabase'   =>'Instal database phpdig',
'error1'   =>'Tidak dapat menenukan template connexion. ',
'error2'   =>'Tidak dapat menulis template connexion. ',
'error3'   =>'Tidak dapat menemukan file init_db.sql. ',
'error4'   =>'Tidak dapat membuat tabel. ',
'error5'   =>'Tidak dapat menemukan semua file-file konfigurasi database. ',
'error6'   =>'Tidak dapat membuat database.<br />Pastikan hak yang dimiliki pengguna. ',
'error7'   =>'Tidak dapat tersambung ke database<br />Pastikan data-data koneksi. ',
'createdb' =>'Buat database',
'createtables' =>'Buat tabel-tabel saja',
'updatedb' =>'Perbarui database yang sudah ada',
'existingdb' =>'Hanya tulis parameter-parameter koneksi',
// CLEANUP_ENGINE
'cleaningindex'   =>'Membersihkan indeks',
'enginenotok'   =>' referensi indeks mengarah pada kata kunci yang tidak ada.',
'engineok'   =>'Mesin koheren.',
// CLEANUP_KEYWORDS
'cleaningdictionnary'   =>'Membersihkan kamus',
'keywordsok'   =>'Semua kata kunci ada di satu atau lebih halaman.',
'keywordsnotok'   =>' kata kunci yang tidak di satu halaman paling tidak.',
// CLEANUP_COMMON
'cleanupcommon' =>'Bersihkan kata-kata umum',
'cleanuptotal' =>'Total ',
'cleaned' =>' dibersihkan.',
'deletedfor' =>' dihapus untuk ',
// INDEX ADMIN
'digthis' =>'Gali ini !',
'databasestatus' =>'Status database',
'entries' =>' Entri ',
'updateform' =>'Form pembaruan',
'deletesite' =>'Hapus situs',
// SPIDER
'spiderresults' =>'Hasil spider',
// STATISTICS
'mostkeywords' =>'Kata kunci terbanyak',
'richestpages' =>'Halaman terkaya',
'mostterms'    =>'Frame pencarian terbanyak',
'largestresults'=>'Hasil terbesar',
'mostempty'     =>'Pencarian terbanyak menghasilkan hasil kosong',
'lastqueries'   =>'Query pencarian terakhir',
'lastclicks'   =>'Klik-klik pencarian terakhir',
'responsebyhour'=>'Waktu jawab dalam jam',
// UPDATE
'userpasschanged' =>'User/Password dirubah !',
'uri' =>'URI : ',
'change' =>'Ubah',
'root' =>'Root',
'pages' =>' halaman',
'locked' => 'Terkunci',
'unlock' => 'Buka kunci situs',
'onelock' => 'Satu situs dikunci, karena sedangg di-spider. Anda tidak bisa melakukannya sekarang',
// PHPDIG_FORM
'go' =>'Lakukan ...',
// SEARCH_FUNCTION
'noresults' =>'Tidak ada hasil'
);
?>