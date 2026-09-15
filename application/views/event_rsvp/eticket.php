<?php $display_name = $event['event_name'] !== '' ? $event['event_name'] : 'Atambah'; ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>E-Ticket — <?= htmlspecialchars($display_name) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500&display=swap" rel="stylesheet">
<style>
	:root {
		--cream: #F7F2E9; --ink: #201B14; --gold: #B8873B; --gold-soft: #E8D9BE; --muted: #8A7F6D;
	}
	* { box-sizing: border-box; }
	body { margin: 0; background: var(--cream); color: var(--ink); font-family: 'Plus Jakarta Sans', sans-serif; -webkit-font-smoothing: antialiased; }
	.shell { max-width: 480px; margin: 0 auto; min-height: 100vh; padding: 28px 24px; }
	.check-circle {
		width: 64px; height: 64px; border-radius: 50%; background: var(--gold-soft); color: var(--gold);
		display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 18px;
	}
	.center { text-align: center; }
	.eyebrow { color: var(--gold); font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
	h1 { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 26px; font-weight: 800; margin: 8px 0; line-height: 1.25; }
	.sub { color: var(--muted); font-size: 14px; margin-bottom: 26px; }

	.ticket-card { background: var(--ink); color: #fff; border-radius: 16px; padding: 22px 22px 26px; margin-bottom: 18px; }
	.ticket-card .ev-name { font-size: 11px; letter-spacing: .1em; text-transform: uppercase; color: var(--gold); }
	.ticket-card .guest-name { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 20px; font-weight: 700; margin-top: 4px; }
	.ticket-card .guest-name .pax { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 14px; font-weight: 500; color: rgba(255,255,255,.6); }
	.ticket-card hr { border: none; border-top: 1px solid rgba(255,255,255,.15); margin: 16px 0; }
	.ticket-card .lbl { font-size: 10px; color: rgba(255,255,255,.55); text-transform: uppercase; letter-spacing: .08em; }
	.ticket-card .val { font-size: 14px; margin-top: 2px; }
	.ticket-card .guest-list .val { line-height: 1.7; }

	.barcode-card { background: #fff; border: 1px solid var(--gold-soft); border-radius: 14px; padding: 18px; text-align: center; margin-bottom: 16px; }
	.barcode-card .title { font-size: 11px; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); margin-bottom: 10px; }
	.barcode-card img { max-width: 100%; height: 64px; }
	.barcode-card .code { font-family: monospace; font-size: 14px; margin-top: 8px; letter-spacing: .05em; }
	.barcode-card.gift .title { color: var(--gold); }
	.barcode-card.gift .gift-cta { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 16px; font-weight: 700; color: var(--ink); margin-bottom: 12px; }

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
		<h1>Your reservation is confirmed</h1>
		<p class="sub">Show this e-ticket upon arrival.</p>
	</div>

	<div class="ticket-card">
		<div class="ev-name"><?= htmlspecialchars(strtoupper($display_name)) ?></div>
		<div class="guest-name"><?= htmlspecialchars($rsvp['orderer_name']) ?> <span class="pax">| <?= (int) $rsvp['guest_count'] ?> Guests</span></div>
		<hr>
		<div class="lbl">Date &amp; Time</div>
		<div class="val"><?= date('l', strtotime($schedule['event_date'])) ?>, <?= tgl_indo($schedule['event_date']) ?> &middot; <?= date('g:i A', strtotime($schedule['event_time'])) ?></div>
		<?php if (!empty($event['venue_name'])): ?>
		<hr>
		<div class="lbl">Venue</div>
		<div class="val"><?= htmlspecialchars($event['venue_name']) ?><?= !empty($event['venue_address']) ? ', ' . htmlspecialchars($event['venue_address']) : '' ?></div>
		<?php endif; ?>
		<?php if (count($guests) > 1): ?>
		<hr>
		<div class="lbl">Guests</div>
		<div class="guest-list"><?php foreach ($guests as $g): ?><div class="val"><?= htmlspecialchars($g['guest_name']) ?></div><?php endforeach; ?></div>
		<?php endif; ?>
	</div>

	<div class="barcode-card">
		<div class="title">Check-in Barcode</div>
		<img src="<?= base_url('rsvp-barcode/' . $rsvp['ticket_code']) ?>" alt="Barcode tiket">
		<div class="code"><?= htmlspecialchars($rsvp['ticket_code']) ?></div>
	</div>

	<?php if (!empty($rsvp['gift_code'])): ?>
	<div class="barcode-card gift">
		<div class="title">Member Benefit</div>
		<div class="gift-cta">Scan to Unlock Your Gift</div>
		<img src="<?= base_url('rsvp-barcode/' . $rsvp['gift_code']) ?>" alt="Barcode gift" style="opacity:.85;">
		<div class="code"><?= htmlspecialchars($rsvp['gift_code']) ?></div>
	</div>
	<?php endif; ?>

	<a href="javascript:window.print()" class="btn-print">Simpan / Print E-Ticket</a>
</div>
</body>
</html>
