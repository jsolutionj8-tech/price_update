<?php
$guest_names = array_column($guests, 'guest_name');
$maps_query = trim(($event['venue_name'] ?? '') . ', ' . ($event['venue_address'] ?? ''), ', ');
$maps_url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($maps_query);

$cal_start = new DateTime($schedule['event_date'] . ' ' . $schedule['event_time']);
$cal_end = (clone $cal_start)->modify('+3 hours');
$cal_title = $event['event_name'] !== '' ? $event['event_name'] : 'Atambah';
$cal_url = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
	. '&text=' . rawurlencode($cal_title)
	. '&dates=' . $cal_start->format('Ymd\THis') . '/' . $cal_end->format('Ymd\THis')
	. '&details=' . rawurlencode('Booking code: ' . $rsvp['ticket_code'])
	. '&location=' . rawurlencode($maps_query);

// Palet biru Atambah versi muda/lembut, khusus email ini (beda dari email konfirmasi
// submit yg pakai palet krem/emas) — sesuai request: "warna template biru atambah, tapi
// biru nya biru muda".
$c_bg     = '#EEF5F8';
$c_ink    = '#1F333D';
$c_accent = '#4F7A8C';
$c_muted  = '#6E8894';
$c_border = '#DCEAEF';
$c_brand  = '#3D5C6C';
?>
<div style="font-family:'Plus Jakarta Sans',Arial,sans-serif;max-width:520px;margin:0 auto;background:<?= $c_bg ?>;padding:24px;">
	<div style="text-align:center;margin-bottom:18px;">
		<div style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:<?= $c_accent ?>;font-family:'Plus Jakarta Sans',Arial,sans-serif;">Reservation Confirmed</div>
		<?php if (!empty($event['event_name'])): ?>
			<div style="font-size:24px;color:<?= $c_ink ?>;margin-top:8px;font-weight:bold;font-family:'Plus Jakarta Sans',Arial,sans-serif;"><?= htmlspecialchars($event['event_name']) ?></div>
		<?php endif; ?>
		<table role="presentation" align="center" style="margin:14px auto 0;"><tr>
			<td><img src="<?= base_url('assets/images/yawara-logo.png') ?>" alt="Yawara" style="height:18px;"></td>
			<td style="padding:0 10px;font-family:'Plus Jakarta Sans',Arial,sans-serif;font-size:14px;color:<?= $c_muted ?>;">&times;</td>
			<td><img src="<?= base_url('assets/images/atambah-logo-white.jpg') ?>" alt="Atambah" style="height:22px;"></td>
		</tr></table>
	</div>

	<p style="font-family:'Plus Jakarta Sans',Arial,sans-serif;font-size:14px;color:<?= $c_ink ?>;line-height:1.6;">
		Hi <b><?= htmlspecialchars($rsvp['orderer_name']) ?></b>, your deposit of <b><?= rupiah($rsvp['deposit_amount']) ?></b> went through.
		Your table is set for <b><?= tgl_indo($schedule['event_date']) ?></b> at <b><?= substr($schedule['event_time'], 0, 5) ?> WIB</b>.
	</p>

	<div style="background:#fff;border:1px solid <?= $c_border ?>;border-radius:14px;padding:22px;text-align:center;margin:20px 0;">
		<div style="font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:<?= $c_muted ?>;margin-bottom:14px;font-family:'Plus Jakarta Sans',Arial,sans-serif;">Your E-Ticket</div>
		<img src="<?= base_url('rsvp-qrcode/' . $rsvp['ticket_code']) ?>" alt="QR code" style="width:160px;height:160px;">
		<div style="font-family:monospace;font-size:16px;font-weight:bold;color:<?= $c_ink ?>;margin-top:12px;letter-spacing:.05em;"><?= htmlspecialchars($rsvp['ticket_code']) ?></div>
		<div style="font-size:12px;color:<?= $c_muted ?>;margin-top:8px;font-family:'Plus Jakarta Sans',Arial,sans-serif;">Show this at the door. One code covers all <?= (int) $rsvp['guest_count'] ?> guests.</div>
	</div>

	<div style="text-align:center;margin-bottom:22px;">
		<a href="<?= htmlspecialchars($cal_url) ?>" style="display:inline-block;background:<?= $c_brand ?>;color:#fff;text-decoration:none;font-family:'Plus Jakarta Sans',Arial,sans-serif;font-size:13px;font-weight:bold;padding:13px 30px;border-radius:999px;">Add to calendar</a>
	</div>

	<table style="width:100%;border-collapse:collapse;font-family:'Plus Jakarta Sans',Arial,sans-serif;font-size:13px;background:#fff;border-radius:8px;overflow:hidden;">
		<tr>
			<td style="padding:14px;color:<?= $c_muted ?>;border-bottom:1px solid <?= $c_border ?>;vertical-align:top;font-family:'Plus Jakarta Sans',Arial,sans-serif;">Date</td>
			<td style="padding:14px;text-align:right;border-bottom:1px solid <?= $c_border ?>;color:<?= $c_ink ?>;font-family:'Plus Jakarta Sans',Arial,sans-serif;"><?= tgl_indo($schedule['event_date']) ?></td>
		</tr>
		<tr>
			<td style="padding:14px;color:<?= $c_muted ?>;border-bottom:1px solid <?= $c_border ?>;vertical-align:top;font-family:'Plus Jakarta Sans',Arial,sans-serif;">Time</td>
			<td style="padding:14px;text-align:right;border-bottom:1px solid <?= $c_border ?>;color:<?= $c_ink ?>;font-family:'Plus Jakarta Sans',Arial,sans-serif;">
				<?= substr($schedule['event_time'], 0, 5) ?> WIB
				<div style="font-size:11px;color:<?= $c_muted ?>;margin-top:6px;font-family:'Plus Jakarta Sans',Arial,sans-serif;">please arrive 15 minutes early</div>
			</td>
		</tr>
		<?php if (!empty($event['venue_name'])): ?>
		<tr>
			<td style="padding:14px;color:<?= $c_muted ?>;border-bottom:1px solid <?= $c_border ?>;vertical-align:top;font-family:'Plus Jakarta Sans',Arial,sans-serif;">Venue</td>
			<td style="padding:14px;text-align:right;border-bottom:1px solid <?= $c_border ?>;color:<?= $c_ink ?>;font-family:'Plus Jakarta Sans',Arial,sans-serif;">
				<?= htmlspecialchars($event['venue_name']) ?>
				<?php if (!empty($event['venue_address'])): ?><div style="font-size:12px;color:<?= $c_ink ?>;margin-top:6px;font-family:'Plus Jakarta Sans',Arial,sans-serif;"><?= htmlspecialchars($event['venue_address']) ?></div><?php endif; ?>
				<div style="margin-top:6px;font-family:'Plus Jakarta Sans',Arial,sans-serif;"><a href="<?= htmlspecialchars($maps_url) ?>" style="font-size:12px;color:<?= $c_accent ?>;">Open in Maps</a></div>
			</td>
		</tr>
		<?php endif; ?>
		<tr>
			<td style="padding:14px;color:<?= $c_muted ?>;border-bottom:1px solid <?= $c_border ?>;vertical-align:top;font-family:'Plus Jakarta Sans',Arial,sans-serif;">Guests</td>
			<td style="padding:14px;text-align:right;border-bottom:1px solid <?= $c_border ?>;color:<?= $c_ink ?>;font-family:'Plus Jakarta Sans',Arial,sans-serif;">
				<?= (int) $rsvp['guest_count'] ?>
				<?php if (!empty($guest_names)): ?><div style="font-size:12px;color:<?= $c_ink ?>;margin-top:6px;font-family:'Plus Jakarta Sans',Arial,sans-serif;"><?= htmlspecialchars(implode(', ', $guest_names)) ?></div><?php endif; ?>
			</td>
		</tr>
		<?php if (!empty($event['dresscode'])): ?>
		<tr>
			<td style="padding:14px;color:<?= $c_muted ?>;border-bottom:1px solid <?= $c_border ?>;font-family:'Plus Jakarta Sans',Arial,sans-serif;">Dress Code</td>
			<td style="padding:14px;text-align:right;border-bottom:1px solid <?= $c_border ?>;color:<?= $c_ink ?>;font-family:'Plus Jakarta Sans',Arial,sans-serif;"><?= htmlspecialchars($event['dresscode']) ?></td>
		</tr>
		<?php endif; ?>
		<tr>
			<td style="padding:14px;color:<?= $c_muted ?>;vertical-align:top;font-family:'Plus Jakarta Sans',Arial,sans-serif;">Deposit</td>
			<td style="padding:14px;text-align:right;color:<?= $c_ink ?>;font-family:'Plus Jakarta Sans',Arial,sans-serif;">
				<b><?= rupiah($rsvp['deposit_amount']) ?></b> paid
				<div style="font-size:11px;color:<?= $c_muted ?>;margin-top:6px;font-family:'Plus Jakarta Sans',Arial,sans-serif;">counts towards your bill on the night</div>
			</td>
		</tr>
	</table>

	<?php if (!empty($event['rsvp_assistance_phone'])): ?>
	<p style="font-family:'Plus Jakarta Sans',Arial,sans-serif;font-size:12px;color:<?= $c_muted ?>;text-align:center;margin-top:22px;">
		Plans changed? Let us know on WhatsApp at <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $event['rsvp_assistance_phone']) ?>" style="color:<?= $c_accent ?>;"><?= htmlspecialchars($event['rsvp_assistance_phone']) ?></a>.
	</p>
	<?php endif; ?>

	<p style="font-family:'Plus Jakarta Sans',Arial,sans-serif;font-size:11px;color:<?= $c_muted ?>;text-align:center;margin-top:18px;">
		Sent automatically for booking <?= htmlspecialchars($rsvp['ticket_code']) ?>.
	</p>
</div>
