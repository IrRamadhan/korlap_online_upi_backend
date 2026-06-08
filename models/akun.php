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
}