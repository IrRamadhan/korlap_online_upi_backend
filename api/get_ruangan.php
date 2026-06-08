<?php
// Hubungkan ke database.php
require_once "../config/database.php";

// Query untuk mengambil semua data ruangan dari tabel tb_ruangan
$query = "SELECT id, nama_ruangan, gedung, lantai, jenis_ruangan, kapasitas, status, keterangan FROM tb_ruangan ORDER BY gedung ASC";
$result = mysqli_query($connect, $query);

if ($result) {
    $ruangan_list = [];

    // Ambil data satu per satu dan masukkan ke dalam array
    while ($row = mysqli_fetch_assoc($result)) {
        $ruangan_list[] = $row;
    }

    // Kirim data ke Flutter dalam bentuk JSON
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Daftar ruangan berhasil diambil.",
        "data" => $ruangan_list
    ]);
} else {
    // Jika query gagal
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Gagal mengambil data ruangan dari database: " . mysqli_error($connect)
    ]);
}