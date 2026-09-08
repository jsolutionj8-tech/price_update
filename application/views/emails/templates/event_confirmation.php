<div style="font-family:Georgia,'Times New Roman',serif;max-width:520px;margin:0 auto;background:#F7F2E9;padding:24px;">
	<div style="text-align:center;margin-bottom:18px;">
		<div style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#B8873B;font-family:Arial,sans-serif;">Reservation Confirmed</div>
		<div style="font-size:24px;color:#201B14;margin-top:6px;"><?= htmlspecialchars($event['event_name']) ?></div>
	</div>

	<p style="font-family:Arial,sans-serif;font-size:14px;color:#3a3226;">
		Halo <b><?= htmlspecialchars($rsvp['orderer_name']) ?></b>, terima kasih sudah melakukan RSVP.
		Berikut ringkasan reservasi Anda:
	</p>

	<table style="width:100%;border-collapse:collapse;font-family:Arial,sans-serif;font-size:13px;background:#fff;border-radius:8px;overflow:hidden;">
		<tr><td style="padding:10px 14px;color:#8A7F6D;border-bottom:1px solid #EFE6D6;">Kode Tiket</td><td style="padding:10px 14px;text-align:right;font-weight:bold;border-bottom:1px solid #EFE6D6;"><?= htmlspecialchars($rsvp['ticket_code']) ?></td></tr>
		<tr><td style="padding:10px 14px;color:#8A7F6D;border-bottom:1px solid #EFE6D6;">Tanggal</td><td style="padding:10px 14px;text-align:right;border-bottom:1px solid #EFE6D6;"><?= tgl_indo($schedule['event_date']) ?></td></tr>
		<tr><td style="padding:10px 14px;color:#8A7F6D;border-bottom:1px solid #EFE6D6;">Jam</td><td style="padding:10px 14px;text-align:right;border-bottom:1px solid #EFE6D6;"><?= substr($schedule['event_time'], 0, 5) ?> WIB</td></tr>
		<?php if (!empty($event['venue_name'])): ?>
		<tr><td style="padding:10px 14px;color:#8A7F6D;border-bottom:1px solid #EFE6D6;">Venue</td><td style="padding:10px 14px;text-align:right;border-bottom:1px solid #EFE6D6;"><?= htmlspecialchars($event['venue_name']) ?></td></tr>
		<?php endif; ?>
		<tr><td style="padding:10px 14px;color:#8A7F6D;border-bottom:1px solid #EFE6D6;">Jumlah Tamu</td><td style="padding:10px 14px;text-align:right;border-bottom:1px solid #EFE6D6;"><?= (int) $rsvp['guest_count'] ?> orang</td></tr>
		<tr><td style="padding:10px 14px;color:#8A7F6D;">Nama Tamu</td><td style="padding:10px 14px;text-align:right;"><?= htmlspecialchars(implode(', ', $guest_names)) ?></td></tr>
	</table>

	<div style="background:#E8D9BE;border-radius:8px;padding:14px 16px;margin-top:14px;font-family:Arial,sans-serif;">
		<div style="font-size:11px;color:#6B5636;">DEPOSIT RESERVASI</div>
		<div style="font-size:20px;color:#201B14;font-weight:bold;"><?= rupiah($rsvp['deposit_amount']) ?></div>
		<?php if (!empty($event['bank_account_number'])): ?>
		<div style="font-size:12px;color:#6B5636;margin-top:6px;">
			Transfer ke <?= htmlspecialchars($event['bank_name'] ?? '') ?> <?= htmlspecialchars($event['bank_account_number']) ?>
			<?= !empty($event['bank_account_name']) ? '(a.n. ' . htmlspecialchars($event['bank_account_name']) . ')' : '' ?>
		</div>
		<?php endif; ?>
	</div>

	<div style="text-align:center;margin-top:22px;">
		<a href="<?= base_url('rsvp-ticket/' . $rsvp['ticket_code']) ?>" style="display:inline-block;background:#201B14;color:#fff;text-decoration:none;font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:12px 28px;border-radius:8px;">Lihat E-Tiket &amp; Barcode</a>
	</div>

	<div style="text-align:center;margin-top:18px;">
		<img src="<?= base_url('rsvp-barcode/' . $rsvp['ticket_code']) ?>" alt="Barcode tiket" style="height:50px;">
		<div style="font-family:monospace;font-size:12px;color:#201B14;margin-top:4px;"><?= htmlspecialchars($rsvp['ticket_code']) ?></div>
	</div>

	<p style="font-family:Arial,sans-serif;font-size:11px;color:#8A7F6D;text-align:center;margin-top:22px;">
		Tunjukkan barcode di atas saat tiba di lokasi acara. Email ini dikirim otomatis oleh sistem RSVP <?= htmlspecialchars($event['event_name']) ?>.
	</p>
</div>
