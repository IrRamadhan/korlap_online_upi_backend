<?php
require_once "../config/database.php";
require_once "../models/Akun.php";

// Tangkap input JSON dari Flutter
$json_data = file_get_contents("php://input");
$data = json_decode($json_data);

// 1. Validasi semua field wajib diisi
if (
    empty($data->nomor_induk) || 
    empty($data->password) || 
    empty($data->nama) || 
    empty($data->role) || 
    empty($data->instansi)
) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Semua data pendaftaran akun wajib diisi."
    ]);
    exit();
}

// 2. Enkripsi Password agar aman di database
$password_aman = password_hash($data->password, PASSWORD_BCRYPT);

// Susun data ke dalam array
$dataInput = [
    'nomor_induk' => $data->nomor_induk,
    'password'    => $password_aman,
    'nama'        => $data->nama,
    'role'        => $data->role, // Diisi 'admin' atau 'user' sesuai input
    'instansi'    => $data->instansi
];

// 3. Eksekusi melalui Model Akun
$akunModel = new Akun($connect);
$proses = $akunModel->registrasiAkun($dataInput);

// 4. Berikan respon balik ke Flutter berdasarkan hasil model
if ($proses === "success") {
    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Akun baru berhasil didaftarkan oleh Admin."
    ]);
} else if ($proses === "exists") {
    http_response_code(409); // 409 Conflict (Data sudah ada)
    echo json_encode([
        "status" => "error",
        "message" => "Gagal, nomor induk tersebut sudah terdaftar di sistem."
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Terjadi kesalahan server saat mendaftarkan akun."
    ]);
}