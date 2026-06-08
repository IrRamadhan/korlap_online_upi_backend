<?php
// 1. Header wajib agar Flutter bisa mengakses backend ini
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 2. Kredensial database (Sesuaikan dengan nama database Anda)
$host     = "localhost";
$username = "root";
$password = "";
$database = "korlap_upi";

// 3. Membuat koneksi menggunakan mysqli
$connect = mysqli_connect($host, $username, $password, $database);

// 4. Cek apakah koneksi berhasil atau gagal
if (!$connect) {
    // Jika gagal konek, kirim status error 500 ke Flutter berupa JSON
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "Koneksi database gagal: " . mysqli_connect_error()
    ]);
    exit(); // Hentikan proses program
}

// Koneksi berhasil, variabel $connect siap digunakan di file api Anda