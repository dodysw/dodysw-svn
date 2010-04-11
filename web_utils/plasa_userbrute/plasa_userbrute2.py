"""
http://login.plasa.com/daftar.php?tanggal=1&bulan=Jan&kelamin=L&alamat=-&kota=Bengkulu&pekerjaan=1&industri=1&bahasa=1&hobby=1&email_lain=7&afiliasi=1&tanya=1&textfield=Pernyataan+Kesepakatan+untuk+Pengguna+Email+PlasaCom+%0D%0A+%0D%0A+%0D%0ABacalah+pernyataan+dibawah+ini+dengan+baik+agar+mudah+untuk+dipahami+dan+dimengerti.+%0D%0ADengan+menekan+tombol+%5BDaftarkan%5D+berarti+anda+sepakat+dengan+kesepakatan+yang+dibuat+dengan+PlasaCOM+sebagaimana+berikut+%3A++%0D%0A%0D%0ABagi+Pengguna+%3A++%0D%0APendaftar%2Fpengguna+email+PlasaCOM+TIDAK+diperkenankan%3A+%0D%0A1.+Menggunakan+email+PlasaCOM+untuk+tujuan+yang+bertentangan+dengan+hukum+di+Indonesia+atapun+Internasional+%0D%0A2.+Menggunakan+email+PlasaCOM+untuk+mengirimkan+pesan-pesan+yang+berbau+SARA%2C+pornografi%2C+menakut-nakuti%2C+mengancam+seseorang%2C+membantu+penyebaran+virus+atau+email+yang+berisikan+rutin+program+yang+dapat+merusak+komputer+orang+lain.+%0D%0A3.+Menjual+kembali+email+PlasaCOM+ke+pihak+ketiga.+%0D%0A4.+Menberikan+Kata+Sandinya+ke+pihak+ketiga.+%0D%0A5.+Menggunakan+email+PlasaCOM+untuk+SPAMMING.+%0D%0A6.+Bersedia+diperlakukan+SAMA+tanpa+memandang+status+dan+kedudukan.+%0D%0A+%0D%0A%0D%0ABagi+PlasaCom+%3A+%0D%0A1.+PlasaCOM+menjamin+dan+menghormati+kerahasiaan+data+pribadi+setiap+anggota+dan+berhak+memberikan+informasi+kepada+pihak+ketiga+berdasarkan+persetujuan+pengguna.+%0D%0A2.+Kapasitas+Email+yang+disediakan+PlasaCOM+adalah+10+MegaBytes%2C+besar+kapasitas+email+dapat+berubah+sewaktu-waktu.+%0D%0A3.+PlasaCOM+TIDAK+bertanggung+jawab+atas+hilangannya+data%2Femail+yang+disimpan+pada+server+PlasaCOM.+%0D%0A4.+PlasaCOM+TIDAK+bertanggung+jawab+atas+kerusakan+perangkat+keras+dan+lunak+pelanggan+yang+diakibatkan+atas+penggunaan+fasilitas+PlasaCOM.+%0D%0A5.+PlasaCOM+BERHAK+menghapus+keanggotaan+email+JIKA+tidak+aktif+selama+3+bulan.+%0D%0A6.+PlasaCOM+BERHAK+mematikan+server+mail+untuk+perawatan+server+mail+sewaktu-waktu+tanpa+memberitahukan+terlebih+dahulu+ke+pengguna+email+PlasaCOM.+%0D%0A7.+PlasaCOM+BERHAK+untuk+menghentikan+layanan+email+gratis+ini+sewaktu-waktu+tanpa+memberitahukan+terlebih+dahulu+kepada+pengguna+email+PlasaCOM.+%0D%0A8.+PlasaCOM+DAPAT+menambah+kesepakatan+tanpa+merubah+inti+kesepakatan.+%0D%0A9.+PlasaCOM+BERHAK+untuk+menghapus+keanggotaan+mail+di+PlasaCOM+apabila+pengguna+tidak+mematuhi+kesepakatan+yang+tersebut+diatas.++%0D%0A&nama=x&tahunlahir=1980&telepon=123&userid=aa&password1=123&password2=123&jawab=1&masuk=oke&Signup=Daftarkan+
http://login.plasa.com/daftar.php?tanggal=1&bulan=Jan&kelamin=L&alamat=-&kota=Bengkulu&pekerjaan=1&industri=1&bahasa=1&hobby=1&email_lain=7&afiliasi=1&tanya=1&nama=x&tahunlahir=1980&telepon=123&userid=aa&password1=123&password2=123&jawab=1&masuk=oke&Signup=Daftarkan+
Kalao OK: "Klik Disini Untuk Login"
got:
    - 0f/123
    - e6/123123
    - i8,l6,l8,o5, 06, p8, y8, 0h, 0l, 0n, 0t, 0u, 0w, 0y, 1u /helloworld
    - 6-
"""
import urllib2,re,string,sys
#~ letters = string.digits + string.ascii_lowercase
password = 'helloworld'
letters1 = '--'
letters2 = '-'
startfrom =''
skipletter = True
for l1 in letters1:
    for l2 in letters2:
        userid = l1+l2
        if startfrom != '' and userid == startfrom:
                skipletter = False
        if startfrom != '' and skipletter:
            continue
        url = 'http://login.plasa.com/daftar.php?tanggal=1&bulan=Jan&kelamin=L&alamat=-&kota=Bengkulu&pekerjaan=1&industri=1&bahasa=1&hobby=1&email_lain=7&afiliasi=1&tanya=1&nama=x&tahunlahir=1980&telepon=123&userid=' + userid + '&password1='+ password + '&password2=' + password + '&jawab=1&masuk=oke&Signup=Daftarkan+'
        print 'Trying', userid,
        buffer = urllib2.urlopen(url).read()
        if re.search('Klik Disini Untuk Login',buffer):
            print 'GOT USER ', userid, '!! password:', password
            #~ sys.exit()
        else:
            print 'taken'
