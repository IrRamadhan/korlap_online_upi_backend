<?php
// 1. Panggil konfigurasi database terlebih dahulu
require_once "../config/database.php";

// 2. 💡 JALUR AMAN: Hapus header CORS bawaan dari database.php agar tidak duplikat/bentrok
header_remove("Access-Control-Allow-Origin");

// 3. Atur ulang CORS Header yang bersih dan mendukung Flutter Web secara penuh
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 55101');    // cache selama 1 hari
}

// 4. Atur izin metode dan header selama preflight OPTIONS dari browser
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    // Hentikan eksekusi di sini khusus untuk request OPTIONS
    exit(0);
}

// Format output utama sebagai JSON
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_ruangan = $_POST['id_ruangan'];
    $id_akun = $_POST['id_akun'];
    $tanggal_peminjaman = $_POST['tanggal_peminjaman'];
    $waktu_mulai = $_POST['waktu_mulai_peminjaman'];
    $waktu_akhir = $_POST['waktu_akhir_peminjaman'];
    $keperluan = $_POST['keperluan'];

    // Folder tempat penyimpanan file dokumen upload
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $doc_SIK_name = null;
    $doc_SPM_name = null;

    // Proses Upload File SIK
    if (isset($_FILES['doc_SIK'])) {
        $ext = pathinfo($_FILES['doc_SIK']['name'], PATHINFO_EXTENSION);
        $doc_SIK_name = "SIK_" . time() . "_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['doc_SIK']['tmp_name'], $target_dir . $doc_SIK_name);
    }

    // Proses Upload File SPM
    if (isset($_FILES['doc_SPM'])) {
        $ext = pathinfo($_FILES['doc_SPM']['name'], PATHINFO_EXTENSION);
        $doc_SPM_name = "SPM_" . time() . "_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['doc_SPM']['tmp_name'], $target_dir . $doc_SPM_name);
    }

    // Query simpan menggunakan variabel $connect dari database.php
    $query = "INSERT INTO tb_peminjaman (id_ruangan, id_akun, tanggal_pengajuan, tanggal_peminjaman, waktu_mulai_peminjaman, waktu_akhir_peminjaman, keperluan, doc_SIK, doc_SPM) 
              VALUES ('$id_ruangan', '$id_akun', CURDATE(), '$tanggal_peminjaman', '$waktu_mulai', '$waktu_akhir', '$keperluan', '$doc_SIK_name', '$doc_SPM_name')";

    if (mysqli_query($connect, $query)) {
        echo json_encode(["success" => true, "message" => "Pengajuan peminjaman berhasil disimpan."]);
    } else {
        echo json_encode(["success" => false, "message" => "Gagal menyimpan ke database: " . mysqli_error($connect)]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Metode request tidak valid."]);
}
?>