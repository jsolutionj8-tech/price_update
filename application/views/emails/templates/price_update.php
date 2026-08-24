<div style="font-family:Arial,sans-serif;max-width:560px;">
	<div style="background:#1F3864;color:#fff;padding:14px 18px;border-radius:6px 6px 0 0;">
		<strong>Update Harga: <?= htmlspecialchars($product['product_name'] ?? '') ?></strong>
	</div>
	<div style="border:1px solid #e0e0e0;border-top:none;padding:18px;border-radius:0 0 6px 6px;">
		<p>Produk <b><?= htmlspecialchars($product['product_name'] ?? '') ?></b> (<?= htmlspecialchars($product['product_code'] ?? '') ?>) mengalami perubahan harga.</p>
		<table style="width:100%;border-collapse:collapse;font-size:13px;">
			<tr><td style="padding:4px 0;color:#666;">Modal</td><td style="text-align:right;"><?= rupiah($calc['srp_suggest'] ?? 0) ?></td></tr>
			<tr><td style="padding:4px 0;color:#666;">SRP Suggest</td><td style="text-align:right;font-weight:bold;"><?= rupiah($calc['srp_suggest'] ?? 0) ?></td></tr>
			<tr><td style="padding:4px 0;color:#666;">Markup</td><td style="text-align:right;"><?= percent_fmt($calc['markup_pct'] ?? 0) ?></td></tr>
			<tr><td style="padding:4px 0;color:#666;">Margin</td><td style="text-align:right;"><?= percent_fmt($calc['margin_pct'] ?? 0) ?></td></tr>
			<tr><td style="padding:4px 0;color:#666;">Tanggal Efektif</td><td style="text-align:right;"><?= tgl_indo($effective_date ?? date('Y-m-d')) ?></td></tr>
			<tr><td style="padding:4px 0;color:#666;">Diubah oleh</td><td style="text-align:right;"><?= htmlspecialchars($changed_by ?? '-') ?></td></tr>
		</table>
		<p style="margin-top:14px;color:#888;font-size:12px;">Ini adalah pratinjau. Rincian harga per kanal akan disertakan pada email aktual setelah data disimpan.</p>
	</div>
</div>
