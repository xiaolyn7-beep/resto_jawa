<?php
/**
 * Upload gambar menu.
 * Return: null  = tidak ada berkas dipilih
 *         false = gagal ($error berisi alasan)
 *         string = nama berkas baru yang tersimpan di folder uploads/
 */
function upload_gambar($file, &$error, $folder = "uploads/") {
    if (!is_array($file) || $file['error'] === UPLOAD_ERR_NO_FILE) return null;

    // 1. Cek kode error bawaan PHP
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Upload gagal (kode error " . $file['error'] . ").";
        return false;
    }
    // 2. Cek ukuran (maks 2 MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        $error = "Ukuran gambar maksimal 2 MB.";
        return false;
    }
    // 3. Cek ekstensi dari nama asli
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
        $error = "Format harus JPG, JPEG, PNG, GIF, atau WEBP.";
        return false;
    }
    // 4. Cek isi berkas benar-benar gambar
    $info = @getimagesize($file['tmp_name']);
    if ($info === false || !in_array($info['mime'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
        $error = "Berkas yang diunggah bukan gambar yang valid.";
        return false;
    }
    // 5. Pindahkan dari lokasi sementara ke lokasi permanen dengan nama unik
    if (!is_dir($folder)) mkdir($folder, 0755, true);
    $namaBaru = 'menu_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $folder . $namaBaru)) {
        $error = "Gagal menyimpan berkas ke server.";
        return false;
    }
    return $namaBaru;
}

function hapus_gambar($nama, $folder = "uploads/") {
    if ($nama && is_file($folder . $nama)) unlink($folder . $nama);
}
