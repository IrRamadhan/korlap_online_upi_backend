<?php
class Akun {
    private $db;
    private $table_name = "tb_akun";

    // Constructor untuk menerima koneksi database $connect dari luar
    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    // Fungsi khusus untuk mencari akun berdasarkan nomor induk
    public function cariBerdasarkanNomorInduk($nomor_induk) {
        $query = "SELECT id, nomor_induk, password, role, nama, instansi FROM " . $this->table_name . " WHERE nomor_induk = ? LIMIT 1";
        
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "s", $nomor_induk);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        
        // Jika ketemu, kembalikan datanya dalam bentuk array asosiatif. Jika tidak, return null.
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        
        return null;
    }
    // Tambahkan fungsi ini di dalam class Akun Anda
    public function registrasiAkun($data) {
        // 1. Validasi: Cek apakah nomor_induk sudah digunakan atau belum
        $cek_query = "SELECT id FROM " . $this->table_name . " WHERE nomor_induk = ? LIMIT 1";
        $cek_stmt = mysqli_prepare($this->db, $cek_query);
        mysqli_stmt_bind_param($cek_stmt, "s", $data['nomor_induk']);
        mysqli_stmt_execute($cek_stmt);
        mysqli_stmt_store_result($cek_stmt);
        
        // Jika nomor_induk sudah ada di database, batalkan registrasi
        if (mysqli_stmt_num_rows($cek_stmt) > 0) {
            return "exists"; 
        }

        // 2. Jika nomor_induk aman, lakukan insert data akun baru
        $query = "INSERT INTO " . $this->table_name . " (nomor_induk, password, nama, role, instansi) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->db, $query);
        
        mysqli_stmt_bind_param(
            $stmt, 
            "sssss", 
            $data['nomor_induk'], 
            $data['password'], 
            $data['nama'], 
            $data['role'], 
            $data['instansi']
        );

        if (mysqli_stmt_execute($stmt)) {
            return "success";
        }

        return "failed";
    }
}