<?php
require_once "../config/database.php";
require_once "../models/Peminjaman.php";

$peminjamanModel = new Peminjaman($connect);
$list_pengajuan = $peminjamanModel->getAllPengajuan();

$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/korlap_online_upi_backend/uploads/";

foreach ($list_pengajuan as &$item) {
    // Menyesuaikan dengan kolom doc_SIK Anda
    $item['url_doc_SIK'] = !empty($item['doc_SIK']) ? $base_url . $item['doc_SIK'] : null;
    $item['url_doc_SPM'] = !empty($item['doc_SPM']) ? $base_url . $item['doc_SPM'] : null;
}

http_response_code(200);
echo json_encode([
    "status" => "success",
    "message" => "Semua data peminjaman berhasil diambil.",
    "data" => $list_pengajuan
]);