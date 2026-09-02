<div class="card card-stat p-3">
	<div class="d-flex justify-content-end mb-3">
		<a href="<?= base_url('users/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah User</a>
	</div>
	<div class="table-responsive">
		<table class="table align-middle">
			<thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead>
			<tbody>
			<?php foreach ($users as $u): ?>
				<tr>
					<td><?= htmlspecialchars($u['full_name']) ?></td>
					<td><?= htmlspecialchars($u['email']) ?></td>
					<td><?= htmlspecialchars($u['role_name']) ?></td>
					<td><?= status_badge($u['is_active'] ? 'active' : 'inactive') ?></td>
					<td class="text-end">
						<a href="<?= base_url('users/edit/' . $u['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
						<a href="<?= base_url('users/delete/' . $u['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Nonaktifkan user ini?')"><i class="bi bi-trash"></i></a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
