<div class="card card-stat p-3">
	<p class="text-muted">Pilih produk yang akan diperbarui harganya. Sistem akan menampilkan form input Modal, Target HPP, dan harga per kanal penjualan untuk tiap vendor.</p>
	<div class="mb-3">
		<input type="text" id="searchProduct" class="form-control" placeholder="Cari produk berdasarkan kode / nama...">
	</div>
	<div class="table-responsive">
		<table class="table align-middle" id="productTable">
			<thead><tr><th>Kode Produk</th><th>Nama Produk</th><th>Brand</th><th></th></tr></thead>
			<tbody>
			<?php foreach ($products as $p): ?>
				<tr>
					<td><?= htmlspecialchars($p['product_code']) ?></td>
					<td><?= htmlspecialchars($p['product_name']) ?></td>
					<td><?= htmlspecialchars($p['brand_name'] ?? '-') ?></td>
					<td class="text-end"><a href="<?= base_url('price-update/form/' . $p['id']) ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil-square"></i> Update Harga</a></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
<script>
document.getElementById('searchProduct').addEventListener('keyup', function () {
	const q = this.value.toLowerCase();
	document.querySelectorAll('#productTable tbody tr').forEach(tr => {
		tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
	});
});
</script>
