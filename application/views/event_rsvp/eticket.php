<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>E-Ticket — <?= htmlspecialchars($event['event_name']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
	:root {
		--cream: #F7F2E9; --ink: #201B14; --gold: #B8873B; --gold-soft: #E8D9BE; --muted: #8A7F6D;
	}
	* { box-sizing: border-box; }
	body { margin: 0; background: var(--cream); color: var(--ink); font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
	.shell { max-width: 480px; margin: 0 auto; min-height: 100vh; padding: 28px 24px; }
	.check-circle {
		width: 64px; height: 64px; border-radius: 50%; background: var(--gold-soft); color: var(--gold);
		display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 18px;
	}
	.center { text-align: center; }
	.eyebrow { color: var(--gold); font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
	h1 { font-family: 'Cormorant Garamond', serif; font-size: 30px; font-weight: 600; margin: 8px 0; line-height: 1.2; }
	.sub { color: var(--muted); font-size: 14px; margin-bottom: 26px; }

	.ticket-card { background: var(--ink); color: #fff; border-radius: 16px; padding: 22px 22px 26px; margin-bottom: 18px; }
	.ticket-card .top-row { display: flex; justify-content: space-between; align-items: flex-start; }
	.ticket-card .ev-name { font-size: 11px; letter-spacing: .1em; text-transform: uppercase; color: var(--gold); }
	.ticket-card .pax { font-size: 11px; background: rgba(255,255,255,.12); border-radius: 999px; padding: 3px 10px; }
	.ticket-card .guest-name { font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 600; margin-top: 4px; }
	.ticket-card hr { border: none; border-top: 1px solid rgba(255,255,255,.15); margin: 16px 0; }
	.ticket-card .lbl { font-size: 10px; color: rgba(255,255,255,.55); text-transform: uppercase; letter-spacing: .08em; }
	.ticket-card .val { font-size: 14px; margin-top: 2px; }
	.ticket-card .row2 { display: flex; gap: 24px; }

	.barcode-card { background: #fff; border: 1px solid var(--gold-soft); border-radius: 14px; padding: 18px; text-align: center; margin-bottom: 16px; }
	.barcode-card .title { font-size: 11px; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); margin-bottom: 10px; }
	.barcode-card img { max-width: 100%; height: 64px; }
	.barcode-card .code { font-family: monospace; font-size: 14px; margin-top: 8px; letter-spacing: .05em; }
	.barcode-card.gift .title { color: var(--gold); }

	.btn-print {
		display: block; width: 100%; text-align: center; border: 1.5px solid var(--ink); border-radius: 12px;
		padding: 15px; font-weight: 700; font-size: 14px; background: #fff; color: var(--ink); cursor: pointer;
		text-decoration: none; margin-top: 6px;
	}
	@media print { .btn-print { display: none; } }
</style>
</head>
<body>
<div class="shell">
	<div class="center">
		<div class="check-circle">&#10003;</div>
		<span class="eyebrow">E-Ticket</span>
		<h1>We look forward to<br>welcoming you.</h1>
		<p class="sub">Simpan halaman ini dan tunjukkan barcode saat tiba di lokasi.</p>
	</div>

	<div class="ticket-card">
		<div class="top-row">
			<span class="ev-name"><?= htmlspecialchars(strtoupper($event['event_name'])) ?></span>
			<span class="pax"><?= (int) $rsvp['guest_count'] ?> PAX</span>
		</div>
		<div class="guest-name"><?= htmlspecialchars($rsvp['orderer_name']) ?></div>
		<hr>
		<div class="row2">
			<div>
				<div class="lbl">Date</div>
				<div class="val"><?= tgl_indo($schedule['event_date']) ?></div>
			</div>
			<div>
				<div class="lbl">Time</div>
				<div class="val"><?= substr($schedule['event_time'], 0, 5) ?> WIB</div>
			</div>
		</div>
		<?php if (!empty($event['venue_name'])): ?>
		<hr>
		<div class="lbl">Venue</div>
		<div class="val"><?= htmlspecialchars($event['venue_name']) ?></div>
		<?php endif; ?>
		<?php if (count($guests) > 1): ?>
		<hr>
		<div class="lbl">Tamu</div>
		<div class="val"><?= htmlspecialchars(implode(', ', array_column($guests, 'guest_name'))) ?></div>
		<?php endif; ?>
	</div>

	<div class="barcode-card">
		<div class="title">Guest Identification</div>
		<img src="<?= base_url('rsvp-barcode/' . $rsvp['ticket_code']) ?>" alt="Barcode tiket">
		<div class="code"><?= htmlspecialchars($rsvp['ticket_code']) ?></div>
	</div>

	<?php if (!empty($rsvp['gift_code'])): ?>
	<div class="barcode-card gift">
		<div class="title">Member Benefit &mdash; Register to Unlock Gift</div>
		<img src="<?= base_url('rsvp-barcode/' . $rsvp['gift_code']) ?>" alt="Barcode gift" style="opacity:.85;">
		<div class="code"><?= htmlspecialchars($rsvp['gift_code']) ?></div>
		<div style="font-size:11px;color:var(--muted);margin-top:4px;">Tunjukkan kepada petugas gift counter.</div>
	</div>
	<?php endif; ?>

	<a href="javascript:window.print()" class="btn-print">Simpan / Print E-Ticket</a>
</div>
</body>
</html>
