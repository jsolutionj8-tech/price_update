<div class="alert alert-info d-flex align-items-start gap-2 mb-3">
	<i class="bi bi-info-circle-fill mt-1"></i>
	<div>Atur menu apa saja yang bisa diakses oleh role <strong>EDITOR</strong> dan <strong>VIEWER</strong>. Menu yang tidak dicentang akan otomatis <strong>disembunyikan dari navbar</strong> role tsb, dan tidak bisa dibuka langsung lewat URL. Role <strong>ADMIN</strong> selalu memiliki akses penuh ke seluruh menu dan tidak bisa dibatasi di sini.</div>
</div>

<form method="post" action="<?= base_url('access-control/update') ?>">
	<?php foreach ($roles as $r): ?>
		<input type="hidden" name="role_ids[]" value="<?= $r['id'] ?>">
	<?php endforeach; ?>

	<div class="card card-stat p-3">
		<div class="table-responsive">
			<table class="table align-middle">
				<thead>
					<tr>
						<th>Menu</th>
						<?php foreach ($roles as $r): ?>
							<th class="text-center"><?= htmlspecialchars($r['role_name']) ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
				<?php $current_group = null; foreach ($menus as $m): ?>
					<?php if ($m['menu_group'] !== $current_group): $current_group = $m['menu_group']; ?>
						<tr class="table-light"><td colspan="<?= count($roles) + 1 ?>" class="fw-bold small text-uppercase text-muted"><?= htmlspecialchars($current_group ?? '') ?></td></tr>
					<?php endif; ?>
					<tr>
						<td><?php if (!empty($m['menu_icon'])): ?><i class="bi <?= htmlspecialchars($m['menu_icon']) ?> me-2 text-muted"></i><?php endif; ?><?= htmlspecialchars($m['menu_label']) ?></td>
						<?php foreach ($roles as $r): ?>
							<td class="text-center">
								<input type="checkbox" class="form-check-input" name="menu[<?= $r['id'] ?>][]" value="<?= $m['id'] ?>"
									<?= !empty($access[$r['id']][$m['id']]) ? 'checked' : '' ?>>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
				<?php if (empty($menus)): ?>
					<tr><td colspan="<?= count($roles) + 1 ?>" class="text-center text-muted py-3">Belum ada menu terdaftar.</td></tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<div class="mt-3">
			<button class="btn btn-primary"><i class="bi bi-save"></i> Simpan Hak Akses</button>
		</div>
	</div>
</form>
