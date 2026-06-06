



<?php
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	include_once('config.php');
	include_once('top_actions.php');

	$userName = $_SESSION['username'] ?? 'SproutSync User';

	function e($value) {
		return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Settings - SproutSync</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
	<style>
		:root {
			--bg: #fcf9f8;
			--surface: #ffffff;
			--surface-soft: #f0eded;
			--primary: #2f3430;
			--primary-soft: #575f58;
			--mint: #ebe7e3;
			--text: #2f3430;
			--muted: #6f756f;
			--line: #d8d5d1;
			--shadow: rgba(47, 52, 48, 0.1);
		}

		:root[data-theme="dark"] {
			--bg: #000000;
			--surface: #06100b;
			--surface-soft: #071f14;
			--primary: #d8f5de;
			--primary-soft: #84b894;
			--mint: #0b3d29;
			--text: #e7f5ea;
			--muted: #9fb3a5;
			--line: #123b28;
			--shadow: rgba(0, 0, 0, 0.68);
		}

		* { box-sizing: border-box; }

		body {
			margin: 0;
			min-height: 100vh;
			background: #ecebea;
			color: var(--text);
			font-family: "Manrope", Arial, sans-serif;
			display: flex;
			justify-content: center;
			transition: background 0.2s ease, color 0.2s ease;
		}

		:root[data-theme="dark"] body {
			background: #000;
		}

		button,
		a,
		select {
			font: inherit;
		}

		.material-symbols-outlined {
			font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24;
			line-height: 1;
		}

		.page {
			width: 100%;
			max-width: 430px;
			min-height: 100vh;
			background: var(--bg);
			padding: 20px 18px 118px;
		}

		.header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 24px;
		}

		.header-left {
			display: flex;
			align-items: center;
			gap: 12px;
			min-width: 0;
		}

		.back-btn {
			width: 38px;
			height: 38px;
			border-radius: 50%;
			color: var(--primary);
			background: var(--surface-soft);
			display: grid;
			place-items: center;
			text-decoration: none;
			flex: 0 0 auto;
		}

		.header-title {
			display: grid;
			gap: 1px;
			min-width: 0;
		}

		.header-title strong {
			color: var(--primary);
			font-size: 21px;
			line-height: 1.05;
			font-weight: 800;
		}

		.header-title span {
			color: var(--muted);
			font-size: 11px;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: 0.7px;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}

		.hero {
			border: 1px solid var(--line);
			border-radius: 10px;
			background: linear-gradient(135deg, var(--surface), var(--surface-soft));
			padding: 22px;
			box-shadow: 0 4px 14px var(--shadow);
			margin-bottom: 20px;
		}

		.hero h1 {
			margin: 0 0 7px;
			color: var(--primary);
			font-size: 30px;
			line-height: 1.05;
			font-weight: 800;
		}

		.hero p {
			margin: 0;
			color: var(--muted);
			font-size: 13px;
			line-height: 1.45;
		}

		.settings-list {
			display: grid;
			gap: 14px;
		}

		.panel {
			border: 1px solid var(--line);
			border-radius: 10px;
			background: var(--surface);
			box-shadow: 0 2px 10px var(--shadow);
			overflow: hidden;
		}

		.panel-head,
		.setting-row {
			display: grid;
			grid-template-columns: 38px 1fr auto;
			gap: 12px;
			align-items: center;
			padding: 14px;
		}

		.panel-head {
			border-bottom: 1px solid var(--line);
		}

		.setting-row + .setting-row {
			border-top: 1px solid var(--line);
		}

		.icon-chip {
			width: 38px;
			height: 38px;
			border-radius: 50%;
			background: var(--mint);
			color: var(--primary);
			display: grid;
			place-items: center;
		}

		.setting-title {
			display: block;
			color: var(--primary);
			font-size: 15px;
			line-height: 1.15;
			font-weight: 800;
		}

		.setting-copy {
			display: block;
			margin-top: 3px;
			color: var(--muted);
			font-size: 12px;
			line-height: 1.3;
		}

		.action-btn,
		.dummy-btn {
			border: 0;
			border-radius: 8px;
			background: var(--primary);
			color: var(--bg);
			padding: 10px 12px;
			font-size: 10px;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: 0.6px;
			cursor: pointer;
			white-space: nowrap;
		}

		.dummy-btn {
			background: var(--surface-soft);
			color: var(--primary);
			border: 1px solid var(--line);
		}

		.segmented {
			display: inline-grid;
			grid-template-columns: 1fr 1fr;
			gap: 4px;
			padding: 4px;
			border: 1px solid var(--line);
			border-radius: 10px;
			background: var(--surface-soft);
		}

		.segmented button {
			border: 0;
			border-radius: 7px;
			background: transparent;
			color: var(--muted);
			padding: 8px 10px;
			display: grid;
			place-items: center;
			cursor: pointer;
		}

		.segmented button.active {
			background: var(--primary);
			color: var(--bg);
		}

		.language-select {
			min-width: 112px;
			border: 1px solid var(--line);
			border-radius: 9px;
			background: var(--surface-soft);
			color: var(--primary);
			padding: 9px 10px;
			font-size: 12px;
			font-weight: 800;
			cursor: pointer;
		}

		.status-pill {
			border-radius: 999px;
			background: var(--surface-soft);
			color: var(--muted);
			border: 1px solid var(--line);
			padding: 7px 10px;
			font-size: 10px;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			white-space: nowrap;
		}

		.modal {
			position: fixed;
			inset: 0;
			z-index: 120;
			background: rgba(35, 35, 35, 0.54);
			backdrop-filter: blur(8px);
			display: none;
			align-items: center;
			justify-content: center;
			padding: 20px;
		}

		.modal.open {
			display: flex;
		}

		.manual-card {
			width: min(100%, 390px);
			max-height: 82vh;
			overflow-y: auto;
			border: 1px solid var(--line);
			border-radius: 14px;
			background: var(--surface);
			color: var(--text);
			box-shadow: 0 28px 70px rgba(35, 35, 35, 0.28);
			padding: 20px;
		}

		.manual-head {
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			gap: 14px;
			margin-bottom: 16px;
		}

		.manual-head h2 {
			margin: 0 0 4px;
			color: var(--primary);
			font-size: 23px;
			line-height: 1.1;
		}

		.manual-head p {
			margin: 0;
			color: var(--muted);
			font-size: 12px;
			line-height: 1.35;
		}

		.close-btn {
			width: 36px;
			height: 36px;
			border: 0;
			border-radius: 50%;
			background: var(--surface-soft);
			color: var(--primary);
			display: grid;
			place-items: center;
			cursor: pointer;
			flex: 0 0 auto;
		}

		.manual-steps {
			display: grid;
			gap: 10px;
			counter-reset: step;
		}

		.manual-step {
			position: relative;
			border: 1px solid var(--line);
			border-radius: 9px;
			background: var(--bg);
			padding: 13px 13px 13px 46px;
			color: var(--muted);
			font-size: 12px;
			line-height: 1.35;
		}

		.manual-step::before {
			counter-increment: step;
			content: counter(step);
			position: absolute;
			left: 13px;
			top: 13px;
			width: 22px;
			height: 22px;
			border-radius: 50%;
			background: var(--primary);
			color: var(--bg);
			display: grid;
			place-items: center;
			font-size: 11px;
			font-weight: 800;
		}

		.manual-step strong {
			display: block;
			margin-bottom: 2px;
			color: var(--primary);
			font-size: 13px;
		}

		.toast {
			position: fixed;
			left: 50%;
			bottom: 92px;
			z-index: 130;
			transform: translate(-50%, 16px);
			min-width: 240px;
			max-width: calc(100vw - 32px);
			border-radius: 12px;
			background: var(--primary);
			color: var(--bg);
			padding: 12px 14px;
			box-shadow: 0 14px 36px var(--shadow);
			font-size: 13px;
			text-align: center;
			opacity: 0;
			pointer-events: none;
			transition: opacity 0.2s ease, transform 0.2s ease;
		}

		.toast.show {
			opacity: 1;
			transform: translate(-50%, 0);
		}

		@media (max-width: 374px) {
			.page { padding-left: 12px; padding-right: 12px; }
			.panel-head,
			.setting-row {
				grid-template-columns: 34px 1fr;
			}
			.panel-head > :last-child,
			.setting-row > :last-child {
				grid-column: 2;
				justify-self: start;
			}
		}
	</style>
	<script>
		(function() {
			const savedTheme = localStorage.getItem('sproutsync-theme') || 'light';
			document.documentElement.dataset.theme = savedTheme;
		})();
	</script>
