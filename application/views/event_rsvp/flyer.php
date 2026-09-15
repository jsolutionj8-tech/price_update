<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($event['event_name'] ?: 'RSVP') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500&display=swap" rel="stylesheet">
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
		font-family: 'Plus Jakarta Sans', sans-serif;
		-webkit-font-smoothing: antialiased;
	}
	.shell { max-width: 480px; margin: 0 auto; min-height: 100vh; background: var(--ink); display: flex; flex-direction: column; }
	.scroll-area { flex: 1; }

	/* Lebar gambar dipatok penuh selebar card (width:100%, height:auto mengikuti rasio
	   asli) supaya tidak ada letterbox/area hitam kosong di kiri-kanan gambar. */
	.flyer-photo { position: relative; background-color: #1a1712; }
	.flyer-photo.no-img { min-height: 40vh; display: flex; flex-direction: column; justify-content: flex-end; padding: 28px 24px 24px; }
	.flyer-img { display: block; width: 100%; height: auto; }
	.flyer-overlay {
		position: absolute; left: 0; right: 0; bottom: 0;
		padding: 34px 24px 20px;
		background: linear-gradient(180deg, rgba(14,12,9,0) 0%, rgba(14,12,9,.65) 35%, rgba(14,12,9,.97) 60%, var(--ink) 100%);
	}
	.flyer-photo.no-img .flyer-overlay { position: static; padding: 0; background: none; }

	.eyebrow { color: var(--gold); font-size: 12px; font-weight: 700; letter-spacing: .22em; text-transform: uppercase; margin-bottom: 8px; text-shadow: 0 2px 6px rgba(0,0,0,.5); }
	h1.title {
		font-family: 'Plus Jakarta Sans', sans-serif;
		font-size: clamp(26px, 7vw, 36px);
		font-weight: 800;
		line-height: 1.14;
		margin: 0 0 8px;
		text-transform: uppercase;
		letter-spacing: .01em;
		text-shadow: 0 2px 10px rgba(0,0,0,.55);
	}
	.rule { width: 40px; height: 1px; background: var(--gold); margin: 12px 0; }
	.desc {
		font-size: 13px; line-height: 1.6; color: #FFFCF6; max-width: 340px;
		text-shadow: 0 1px 4px rgba(0,0,0,.5);
	}

	.info-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(84px, 1fr));
		gap: 18px 12px;
	}
	.info-grid.has-text-above { margin-top: 20px; padding-top: 18px; border-top: 1px solid rgba(255,255,255,.15); }
	.info-grid .lbl { color: var(--gold); font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; margin-bottom: 6px; text-shadow: 0 1px 4px rgba(0,0,0,.6); }
	.info-grid .val { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 14px; font-weight: 700; line-height: 1.35; text-shadow: 0 1px 4px rgba(0,0,0,.6); }
	.info-grid .sub { color: var(--muted); font-size: 11.5px; margin-top: 3px; text-shadow: 0 1px 4px rgba(0,0,0,.6); }

	.cta-wrap {
		position: sticky; bottom: 0;
		background: var(--ink);
		border-top: 1px solid rgba(255,255,255,.08);
		padding: 14px 24px calc(16px + env(safe-area-inset-bottom));
		margin-top: 10px;
	}
	.cta-btn {
		display: block; width: 100%; text-align: center; text-decoration: none;
		background: var(--gold); color: var(--ink); font-weight: 700; font-size: 15px;
		letter-spacing: .03em; padding: 16px; border-radius: 12px;
	}

	.rsvp-line { display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 12.5px; color: var(--muted); margin: 12px 0 0; }
	.rsvp-line svg { flex-shrink: 0; }
	.rsvp-line a { color: var(--gold); text-decoration: none; font-weight: 700; }
