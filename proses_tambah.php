<?php

require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;

define('ORACLE_ACCESS_KEY', 'a70f1894bf90fd1e18a71ce0b6798ca1a01d5e8b');
define('ORACLE_SECRET_KEY', 'IIr2S3hEpad3SLswUNhmVGyq/qRELVOKxU5hGRpV0cU=');
define('ORACLE_REGION', 'ap-melbourne-1');
define('ORACLE_ENDPOINT', 'https://objectstorage.ap-melbourne-1.oraclecloud.com/p/vRJwMWOgtU27BSS9oHfOIHNUTkdXaAuNgS552vOM7dGzQ3iYDJToSv_pK5BwRXiz/n/axnlcng6z9ko/b/UTS2-vildanuramalia/o/');

// memanggil file koneksi.php untuk melakukan koneksi database
include 'koneksi.php';

	// membuat variabel untuk menampung data dari form
  $nama_produk   = $_POST['nama_produk'];
  $deskripsi     = $_POST['deskripsi'];
  $harga_beli    = $_POST['harga_beli'];
  $harga_jual    = $_POST['harga_jual'];
  $gambar_produk = $_FILES['gambar_produk']['name'];


//cek dulu jika ada gambar produk jalankan coding ini
if($gambar_produk != "") {
  $ekstensi_diperbolehkan = array('png','jpg'); //ekstensi file gambar yang bisa diupload 
  $x = explode('.', $gambar_produk); //memisahkan nama file dengan ekstensi yang diupload
  $ekstensi = strtolower(end($x));
  $file_tmp = $_FILES['gambar_produk']['tmp_name'];   
  $angka_acak     = rand(1,999);
  $nama_gambar_baru = $angka_acak.'-'.$gambar_produk; //menggabungkan angka acak dengan nama file sebenarnya
        if(in_array($ekstensi, $ekstensi_diperbolehkan) === true)  {     

                // UPLOAD TO OBJECT STORAGE
                $S3 = new S3Client([
                  'region'  => ORACLE_REGION,
                  'version' => 'latest',
                  'credentials' => [
                      'key'    => ORACLE_ACCESS_KEY,
                      'secret' => ORACLE_SECRET_KEY
                  ],
                  'bucket_endpoint' => true,
                  'endpoint' => ORACLE_ENDPOINT
              ]);

              $result = $S3->putObject([
                  'Bucket' => 'uts',
                  'Key' => $nama_gambar_baru,
                  'SourceFile' => $file_tmp,
                  'StorageClass' => 'REDUCED_REDUNDANCY',
              ]);
              $url = $result['ObjectURL'] . PHP_EOL;

                // move_uploaded_file($file_tmp, 'gambar/'.$nama_gambar_baru); //memindah file gambar ke folder gambar
                  // jalankan query INSERT untuk menambah data ke database pastikan sesuai urutan (id tidak perlu karena dibikin otomatis)
                  $query = "INSERT INTO produk (nama_produk, deskripsi, harga_beli, harga_jual, gambar_produk) VALUES ('$nama_produk', '$deskripsi', '$harga_beli', '$harga_jual', '$url')";
                  $result = mysqli_query($koneksi, $query);
                  // periska query apakah ada error
                  if(!$result){
                      die ("Query gagal dijalankan: ".mysqli_errno($koneksi).
                           " - ".mysqli_error($koneksi));
                  } else {
                    //tampil alert dan akan redirect ke halaman index.php
                    //silahkan ganti index.php sesuai halaman yang akan dituju
                    echo "<script>alert('Data berhasil ditambah.');window.location='index.php';</script>";
                  }

            } else {     
             //jika file ekstensi tidak jpg dan png maka alert ini yang tampil
                echo "<script>alert('Ekstensi gambar yang boleh hanya jpg atau png.');window.location='tambah_produk.php';</script>";
            }
} else {
   $query = "INSERT INTO produk (nama_produk, deskripsi, harga_beli, harga_jual, gambar_produk) VALUES ('$nama_produk', '$deskripsi', '$harga_beli', '$harga_jual', null)";
                  $result = mysqli_query($koneksi, $query);
                  // periska query apakah ada error
                  if(!$result){
                      die ("Query gagal dijalankan: ".mysqli_errno($koneksi).
                           " - ".mysqli_error($koneksi));
                  } else {
                    //tampil alert dan akan redirect ke halaman index.php
                    //silahkan ganti index.php sesuai halaman yang akan dituju
                    echo "<script>alert('Data berhasil ditambah.');window.location='index.php';</script>";
                  }
}

 

