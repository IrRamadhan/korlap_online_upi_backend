<?php
require_once "../config/database.php";
require_once "../models/Peminjaman.php";

$json_data = file_get_contents("php://input");
$data = json_decode($json_data);

if (empty($data->id_peminjaman) || empty($data->status_pengajuan)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "ID peminjaman dan status baru wajib diisi."
    ]);
    exit();
}

// Pastikan input status hanya 'diterima' atau 'ditolak'
if (!in_array($data->status_pengajuan, ['diterima', 'ditolak'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Status harus berupa 'diterima' atau 'ditolak'."
    ]);
    exit();
}

$id_peminjaman = intval($data->id_peminjaman);
$status_baru   = $data->status_pengajuan;
$catatan       = isset($data->catatan_petugas) ? $data->catatan_petugas : null;

$peminjamanModel = new Peminjaman($connect);

if ($peminjamanModel->prosesPengajuan($id_peminjaman, $status_baru, $catatan)) {
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Status pengajuan peminjaman berhasil diperbarui menjadi " . $status_baru
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Gagal memperbarui status pengajuan."
    ]);
}