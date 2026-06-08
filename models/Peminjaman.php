<?php
class Peminjaman {
    private $db;
    private $table_name = "tb_peminjaman";

    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    // Fungsi untuk membuat pengajuan peminjaman baru
    public function buatPengajuan($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_ruangan, id_akun, tanggal_pengajuan, tanggal_peminjaman, waktu_mulai_peminjaman, waktu_akhir_peminjaman, keperluan, status_pengajuan, doc_SK, doc_SPM) 
                  VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->db, $query);
        
        // Default status pengajuan baru adalah 'diproses'
        $status_default = "diproses";

        mysqli_stmt_bind_param(
            $stmt, 
            "iisssssss", 
            $data['id_ruangan'], 
            $data['id_akun'], 
            $data['tanggal_peminjaman'], 
            $data['waktu_mulai_peminjaman'], 
            $data['waktu_akhir_peminjaman'], 
            $data['keperluan'], 
            $status_default, 
            $data['doc_SK'], 
            $data['doc_SPM']
        );

        // Eksekusi query insert
        if (mysqli_stmt_execute($stmt)) {
            // Jika sukses insert, otomatis ubah status ruangan di tb_ruangan menjadi 'diajukan'
            $this->updateStatusRuangan($data['id_ruangan'], 'diajukan');
            return true;
        }

        return false;
    }

    // Fungsi internal untuk mengubah status ketersediaan ruangan
    private function updateStatusRuangan($id_ruangan, $status_baru) {
        $query = "UPDATE tb_ruangan SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "si", $status_baru, $id_ruangan);
        mysqli_stmt_execute($stmt);
    }
}