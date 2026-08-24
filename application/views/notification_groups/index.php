<div class="card card-stat p-3">
	<div class="d-flex justify-content-end mb-3">
		<a href="<?= base_url('notification-groups/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Grup</a>
	</div>
	<div class="table-responsive">
		<table class="table align-middle">
			<thead><tr><th>Nama Grup</th><th>Deskripsi</th><th>Status</th><th></th></tr></thead>
			<tbody>
			<?php foreach ($groups as $g): ?>
				<tr>
					<td><?= htmlspecialchars($g['group_name']) ?></td>
					<td><?= htmlspecialchars($g['description']) ?></td>
					<td><?= status_badge($g['is_active'] ? 'active' : 'inactive') ?></td>
					<td class="text-end">
						<a href="<?= base_url('notification-groups/edit/' . $g['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
						<a href="<?= base_url('notification-groups/delete/' . $g['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus grup ini?')"><i class="bi bi-trash"></i></a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
