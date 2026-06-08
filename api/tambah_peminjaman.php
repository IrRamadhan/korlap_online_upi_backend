<?php
// Hubungkan ke database.php
require_once "../config/database.php";

// 1. Validasi Input Wajib (Pastikan parameter teks dikirim oleh Flutter)
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
        "message" => "Semua data kuisioner peminjaman wajib diisi."
    ]);
    exit();
}

// Tangkap data teks dari $_POST
$id_ruangan             = $_POST['id_ruangan'];
$id_akun                = $_POST['id_akun'];
$tanggal_peminjaman     = $_POST['tanggal_peminjaman'];     // Format dari Flutter: YYYY-MM-DD
$waktu_mulai_peminjaman = $_POST['waktu_mulai_peminjaman']; // Format dari Flutter: HH:MM:SS
$waktu_akhir_peminjaman = $_POST['waktu_akhir_peminjaman']; // Format dari Flutter: HH:MM:SS
$keperluan              = isset($_POST['keperluan']) ? $_POST['keperluan'] : null;

// Catatan Kolom Status Pengajuan:
// Di screenshot DB Anda, status berbentuk enum('ditolak', 'diterima'). 
// Agar pengajuan baru berstatus netral saat pertama dikirim, Anda disarankan menambahkan opsi 'diproses' pada enum tb_peminjaman di phpMyAdmin Anda.
// Untuk sementara, kita isi default awal dengan status 'diproses'.
$status_pengajuan       = "diproses"; 

// Siapkan nama file sebagai null secara default jika user tidak upload
$doc_SK_name  = null;
$doc_SPM_name = null;

// 2. Setup Folder Penyimpanan File Upload
$target_dir = "../uploads/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true); // Buat folder 'uploads' otomatis jika belum ada
}

// 3. Proses Upload File doc_SK (Jika ada file yang dikirim)
if (!empty($_FILES['doc_SK']['name'])) {
    $ext_SK = pathinfo($_FILES['doc_SK']['name'], PATHINFO_EXTENSION);
    // Buat nama file unik agar tidak saling menimpa (Contoh: SK_1717800000_64a1b.pdf)
    $doc_SK_name = "SK_" . time() . "_" . uniqid() . "." . $ext_SK;
    $target_file_SK = $target_dir . $doc_SK_name;
    
    move_uploaded_file($_FILES['doc_SK']['tmp_name'], $target_file_SK);
}

// 4. Proses Upload File doc_SPM (Jika ada file yang dikirim)
if (!empty($_FILES['doc_SPM']['name'])) {
    $ext_SPM = pathinfo($_FILES['doc_SPM']['name'], PATHINFO_EXTENSION);
    $doc_SPM_name = "SPM_" . time() . "_" . uniqid() . "." . $ext_SPM;
    $target_file_SPM = $target_dir . $doc_doc_SPM_name;
    
    move_uploaded_file($_FILES['doc_SPM']['tmp_name'], $target_file_SPM);
}

// 5. Query INSERT ke tb_peminjaman (Menggunakan Prepared Statements)
$query = "INSERT INTO tb_peminjaman (id_ruangan, id_akun, tanggal_peminjaman, waktu_mulai_peminjaman, waktu_akhir_peminjaman, keperluan, status_pengajuan, doc_SK, doc_SPM) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($connect, $query);
// "iisssssss" berarti: integer, integer, string, string, dst... sesuai urutan tanda tanya
mysqli_stmt_bind_param($stmt, "iisssssss", $id_ruangan, $id_akun, $tanggal_peminjaman, $waktu_mulai_peminjaman, $waktu_akhir_peminjaman, $keperluan, $status_pengajuan, $doc_SK_name, $doc_SPM_name);

if (mysqli_stmt_execute($stmt)) {
    
    // 🌟 LOGIKA TAMBAHAN OTOMATIS: 
    // Mengubah status ruangan di `tb_ruangan` menjadi 'diajukan'.
    // Dengan begitu, user lain akan melihat status ruangan tersebut sudah ada yang mengajukan.
    $update_ruangan_query = "UPDATE tb_ruangan SET status = 'diajukan' WHERE id = ?";
    $stmt_ruangan = mysqli_prepare($connect, $update_ruangan_query);
    mysqli_stmt_bind_param($stmt_ruangan, "i", $id_ruangan);
    mysqli_stmt_execute($stmt_ruangan);

    // Kirim respon sukses ke Flutter
    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Pengajuan peminjaman ruangan berhasil dikirim."
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Gagal menyimpan pengajuan ke database: " . mysqli_error($connect)
    ]);
}