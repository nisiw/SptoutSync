



<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!function_exists('ss_e')) {
	function ss_e($value) {
		return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
	}
}

if (!function_exists('ss_notification_time')) {
	function ss_notification_time($createdAt) {
		if (empty($createdAt)) {
			return '';
		}

		$timestamp = strtotime($createdAt);
		if (!$timestamp) {
			return '';
		}

		$diff = time() - $timestamp;
		if ($diff < 60) {
			return 'Just now';
		}
		if ($diff < 3600) {
			return floor($diff / 60) . 'm ago';
		}
		if ($diff < 86400) {
			return floor($diff / 3600) . 'h ago';
		}

		return date('M j', $timestamp);
	}
}

if (!function_exists('ss_get_notifications')) {
	function ss_get_notifications($conn) {
		$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
		if ($userId <= 0 || !($conn instanceof PDO)) {
			return [];
		}

		try {
			$stmt = $conn->prepare(
				"SELECT n.notification_id, n.title, n.message, n.is_read, n.created_at,
				        p.nickname, s.common_name
				   FROM notifications n
				   LEFT JOIN plants p ON n.plant_id = p.plant_id
				   LEFT JOIN plant_species s ON p.species_id = s.species_id
				  WHERE n.user_id = :user_id
				  ORDER BY n.created_at DESC
				  LIMIT 10"
			);
			$stmt->execute([':user_id' => $userId]);
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (Throwable $e) {
			return [];
		}
	}
}

if (!function_exists('ss_render_top_actions')) {
	function ss_render_top_actions($conn = null) {
		$notifications = ss_get_notifications($conn);
		$unreadCount = count(array_filter($notifications, function($notification) {
			return empty($notification['is_read']);
		}));
		?>
		<script>
			(function() {
				const theme = localStorage.getItem('sproutsync-theme') || 'light';
				document.documentElement.dataset.theme = theme;
			})();
		</script>
		<script>
			window.SS_I18N = {
				en: {
					notifications: 'Notifications', settings: 'Settings', logout: 'Logout', noNotifications: 'No notifications yet.', recent: 'recent',
					navHome: 'Home', navDiagnose: 'Diagnose', navPlants: 'Plants', navDevices: 'Devices', openCamera: 'Open camera',
					homeTitle: 'Your Garden', homeCopy: 'Keep track of your botanical journey and plant health trends.', emptyGarden: 'Start building your garden by adding your first plant.', plantTools: 'Plant Tools', plantToolsCopy: 'Camera-powered diagnostics for your garden', lightMeter: 'Light Meter', potMeasure: 'Pot Measure',
					plantsTitle: 'Scan History', plantsUnavailable: 'Plants unavailable', noScans: 'No scanned plants yet', viewDiagnosis: 'View Diagnosis', scanInsights: 'Scan Insights', chooseFavorites: 'Choose Favorites',
					deviceTitle: 'Cube-Monitor Settings', deviceCopy: 'Configure and monitor your SproutSync device connection and sensors.', connectedDevices: 'Connected Devices', connectionSummary: 'Connection Summary', sensorStatus: 'Sensor Status & Calibration', powerSolar: 'Power & Solar', networkHotspot: 'Network & Hotspot', hotspotSettings: 'Hotspot Settings', configure: 'Configure', change: 'Change', deviceSettings: 'Device Settings',
					cameraTitle: 'Scan a Plant', cameraCopy: 'Point your camera at a plant to identify it, then add it to your garden.', cameraAccess: 'Requesting camera access...', frameHint: 'Position the plant within the frame for the best result.',
					diagnosisTitle: 'Diagnosis', diagnosisCopy: 'Based on live sensor readings and the latest leaf scan. A few things need your attention.', plantDiagnosis: 'Plant Diagnosis', newScan: 'New Scan',
					settingsHeader: 'Settings', controlCenter: 'Control Center'
				},
				es: {
					notifications: 'Notificaciones', settings: 'Ajustes', logout: 'Salir', noNotifications: 'No hay notificaciones.', recent: 'recientes',
					navHome: 'Inicio', navDiagnose: 'Diagnosticar', navPlants: 'Plantas', navDevices: 'Dispositivos', openCamera: 'Abrir camara',
					homeTitle: 'Tu Jardin', homeCopy: 'Sigue tu recorrido botanico y las tendencias de salud de tus plantas.', emptyGarden: 'Empieza tu jardin agregando tu primera planta.', plantTools: 'Herramientas', plantToolsCopy: 'Diagnosticos con camara para tu jardin', lightMeter: 'Medidor de Luz', potMeasure: 'Medir Maceta',
					plantsTitle: 'Historial de Escaneos', plantsUnavailable: 'Plantas no disponibles', noScans: 'Aun no hay escaneos', viewDiagnosis: 'Ver Diagnostico', scanInsights: 'Resumen de Escaneos', chooseFavorites: 'Elegir Favoritas',
					deviceTitle: 'Ajustes de Cube-Monitor', deviceCopy: 'Configura y monitorea la conexion y sensores de SproutSync.', connectedDevices: 'Dispositivos Conectados', connectionSummary: 'Resumen de Conexion', sensorStatus: 'Sensores y Calibracion', powerSolar: 'Energia y Solar', networkHotspot: 'Red y Hotspot', hotspotSettings: 'Ajustes de Hotspot', configure: 'Configurar', change: 'Cambiar', deviceSettings: 'Ajustes del Dispositivo',
					cameraTitle: 'Escanear Planta', cameraCopy: 'Apunta la camara a una planta para identificarla y agregarla a tu jardin.', cameraAccess: 'Solicitando acceso a camara...', frameHint: 'Coloca la planta dentro del marco para mejores resultados.',
					diagnosisTitle: 'Diagnostico', diagnosisCopy: 'Basado en sensores en vivo y el ultimo escaneo de hojas. Algunas cosas necesitan atencion.', plantDiagnosis: 'Diagnostico de Planta', newScan: 'Nuevo Escaneo',
					settingsHeader: 'Ajustes', controlCenter: 'Centro de Control'
				},
				pl: {
					notifications: 'Powiadomienia', settings: 'Ustawienia', logout: 'Wyloguj', noNotifications: 'Brak powiadomien.', recent: 'ostatnie',
					navHome: 'Start', navDiagnose: 'Diagnoza', navPlants: 'Rosliny', navDevices: 'Urzadzenia', openCamera: 'Otworz kamere',
					homeTitle: 'Twoj Ogrod', homeCopy: 'Sledz swoja botaniczna podroz i zdrowie roslin.', emptyGarden: 'Zacznij ogrod, dodajac pierwsza rosline.', plantTools: 'Narzedzia', plantToolsCopy: 'Diagnostyka ogrodu z kamera', lightMeter: 'Miernik Swiatla', potMeasure: 'Pomiar Doniczki',
					plantsTitle: 'Historia Skanow', plantsUnavailable: 'Rosliny niedostepne', noScans: 'Brak skanow', viewDiagnosis: 'Zobacz Diagnoze', scanInsights: 'Wnioski ze Skanow', chooseFavorites: 'Wybierz Ulubione',
					deviceTitle: 'Ustawienia Cube-Monitor', deviceCopy: 'Konfiguruj i monitoruj polaczenie oraz sensory SproutSync.', connectedDevices: 'Polaczone Urzadzenia', connectionSummary: 'Podsumowanie Polaczenia', sensorStatus: 'Sensory i Kalibracja', powerSolar: 'Zasilanie i Solar', networkHotspot: 'Siec i Hotspot', hotspotSettings: 'Ustawienia Hotspot', configure: 'Konfiguruj', change: 'Zmien', deviceSettings: 'Ustawienia Urzadzenia',
					cameraTitle: 'Skanuj Rosline', cameraCopy: 'Skieruj kamere na rosline, aby ja rozpoznac i dodac do ogrodu.', cameraAccess: 'Prosba o dostep do kamery...', frameHint: 'Umiesc rosline w kadrze, aby uzyskac najlepszy wynik.',
					diagnosisTitle: 'Diagnoza', diagnosisCopy: 'Na podstawie odczytow sensorow i ostatniego skanu lisci. Kilka rzeczy wymaga uwagi.', plantDiagnosis: 'Diagnoza Rosliny', newScan: 'Nowy Skan',
					settingsHeader: 'Ustawienia', controlCenter: 'Centrum Sterowania'
				},
				sq: {
					notifications: 'Njoftime', settings: 'Cilesimet', logout: 'Dil', noNotifications: 'Nuk ka njoftime.', recent: 'te fundit',
					navHome: 'Kreu', navDiagnose: 'Diagnoza', navPlants: 'Bimet', navDevices: 'Pajisjet', openCamera: 'Hap kameren',
					homeTitle: 'Kopshti Yt', homeCopy: 'Ndiq udhetimin botanik dhe shendetin e bimeve.', emptyGarden: 'Fillo kopshtin duke shtuar bimen e pare.', plantTools: 'Mjetet e Bimeve', plantToolsCopy: 'Diagnostike me kamere per kopshtin', lightMeter: 'Mates Drite', potMeasure: 'Mat Vazot',
					plantsTitle: 'Historia e Skanimeve', plantsUnavailable: 'Bimet nuk jane te disponueshme', noScans: 'Nuk ka skanime ende', viewDiagnosis: 'Shiko Diagnozen', scanInsights: 'Te Dhena Skanimi', chooseFavorites: 'Zgjidh te Preferuarat',
					deviceTitle: 'Cilesimet Cube-Monitor', deviceCopy: 'Konfiguro dhe monitoro lidhjen dhe sensoret SproutSync.', connectedDevices: 'Pajisjet e Lidhura', connectionSummary: 'Permbledhje Lidhjeje', sensorStatus: 'Sensoret dhe Kalibrimi', powerSolar: 'Energjia dhe Solari', networkHotspot: 'Rrjeti dhe Hotspot', hotspotSettings: 'Cilesimet Hotspot', configure: 'Konfiguro', change: 'Ndrysho', deviceSettings: 'Cilesimet e Pajisjes',
					cameraTitle: 'Skano Bimen', cameraCopy: 'Drejto kameren te bima per ta identifikuar dhe shtuar ne kopsht.', cameraAccess: 'Po kerkohet aksesi te kamera...', frameHint: 'Vendose bimen brenda kornizes per rezultatin me te mire.',
					diagnosisTitle: 'Diagnoza', diagnosisCopy: 'Bazuar ne sensore live dhe skanimin e fundit te gjetheve. Disa gjera kerkojne vemendje.', plantDiagnosis: 'Diagnoza e Bimes', newScan: 'Skanim i Ri',
					settingsHeader: 'Cilesimet', controlCenter: 'Qendra e Kontrollit'
				},
				de: {
					notifications: 'Meldungen', settings: 'Einstellungen', logout: 'Abmelden', noNotifications: 'Keine Meldungen.', recent: 'aktuell',
					navHome: 'Start', navDiagnose: 'Diagnose', navPlants: 'Pflanzen', navDevices: 'Gerate', openCamera: 'Kamera offnen',
					homeTitle: 'Dein Garten', homeCopy: 'Verfolge deine botanische Reise und Pflanzengesundheit.', emptyGarden: 'Beginne deinen Garten mit der ersten Pflanze.', plantTools: 'Pflanzen Tools', plantToolsCopy: 'Kamera Diagnose fur deinen Garten', lightMeter: 'Lichtmesser', potMeasure: 'Topf Messen',
					plantsTitle: 'Scan Verlauf', plantsUnavailable: 'Pflanzen nicht verfugbar', noScans: 'Noch keine Scans', viewDiagnosis: 'Diagnose Anzeigen', scanInsights: 'Scan Einblicke', chooseFavorites: 'Favoriten Wahlen',
					deviceTitle: 'Cube-Monitor Einstellungen', deviceCopy: 'Konfiguriere und uberwache Verbindung und Sensoren.', connectedDevices: 'Verbundene Gerate', connectionSummary: 'Verbindungsubersicht', sensorStatus: 'Sensoren und Kalibrierung', powerSolar: 'Strom und Solar', networkHotspot: 'Netzwerk und Hotspot', hotspotSettings: 'Hotspot Einstellungen', configure: 'Konfigurieren', change: 'Andern', deviceSettings: 'Gerate Einstellungen',
					cameraTitle: 'Pflanze Scannen', cameraCopy: 'Richte die Kamera auf eine Pflanze, um sie zu erkennen und zu speichern.', cameraAccess: 'Kamerazugriff wird angefragt...', frameHint: 'Positioniere die Pflanze fur das beste Ergebnis im Rahmen.',
					diagnosisTitle: 'Diagnose', diagnosisCopy: 'Basierend auf Live Sensoren und dem letzten Blattscan. Ein paar Dinge brauchen Aufmerksamkeit.', plantDiagnosis: 'Pflanzen Diagnose', newScan: 'Neuer Scan',
					settingsHeader: 'Einstellungen', controlCenter: 'Kontrollzentrum'
				},
				fr: {
					notifications: 'Notifications', settings: 'Parametres', logout: 'Deconnexion', noNotifications: 'Aucune notification.', recent: 'recentes',
					navHome: 'Accueil', navDiagnose: 'Diagnostic', navPlants: 'Plantes', navDevices: 'Appareils', openCamera: 'Ouvrir camera',
					homeTitle: 'Ton Jardin', homeCopy: 'Suis ton parcours botanique et la sante de tes plantes.', emptyGarden: 'Commence ton jardin en ajoutant ta premiere plante.', plantTools: 'Outils Plantes', plantToolsCopy: 'Diagnostics avec camera pour ton jardin', lightMeter: 'Luxmetre', potMeasure: 'Mesure Pot',
					plantsTitle: 'Historique des Scans', plantsUnavailable: 'Plantes indisponibles', noScans: 'Aucun scan encore', viewDiagnosis: 'Voir Diagnostic', scanInsights: 'Infos des Scans', chooseFavorites: 'Choisir Favoris',
					deviceTitle: 'Parametres Cube-Monitor', deviceCopy: 'Configure et surveille la connexion et les capteurs SproutSync.', connectedDevices: 'Appareils Connectes', connectionSummary: 'Resume Connexion', sensorStatus: 'Capteurs et Calibration', powerSolar: 'Alimentation et Solaire', networkHotspot: 'Reseau et Hotspot', hotspotSettings: 'Parametres Hotspot', configure: 'Configurer', change: 'Changer', deviceSettings: 'Parametres Appareil',
					cameraTitle: 'Scanner une Plante', cameraCopy: 'Pointe la camera vers une plante pour l identifier et l ajouter au jardin.', cameraAccess: 'Demande acces camera...', frameHint: 'Place la plante dans le cadre pour le meilleur resultat.',
					diagnosisTitle: 'Diagnostic', diagnosisCopy: 'Base sur les capteurs en direct et le dernier scan de feuille. Quelques points demandent attention.', plantDiagnosis: 'Diagnostic Plante', newScan: 'Nouveau Scan',
					settingsHeader: 'Parametres', controlCenter: 'Centre de Controle'
				}
			};

			function ssApplyLanguage(language) {
				const lang = window.SS_I18N[language] ? language : 'en';
				const dict = window.SS_I18N[lang];
				document.documentElement.lang = lang;
				document.querySelectorAll('[data-ss-i18n]').forEach(element => {
					const value = dict[element.dataset.ssI18n];
					if (value) element.textContent = value;
				});
				document.querySelectorAll('[data-ss-i18n-aria]').forEach(element => {
					const value = dict[element.dataset.ssI18nAria];
					if (value) element.setAttribute('aria-label', value);
				});
				document.querySelectorAll('[data-ss-i18n-title]').forEach(element => {
					const value = dict[element.dataset.ssI18nTitle];
					if (value) element.setAttribute('title', value);
				});
				document.querySelectorAll('[data-ss-i18n-count]').forEach(element => {
					element.textContent = element.dataset.count + ' ' + dict.recent;
				});
			}

			document.addEventListener('DOMContentLoaded', function() {
				ssApplyLanguage(localStorage.getItem('sproutsync-language') || 'en');
			});
			window.addEventListener('sproutsync-language-change', function(event) {
				ssApplyLanguage(event.detail && event.detail.language ? event.detail.language : localStorage.getItem('sproutsync-language') || 'en');
			});
		</script>
		<style>
			:root[data-theme="dark"] body {
				background: #000 !important;
				color: #e7f5ea !important;
			}

			:root[data-theme="dark"] .bg-background,
			:root[data-theme="dark"] [class*="bg-background"],
			:root[data-theme="dark"] .page,
			:root[data-theme="dark"] .app-shell,
			:root[data-theme="dark"] main {
				background-color: #000 !important;
				color: #e7f5ea !important;
			}

			:root[data-theme="dark"] header,
			:root[data-theme="dark"] .header,
			:root[data-theme="dark"] .topbar {
				background: rgba(0, 0, 0, 0.92) !important;
				color: #e7f5ea !important;
			}

			:root[data-theme="dark"] .text-primary,
			:root[data-theme="dark"] .brand,
			:root[data-theme="dark"] h1,
			:root[data-theme="dark"] h2,
			:root[data-theme="dark"] h3 {
				color: #d8f5de !important;
			}

			:root[data-theme="dark"] .bg-white,
			:root[data-theme="dark"] .card,
			:root[data-theme="dark"] .featured-card,
			:root[data-theme="dark"] .history-card,
			:root[data-theme="dark"] .empty-card,
			:root[data-theme="dark"] .device-summary,
			:root[data-theme="dark"] .favorite-panel,
			:root[data-theme="dark"] .settings-panel {
				background: #06100b !important;
				border-color: #123b28 !important;
				color: #e7f5ea !important;
			}

			:root[data-theme="dark"] .bg-primary,
			:root[data-theme="dark"] .bg-primary-container,
			:root[data-theme="dark"] .primary-btn,
			:root[data-theme="dark"] .filter-btn.active {
				background-color: #0b3d29 !important;
				color: #e7f5ea !important;
			}

			:root[data-theme="dark"] .border-outline-variant\/30,
			:root[data-theme="dark"] .border-outline-variant\/40 {
				border-color: rgba(35, 90, 60, 0.78) !important;
			}

			.ss-top-actions {
				position: relative;
				display: flex;
				align-items: center;
				gap: 8px;
			}

			.ss-icon-action {
				position: relative;
				width: 38px;
				height: 38px;
				border: 0;
				border-radius: 50%;
				background: transparent;
				color: #012d1d;
				display: grid;
				place-items: center;
				text-decoration: none;
				cursor: pointer;
			}

			:root[data-theme="dark"] .ss-icon-action {
				color: #d8f5de;
			}

			.ss-icon-action:hover,
			.ss-icon-action:focus-visible {
				background: rgba(206, 233, 211, 0.58);
				outline: none;
			}

			:root[data-theme="dark"] .ss-icon-action:hover,
			:root[data-theme="dark"] .ss-icon-action:focus-visible {
				background: #071f14;
			}

			.ss-action-badge {
				position: absolute;
				top: 6px;
				right: 6px;
				min-width: 15px;
				height: 15px;
				border-radius: 999px;
				background: #c83c3c;
				color: #fff;
				border: 2px solid #fcf9f8;
				display: grid;
				place-items: center;
				font-size: 8px;
				font-weight: 800;
				line-height: 1;
			}

			:root[data-theme="dark"] .ss-action-badge {
				border-color: #000;
			}

			.ss-notification-panel {
				position: absolute;
				top: calc(100% + 10px);
				right: 0;
				z-index: 90;
				width: min(330px, calc(100vw - 28px));
				max-height: 430px;
				overflow-y: auto;
				background: #fff;
				border: 1px solid #d5dbd4;
				border-radius: 12px;
				box-shadow: 0 20px 50px rgba(1, 45, 29, 0.2);
				padding: 12px;
				display: none;
				text-align: left;
			}

			:root[data-theme="dark"] .ss-notification-panel {
				background: #030604;
				border-color: #123b28;
				box-shadow: 0 20px 50px rgba(0, 0, 0, 0.58);
			}

			.ss-notification-panel.open {
				display: block;
			}

			.ss-notification-head {
				display: flex;
				align-items: center;
				justify-content: space-between;
				padding: 4px 4px 10px;
				color: #012d1d;
				font-size: 14px;
				font-weight: 800;
			}

			:root[data-theme="dark"] .ss-notification-head,
			:root[data-theme="dark"] .ss-notification-title {
				color: #d8f5de;
			}

			.ss-notification-count {
				color: #626b64;
				font-size: 11px;
				font-weight: 700;
			}

			:root[data-theme="dark"] .ss-notification-count,
			:root[data-theme="dark"] .ss-notification-empty,
			:root[data-theme="dark"] .ss-notification-time,
			:root[data-theme="dark"] .ss-notification-message {
				color: #a5b8aa;
			}

			.ss-notification-list {
				display: grid;
				gap: 8px;
			}

			.ss-notification-item {
				display: grid;
				grid-template-columns: 32px 1fr;
				gap: 10px;
				padding: 11px;
				border: 1px solid #e2e7e1;
				border-radius: 9px;
				background: #fcf9f8;
				color: #1d2f27;
			}

			.ss-notification-item.unread {
				background: #f0faef;
				border-color: #cee9d3;
			}

			:root[data-theme="dark"] .ss-notification-item,
			:root[data-theme="dark"] .ss-notification-item.unread {
				background: #06100b;
				border-color: #123b28;
				color: #e7f5ea;
			}

			.ss-notification-dot {
				width: 32px;
				height: 32px;
				border-radius: 50%;
				background: #cee9d3;
				color: #012d1d;
				display: grid;
				place-items: center;
			}

			:root[data-theme="dark"] .ss-notification-dot {
				background: #0b3d29;
				color: #d8f5de;
			}

			.ss-notification-title {
				display: block;
				color: #012d1d;
				font-size: 13px;
				line-height: 1.2;
				font-weight: 800;
			}

			.ss-notification-message {
				margin-top: 3px;
				color: #414844;
				font-size: 12px;
				line-height: 1.35;
			}

			.ss-notification-time {
				display: block;
				margin-top: 6px;
				color: #626b64;
				font-size: 10px;
				font-weight: 800;
				text-transform: uppercase;
				letter-spacing: 0.5px;
			}

			.ss-notification-empty {
				padding: 24px 10px;
				color: #626b64;
				text-align: center;
				font-size: 13px;
				line-height: 1.35;
			}
		</style>
		<div class="ss-top-actions">
			<div class="ss-notification-menu">
				<button class="ss-icon-action" type="button" aria-label="Notifications" data-ss-i18n-aria="notifications" aria-expanded="false" onclick="ssToggleNotifications(this)">
					<span class="material-symbols-outlined">notifications</span>
					<?php if ($unreadCount > 0): ?>
						<span class="ss-action-badge"><?php echo $unreadCount > 9 ? '9+' : (int) $unreadCount; ?></span>
					<?php endif; ?>
				</button>
				<section class="ss-notification-panel" aria-label="Notifications" data-ss-i18n-aria="notifications">
					<div class="ss-notification-head">
						<span data-ss-i18n="notifications">Notifications</span>
						<span class="ss-notification-count" data-ss-i18n-count="recent" data-count="<?php echo count($notifications); ?>"><?php echo count($notifications); ?> recent</span>
					</div>
					<?php if (empty($notifications)): ?>
						<div class="ss-notification-empty" data-ss-i18n="noNotifications">No notifications yet.</div>
					<?php else: ?>
						<div class="ss-notification-list">
							<?php foreach ($notifications as $notification): ?>
								<article class="ss-notification-item <?php echo empty($notification['is_read']) ? 'unread' : ''; ?>">
									<span class="ss-notification-dot"><span class="material-symbols-outlined">eco</span></span>
									<span>
										<strong class="ss-notification-title"><?php echo ss_e($notification['title']); ?></strong>
										<span class="ss-notification-message"><?php echo ss_e($notification['message']); ?></span>
										<span class="ss-notification-time"><?php echo ss_e(ss_notification_time($notification['created_at'])); ?></span>
									</span>
								</article>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</section>
			</div>
			<a class="ss-icon-action" href="settings.php" aria-label="Settings" title="Settings" data-ss-i18n-aria="settings" data-ss-i18n-title="settings">
				<span class="material-symbols-outlined">settings</span>
			</a>
			<a class="ss-icon-action" href="logout.php" aria-label="Logout" title="Logout" data-ss-i18n-aria="logout" data-ss-i18n-title="logout">
				<span class="material-symbols-outlined">logout</span>
			</a>
		</div>
		<script>
			function ssToggleNotifications(button) {
				const panel = button.closest('.ss-notification-menu').querySelector('.ss-notification-panel');
				const isOpen = panel.classList.toggle('open');
				button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			}

			document.addEventListener('click', function(event) {
				document.querySelectorAll('.ss-notification-menu').forEach(function(menu) {
					if (!menu.contains(event.target)) {
						const panel = menu.querySelector('.ss-notification-panel');
						const button = menu.querySelector('.ss-icon-action');
						panel.classList.remove('open');
						button.setAttribute('aria-expanded', 'false');
					}
				});
			});
		</script>
		<?php
	}
}
?>