</style>
</head>
<body>
<div class="shell">
	<div class="scroll-area">
		<?php $has_overlay_text = !empty($event['invite_text']) || !empty($event['event_name']) || !empty($event['tagline']); ?>
		<div class="flyer-photo<?= empty($event['flyer_background']) ? ' no-img' : '' ?>">
			<?php if (!empty($event['flyer_background'])): ?>
				<img class="flyer-img" src="<?= base_url('assets/images/events/' . $event['flyer_background']) ?>" alt="<?= htmlspecialchars($event['event_name'] ?: '') ?>">
			<?php endif; ?>
			<div class="flyer-overlay">
				<?php if (!empty($event['invite_text'])): ?>
					<div class="eyebrow"><?= htmlspecialchars($event['invite_text']) ?></div>
				<?php endif; ?>
				<?php if (!empty($event['event_name'])): ?>
					<h1 class="title"><?= htmlspecialchars($event['event_name']) ?></h1>
				<?php endif; ?>
				<?php if (!empty($event['tagline'])): ?>
					<div class="rule"></div>
					<p class="desc"><?= nl2br(htmlspecialchars($event['tagline'])) ?></p>
				<?php endif; ?>

				<div class="info-grid<?= $has_overlay_text ? ' has-text-above' : '' ?>">
					<?php if (!empty($date_text)): ?>
					<div>
						<div class="lbl">Date</div>
						<div class="val"><?= htmlspecialchars($date_text) ?></div>
						<?php if (!empty($day_text)): ?><div class="sub"><?= htmlspecialchars($day_text) ?></div><?php endif; ?>
					</div>
					<?php endif; ?>
					<?php if (!empty($time_text)): ?>
					<div>
						<div class="lbl">Time</div>
						<div class="val"><?= htmlspecialchars($time_text) ?></div>
					</div>
					<?php endif; ?>
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
						<div class="lbl">Dress Code</div>
						<div class="val"><?= htmlspecialchars($event['dresscode']) ?></div>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<div class="cta-wrap">
		<a href="<?= base_url('rsvp/' . $event['slug'] . '/daftar') ?>" class="cta-btn">Reserve Your Seat &rarr;</a>
		<?php if (!empty($event['rsvp_assistance_phone'])): ?>
			<p class="rsvp-line">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="#C9A15A"><path d="M12.04 2c-5.52 0-10 4.48-10 10 0 1.77.46 3.45 1.28 4.9L2 22l5.25-1.38a9.94 9.94 0 0 0 4.79 1.22h.01c5.52 0 10-4.48 10-10s-4.49-9.84-10.01-9.84Zm.01 18.15h-.01a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.11.82.83-3.03-.2-.31a8.18 8.18 0 0 1-1.26-4.36c0-4.52 3.68-8.2 8.21-8.2 2.19 0 4.25.85 5.8 2.41a8.15 8.15 0 0 1 2.4 5.8c0 4.52-3.68 8.2-8.18 8.2Zm4.49-6.14c-.25-.12-1.46-.72-1.68-.8-.23-.08-.39-.12-.56.12-.16.25-.64.8-.78.96-.15.16-.29.18-.54.06-.25-.12-1.04-.38-1.99-1.22-.73-.65-1.23-1.46-1.37-1.7-.15-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.12-.15.16-.25.24-.41.08-.16.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.4-.42-.56-.42-.14-.01-.31-.01-.47-.01-.16 0-.43.06-.66.31-.23.25-.86.84-.86 2.05 0 1.21.88 2.38 1 2.54.12.16 1.73 2.64 4.19 3.7.59.25 1.04.4 1.4.52.59.19 1.12.16 1.54.1.47-.07 1.46-.6 1.66-1.18.21-.58.21-1.07.15-1.18-.06-.1-.23-.16-.48-.28Z"/></svg>
				Need assistance? <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $event['rsvp_assistance_phone']) ?>">WhatsApp <?= htmlspecialchars($event['rsvp_assistance_phone']) ?></a>
			</p>
		<?php endif; ?>
	</div>
</div>
</body>
</html>
