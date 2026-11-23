<?php
// Fungsi bantu bersama (Developer A)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

define('NAMA_RESTORAN', 'Nusantara Food Hub');
define('DIR_GAMBAR', __DIR__ . '/uploads/menu/');
define('URL_GAMBAR', 'uploads/menu/');
define('KATEGORI_MENU', ['Makanan', 'Minuman', 'Cemilan']);
define('PAJAK_PERSEN', 10);      // pajak restoran (%)
define('JUMLAH_MEJA', 20);       // jumlah meja pada pilihan "Lokasi Meja"
define('ALASAN_REFUND', ['Rasa tidak sesuai standar', 'Suhu tidak sesuai', 'Ada benda asing', 'Basi / tidak layak konsumsi', 'Porsi tidak sesuai standar', 'Salah penyajian', 'Lainnya']);

class PesananError extends Exception {}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupiah($n) { return 'Rp ' . number_format((int)$n, 0, ',', '.'); }
function sudah_login() { return isset($_SESSION['status_login']) && $_SESSION['status_login'] === true; }

function json_out($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}

function wajib_login($json = false) {
    if (!sudah_login()) {
        if ($json) json_out(['sukses' => false, 'pesan' => 'Sesi login berakhir, silakan login ulang.'], 401);
        header("Location: index.php");
        exit();
    }
}

function role() { return $_SESSION['role'] ?? 'kasir'; }
function is_admin() { return sudah_login() && role() === 'admin'; }

function wajib_admin($json = false) {
    wajib_login($json);
    if (!is_admin()) {
        if ($json) json_out(['sukses' => false, 'pesan' => 'Hanya admin yang boleh melakukan aksi ini.'], 403);
        $_SESSION['flash'] = ['danger', 'Akses ditolak: halaman ini khusus admin.'];
        header("Location: kasir.php");
        exit();
    }
}

function cari_promo($connection, $kode) {
    $stmt = $connection->prepare("SELECT * FROM promo WHERE kode = ? AND aktif = 1 AND (berlaku_sampai IS NULL OR berlaku_sampai >= CURDATE())");
    $stmt->bind_param("s", $kode);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
function hitung_diskon($promo, $subtotal) {
    if (!$promo || $subtotal < (int)$promo['min_belanja']) return 0;
    $d = $promo['tipe'] === 'persen' ? intdiv($subtotal * (int)$promo['nilai'], 100) : (int)$promo['nilai'];
    return min($d, $subtotal);
}
function hitung_pajak($dasar) { return (int)round($dasar * PAJAK_PERSEN / 100); }

function url_gambar($file) {
    return ($file && is_file(DIR_GAMBAR . $file)) ? URL_GAMBAR . $file : null;
}

/** Return: null = tidak ada file diunggah, false = error ($error terisi), string = nama file baru */
function simpan_gambar($file, &$error) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) { $error = 'Upload gambar gagal (kode ' . $file['error'] . ').'; return false; }
    if ($file['size'] > 2 * 1024 * 1024) { $error = 'Ukuran gambar maksimal 2 MB.'; return false; }
    $info = @getimagesize($file['tmp_name']);
    $ext  = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($ext[$info[2]])) { $error = 'Format gambar harus JPG, PNG, atau WEBP.'; return false; }
    if (!is_dir(DIR_GAMBAR)) mkdir(DIR_GAMBAR, 0775, true);
    $nama = 'menu_' . bin2hex(random_bytes(8)) . '.' . $ext[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], DIR_GAMBAR . $nama)) { $error = 'Gagal menyimpan gambar di server.'; return false; }
    return $nama;
}

function hapus_gambar($file) {
    if ($file && is_file(DIR_GAMBAR . basename($file))) @unlink(DIR_GAMBAR . basename($file));
}
