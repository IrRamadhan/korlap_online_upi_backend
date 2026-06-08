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
                  (id_ruangan, id_akun, tanggal_pengajuan, tanggal_peminjaman, waktu_mulai_peminjaman, waktu_akhir_peminjaman, keperluan, status_pengajuan, doc_SIK, doc_SPM) 
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
            $data['doc_SIK'], 
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
    // Tambahkan fungsi ini di dalam class Peminjaman Anda
    public function getHistoryByUser($id_akun) {
        // Menggunakan JOIN untuk mengambil data nama_ruangan, gedung, dan lantai dari tb_ruangan
        $query = "SELECT p.*, r.nama_ruangan, r.gedung, r.lantai 
                FROM " . $this->table_name . " p
                JOIN tb_ruangan r ON p.id_ruangan = r.id
                WHERE p.id_akun = ?
                ORDER BY p.tanggal_pengajuan DESC";

        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id_akun);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $history_list = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $history_list[] = $row;
        }

        return $history_list;
    }
    // 1. Fungsi untuk Admin melihat SEMUA pengajuan dari seluruh user
    public function getAllPengajuan() {
        $query = "SELECT p.*, r.nama_ruangan, r.gedung, r.lantai, a.nama as nama_pemohon, a.instansi
                  FROM " . $this->table_name . " p
                  JOIN tb_ruangan r ON p.id_ruangan = r.id
                  JOIN tb_akun a ON p.id_akun = a.id
                  ORDER BY p.tanggal_pengajuan DESC";

        $result = mysqli_query($this->db, $query);
        $all_list = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $all_list[] = $row;
        }
        return $all_list;
    }

    // 2. Fungsi untuk Admin melakukan ACC atau TOLAK pengajuan
    public function prosesPengajuan($id_peminjaman, $status_baru, $catatan) {
        // Ambil id_ruangan terlebih dahulu sebelum status diubah
        $query_cari = "SELECT id_ruangan FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt_cari = mysqli_prepare($this->db, $query_cari);
        mysqli_stmt_bind_param($stmt_cari, "i", $id_peminjaman);
        mysqli_stmt_execute($stmt_cari);
        $res_cari = mysqli_stmt_get_result($stmt_cari);
        $data_pinjam = mysqli_fetch_assoc($res_cari);
        
        if (!$data_pinjam) return false;
        $id_ruangan = $data_pinjam['id_ruangan'];

        // Update status pengajuan di tb_peminjaman
        $query_update = "UPDATE " . $this->table_name . " SET status_pengajuan = ?, catatan_petugas = ?, updated_at = NOW() WHERE id = ?";
        $stmt_update = mysqli_prepare($this->db, $query_update);
        mysqli_stmt_bind_param($stmt_update, "ssi", $status_baru, $catatan, $id_peminjaman);
        
        if (mysqli_stmt_execute($stmt_update)) {
            // LOGIKA OTOMATIS STATUS RUANGAN:
            // Jika diterima -> status ruangan jadi 'sudah dipinjam'
            // Jika ditolak  -> status ruangan kembali 'tersedia'
            $status_ruangan = ($status_baru === "diterima") ? "sudah dipinjam" : "tersedia";
            $this->updateStatusRuangan($id_ruangan, $status_ruangan);
            return true;
        }
        return false;
    }
}