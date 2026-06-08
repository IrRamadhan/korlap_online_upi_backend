<?php
// Hubungkan koneksi database dan model
require_once "../config/database.php";
require_once "../models/Peminjaman.php";

// Ingat: Karena Flutter mengirim file, kita gunakan $_POST dan $_FILES (bukan php://input)
if (
    empty($_POST['id_ruangan']) || 
    empty($_POST['id_akun']) || 
    empty($_POST['tanggal_peminjaman']) || 
    empty($_POST['waktu_mulai_peminjaman']) || 
    empty($_POST['waktu_akhir_peminjaman'])
) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Data peminjaman tidak lengkap."
    ]);
    exit();
}

// 1. Proses Upload File Dokumen (jika ada)
$target_dir = "../uploads/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$doc_SIK_name  = null;
$doc_SPM_name = null;

// Proses file doc_SIK
if (!empty($_FILES['doc_SIK']['name'])) {
    $ext_SIK = pathinfo($_FILES['doc_SIK']['name'], PATHINFO_EXTENSION);
    $doc_SIK_name = "SIK_" . time() . "_" . uniqid() . "." . $ext_SIK;
    move_uploaded_file($_FILES['doc_SIK']['tmp_name'], $target_dir . $doc_SIK_name);
}

// Proses file doc_SPM
if (!empty($_FILES['doc_SPM']['name'])) {
    $ext_SPM = pathinfo($_FILES['doc_SPM']['name'], PATHINFO_EXTENSION);
    $doc_SPM_name = "SPM_" . time() . "_" . uniqid() . "." . $ext_SPM;
    move_uploaded_file($_FILES['doc_SPM']['tmp_name'], $target_dir . $doc_SPM_name);
}

// 2. Bungkus semua data ke dalam satu array untuk dikirim ke Model
$dataInput = [
    'id_ruangan'             => intval($_POST['id_ruangan']),
    'id_akun'                => intval($_POST['id_akun']),
    'tanggal_peminjaman'     => $_POST['tanggal_peminjaman'],
    'waktu_mulai_peminjaman' => $_POST['waktu_mulai_peminjaman'],
    'waktu_akhir_peminjaman' => $_POST['waktu_akhir_peminjaman'],
    'keperluan'              => isset($_POST['keperluan']) ? $_POST['keperluan'] : null,
    'doc_SIK'                 => $doc_SIK_name,
    'doc_SPM'                => $doc_SPM_name
];

// 3. Panggil Model Peminjaman untuk eksekusi ke database
$peminjamanModel = new Peminjaman($connect);

if ($peminjamanModel->buatPengajuan($dataInput)) {
    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Pengajuan peminjaman berhasil diproses."
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Gagal menyimpan data pengajuan ke database."
    ]);
}