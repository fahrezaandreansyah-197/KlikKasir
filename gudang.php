<?php
require_once __DIR__ . '/config/session.php';
require_login('index.php');
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/config/database.php';

$stats = [
    'total_barang' => 0,
    'total_unit' => 0,
    'nilai_aset' => 0,
];

$statResult = $koneksi->query(
    'SELECT COUNT(*) AS total_barang, COALESCE(SUM(stok), 0) AS total_unit, COALESCE(SUM(stok * harga), 0) AS nilai_aset FROM barang'
);

if ($statResult) {
    $statData = $statResult->fetch_assoc();
    if ($statData) {
        $stats = $statData;
    }
}

$barangResult = $koneksi->query(
    'SELECT b.id, b.nama_barang, b.kategori, b.harga, b.stok, g.nama_gudang, g.lokasi
	 FROM barang b
	 INNER JOIN gudang g ON g.id_gudang = b.id_gudang
	 ORDER BY b.nama_barang ASC'
);

$pageTitle = 'Gudang - Stok';
$bodyClass = 'min-h-screen bg-gradient-to-br from-slate-50 via-slate-100 to-slate-200 text-slate-800';
$activePage = 'gudang';
$pageScripts = ['assets/js/app.js'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<main class="mx-auto max-w-7xl space-y-6 px-4 py-6">
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">Total Barang</div>
            <div id="statSKU" class="mt-1 text-2xl font-extrabold tracking-tight text-indigo-600"><?= h($stats['total_barang'] ?? 0) ?></div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">Total Unit</div>
            <div id="statUnits" class="mt-1 text-2xl font-extrabold tracking-tight text-indigo-600"><?= h($stats['total_unit'] ?? 0) ?></div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">Nilai Aset</div>
            <div id="statValue" class="mt-1 text-2xl font-extrabold tracking-tight text-indigo-600">Rp <?= number_format((float) ($stats['nilai_aset'] ?? 0), 0, ',', '.') ?></div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Nama Barang</th>
                        <th class="px-4 py-3 font-semibold">Kategori</th>
                        <th class="px-4 py-3 font-semibold">Gudang</th>
                        <th class="px-4 py-3 text-right font-semibold">Harga</th>
                        <th class="px-4 py-3 text-center font-semibold">Stok</th>
                        <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if ($barangResult && $barangResult->num_rows > 0): ?>
                        <?php while ($barang = $barangResult->fetch_assoc()): ?>
                            <?php $stockBadgeClass = ((int) $barang['stok'] <= 10) ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'; ?>
                            <tr data-barang-row="<?= h($barang['id']) ?>" data-stock-value="<?= h($barang['stok']) ?>" data-price-value="<?= h($barang['harga']) ?>" class="hover:bg-slate-50/80">
                                <td class="px-4 py-4 font-semibold text-slate-900"><?= h($barang['nama_barang']) ?></td>
                                <td class="px-4 py-4"><span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?= h($barang['kategori']) ?></span></td>
                                <td class="px-4 py-4 text-sm text-slate-700">
                                    <div class="font-medium text-slate-900"><?= h($barang['nama_gudang']) ?></div>
                                    <div class="text-slate-500"><?= h($barang['lokasi']) ?></div>
                                </td>
                                <td class="px-4 py-4 text-right font-semibold text-slate-700">Rp <?= number_format((float) $barang['harga'], 0, ',', '.') ?></td>
                                <td class="px-4 py-4 text-center">
                                    <span data-stock-badge class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?= $stockBadgeClass ?>"><?= h($barang['stok']) ?> pcs</span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <button type="button" data-open-stock data-id="<?= (int) $barang['id'] ?>" data-name="<?= h($barang['nama_barang']) ?>" data-stock="<?= (int) $barang['stok'] ?>" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">+ Stok</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada data barang.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div id="stockModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4 py-6" onclick="closeStockModal()">
    <div class="w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-2xl shadow-slate-900/20" onclick="event.stopPropagation()">
        <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5">
            <div class="flex items-start gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                    <i data-lucide="package-plus" class="h-5 w-5"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">Update Stok Barang</p>
                    <h3 id="stockModalTitle" class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Pilih Barang</h3>
                </div>
            </div>
            <button type="button" onclick="closeStockModal()" class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Tutup modal stok">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>
        </div>

        <div class="space-y-6 p-6">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-500">Nama Barang</div>
                    <div id="stockModalName" class="mt-1 text-lg font-semibold text-slate-900">-</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-500">Stok Saat Ini</div>
                    <div id="stockModalCurrentStock" class="mt-1 text-lg font-semibold text-slate-900">-</div>
                </div>
            </div>

            <div class="space-y-2">
                <label for="stockAmount" class="text-sm font-semibold text-slate-700">Jumlah Stok</label>
                <input id="stockAmount" type="number" min="0" step="1" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100" placeholder="Masukkan jumlah stok baru">
                <p class="text-xs text-slate-500">Gunakan angka untuk menghitung stok baru berdasarkan aksi yang dipilih.</p>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                <button type="button" onclick="processStockAction('set')" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                    <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                    Set Langsung
                </button>
                <button type="button" onclick="processStockAction('add')" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    Tambah (+)
                </button>
                <button type="button" onclick="processStockAction('subtract')" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-rose-700">
                    <i data-lucide="minus" class="h-4 w-4"></i>
                    Kurangi (-)
                </button>
            </div>

            <div class="flex justify-end border-t border-slate-200 pt-4">
                <button type="button" onclick="closeStockModal()" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                    Batal / Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>