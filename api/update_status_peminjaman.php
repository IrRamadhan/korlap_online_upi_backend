<?php
// korlap_online_upi_backend/api/get_peminjaman.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

require_once "../config/database.php";

if (empty($_GET['id_akun'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "ID Akun tidak ditemukan."]);
    exit();
}

$id_akun = intval($_GET['id_akun']);

// Mengambil data peminjaman digabung dengan info ruangan
$query = "SELECT 
            p.id, 
            p.id_ruangan, 
            p.status_pengajuan AS status_peminjaman, 
            p.tanggal_peminjaman AS tanggal, 
            p.waktu_mulai_peminjaman AS waktu_awal, 
            p.waktu_akhir_peminjaman AS waktu_selesai,
            r.nama_ruangan, 
            r.gedung, 
            r.lantai 
          FROM tb_peminjaman p
          JOIN tb_ruangan r ON p.id_ruangan = r.id
          WHERE p.id_akun = $id_akun
          ORDER BY p.id DESC";

$result = mysqli_query($connect, $query);
$data_peminjaman = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data_peminjaman[] = $row;
}

http_response_code(200);
echo json_encode([
    "status" => "success",
    "data" => $data_peminjaman
]);
?>