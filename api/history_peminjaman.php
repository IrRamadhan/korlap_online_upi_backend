<?php
// Hubungkan koneksi database dan model
require_once "../config/database.php";
require_once "../models/Peminjaman.php";

// Karena ini proses mengambil data (READ), kita gunakan method GET agar mudah di-test
// Contoh akses: http://localhost/korlap_online_upi_backend/api/history_peminjaman.php?id_akun=1
if (empty($_GET['id_akun'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Parameter id_akun tidak ditemukan."
    ]);
    exit();
}

$id_akun = intval($_GET['id_akun']);

// Inisialisasi Model Peminjaman
$peminjamanModel = new Peminjaman($connect);
$history = $peminjamanModel->getHistoryByUser($id_akun);

// Membuat otomatis Base URL untuk folder uploads Anda
// Hasilnya dinamis, contoh: http://localhost/korlap_online_upi_backend/uploads/
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/korlap_online_upi_backend/uploads/";

// Lakukan perulangan untuk mengubah nama file mentah menjadi URL dokumen yang siap klik
foreach ($history as &$item) {
    $item['url_doc_SIK']  = !empty($item['doc_SIK']) ? $base_url . $item['doc_SIK'] : null;
    $item['url_doc_SPM'] = !empty($item['doc_SPM']) ? $base_url . $item['doc_SPM'] : null;
}

// Kirim respon JSON sukses ke Flutter
http_response_code(200);
echo json_encode([
    "status" => "success",
    "message" => "Riwayat peminjaman berhasil diambil.",
    "data" => $history
]);