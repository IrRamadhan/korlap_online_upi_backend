<?php
// 1. Berikan izin akses ke semua client (termasuk Flutter Web Anda)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// 2. Handle 'Preflight Request' (Sangat penting untuk Flutter Web!)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}


// 1. Hubungkan koneksi database dan file model Akun
require_once "../config/database.php";
require_once "../models/Akun.php";

// 2. Tangkap data JSON dari Flutter
$json_data = file_get_contents("php://input");
$data = json_decode($json_data);

// 3. Validasi input awal
if (empty($data->nomor_induk) || empty($data->password)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Nomor induk dan password wajib diisi."
    ]);
    exit();
}

// 4. Inisialisasi class Model Akun dengan memasukkan koneksi $connect
$akunModel = new Akun($connect);

// 5. Panggil fungsi dari model untuk mencari user
$user = $akunModel->cariBerdasarkanNomorInduk($data->nomor_induk);

// 6. Validasi hasil pencarian dari Model
if ($user != null) {
    
    // Verifikasi password (menggunakan password_verify jika di-hash)
    // Jika password di database berupa teks mentah, ganti dengan: if ($data->password === $user['password'])
    if ($data->password === $user['password']) {
        
        // LOGIN SUKSES
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Login berhasil.",
            "data" => [
                "id"          => $user['id'],
                "nomor_induk" => $user['nomor_induk'],
                "nama"        => $user['nama'],
                "role"        => $user['role'],
                "instansi"    => $user['instansi']
            ]
        ]);
        
    } else {
        http_response_code(401);
        echo json_encode([
            "status" => "error",
            "message" => "Password yang Anda masukkan salah."
        ]);
    }
} else {
    // Jika nomor induk tidak ditemukan di database
    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "message" => "Nomor induk tidak terdaftar."
    ]);
}
