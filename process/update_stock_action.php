<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

function json_response(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

if (empty($_SESSION['username'])) {
    json_response([
        'success' => false,
        'message' => 'Silakan login terlebih dahulu.',
    ], 401);
}

$idBarang = isset($input['id_barang']) ? (int) $input['id_barang'] : (isset($input['id']) ? (int) $input['id'] : 0);
$stokBaru = isset($input['stok_baru']) ? (int) $input['stok_baru'] : -1;

if ($idBarang <= 0 || $stokBaru < 0) {
    json_response([
        'success' => false,
        'message' => 'ID barang dan stok baru harus valid.',
    ], 400);
}

$checkStmt = $koneksi->prepare('SELECT id, nama_barang, stok FROM barang WHERE id = ?');
if (!$checkStmt) {
    json_response([
        'success' => false,
        'message' => 'Gagal menyiapkan validasi barang: ' . $koneksi->error,
    ], 500);
}

$checkStmt->bind_param('i', $idBarang);
$checkStmt->execute();
$result = $checkStmt->get_result();
$barang = $result ? $result->fetch_assoc() : null;
$checkStmt->close();

if (!$barang) {
    json_response([
        'success' => false,
        'message' => 'Barang tidak ditemukan.',
    ], 404);
}

$updateStmt = $koneksi->prepare('UPDATE barang SET stok = ? WHERE id = ?');
if (!$updateStmt) {
    json_response([
        'success' => false,
        'message' => 'Gagal menyiapkan update stok: ' . $koneksi->error,
    ], 500);
}

$updateStmt->bind_param('ii', $stokBaru, $idBarang);

if (!$updateStmt->execute()) {
    json_response([
        'success' => false,
        'message' => 'Gagal memperbarui stok: ' . $updateStmt->error,
    ], 500);
}

$updateStmt->close();

json_response([
    'success' => true,
    'message' => 'Stok berhasil diperbarui.',
    'data' => [
        'id' => $idBarang,
        'nama_barang' => $barang['nama_barang'],
        'stok_lama' => (int) $barang['stok'],
        'stok' => $stokBaru,
    ],
]);
