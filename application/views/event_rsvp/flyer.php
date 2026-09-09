<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($event['event_name']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
	:root {
		--ink: #0E0C09;
		--gold: #C9A15A;
		--gold-soft: #E8D9BE;
		--muted: #B8AFA0;
	}
	* { box-sizing: border-box; }
	body {
		margin: 0;
		background: var(--ink);
		color: #fff;
		font-family: 'Inter', sans-serif;
		-webkit-font-smoothing: antialiased;
	}
	.shell { max-width: 480px; margin: 0 auto; min-height: 100vh; background: var(--ink); }

	.flyer-photo {
		position: relative;
		min-height: 62vh;
		background-color: #1a1712;
		background-size: cover;
		background-position: center;
		display: flex;
		flex-direction: column;
		justify-content: flex-end;
		padding: 32px 26px 28px;
	}
	.flyer-photo::before {
		content: '';
		position: absolute; inset: 0;
		background: linear-gradient(180deg, rgba(14,12,9,.15) 0%, rgba(14,12,9,.55) 55%, rgba(14,12,9,.97) 100%);
	}
	.flyer-photo > * { position: relative; z-index: 1; }

	.eyebrow { color: var(--gold); font-size: 12px; font-weight: 700; letter-spacing: .22em; text-transform: uppercase; margin-bottom: 10px; }
	h1.title {
		font-family: 'Cormorant Garamond', serif;
		font-size: clamp(34px, 9vw, 46px);
		font-weight: 700;
		line-height: 1.08;
		margin: 0 0 10px;
		text-transform: uppercase;
		letter-spacing: .02em;
	}
	.rule { width: 46px; height: 1px; background: var(--gold); margin: 14px 0; }
	.desc { font-size: 14px; line-height: 1.6; color: #EFE9DD; max-width: 340px; }

	.info-panel { padding: 26px 24px 34px; }
	.info-grid {
		display: grid;
		grid-template-columns: repeat(2, 1fr);
		gap: 18px 14px;
		margin-bottom: 26px;
	}
	.info-grid .lbl { color: var(--gold); font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; margin-bottom: 4px; }
	.info-grid .val { font-family: 'Cormorant Garamond', serif; font-size: 19px; font-weight: 600; line-height: 1.25; }
	.info-grid .sub { color: var(--muted); font-size: 12px; margin-top: 2px; }

	.cta-btn {
		display: block; width: 100%; text-align: center; text-decoration: none;
		background: var(--gold); color: var(--ink); font-weight: 700; font-size: 15px;
		letter-spacing: .03em; padding: 16px; border-radius: 12px; margin-top: 6px;
	}

	.rsvp-line { text-align: center; font-size: 12px; color: var(--muted); margin-top: 16px; }
	.rsvp-line a { color: var(--gold-soft); text-decoration: none; font-weight: 600; }
</style>
</head>
<body>
<div class="shell">
	<div class="flyer-photo"<?= !empty($event['flyer_background']) ? ' style="background-image:url(\'' . base_url('assets/images/events/' . $event['flyer_background']) . '\')"' : '' ?>>
		<div class="eyebrow">You're Invited</div>
		<h1 class="title"><?= htmlspecialchars($event['event_name']) ?></h1>
		<?php if (!empty($event['tagline'])): ?>
			<div class="rule"></div>
			<p class="desc"><?= nl2br(htmlspecialchars($event['tagline'])) ?></p>
		<?php endif; ?>
	</div>

	<div class="info-panel">
		<div class="info-grid">
			<div>
				<div class="lbl">Date</div>
				<div class="val"><?= htmlspecialchars($date_text ?: '-') ?></div>
			</div>
			<div>
				<div class="lbl">Time</div>
				<div class="val"><?= htmlspecialchars($time_text ?: '-') ?></div>
			</div>
			<?php if (!empty($event['price_text'])): ?>
			<div>
				<div class="lbl">Price</div>
				<div class="val"><?= htmlspecialchars($event['price_text']) ?></div>
			</div>
			<?php endif; ?>
			<?php if (!empty($event['venue_name'])): ?>
			<div>
				<div class="lbl">Venue</div>
				<div class="val"><?= htmlspecialchars($event['venue_name']) ?></div>
				<?php if (!empty($event['venue_address'])): ?><div class="sub"><?= htmlspecialchars($event['venue_address']) ?></div><?php endif; ?>
			</div>
			<?php endif; ?>
			<?php if (!empty($event['dresscode'])): ?>
			<div>
				<div class="lbl">Dresscode</div>
				<div class="val"><?= htmlspecialchars($event['dresscode']) ?></div>
			</div>
			<?php endif; ?>
		</div>

		<a href="<?= base_url('rsvp/' . $event['slug'] . '/daftar') ?>" class="cta-btn">RSVP Now &rarr;</a>

		<?php if (!empty($event['rsvp_assistance_phone'])): ?>
			<p class="rsvp-line">RSVP Assistance &middot; <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $event['rsvp_assistance_phone']) ?>"><?= htmlspecialchars($event['rsvp_assistance_phone']) ?></a></p>
		<?php endif; ?>
	</div>
</div>
</body>
</html>
