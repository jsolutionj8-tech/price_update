<?php
/**
 * Partial: isi modal "Lihat Detail" pada banner "Kirim Notifikasi Sekarang" — daftar
 * batch perubahan harga berstatus 'pending' yang akan ikut dalam email berikutnya.
 * Dipanggil via AJAX oleh Price_update::pending_list(). Variabel wajib: $batches, $can_view_detail.
 */
?>
<div class="table-responsive">
	<table class="table table-sm align-middle mb-0">
		<thead><tr><th>Tanggal Efektif</th><th>Produk</th><th>Vendor</th><th>Diubah Oleh</th><th></th></tr></thead>
		<tbody>
		<?php foreach ($batches as $b): ?>
			<tr>
				<td><?= tgl_indo($b['effective_date']) ?></td>
				<td><?= htmlspecialchars($b['product_name']) ?> <small class="text-muted d-block"><?= htmlspecialchars($b['product_code']) ?></small></td>
				<td><?= htmlspecialchars($b['vendor_code']) ?></td>
				<td><?= htmlspecialchars($b['changed_by_name']) ?></td>
				<td>
					<div class="d-flex gap-1">
						<?php if ($can_view_detail): ?>
							<a href="<?= base_url('price-history/detail/' . $b['id']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank">Detail</a>
						<?php endif; ?>
						<button type="button" class="btn btn-sm btn-outline-danger btn-cancel-pending" data-batch-id="<?= $b['id'] ?>" data-product-name="<?= htmlspecialchars($b['product_name']) ?>"><i class="bi bi-x-circle"></i> Batalkan</button>
					</div>
				</td>
			</tr>
		<?php endforeach; ?>
		<?php if (empty($batches)): ?>
			<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada perubahan harga yang menunggu dikirim.</td></tr>
		<?php endif; ?>
		</tbody>
	</table>
</div>
