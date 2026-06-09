<?php
// file: korlap_online_upi_backend/api/get_all_peminjaman.php

require_once "../config/database.php";
require_once "../models/Peminjaman.php";

// Inisialisasi koneksi database Anda
// Query untuk mengambil SEMUA pengajuan dari seluruh akun
$query = "SELECT 
            p.id, 
            p.id_ruangan, 
            p.status_pengajuan, 
            p.tanggal_peminjaman, 
            p.waktu_mulai_peminjaman, 
            p.waktu_akhir_peminjaman,
            r.nama_ruangan, 
            r.gedung, 
            r.lantai 
          FROM tb_peminjaman p
          JOIN tb_ruangan r ON p.id_ruangan = r.id
          ORDER BY p.id DESC"; // Urutkan dari pengajuan terbaru

$result = mysqli_query($connect, $query);
$history = array();

while ($row = mysqli_fetch_assoc($result)) {
    $history[] = $row;
}

$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/korlap_online_upi_backend/uploads/";

foreach ($history as &$item) {
    $item['url_doc_SIK'] = !empty($item['doc_SIK']) ? $base_url . $item['doc_SIK'] : null;
    $item['url_doc_SPM'] = !empty($item['doc_SPM']) ? $base_url . $item['doc_SPM'] : null;
}

// Kirim respon JSON sukses ke Flutter
http_response_code(200);
echo json_encode([
    "status" => "success",
    "message" => "Seluruh data pengajuan berhasil diambil.",
    "data" => $history
]);
?>