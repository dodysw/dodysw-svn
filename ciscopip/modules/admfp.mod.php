<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

class admfp {
    function admfp() {
        // initialize instance
        global $html_title;
        $this->title = 'Frontpage';
        $html_title = $this->title;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') { // validate user logon
            $this->post_handler();
        }
        $this->must_authenticated = True;
    }

    function go() {
        // called inside main content
        #~ global $appconf;
        #~ echo "<h1>{$appconf['site_title']}</h1>";
        #~ echo "<p>".$appconf['site_description'];

        if ($_SESSION['login_group'] == 'PA') {
            # START -- MASUKKAN INFO DI HOME KHUSUS UTK PARTNER
            echo <<< __END__
<p>Selamat datang di situs Cisco Partner Incentive Program. Situs ini adalah sebuah alat yang kami sediakan untuk mempermudah Anda partner kami dalam mengikuti program ini.
<p>Pada situs ini Anda akan dapat memasukkan informasi project yang Anda miliki serta membaca berita terkini perihal program PIP ini.
<p>Ikuti langkah berikut:
<ol>
<li>Merubah password<br>
    Anda dapat merubah password yang pertama Anda peroleh dari kami, dengan langkah berikut:
    <ul>
    <li>Klik My Profile
    <li>Klik Change Password
    <li>Masukkan password lama anda, dan password baru Anda
    <li>Save
    </ul>

<li>Mendaftarkan project<br>
    Anda dapat mulai mendaftarkan project Anda, dengan langkah berikut:
    <ul>
    <li>Klik Projects
    <li>Klik Register New Project
    <li>Isi semua data yang dibutuhkan dalam formulir
    <li>Preview, periksa semua data pastikan tidak ada yang salah
    <li>Sekiranya ada yang hendak anda rubah, maka klik tombol Back pada explorer Anda.
    <li>Sekiranya tidak ada yang hendak Anda rubah, klik save
    </ul>

<li>Baca berita
    <ul>
    <li>Klik News
    <li>Baca berita terkini dari Cisco PIP.
    </ul>
</ol>

<p>Sekiranya ada yang hendak Anda tanyakan untuk bantuan teknis, silahkan email ke:
<a href="mailto:Cisco-pip@ciscopartners.interactive.web.id">Cisco-pip@ciscopartners.interactive.web.id</a>

__END__;
            # END -- MASUKKAN INFO DI HOME KHUSUS UTK PARTNER
        }

        elseif ($_SESSION['login_group'] == 'DE') {
            # START -- MASUKKAN INFO DI HOME KHUSUS UTK DE
            echo <<< __END__
<p>Selamat datang di situs Cisco PIP. Silahkan mulai memasukkan data partner Cisco maupun berita seputar program PIP. Ikuti langkah berikut:
<ol>
<li>Memasukkan data partner Cisco:
    <ol type=a>
    <li>	Klik Partner's List untuk memasukkan data partner cisco
    <li>	Klik Add partner
    <li>	Isi formulir data yang tersedia
    <li>	Save
    </ol>
<li>Mengedit data partner Cisco:
    <ol type=a>
<li>	Klik partner's list
<li>	Klik pada partner ID yang hendak di edit
<li>	Klik pada menu drop down kegiatan yang hendak anda lakukan, Edit atau Delete atau Add partner.
<li>	Jika Anda pilih Edit, isi formulir dan save
<li>	Jika Anda pilih Delete, data akan langsung terhapus dari database
<li>	Jika Anda pilih New, formulir penambahan partner akan terbuka. Isi formulir dan save.
</ol>

<li>	Memasukkan berita
<ol type=a>
<li>	Klik Add News
<li>	Masukkan judul berita pada: Title
<li>	Masukkan rangkuman berita pada: Summary
<li>	Masukkan detil / isi berita pada: Body [jangan masukkan isi pada kolom path:body]
<li>	Save
</ol>
</ol>
__END__;
            # END -- MASUKKAN INFO DI HOME KHUSUS UTK DE
        }

        elseif ($_SESSION['login_group'] == 'PO') {
            # START -- MASUKKAN INFO DI HOME KHUSUS UTK PO
            echo <<< __END__
<p>Selamat datang di situs Cisco PIP. 
<p>Di situs ini Anda dapat melakukan accept/deny terhadap project yang disampaikan oleh para partner maupun juga melihat status project serta jumlah project yang telah ada hingga saat ini.
<p>
<p>Ikuti langkah berikut:
<ol>
<li>Untuk Accept/Deny Project
    <ol type=a>
    <li>	Klik Search project
    <li>	Masukkan no. registrasi project yang hendak Anda proses
    <li>	Setelah hasil search muncul, klik pada no. registrasi project yang hendak Anda proses.
    <li>	Pilih tindakan yang hendak Anda lakukan: Accept/Deny
    <li>	Accept => Pilih Sales person yang Anda tugaskan
    <li>	Deny => Pilih alasan penolakan
    <li>	Masukkan note tambahan sekiranya perlu. 
    <li>	Submit
    <li>	Engine akan mengirimkan email kepada partner yang bersangkutan.
    <li>	Engine akan MENGHAPUS DATA PROJECT SECARA PERMANEN    
    </ol>
    
<li>Untuk melihat jumlah project per partner
    <ol type=a>
<li>	Klik Projects Number
</ol>

<li>Untuk melihat status seluruh project
<ol type=a>
<li>	Klik project status
</ol>
</ol>
__END__;
            # END -- MASUKKAN INFO DI HOME KHUSUS UTK PO
        }        
        
        else {
            echo <<< __END__
        <p style="margin-left: 20; margin-right: 20"><br>The Cisco Premier Incentive
        Program is Cisco Indonesia Commercial Sales' most recent initiative
        designed to help the profitability and success of its premier partners.
        The Premier Incentive Program rewards premier partners who actively
        identify, develop, and win new business opportunities in Cisco
        Commercial market segments. This program incorporates deal registration
        that is designed to protect the premier partner's pre-sales investment
        and enable them to focus on value delivery to win the opportunity.
__END__;
        }

    }

    function post_handler() {
    }
}

?>
