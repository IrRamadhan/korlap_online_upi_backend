<?php
// korlap_online_upi_backend/api/get_detail_peminjaman.php
require_once "../config/database.php";

if (empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "ID tidak ditemukan."]);
    exit();
}

$id = intval($_GET['id']);

// 💡 SELEKSI DIBAWAH INI DISESUAIKAN DENGAN KEY DI PEMINJAMAN_MODEL.DART
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
          WHERE p.id = $id 
          LIMIT 1";

$result = mysqli_query($connect, $query);

if ($row = mysqli_fetch_assoc($result)) {
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "data" => $row
    ]);
} else {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Data tidak ditemukan."]);
}
?>