</head>
<body>
	<div class="page">
		<header class="header">
			<div class="header-left">
				<a class="back-btn" href="home.php" aria-label="Back to home">
					<span class="material-symbols-outlined">arrow_back</span>
				</a>
				<div class="header-title">
					<strong data-i18n="headerTitle">Settings</strong>
					<span><?php echo e($userName); ?></span>
				</div>
			</div>
			<?php ss_render_top_actions($conn); ?>
		</header>

		<section class="hero">
			<h1 data-i18n="heroTitle">Control Center</h1>
			<p data-i18n="heroCopy">Tune the app experience, open the service manual, and manage your SproutSync preferences.</p>
		</section>

		<main class="settings-list">
			<section class="panel" aria-label="Core settings">
				<div class="panel-head">
					<span class="icon-chip"><span class="material-symbols-outlined">support_agent</span></span>
					<span>
						<span class="setting-title" data-i18n="manualTitle">Service Manual</span>
						<span class="setting-copy" data-i18n="manualCopy">Open setup, scanning, watering, and device-care guidance.</span>
					</span>
					<button class="action-btn" type="button" onclick="openManual()" data-i18n="openButton">Open</button>
				</div>

				<div class="setting-row">
					<span class="icon-chip"><span class="material-symbols-outlined">routine</span></span>
					<span>
						<span class="setting-title" data-i18n="themeTitle">Theme</span>
						<span class="setting-copy" data-i18n="themeCopy">Switch between light and dark mode. Your choice is saved.</span>
					</span>
					<span class="segmented" aria-label="Theme">
						<button type="button" id="light-theme" onclick="setTheme('light')" aria-label="Light theme">
							<span class="material-symbols-outlined">light_mode</span>
						</button>
						<button type="button" id="dark-theme" onclick="setTheme('dark')" aria-label="Dark theme">
							<span class="material-symbols-outlined">dark_mode</span>
						</button>
					</span>
				</div>

				<div class="setting-row">
					<span class="icon-chip"><span class="material-symbols-outlined">translate</span></span>
					<span>
						<span class="setting-title" data-i18n="languageTitle">Language</span>
						<span class="setting-copy" data-i18n="languageCopy">Change the settings screen language.</span>
					</span>
					<select class="language-select" id="language-select" onchange="setLanguage(this.value)" aria-label="Language">
						<option value="en">English</option>
						<option value="es">Español</option>
						<option value="pl">Polski</option>
						<option value="sq">Shqip</option>
						<option value="de">Deutsch</option>
						<option value="fr">Francais</option>
					</select>
				</div>
			</section>

			<section class="panel" aria-label="More settings">
				<div class="setting-row">
					<span class="icon-chip"><span class="material-symbols-outlined">notifications_active</span></span>
					<span>
						<span class="setting-title" data-i18n="pushTitle">Push Alerts</span>
						<span class="setting-copy" data-i18n="dummyCopy">Coming soon.</span>
					</span>
					<button class="dummy-btn" type="button" onclick="showToast(translations[currentLanguage].comingSoon)">Setup</button>
				</div>
				<div class="setting-row">
					<span class="icon-chip"><span class="material-symbols-outlined">cloud_sync</span></span>
					<span>
						<span class="setting-title" data-i18n="syncTitle">Cloud Backup</span>
						<span class="setting-copy" data-i18n="dummyCopy">Coming soon.</span>
					</span>
					<button class="dummy-btn" type="button" onclick="showToast(translations[currentLanguage].comingSoon)">Connect</button>
				</div>
				<div class="setting-row">
					<span class="icon-chip"><span class="material-symbols-outlined">privacy_tip</span></span>
					<span>
						<span class="setting-title" data-i18n="privacyTitle">Privacy Tools</span>
						<span class="setting-copy" data-i18n="dummyCopy">Coming soon.</span>
					</span>
					<button class="dummy-btn" type="button" onclick="showToast(translations[currentLanguage].comingSoon)">Review</button>
				</div>
				<div class="setting-row">
					<span class="icon-chip"><span class="material-symbols-outlined">memory</span></span>
					<span>
						<span class="setting-title" data-i18n="deviceTitle">Device Diagnostics</span>
						<span class="setting-copy" data-i18n="deviceCopy">Open the existing Cube-Monitor settings page.</span>
					</span>
					<a class="action-btn" href="device.php" data-i18n="openButton">Open</a>
				</div>
			</section>
		</main>
	</div>

	<div class="modal" id="manual-modal" aria-hidden="true">
		<div class="manual-card" role="dialog" aria-modal="true" aria-labelledby="manual-title">
			<div class="manual-head">
				<div>
					<h2 id="manual-title" data-i18n="manualModalTitle">Service Manual</h2>
					<p data-i18n="manualModalCopy">Quick guide for everyday SproutSync care.</p>
				</div>
				<button class="close-btn" type="button" aria-label="Close manual" onclick="closeManual()">
					<span class="material-symbols-outlined">close</span>
				</button>
			</div>
			<div class="manual-steps">
				<div class="manual-step">
					<strong data-i18n="stepOneTitle">Scan plants in good light</strong>
					<span data-i18n="stepOneCopy">Hold the camera steady and keep the full leaf or flower inside the frame.</span>
				</div>
				<div class="manual-step">
					<strong data-i18n="stepTwoTitle">Save confirmed plants</strong>
					<span data-i18n="stepTwoCopy">After a scan, add the plant to your garden so notifications and history can track it.</span>
				</div>
				<div class="manual-step">
					<strong data-i18n="stepThreeTitle">Check health alerts</strong>
					<span data-i18n="stepThreeCopy">Open the notification bell for watering, device, and diagnosis messages.</span>
				</div>
				<div class="manual-step">
					<strong data-i18n="stepFourTitle">Maintain devices</strong>
					<span data-i18n="stepFourCopy">Keep Cube-Monitor online and recalibrate sensors when readings look unusual.</span>
				</div>
			</div>
		</div>
	</div>

	<div class="toast" id="settings-toast" role="status" aria-live="polite"></div>

	<style>
		.ss-icon-action {
			color: var(--primary);
		}

		.ss-icon-action:hover,
		.ss-icon-action:focus-visible {
			background: var(--surface-soft);
		}

		.ss-action-badge {
			border-color: var(--bg);
		}

		.ss-notification-panel {
			background: var(--surface);
			border-color: var(--line);
			box-shadow: 0 20px 50px var(--shadow);
		}

		.ss-notification-head,
		.ss-notification-title {
			color: var(--primary);
		}

		.ss-notification-count,
		.ss-notification-empty,
		.ss-notification-time {
			color: var(--muted);
		}

		.ss-notification-item,
		.ss-notification-item.unread {
			background: var(--bg);
			border-color: var(--line);
			color: var(--text);
		}

		.ss-notification-dot {
			background: var(--surface-soft);
			color: var(--primary);
		}
	</style>

	<?php $activePage = 'settings'; include('nav.php'); ?>

	<script>
		const translations = {
			en: {
				headerTitle: 'Settings',
				heroTitle: 'Control Center',
				heroCopy: 'Tune the app experience, open the service manual, and manage your SproutSync preferences.',
				manualTitle: 'Service Manual',
				manualCopy: 'Open setup, scanning, watering, and device-care guidance.',
				openButton: 'Open',
				themeTitle: 'Theme',
				themeCopy: 'Switch between light and dark mode. Your choice is saved.',
				languageTitle: 'Language',
				languageCopy: 'Change the settings screen language.',
				pushTitle: 'Push Alerts',
				syncTitle: 'Cloud Backup',
				privacyTitle: 'Privacy Tools',
				deviceTitle: 'Device Diagnostics',
				deviceCopy: 'Open the existing Cube-Monitor settings page.',
				dummyCopy: 'Coming soon.',
				manualModalTitle: 'Service Manual',
				manualModalCopy: 'Quick guide for everyday SproutSync care.',
				stepOneTitle: 'Scan plants in good light',
				stepOneCopy: 'Hold the camera steady and keep the full leaf or flower inside the frame.',
				stepTwoTitle: 'Save confirmed plants',
				stepTwoCopy: 'After a scan, add the plant to your garden so notifications and history can track it.',
				stepThreeTitle: 'Check health alerts',
				stepThreeCopy: 'Open the notification bell for watering, device, and diagnosis messages.',
				stepFourTitle: 'Maintain devices',
				stepFourCopy: 'Keep Cube-Monitor online and recalibrate sensors when readings look unusual.',
				themeSaved: 'Theme saved.',
				languageSaved: 'Language updated.',
				comingSoon: 'This setting is coming soon.'
			},
			es: {
				headerTitle: 'Ajustes',
				heroTitle: 'Centro de Control',
				heroCopy: 'Ajusta la experiencia, abre el manual de servicio y administra tus preferencias.',
				manualTitle: 'Manual de Servicio',
				manualCopy: 'Abre guías de configuración, escaneo, riego y cuidado del dispositivo.',
				openButton: 'Abrir',
				themeTitle: 'Tema',
				themeCopy: 'Cambia entre modo claro y oscuro. Tu elección se guarda.',
				languageTitle: 'Idioma',
				languageCopy: 'Cambia el idioma de esta pantalla.',
				pushTitle: 'Alertas Push',
				syncTitle: 'Copia en la Nube',
				privacyTitle: 'Privacidad',
				deviceTitle: 'Diagnóstico del Dispositivo',
				deviceCopy: 'Abre la página actual de Cube-Monitor.',
				dummyCopy: 'Próximamente.',
				manualModalTitle: 'Manual de Servicio',
				manualModalCopy: 'Guía rápida para el cuidado diario de SproutSync.',
				stepOneTitle: 'Escanea con buena luz',
				stepOneCopy: 'Mantén la cámara estable y la hoja o flor completa dentro del marco.',
				stepTwoTitle: 'Guarda plantas confirmadas',
				stepTwoCopy: 'Después de escanear, agrega la planta a tu jardín para activar historial y avisos.',
				stepThreeTitle: 'Revisa alertas de salud',
				stepThreeCopy: 'Abre la campana para mensajes de riego, dispositivo y diagnóstico.',
				stepFourTitle: 'Mantén los dispositivos',
				stepFourCopy: 'Mantén Cube-Monitor conectado y recalibra sensores si las lecturas parecen raras.',
				themeSaved: 'Tema guardado.',
				languageSaved: 'Idioma actualizado.',
				comingSoon: 'Esta opción estará disponible pronto.'
			},
			pl: {
				headerTitle: 'Ustawienia',
				heroTitle: 'Centrum Sterowania',
				heroCopy: 'Dostosuj aplikacje, otwórz instrukcje serwisowa i zarzadzaj preferencjami.',
				manualTitle: 'Instrukcja Serwisowa',
				manualCopy: 'Otwórz konfiguracje, skanowanie, podlewanie i wskazówki dla urzadzenia.',
				openButton: 'Otwórz',
				themeTitle: 'Motyw',
				themeCopy: 'Przelacz jasny albo ciemny motyw. Wybor zostanie zapisany.',
				languageTitle: 'Jezyk',
				languageCopy: 'Zmien jezyk ekranu ustawien.',
				pushTitle: 'Alerty Push',
				syncTitle: 'Kopia w Chmurze',
				privacyTitle: 'Prywatnosc',
				deviceTitle: 'Diagnostyka Urzadzenia',
				deviceCopy: 'Otwórz istniejaca strone ustawien Cube-Monitor.',
				dummyCopy: 'Wkrótce.',
				manualModalTitle: 'Instrukcja Serwisowa',
				manualModalCopy: 'Szybki przewodnik codziennej opieki SproutSync.',
				stepOneTitle: 'Skanuj w dobrym swietle',
				stepOneCopy: 'Trzymaj kamere stabilnie i umiesc caly lisc albo kwiat w kadrze.',
				stepTwoTitle: 'Zapisuj potwierdzone rosliny',
				stepTwoCopy: 'Po skanie dodaj rosline do ogrodu, aby wlaczyc historie i powiadomienia.',
				stepThreeTitle: 'Sprawdz alerty zdrowia',
				stepThreeCopy: 'Otwórz dzwonek, aby zobaczyc wiadomosci o podlewaniu, urzadzeniu i diagnozie.',
				stepFourTitle: 'Dbaj o urzadzenia',
				stepFourCopy: 'Utrzymuj Cube-Monitor online i kalibruj sensory, gdy odczyty wygladaja dziwnie.',
				themeSaved: 'Motyw zapisany.',
				languageSaved: 'Jezyk zaktualizowany.',
				comingSoon: 'Ta opcja pojawi sie wkrotce.'
			},
			sq: {
				headerTitle: 'Cilesimet',
				heroTitle: 'Qendra e Kontrollit',
				heroCopy: 'Rregullo pervojen e aplikacionit, hap manualin e sherbimit dhe menaxho preferencat.',
				manualTitle: 'Manuali i Sherbimit',
				manualCopy: 'Hap udhezime per konfigurim, skanim, ujitje dhe kujdes per pajisjen.',
				openButton: 'Hap',
				themeTitle: 'Tema',
				themeCopy: 'Kalo midis temes se hapur dhe AMOLED te erret. Zgjedhja ruhet.',
				languageTitle: 'Gjuha',
				languageCopy: 'Ndrysho gjuhen e ekranit te cilesimeve.',
				pushTitle: 'Njoftime Push',
				syncTitle: 'Rezervim ne Cloud',
				privacyTitle: 'Privatesia',
				deviceTitle: 'Diagnostika e Pajisjes',
				deviceCopy: 'Hap faqen ekzistuese te cilesimeve Cube-Monitor.',
				dummyCopy: 'Se shpejti.',
				manualModalTitle: 'Manuali i Sherbimit',
				manualModalCopy: 'Udhezues i shpejte per kujdesin e perditshem te SproutSync.',
				stepOneTitle: 'Skano bimet ne drite te mire',
				stepOneCopy: 'Mbaje kameren qendrueshem dhe fletan ose lulen brenda kornizes.',
				stepTwoTitle: 'Ruaj bimet e konfirmuara',
				stepTwoCopy: 'Pas skanimit, shto bimen ne kopsht qe historia dhe njoftimet ta ndjekin.',
				stepThreeTitle: 'Kontrollo alarmet e shendetit',
				stepThreeCopy: 'Hap zile njoftimesh per mesazhe ujitjeje, pajisjeje dhe diagnoze.',
				stepFourTitle: 'Mirembaj pajisjet',
				stepFourCopy: 'Mbaje Cube-Monitor online dhe rikalibro sensoret kur leximet duken te pazakonta.',
				themeSaved: 'Tema u ruajt.',
				languageSaved: 'Gjuha u perditesua.',
				comingSoon: 'Ky cilesim vjen se shpejti.'
			},
			de: {
				headerTitle: 'Einstellungen',
				heroTitle: 'Kontrollzentrum',
				heroCopy: 'Passe die App an, offne das Servicehandbuch und verwalte deine SproutSync Einstellungen.',
				manualTitle: 'Servicehandbuch',
				manualCopy: 'Offne Hilfe zu Einrichtung, Scan, Bewasserung und Geratepflege.',
				openButton: 'Offnen',
				themeTitle: 'Design',
				themeCopy: 'Wechsle zwischen hellem Design und AMOLED Dunkelmodus. Die Auswahl wird gespeichert.',
				languageTitle: 'Sprache',
				languageCopy: 'Andere die Sprache dieser Einstellungsseite.',
				pushTitle: 'Push Meldungen',
				syncTitle: 'Cloud Backup',
				privacyTitle: 'Datenschutz',
				deviceTitle: 'Gerate Diagnose',
				deviceCopy: 'Offne die vorhandene Cube-Monitor Einstellungsseite.',
				dummyCopy: 'Kommt bald.',
				manualModalTitle: 'Servicehandbuch',
				manualModalCopy: 'Kurzanleitung fur die tagliche SproutSync Pflege.',
				stepOneTitle: 'Pflanzen bei gutem Licht scannen',
				stepOneCopy: 'Halte die Kamera ruhig und lasse Blatt oder Blute voll im Bild.',
				stepTwoTitle: 'Bestatigte Pflanzen speichern',
				stepTwoCopy: 'Fuge die Pflanze nach dem Scan deinem Garten hinzu, damit Verlauf und Meldungen greifen.',
				stepThreeTitle: 'Gesundheitsmeldungen prufen',
				stepThreeCopy: 'Offne die Glocke fur Bewasserungs-, Gerate- und Diagnosemeldungen.',
				stepFourTitle: 'Gerate warten',
				stepFourCopy: 'Halte Cube-Monitor online und kalibriere Sensoren neu, wenn Werte ungewohnlich wirken.',
				themeSaved: 'Design gespeichert.',
				languageSaved: 'Sprache aktualisiert.',
				comingSoon: 'Diese Einstellung kommt bald.'
			},
			fr: {
				headerTitle: 'Parametres',
				heroTitle: 'Centre de Controle',
				heroCopy: 'Ajuste l experience de l app, ouvre le manuel de service et gere tes preferences SproutSync.',
				manualTitle: 'Manuel de Service',
				manualCopy: 'Ouvre les guides de configuration, scan, arrosage et entretien de l appareil.',
				openButton: 'Ouvrir',
				themeTitle: 'Theme',
				themeCopy: 'Passe du mode clair au mode sombre AMOLED. Ton choix est enregistre.',
				languageTitle: 'Langue',
				languageCopy: 'Change la langue de cet ecran de parametres.',
				pushTitle: 'Alertes Push',
				syncTitle: 'Sauvegarde Cloud',
				privacyTitle: 'Confidentialite',
				deviceTitle: 'Diagnostic Appareil',
				deviceCopy: 'Ouvre la page existante des reglages Cube-Monitor.',
				dummyCopy: 'Bientot disponible.',
				manualModalTitle: 'Manuel de Service',
				manualModalCopy: 'Guide rapide pour l entretien quotidien de SproutSync.',
				stepOneTitle: 'Scanner avec une bonne lumiere',
				stepOneCopy: 'Garde la camera stable et place toute la feuille ou fleur dans le cadre.',
				stepTwoTitle: 'Enregistrer les plantes confirmees',
				stepTwoCopy: 'Apres un scan, ajoute la plante au jardin pour activer historique et notifications.',
				stepThreeTitle: 'Verifier les alertes sante',
				stepThreeCopy: 'Ouvre la cloche pour les messages d arrosage, appareil et diagnostic.',
				stepFourTitle: 'Entretenir les appareils',
				stepFourCopy: 'Garde Cube-Monitor en ligne et recalibre les capteurs si les mesures semblent etranges.',
				themeSaved: 'Theme enregistre.',
				languageSaved: 'Langue mise a jour.',
				comingSoon: 'Ce reglage arrive bientot.'
			}
		};

		let currentLanguage = localStorage.getItem('sproutsync-language') || 'en';
		let toastTimer = null;

		function showToast(message) {
			const toast = document.getElementById('settings-toast');
			toast.textContent = message;
			toast.classList.add('show');
			clearTimeout(toastTimer);
			toastTimer = setTimeout(() => toast.classList.remove('show'), 2200);
		}

		function setTheme(theme) {
			document.documentElement.dataset.theme = theme;
			localStorage.setItem('sproutsync-theme', theme);
			document.getElementById('light-theme').classList.toggle('active', theme === 'light');
			document.getElementById('dark-theme').classList.toggle('active', theme === 'dark');
			showToast(translations[currentLanguage].themeSaved);
		}

		function applyLanguage(language, notify) {
			currentLanguage = translations[language] ? language : 'en';
			document.documentElement.lang = currentLanguage;
			localStorage.setItem('sproutsync-language', currentLanguage);
			document.getElementById('language-select').value = currentLanguage;
			document.querySelectorAll('[data-i18n]').forEach(element => {
				const key = element.dataset.i18n;
				if (translations[currentLanguage][key]) {
					element.textContent = translations[currentLanguage][key];
				}
			});
			if (notify) {
				showToast(translations[currentLanguage].languageSaved);
			}
		}

		function setLanguage(language) {
			applyLanguage(language, true);
		}

		function openManual() {
			const modal = document.getElementById('manual-modal');
			modal.classList.add('open');
			modal.setAttribute('aria-hidden', 'false');
		}

		function closeManual() {
			const modal = document.getElementById('manual-modal');
			modal.classList.remove('open');
			modal.setAttribute('aria-hidden', 'true');
		}

		document.getElementById('manual-modal').addEventListener('click', event => {
			if (event.target.id === 'manual-modal') {
				closeManual();
			}
		});

		document.addEventListener('keydown', event => {
			if (event.key === 'Escape') {
				closeManual();
			}
		});

		setTheme(localStorage.getItem('sproutsync-theme') || 'light');
		applyLanguage(currentLanguage, false);
	</script>
</body>
</html>
