<?php
// rapor.php - Dokumen utama yang menggabungkan semua halaman
$nama_siswa = "AMELINA CAHAYA PURNAMA";
$nisn = "0083275807";
$kelas = "X AKL";
$versi = "v.7.0.6";
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rapor Peserta Didik</title>
    <style>
        /* Gaya untuk semua halaman */
        @page {
            size: A4;
            margin: 0;
        }

        body {
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            margin: 0;
            padding: 0;
        }

        .page {
            width: 210mm;
            height: 297mm;
            page-break-after: always;
            position: relative;
        }

        /* Gaya khusus untuk cover */
        .cover {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .cover .container {
            width: 190mm;
            height: 277mm;
            border: 5px solid #6a5acd;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-sizing: border-box;
            padding-top: 50px;
        }

        .cover .logo {
            width: 120px;
            margin-bottom: 100px;
        }

        .cover h1 {
            font-size: 16px;
            text-transform: uppercase;
            line-height: 0.5;
            margin: 0 0 15px 0;
            font-weight: normal;
        }

        .cover .subtitle {
            font-size: 16px;
            margin: 0;
            text-transform: uppercase;
            text-align: center;
            line-height: 2;
            font-weight: normal;
            margin-bottom: 100px;
        }

        .cover .label {
            margin: 30px 0 5px;
            font-weight: normal;
        }

        .cover .box {
            border: 1px solid #000;
            padding: 10px 20px;
            display: inline-block;
            min-width: 300px;
            text-align: center;
            font-size: 16px;
            font-weight: normal;
            margin-bottom: 50px;
        }

        .cover p {
            margin-top: 180px;
            text-align: center;
            line-height: 1.5;
            font-weight: normal;
        }

        /* Gaya untuk footer semua halaman */
        .footer {
            position: absolute;
            bottom: 0;
            left: 30px;
            right: 30px;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            padding-bottom: 10px;
        }

        /* Gaya untuk halaman 1 */
        .halaman1 {
            padding: 0mm 30mm 30mm 30mm;
        }

        .halaman1 h1 {
            text-align: center;
            font-size: 16px;
            line-height: 1.5;
            font-weight: normal;
            margin-bottom: 40px;
        }

        .halaman1 .info {
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-size: 16px;
            line-height: 4;
        }

        .halaman1 .info div {
            display: flex;
        }

        .halaman1 .label {
            min-width: 50mm;
        }

        /* Gaya untuk halaman 2 */
        .halaman2 {
            padding: 10mm 30mm 30mm 30mm;
            font-size: 12px;
        }

        .halaman2 .title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 60px;
            font-size: 20px;
        }

        .halaman2 .content table {
            width: 100%;
            border-collapse: collapse;
        }

        .halaman2 .content td {
            vertical-align: top;
            padding-bottom: 5px;
        }

        .halaman2 .content td:first-child {
            width: 40%;
            padding-right: 10px;
        }

        .halaman2 .content td:nth-child(2) {
            width: 60%;
            padding-left: 20px;
            position: relative;
        }

        .halaman2 .content td:nth-child(2)::before {
            content: ":";
            position: absolute;
            left: 0;
        }

        .halaman2 .photo-box {
            width: 3cm;
            height: 4cm;
            border: 1px solid black;
            text-align: center;
            line-height: 4cm;
            position: absolute;
            top: 230mm;
            right: 130mm;
        }

        .halaman2 .signature-box {
            position: absolute;
            top: 230mm;
            left: 100mm;
        }

        /* Gaya untuk halaman 3 */
        .halaman3 {
            padding: 10mm 30mm 30mm 30mm;
            font-size: 12px;
        }

        .halaman3 .header .info {
            display: flex;
            justify-content: space-between;
        }

        .halaman3 .header .info .column {
            flex-basis: 48%;
        }

        .halaman3 .header .info .column:last-child {
            margin-left: 50px;
        }

        .halaman3 .header .info .row {
            display: flex;
            align-items: baseline;
            margin-bottom: 10px;
        }

        .halaman3 .header .info .row .label {
            flex-basis: 150px;
            text-align: left;
            white-space: nowrap;
            position: relative;
            padding-right: 10px;
        }

        .halaman3 .header .info .row .label::after {
            content: ":";
            position: absolute;
            right: 10px;
        }

        .halaman3 .header .info .column:first-child .row .label {
            flex-basis: 110px;
        }

        .halaman3 .header .info .column:first-child .row .label::after {
            right: 0;
        }

        .halaman3 table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .halaman3 th,
        .halaman3 td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        .halaman3 th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        /* Gaya untuk halaman 4 */
        .halaman4 {
            padding: 10mm 30mm 30mm 30mm;
            font-size: 12px;
        }

        .halaman4 .content {
            line-height: 1.5;
        }

        .halaman4 .info-row {
            position: relative;
            padding-left: 150px;
            margin-bottom: 5px;
        }

        .halaman4 .info-row .label {
            position: absolute;
            left: 0;
            width: 140px;
            text-align: left;
            font-weight: bold;
        }

        .halaman4 .info-row .value {
            margin-left: 10px;
        }

        .halaman4 .info-row .label::after {
            content: ":";
            position: absolute;
            right: -20px;
        }

        .halaman4 table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .halaman4 th,
        .halaman4 td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        .halaman4 th {
            background-color: #f2f2f2;
        }

        .halaman4 .signature-section {
            margin-top: 50px;
        }

        .halaman4 .signature-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .halaman4 .signature {
            text-align: left;
            width: 45%;
        }

        .halaman4 .signature p {
            margin: 5px 0;
        }

        .halaman4 .kepala-sekolah {
            text-align: center;
            margin-top: 50px;
            line-height: 1.4;
        }

        .halaman4 .signature.wali-kelas {
            margin-left: 40%;
        }

        .halaman4 .signature p.nama,
        .halaman4 .kepala-sekolah p.nama {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Halaman Cover -->
    <div class="page cover">
        <div class="container">
            <img src="images/Logo Pelita.png" alt="Logo SMK" class="logo">

            <h1>Rapor Peserta Didik</h1>
            <div class="subtitle">
                Sekolah Menengah Kejuruan<br>(SMK)
            </div>

            <div class="label">Nama Peserta Didik:</div>
            <div class="box"><?php echo $nama_siswa; ?></div>

            <div class="label">NISN:</div>
            <div class="box"><?php echo $nisn; ?></div>

            <p>KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET DAN TEKNOLOGI<br>REPUBLIK INDONESIA</p>
        </div>

        <div class="footer">
            <span><?php echo "$nama_siswa - $kelas"; ?></span>
            <span>Dicetak dari <?php echo $versi; ?></span>
        </div>
    </div>

    <!-- Halaman 1 -->
    <div class="page halaman1">
        <div class="content">
            <h1>RAPOR PESERTA DIDIK<br>SEKOLAH MENENGAH KEJURUAN<br>(SMK)</h1>

            <div class="info">
                <div><span class="label">Nama Sekolah</span> : SMKN 2 BENGKALIS</div>
                <div><span class="label">NPSN / NSS</span> : 10495329 / 341090201002</div>
                <div><span class="label">Alamat</span> : ASSALAM<br>&nbsp;Kode Pos 28751 Telp. 076621952</div>
                <div><span class="label">Kelurahan</span> : Kelapa Pati</div>
                <div><span class="label">Kecamatan</span> : Bengkalis</div>
                <div><span class="label">Kabupaten/Kota</span> : Bengkalis</div>
                <div><span class="label">Provinsi</span> : Riau</div>
                <div><span class="label">Website</span> : http://smkn2bengkalis.sch.id</div>
                <div><span class="label">Email</span> : smkn2bengkalis@yahoo.co.id</div>
            </div>
        </div>

        <div class="footer">
            <span><?php echo "$nama_siswa - $kelas"; ?></span>
            <span>2</span>
            <span>Dicetak dari <?php echo $versi; ?></span>
        </div>
    </div>

    <!-- Halaman 2 -->
    <div class="page halaman2">
        <div class="container">
            <div class="title">KETERANGAN TENTANG DIRI PESERTA DIDIK</div>
            <div class="content">
                <table>
                    <tr>
                        <td>1. Nama Peserta Didik (Lengkap)</td>
                        <td><?php echo $nama_siswa; ?></td>
                    </tr>
                    <tr>
                        <td>2. Nomor Induk/NISN</td>
                        <td>083055224 / <?php echo $nisn; ?></td>
                    </tr>
                    <tr>
                        <td>3. Tempat, Tanggal Lahir</td>
                        <td>Sidomulyo, 05 Agustus 2009</td>
                    </tr>
                    <tr>
                        <td>4. Jenis Kelamin</td>
                        <td>Perempuan</td>
                    </tr>
                    <tr>
                        <td>5. Agama</td>
                        <td>Islam</td>
                    </tr>
                    <tr>
                        <td>6. Status dalam Keluarga</td>
                        <td>Anak Kandung</td>
                    </tr>
                    <tr>
                        <td>7. Anak Ke</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>8. Alamat Peserta Didik</td>
                        <td>Jl. Panglima Minai RT 1 / RW 1, Senggoro Kec. Bengkalis Kab. Bengkalis 28751</td>
                    </tr>
                    <tr>
                        <td>9. Nomor Telepon Rumah</td>
                        <td>089623339246</td>
                    </tr>
                    <tr>
                        <td>10. Sekolah Asal</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td>11. Diterima di sekolah ini</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td> Di kelas</td>
                        <td><?php echo $kelas; ?></td>
                    </tr>
                    <tr>
                        <td> Pada tanggal</td>
                        <td>01 Juli 2024</td>
                    </tr>
                    <tr>
                        <td> Nama Orang Tua</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td> a. Ayah</td>
                        <td>HERRY IRAWA</td>
                    </tr>
                    <tr>
                        <td> b. Ibu</td>
                        <td>LENI MARLINA</td>
                    </tr>
                    <tr>
                        <td>12. Alamat Orang Tua</td>
                        <td>Jl. Panglima Minai RT 1 / RW 1, Senggoro Kec. Bengkalis Kab. Bengkalis 28751</td>
                    </tr>
                    <tr>
                        <td> Nomor Telepon Rumah</td>
                        <td>089623339246</td>
                    </tr>
                    <tr>
                        <td>13. Pekerjaan Orang Tua</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td> a. Ayah</td>
                        <td>Karyawan Swasta</td>
                    </tr>
                    <tr>
                        <td> b. Ibu</td>
                        <td>Tidak bekerja</td>
                    </tr>
                    <tr>
                        <td>14. Nama Wali Peserta Didik</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td>15. Alamat Wali Peserta Didik</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td> Nomor Telepon Rumah</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td>16. Pekerjaan Wali Peserta Didik</td>
                        <td>-</td>
                    </tr>
                </table>

                <!-- Pas Foto dan Tanda Tangan Kepala Sekolah -->
                <div class="photo-box">3x4</div>
                <div class="signature-box">
                    Bengkalis, 01 Juli 2024<br>
                    Kepala Sekolah<br><br><br>
                    JEFRI, S.Pd.I.<br>
                    NIP. 197503202008011009
                </div>
            </div>
        </div>

        <div class="footer">
            <span><?php echo "$nama_siswa - $kelas"; ?></span>
            <span>3</span>
            <span>Dicetak dari <?php echo $versi; ?></span>
        </div>
    </div>

    <!-- Halaman 3 -->
    <div class="page halaman3">
        <div class="header">
            <div class="info">
                <!-- Kolom Kiri -->
                <div class="column">
                    <div class="row">
                        <div class="label">Nama Peserta Didik</div>
                        <div class="value"><?php echo $nama_siswa; ?></div>
                    </div>
                    <div class="row">
                        <div class="label">Nomor Induk/NISN</div>
                        <div class="value">083055224 / <?php echo $nisn; ?></div>
                    </div>
                    <div class="row">
                        <div class="label">Sekolah</div>
                        <div class="value">SMKN 2 BENGKALIS</div>
                    </div>
                    <div class="row">
                        <div class="label">Alamat</div>
                        <div class="value">ASSALAM</div>
                    </div>
                </div>
                <!-- Kolom Kanan -->
                <div class="column">
                    <div class="row">
                        <div class="label">Kelas</div>
                        <div class="value"><?php echo $kelas; ?></div>
                    </div>
                    <div class="row">
                        <div class="label">Fase</div>
                        <div class="value">E</div>
                    </div>
                    <div class="row">
                        <div class="label">Semester</div>
                        <div class="value">Ganjil</div>
                    </div>
                    <div class="row">
                        <div class="label">Tahun Pelajaran</div>
                        <div class="value">2024/2025</div>
                    </div>
                </div>
            </div>
        </div>

        <h2>A. Nilai Akademik</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Mata Pelajaran</th>
                    <th>Nilai Akhir</th>
                    <th>Capaian Kompetensi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4"><strong>A. Kelompok Mata Pelajaran Umum</strong></td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Pendidikan Agama Islam dan Budi Pekerti</td>
                    <td>0</td>
                    <td></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Pendidikan Pancasila</td>
                    <td>0</td>
                    <td></td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Bahasa Indonesia</td>
                    <td>0</td>
                    <td></td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>Pendidikan Jasmani, Olahraga, dan Kesehatan</td>
                    <td>90</td>
                    <td>Menunjukkan penguasaan yang baik dalam proses dilakukan oleh Peserta didik berpikir dan bermain bola besar, mulai dari sepak bola, bola voli dan bola basket serta mengatur strategi bermain</td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>Sejarah</td>
                    <td>0</td>
                    <td></td>
                </tr>
                <tr>
                    <td>6</td>
                    <td>Seni Musik</td>
                    <td>0</td>
                    <td></td>
                </tr>
                <tr>
                    <td>7</td>
                    <td>Muatan Lokal Potensi Daerah</td>
                    <td>0</td>
                    <td></td>
                </tr>
                <tr>
                    <td>8</td>
                    <td>Project Penguatan Profil Pelajar Pancasila</td>
                    <td>0</td>
                    <td>Perlu ditingkatkan dalam menguatkan karakter siswa agar sesuai dengan nilai-nilai pancasila</td>
                </tr>
                <tr>
                    <td colspan="4"><strong>B. Kelompok Mata Pelajaran Kejuruan</strong></td>
                </tr>
                <tr>
                    <td>9</td>
                    <td>Matematika (Umum)</td>
                    <td>0</td>
                    <td></td>
                </tr>
                <tr>
                    <td>10</td>
                    <td>Bahasa Inggris</td>
                    <td>0</td>
                    <td></td>
                </tr>
                <tr>
                    <td>11</td>
                    <td>Informatika</td>
                    <td>0</td>
                    <td></td>
                </tr>
                <tr>
                    <td>12</td>
                    <td>Projek IPAS</td>
                    <td>0</td>
                    <td></td>
                </tr>
                <tr>
                    <td>13</td>
                    <td>Dasar Dasar Akuntansi dan Keuangan Lembaga</td>
                    <td>0</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <span><?php echo "$nama_siswa - $kelas"; ?></span>
            <span>4</span>
            <span>Diterbitkan dari e-rapor SMK <?php echo $versi; ?></span>
        </div>
    </div>

    <!-- Halaman 4 -->
    <div class="page halaman4">
        <div class="content">
            <div class="info-row">
                <span class="label">Nama Peserta Didik</span>
                <span class="value"><?php echo $nama_siswa; ?></span>
            </div>
            <div class="info-row">
                <span class="label">Nomor Induk/NISN</span>
                <span class="value">083055224 / <?php echo $nisn; ?></span>
            </div>
            <div class="info-row">
                <span class="label">Kelas</span>
                <span class="value"><?php echo $kelas; ?></span>
            </div>
            <div class="info-row">
                <span class="label">Tahun Pelajaran</span>
                <span class="value">2024/2025</span>
            </div>
            <div class="info-row">
                <span class="label">Semester</span>
                <span class="value">Ganjil</span>
            </div>

            <h3>B. Ekstrakurikuler</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kegiatan Ekstrakurikuler</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>[Nama Kegiatan]</td>
                        <td>[Keterangan]</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>[Nama Kegiatan]</td>
                        <td>[Keterangan]</td>
                    </tr>
                </tbody>
            </table>

            <h3>C. Ketidakhadiran</h3>

            <table>
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Jumlah Hari</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Sakit</td>
                        <td>0 hari</td>
                    </tr>

                    <tr>
                        <td>Izin</td>
                        <td>0 hari</td>
                    </tr>

                    <tr>
                        <td>Tanpa Keterangan</td>
                        <td>0 hari</td>
                    </tr>
                </tbody>
            </table>

            <div class="signature-section">
                <div class="signature-container">
                    <div class="signature">
                        <p>Orang Tua/Wali</p><br /><br /><br />
                        <p>.....................................................</p><br />
                        <p>(Tanda Tangan)</p>
                    </div>

                    <div class="signature wali-kelas">
                        <p>Bengkalis, 20 Desember 2024</p>
                        <p>Wali Kelas</p><br /><br /><br />
                        <p class="nama">RAMZINUR EFENDI, S. E. I.</p>
                        <p>NIP. 198402152023211009</p>
                    </div>
                </div>

                <div class="kepala-sekolah">
                    <p>Kepala Sekolah</p><br /><br /><br />
                    <p class="nama">JEFRI, S.Pd.I.</p>
                    <p>NIP. 197503202008011009</p>
                </div>
            </div>

            <div class="footer">
                <span><?php echo "$nama_siswa - $kelas"; ?></span>
                <span>5</span>
                <span>Diterbitkan dari e-rapor SMK <?php echo $versi; ?></span>
            </div>
        </div>
    </div>
</body>

</html>