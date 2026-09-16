<?php
$display_name = $event['event_name'] !== '' ? $event['event_name'] : 'Atambah';
$guest_names = array_column($guests, 'guest_name');
$maps_query = trim(($event['venue_name'] ?? '') . ', ' . ($event['venue_address'] ?? ''), ', ');
$maps_url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($maps_query);

$cal_start = new DateTime($schedule['event_date'] . ' ' . $schedule['event_time']);
$cal_end = (clone $cal_start)->modify('+3 hours');
$cal_url = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
	. '&text=' . rawurlencode($display_name)
	. '&dates=' . $cal_start->format('Ymd\THis') . '/' . $cal_end->format('Ymd\THis')
	. '&details=' . rawurlencode('Booking code: ' . $rsvp['ticket_code'])
	. '&location=' . rawurlencode($maps_query);
?>
<div style="font-family:'Plus Jakarta Sans',Arial,sans-serif;max-width:520px;margin:0 auto;background:#F7F2E9;padding:24px;">
	<div style="text-align:center;margin-bottom:18px;">
		<div style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#B8873B;font-family:'Plus Jakarta Sans',Arial,sans-serif;">Reservation Confirmed</div>
		<div style="font-size:26px;color:#201B14;margin-top:8px;font-weight:bold;"><?= htmlspecialchars($display_name) ?></div>
	</div>

	<p style="font-family:'Plus Jakarta Sans',Arial,sans-serif;font-size:14px;color:#3a3226;line-height:1.6;">
		Hi <b><?= htmlspecialchars($rsvp['orderer_name']) ?></b>, your deposit of <b><?= rupiah($rsvp['deposit_amount']) ?></b> went through.
		Your table is set for <b><?= tgl_indo($schedule['event_date']) ?></b> at <b><?= substr($schedule['event_time'], 0, 5) ?> WIB</b>.
	</p>

	<div style="background:#fff;border:1px solid #EFE6D6;border-radius:14px;padding:22px;text-align:center;margin:20px 0;">
		<div style="font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#8A7F6D;margin-bottom:14px;">Your E-Ticket</div>
		<img src="<?= base_url('rsvp-qrcode/' . $rsvp['ticket_code']) ?>" alt="QR code" style="width:160px;height:160px;">
		<div style="font-family:monospace;font-size:16px;font-weight:bold;color:#201B14;margin-top:12px;letter-spacing:.05em;"><?= htmlspecialchars($rsvp['ticket_code']) ?></div>
		<div style="font-size:12px;color:#8A7F6D;margin-top:8px;">Show this at the door. One code covers all <?= (int) $rsvp['guest_count'] ?> guests.</div>
	</div>

	<div style="text-align:center;margin-bottom:22px;">
		<a href="<?= htmlspecialchars($cal_url) ?>" style="display:inline-block;background:#201B14;color:#fff;text-decoration:none;font-family:'Plus Jakarta Sans',Arial,sans-serif;font-size:13px;font-weight:bold;padding:13px 30px;border-radius:999px;">Add to calendar</a>
	</div>

	<table style="width:100%;border-collapse:collapse;font-family:'Plus Jakarta Sans',Arial,sans-serif;font-size:13px;background:#fff;border-radius:8px;overflow:hidden;">
		<tr>
			<td style="padding:14px;color:#8A7F6D;border-bottom:1px solid #EFE6D6;vertical-align:top;">Date</td>
			<td style="padding:14px;text-align:right;border-bottom:1px solid #EFE6D6;"><?= tgl_indo($schedule['event_date']) ?></td>
		</tr>
		<tr>
			<td style="padding:14px;color:#8A7F6D;border-bottom:1px solid #EFE6D6;vertical-align:top;">Time</td>
			<td style="padding:14px;text-align:right;border-bottom:1px solid #EFE6D6;">
				<?= substr($schedule['event_time'], 0, 5) ?> WIB
				<div style="font-size:11px;color:#8A7F6D;margin-top:2px;">please arrive 15 minutes early</div>
			</td>
		</tr>
		<?php if (!empty($event['venue_name'])): ?>
		<tr>
			<td style="padding:14px;color:#8A7F6D;border-bottom:1px solid #EFE6D6;vertical-align:top;">Venue</td>
			<td style="padding:14px;text-align:right;border-bottom:1px solid #EFE6D6;">
				<?= htmlspecialchars($event['venue_name']) ?>
				<?php if (!empty($event['venue_address'])): ?><div style="font-size:12px;color:#3a3226;margin-top:2px;"><?= htmlspecialchars($event['venue_address']) ?></div><?php endif; ?>
				<div style="margin-top:4px;"><a href="<?= htmlspecialchars($maps_url) ?>" style="font-size:12px;color:#B8873B;">Open in Maps</a></div>
			</td>
		</tr>
		<?php endif; ?>
		<tr>
			<td style="padding:14px;color:#8A7F6D;border-bottom:1px solid #EFE6D6;vertical-align:top;">Guests</td>
			<td style="padding:14px;text-align:right;border-bottom:1px solid #EFE6D6;">
				<?= (int) $rsvp['guest_count'] ?>
				<?php if (!empty($guest_names)): ?><div style="font-size:12px;color:#3a3226;margin-top:2px;"><?= htmlspecialchars(implode(', ', $guest_names)) ?></div><?php endif; ?>
			</td>
		</tr>
		<?php if (!empty($event['dresscode'])): ?>
		<tr>
			<td style="padding:14px;color:#8A7F6D;border-bottom:1px solid #EFE6D6;">Dress Code</td>
			<td style="padding:14px;text-align:right;border-bottom:1px solid #EFE6D6;"><?= htmlspecialchars($event['dresscode']) ?></td>
		</tr>
		<?php endif; ?>
		<tr>
			<td style="padding:14px;color:#8A7F6D;vertical-align:top;">Deposit</td>
			<td style="padding:14px;text-align:right;">
				<b><?= rupiah($rsvp['deposit_amount']) ?></b> paid
				<div style="font-size:11px;color:#8A7F6D;margin-top:2px;">counts towards your bill on the night</div>
			</td>
		</tr>
	</table>

	<?php if (!empty($event['rsvp_assistance_phone'])): ?>
	<p style="font-family:'Plus Jakarta Sans',Arial,sans-serif;font-size:12px;color:#8A7F6D;text-align:center;margin-top:22px;">
		Plans changed? Let us know on WhatsApp at <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $event['rsvp_assistance_phone']) ?>" style="color:#B8873B;"><?= htmlspecialchars($event['rsvp_assistance_phone']) ?></a> and we will sort out the table.
	</p>
	<?php endif; ?>

	<p style="font-family:'Plus Jakarta Sans',Arial,sans-serif;font-size:11px;color:#8A7F6D;text-align:center;margin-top:18px;">
		Atambah<br>
		Sent automatically for booking <?= htmlspecialchars($rsvp['ticket_code']) ?>.
	</p>
</div>
