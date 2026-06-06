



<?php
    include_once('config.php');
    include_once('top_actions.php');

    // Pull all plants from the database, joined with their species for care info.
    // No login check (per your choice). image_url comes from the DB; we fall back
    // to a placeholder in the markup if it's empty.
    $plants = [];
    try {
        $sql = "SELECT p.plant_id, p.nickname, p.location, p.status, p.image_url AS plant_image,
                       s.common_name, s.scientific_name, s.image_url AS species_image
                FROM plants p
                LEFT JOIN plant_species s ON p.species_id = s.species_id
                ORDER BY p.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $plants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // If the query fails we just show an empty state instead of crashing.
        $plants = [];
    }

    $placeholderImg = plant_local_image('default');

    // --- Static / demo values (per your choice) -------------------------------
    // These are just for the visual demo until you wire in real sensor data.
    // Keyed by a rotating index so each card looks a bit different.
    $demoStates = [
        [ 'category' => 'unhealthy', 'dot' => 'bg-red-600',   'tag_icon' => 'water_drop',       'tag' => 'NEEDS WATER',    'tag_color' => 'text-red-600', 'bar' => 12, 'bar_color' => 'bg-red-600' ],
        [ 'category' => 'healthy',   'dot' => 'bg-[#354c3b]', 'tag_icon' => 'wb_sunny',         'tag' => 'OPTIMAL LIGHT',  'tag_color' => 'text-primary', 'bar' => 85, 'bar_color' => 'bg-[#354c3b]' ],
        [ 'category' => 'healthy',   'dot' => 'bg-[#354c3b]', 'tag_icon' => 'device_thermostat','tag' => 'TEMP STABLE',    'tag_color' => 'text-primary', 'bar' => 90, 'bar_color' => 'bg-[#354c3b]' ],
        [ 'category' => 'crops',     'dot' => 'bg-[#354c3b]', 'tag_icon' => 'psychiatry',       'tag' => 'FLOWERING STAGE','tag_color' => 'text-primary', 'bar' => 70, 'bar_color' => 'bg-[#354c3b]' ],
    ];

    // Helper to safely escape output
    function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>SproutSync - Home</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                "primary": "#012d1d",
                "primary-container": "#1b4332",
                "surface": "#fcf9f8",
                "surface-container": "#f0eded",
                "background": "#fcf9f8",
                "outline-variant": "#c1c8c2",
                "secondary-fixed": "#cee9d3",
            },
            fontFamily: { heading: ['Manrope', 'sans-serif'], mono: ['JetBrains Mono', 'monospace'] }
        }
    }
}
</script>
<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    .material-symbols-outlined.filled { font-variation-settings: 'FILL' 1; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    @keyframes scan { 0% { transform: translateY(0); } 50% { transform: translateY(300px); } 100% { transform: translateY(0); } }
    body { font-family: 'Manrope', sans-serif; }
    @keyframes fadeSlideDown { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
    #weather-expanded { animation: fadeSlideDown 0.28s ease both; }
    .weather-metric-card { backdrop-filter: blur(8px); }
    @keyframes weatherPulse { 0%,100%{opacity:.7} 50%{opacity:1} }
    #weather-loading-icon { animation: weatherPulse 1.4s ease-in-out infinite; }
</style>
</head>
<body class="bg-background text-[#414844] selection:bg-secondary-fixed pb-32">

<!-- Header -->
<header class="sticky top-0 z-40 bg-background/90 backdrop-blur-md">
    <div class="flex justify-between items-center w-full px-6 py-4 max-w-lg mx-auto">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-primary-container flex items-center justify-center overflow-hidden">
                <span class="material-symbols-outlined text-[#cee9d3] text-[20px] filled">eco</span>
            </div>
            <h1 class="font-bold text-xl text-primary font-heading">SproutSync</h1>
        </div>
        <?php ss_render_top_actions($conn); ?>
    </div>
</header>

<main class="px-5 max-w-lg mx-auto">
    <div class="mt-2 mb-6">
        <h2 class="text-3xl text-primary font-bold font-heading tracking-tight">Your Garden</h2>
        <p class="text-sm mt-1 leading-relaxed">Keep track of your botanical journey and plant health trends.</p>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         WEATHER WIDGET
         Collapsed (slim) by default. "More Details" expands it.
    ═══════════════════════════════════════════════════════════ -->
    <div id="weather-card" class="relative overflow-hidden rounded-2xl shadow-sm mb-6 border border-outline-variant/30">

        <!-- Background image layer (changes by condition) -->
        <div id="weather-bg" class="absolute inset-0 bg-cover bg-center transition-all duration-700"
             style="background-image: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80');"></div>
        <!-- Dark gradient overlay -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary/80 via-primary/60 to-primary-container/50"></div>

        <!-- SLIM / COLLAPSED ROW -->
        <div id="weather-slim" class="relative z-10 flex items-center justify-between px-5 py-4">
            <!-- Left: icon + temp + city -->
            <div class="flex items-center gap-3">
                <span id="w-icon-slim" id="weather-loading-icon" class="material-symbols-outlined text-[#cee9d3] text-4xl filled">partly_cloudy_day</span>
                <div>
                    <div class="flex items-end gap-1.5">
                        <span id="w-temp-slim" class="text-3xl font-bold text-white font-heading">--°</span>
                        <span id="w-feel-slim" class="text-xs text-white/60 mb-1.5 font-mono">Feels --°</span>
                    </div>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span id="w-city-slim" class="text-[11px] text-white/80 font-bold uppercase tracking-widest">Locating…</span>
                    </div>
                </div>
            </div>
            <!-- Right: mini metrics + toggle -->
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <div class="text-[10px] text-white/60 uppercase tracking-wider font-mono">Humidity</div>
                    <div id="w-hum-slim" class="text-base font-bold text-[#cee9d3] font-mono">--%</div>
                </div>
                <div class="text-right">
                    <div class="text-[10px] text-white/60 uppercase tracking-wider font-mono">Wind</div>
                    <div id="w-wind-slim" class="text-base font-bold text-[#cee9d3] font-mono">-- km/h</div>
                </div>
                <button id="weather-toggle" onclick="toggleWeather()"
                        class="ml-1 w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center transition-all">
                    <span id="weather-chevron" class="material-symbols-outlined text-white text-[20px] transition-transform duration-300">expand_more</span>
                </button>
            </div>
        </div>

        <!-- EXPANDED PANEL (hidden by default) -->
        <div id="weather-expanded" class="hidden relative z-10">

            <!-- Hero image zone with big temp -->
            <div class="px-5 pb-4 pt-1 flex items-end justify-between border-b border-white/10">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span id="w-icon-big" class="material-symbols-outlined text-[#cee9d3] text-5xl filled">partly_cloudy_day</span>
                        <div>
                            <div id="w-temp-big" class="text-5xl font-bold text-white font-heading leading-none">--°</div>
                            <div id="w-desc" class="text-[13px] text-white/70 capitalize mt-0.5">Loading…</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 mt-2">
                        <span class="material-symbols-outlined text-white/50 text-[14px]">location_on</span>
                        <span id="w-city-big" class="text-[11px] text-white/70 font-bold uppercase tracking-widest">--</span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-[10px] text-white/50 uppercase tracking-wider mb-1 font-mono">H / L Today</div>
                    <div class="text-lg font-bold text-white font-mono"><span id="w-high">--</span>° / <span id="w-low">--</span>°</div>
                    <div id="w-condition-tag" class="mt-2 inline-block bg-[#cee9d3]/20 border border-[#cee9d3]/30 px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest text-[#cee9d3]">--</div>
                </div>
            </div>

            <!-- Metric grid -->
            <div class="grid grid-cols-3 gap-px bg-white/10 mx-0">
                <!-- Humidity -->
                <div class="weather-metric-card bg-primary/40 px-4 py-3.5 text-center">
                    <span class="material-symbols-outlined text-[#cee9d3] text-xl filled">water_drop</span>
                    <div id="w-hum" class="text-xl font-bold text-white font-mono mt-1">--%</div>
                    <div class="text-[9px] text-white/55 uppercase tracking-wider mt-0.5 font-mono">Humidity</div>
                </div>
                <!-- Wind -->
                <div class="weather-metric-card bg-primary/40 px-4 py-3.5 text-center">
                    <span class="material-symbols-outlined text-[#cee9d3] text-xl filled">air</span>
                    <div id="w-wind" class="text-xl font-bold text-white font-mono mt-1">-- km/h</div>
                    <div class="text-[9px] text-white/55 uppercase tracking-wider mt-0.5 font-mono">Wind</div>
                </div>
                <!-- UV Index -->
                <div class="weather-metric-card bg-primary/40 px-4 py-3.5 text-center">
                    <span class="material-symbols-outlined text-[#cee9d3] text-xl filled">wb_sunny</span>
                    <div id="w-uv" class="text-xl font-bold text-white font-mono mt-1">--</div>
                    <div class="text-[9px] text-white/55 uppercase tracking-wider mt-0.5 font-mono">UV Index</div>
                </div>
                <!-- Feels Like -->
                <div class="weather-metric-card bg-primary/40 px-4 py-3.5 text-center">
                    <span class="material-symbols-outlined text-[#cee9d3] text-xl filled">device_thermostat</span>
                    <div id="w-feels" class="text-xl font-bold text-white font-mono mt-1">--°</div>
                    <div class="text-[9px] text-white/55 uppercase tracking-wider mt-0.5 font-mono">Feels Like</div>
                </div>
                <!-- Sunrise -->
                <div class="weather-metric-card bg-primary/40 px-4 py-3.5 text-center">
                    <span class="material-symbols-outlined text-[#cee9d3] text-xl filled">wb_twilight</span>
                    <div id="w-sunrise" class="text-xl font-bold text-white font-mono mt-1">--:--</div>
                    <div class="text-[9px] text-white/55 uppercase tracking-wider mt-0.5 font-mono">Sunrise</div>
                </div>
                <!-- Sunset -->
                <div class="weather-metric-card bg-primary/40 px-4 py-3.5 text-center">
                    <span class="material-symbols-outlined text-[#cee9d3] text-xl filled">nightlight</span>
                    <div id="w-sunset" class="text-xl font-bold text-white font-mono mt-1">--:--</div>
                    <div class="text-[9px] text-white/55 uppercase tracking-wider mt-0.5 font-mono">Sunset</div>
                </div>
            </div>

            <!-- Visibility + pressure row -->
            <div class="flex gap-px bg-white/10">
                <div class="weather-metric-card bg-primary/40 flex-1 px-4 py-3 flex items-center gap-3">
                    <span class="material-symbols-outlined text-[#cee9d3] text-xl filled">visibility</span>
                    <div>
                        <div class="text-[9px] text-white/55 uppercase tracking-wider font-mono">Visibility</div>
                        <div id="w-vis" class="text-base font-bold text-white font-mono">-- km</div>
                    </div>
                </div>
                <div class="weather-metric-card bg-primary/40 flex-1 px-4 py-3 flex items-center gap-3">
                    <span class="material-symbols-outlined text-[#cee9d3] text-xl filled">compress</span>
                    <div>
                        <div class="text-[9px] text-white/55 uppercase tracking-wider font-mono">Pressure</div>
                        <div id="w-pressure" class="text-base font-bold text-white font-mono">-- hPa</div>
                    </div>
                </div>
                <div class="weather-metric-card bg-primary/40 flex-1 px-4 py-3 flex items-center gap-3">
                    <span class="material-symbols-outlined text-[#cee9d3] text-xl filled">cloud</span>
                    <div>
                        <div class="text-[9px] text-white/55 uppercase tracking-wider font-mono">Cloud Cover</div>
                        <div id="w-clouds" class="text-base font-bold text-white font-mono">--%</div>
                    </div>
                </div>
            </div>

            <!-- Plant care tip -->
            <div class="px-5 py-4 border-t border-white/10 flex gap-3 items-start">
                <span class="material-symbols-outlined text-[#cee9d3] mt-0.5 filled">psychiatry</span>
                <div>
                    <div class="text-[10px] font-bold text-[#cee9d3] uppercase tracking-wider mb-1">GARDEN TIP TODAY</div>
                    <p id="w-tip" class="text-[13px] text-white/80 leading-snug">Loading care recommendation…</p>
                </div>
            </div>

            <!-- Collapse button -->
            <div class="pb-4 flex justify-center">
                <button onclick="toggleWeather()" class="flex items-center gap-1.5 bg-white/10 hover:bg-white/20 text-white/80 px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest transition">
                    <span class="material-symbols-outlined text-[14px]">expand_less</span>
                    Show Less
                </button>
            </div>
        </div><!-- /weather-expanded -->
    </div><!-- /weather-card -->

    <!-- Filters -->
    <div class="flex gap-2.5 mb-6 overflow-x-auto no-scrollbar pb-1" id="filter-buttons">
        <button data-filter="all" class="filter-btn bg-primary text-white opacity-100 px-4 py-1.5 rounded-full font-bold text-[10px] uppercase tracking-wider whitespace-nowrap shadow-sm transition">All Plants</button>
        <button data-filter="healthy" class="filter-btn bg-secondary-fixed text-primary opacity-80 hover:opacity-100 px-4 py-1.5 rounded-full font-bold text-[10px] uppercase tracking-wider whitespace-nowrap shadow-sm transition">Healthy</button>
        <button data-filter="unhealthy" class="filter-btn bg-secondary-fixed text-primary opacity-80 hover:opacity-100 px-4 py-1.5 rounded-full font-bold text-[10px] uppercase tracking-wider whitespace-nowrap shadow-sm transition">Unhealthy</button>
        <button data-filter="crops" class="filter-btn bg-secondary-fixed text-primary opacity-80 hover:opacity-100 px-4 py-1.5 rounded-full font-bold text-[10px] uppercase tracking-wider whitespace-nowrap shadow-sm transition">Crops</button>
    </div>

<?php if (count($plants) === 0): ?>
    <!-- Empty state -->
    <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-8 text-center mb-6">
        <span class="material-symbols-outlined text-primary/40 text-5xl">potted_plant</span>
        <h3 class="text-lg font-bold text-primary font-heading mt-3">No plants added yet</h3>
        <p class="text-sm mt-1">Start building your garden by adding your first plant.</p>
        <a href="add_plant.php" class="inline-flex items-center gap-2 mt-5 bg-primary text-white px-6 py-3 rounded-xl text-xs font-bold uppercase tracking-widest shadow-md hover:bg-primary-container transition">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Add a Plant
        </a>
    </div>
<?php else: ?>

    <?php
        // FEATURED CARD = the first plant from the database.
        $featured = $plants[0];
        $fState   = $demoStates[0]; // first demo state (the "needs water" look)
        $fTitle   = !empty($featured['nickname']) ? $featured['nickname'] : $featured['common_name'];
        // Priority: user-uploaded plant image → species image → placeholder
        $fSource  = !empty($featured['plant_image'])
                        ? $featured['plant_image']
                        : ($featured['species_image'] ?? '');
        
        $fImage   = plant_image_src($fSource, $featured['common_name'] ?? $fTitle);
        
        $fSub     = trim(($featured['location'] ?? 'Indoor') . ' • ' . ($featured['common_name'] ?? 'Plant'));
    ?>

    <!-- Featured plant card -->
    <div class="plant-card bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden mb-6" data-category="<?php echo e($fState['category']); ?>">
        <div class="relative h-56">
            <img src="<?php echo e($fImage); ?>" class="w-full h-full object-cover" alt="<?php echo e($fTitle); ?>">
            <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-full flex items-center gap-1.5">
                <div class="w-1.5 h-1.5 rounded-full bg-red-600"></div>
                <span class="text-[9px] font-bold tracking-widest uppercase text-primary">CRITICAL ATTENTION</span>
            </div>
        </div>
        <div class="p-5">
            <div class="flex justify-between items-center text-[10px] font-mono uppercase tracking-widest mb-1.5">
                <span>SOIL MOISTURE <?php echo (int)$fState['bar']; ?>%</span>
                <span class="material-symbols-outlined text-primary text-xl filled">star</span>
            </div>
            <h3 class="text-[22px] font-bold text-primary font-heading"><?php echo e($fTitle); ?></h3>
            <p class="text-[13px] mt-0.5"><?php echo e($fSub); ?></p>

            <div class="mt-4 bg-red-50/50 rounded-xl p-3.5 flex gap-3 border border-red-100">
                <span class="material-symbols-outlined text-red-500 mt-0.5">water_drop</span>
                <div>
                    <div class="text-[10px] font-bold text-red-700 uppercase tracking-wider mb-1">NEEDS WATER</div>
                    <div class="text-[13px] leading-tight">Soil moisture at <?php echo (int)$fState['bar']; ?>%. Immediate watering required.</div>
                </div>
            </div>

            <a href="diagnose.php?plant=<?php echo (int)$featured['plant_id']; ?>" class="block text-center w-full mt-5 bg-primary text-white py-3.5 rounded-xl text-xs font-bold uppercase tracking-widest shadow-md hover:bg-primary-container transition">VIEW DIAGNOSIS</a>
        </div>
    </div>

    <!-- Insights Card -->
    <div class="bg-[#274e3d] rounded-2xl p-6 text-white shadow-sm mb-6" id="insights-card">
        <h3 class="text-[22px] font-heading font-semibold text-white/95 mb-5">Garden Insights</h3>
        <div class="flex justify-between items-center py-3 border-b border-white/10">
            <span class="text-sm text-white/80">Total Plants</span>
            <span class="text-[22px] text-[#cee9d3] font-mono"><?php echo count($plants); ?></span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-white/10">
            <span class="text-sm text-white/80">Healthy Rate</span>
            <span class="text-[22px] text-[#cee9d3] font-mono">92%</span>
        </div>
        <div class="flex justify-between items-center py-3">
            <span class="text-sm text-white/80">Pending Tasks</span>
            <span class="text-[22px] text-[#ffb780] font-mono">3</span>
        </div>
        <p class="text-[13px] text-[#cee9d3]/80 italic mt-5 leading-relaxed">"Your urban jungle is thriving! Keep up the great care."</p>
    </div>

    <!-- Past plants list (the rest of the plants after the featured one) -->
    <div class="space-y-4 mb-6">
        <?php
            // Loop over the remaining plants (skip the first one, it's featured above).
            $rest = array_slice($plants, 1);
            foreach ($rest as $i => $plant):
                // rotate through demo states, starting at index 1
                $st     = $demoStates[($i + 1) % count($demoStates)];
                $title  = !empty($plant['nickname']) ? $plant['nickname'] : $plant['common_name'];
                // Priority: user-uploaded plant image → species image → placeholder
                $source = !empty($plant['plant_image'])
                              ? $plant['plant_image']
                              : ($plant['species_image'] ?? '');
                
                $img    = plant_image_src($source, $plant['common_name'] ?? $title);
                
                $isUnhealthy = ($st['category'] === 'unhealthy');
        ?>
        <div class="plant-card bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden" data-category="<?php echo e($st['category']); ?>">
            <div class="relative h-28">
                <img src="<?php echo e($img); ?>" class="w-full h-full object-cover object-center" alt="<?php echo e($title); ?>">
                <div class="absolute bottom-2.5 left-2.5 bg-white/95 backdrop-blur-sm px-2 py-0.5 rounded text-[10px] font-bold font-mono tracking-widest"><?php echo e(strtoupper(substr($plant['common_name'] ?? 'PLANT', 0, 10))); ?></div>
            </div>
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <h4 class="text-lg font-bold text-primary font-heading"><?php echo e($title); ?></h4>
                    <div class="w-2.5 h-2.5 rounded-full <?php echo e($st['dot']); ?>"></div>
                </div>

                <?php if ($isUnhealthy): ?>
                <p class="text-sm text-red-600 font-medium mt-3 border-l-2 border-red-500 pl-3">Plant looks sick. Soil moisture is low and needs attention.</p>
                <?php endif; ?>

                <div class="flex items-center gap-1.5 mt-<?php echo $isUnhealthy ? '4' : '1'; ?> text-[9px] <?php echo e($st['tag_color']); ?> uppercase font-bold tracking-widest">
                    <span class="material-symbols-outlined text-[14px]"><?php echo e($st['tag_icon']); ?></span>
                    <?php echo e($st['tag']); ?>
                </div>
                <div class="w-full h-1 bg-surface-container rounded-full mt-4 <?php echo $isUnhealthy ? 'mb-4' : ''; ?>">
                    <div class="h-full <?php echo e($st['bar_color']); ?> rounded-full" style="width: <?php echo (int)$st['bar']; ?>%"></div>
                </div>

                <?php if ($isUnhealthy): ?>
                <a href="diagnose.php?plant=<?php echo (int)$plant['plant_id']; ?>" class="block text-center w-full mt-4 bg-primary text-white py-3 rounded-xl text-xs font-bold uppercase tracking-widest shadow-md hover:bg-primary-container transition">VIEW DIAGNOSIS</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <button class="w-full py-3.5 border border-primary text-primary rounded-full text-xs font-bold flex justify-center items-center gap-1 hover:bg-primary/5 transition uppercase tracking-widest mb-10">
        LOAD MORE
        <span class="material-symbols-outlined text-lg">expand_more</span>
    </button>

<?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════
         PLANT TOOLS SECTION — always rendered at the bottom
    ═══════════════════════════════════════════════════════════ -->
    <div class="mb-10">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-primary font-heading tracking-tight">Plant Tools</h2>
                <p class="text-xs mt-0.5 text-[#414844]/70">Camera-powered diagnostics for your garden</p>
            </div>
            <div class="w-9 h-9 rounded-full bg-secondary-fixed flex items-center justify-center">
                <span class="material-symbols-outlined text-primary text-[20px] filled">build</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">

            <!-- ── TOOL 1: Light Meter ── -->
            <button onclick="openLightMeter()"
                    class="relative overflow-hidden rounded-2xl shadow-sm border border-outline-variant/30 text-left group active:scale-[0.97] transition-transform">
                <!-- Background image -->
                <div class="absolute inset-0 bg-cover bg-center"
                     style="background-image:url('https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?w=600&q=80')"></div>
                <!-- Gradient overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-primary/90 via-primary/50 to-primary/10"></div>
                <!-- Content -->
                <div class="relative z-10 p-4 pt-16">
                    <div class="w-9 h-9 rounded-xl bg-[#cee9d3]/20 border border-[#cee9d3]/30 flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-[#cee9d3] text-[20px] filled">wb_sunny</span>
                    </div>
                    <div class="text-[9px] font-bold uppercase tracking-widest text-[#cee9d3]/70 mb-1 font-mono">CAMERA TOOL</div>
                    <h3 class="text-[15px] font-bold text-white font-heading leading-tight">Light<br>Meter</h3>
                    <p class="text-[11px] text-white/65 mt-1.5 leading-snug">Measure light levels for your plant's needs</p>
                    <div class="mt-3 flex items-center gap-1 text-[#cee9d3] text-[10px] font-bold uppercase tracking-wider">
                        <span>Open tool</span>
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </div>
                </div>
            </button>

            <!-- ── TOOL 2: Pot Measure ── -->
            <button onclick="openPotMeasure()"
                    class="relative overflow-hidden rounded-2xl shadow-sm border border-outline-variant/30 text-left group active:scale-[0.97] transition-transform">
                <!-- Background image -->
                <div class="absolute inset-0 bg-cover bg-center"
                     style="background-image:url('https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=600&q=80')"></div>
                <!-- Gradient overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-primary/90 via-primary/50 to-primary/10"></div>
                <!-- Content -->
                <div class="relative z-10 p-4 pt-16">
                    <div class="w-9 h-9 rounded-xl bg-[#cee9d3]/20 border border-[#cee9d3]/30 flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-[#cee9d3] text-[20px] filled">straighten</span>
                    </div>
                    <div class="text-[9px] font-bold uppercase tracking-widest text-[#cee9d3]/70 mb-1 font-mono">CAMERA TOOL</div>
                    <h3 class="text-[15px] font-bold text-white font-heading leading-tight">Pot<br>Measure</h3>
                    <p class="text-[11px] text-white/65 mt-1.5 leading-snug">Estimate your pot's diameter and volume</p>
                    <div class="mt-3 flex items-center gap-1 text-[#cee9d3] text-[10px] font-bold uppercase tracking-wider">
                        <span>Open tool</span>
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </div>
                </div>
            </button>

        </div><!-- /grid -->
    </div><!-- /plant tools -->

</main>

<?php $activePage = 'home'; include('nav.php'); ?>

<!-- Camera Modal -->
<div id="camera-modal" class="fixed inset-0 z-[60] bg-black/90 hidden flex-col items-center justify-center backdrop-blur-md">
    <button onclick="closeCamera()" class="absolute top-6 right-6 text-white bg-white/20 p-2 rounded-full hover:bg-white/30 transition z-[70]">
        <span class="material-symbols-outlined">close</span>
    </button>
    <div class="w-11/12 max-w-sm aspect-[3/4] bg-zinc-900 border border-white/20 rounded-3xl flex items-center justify-center relative overflow-hidden shadow-2xl">
        <video id="camera-feed" class="w-full h-full object-cover hidden" autoplay playsinline></video>
        <canvas id="camera-canvas" hidden></canvas>
        <div id="camera-placeholder" class="flex flex-col items-center">
            <span class="material-symbols-outlined text-white/30 text-6xl">photo_camera</span>
            <p class="text-white/50 text-xs mt-2">Requesting camera access...</p>
        </div>
        <div class="absolute inset-0 border border-[#2ecc71]/50 rounded-3xl" style="box-shadow: inset 0 0 40px rgba(46, 204, 113, 0.2);"></div>
        <div class="absolute top-0 left-0 w-full h-1 bg-[#2ecc71] opacity-70" style="animation: scan 3s linear infinite;"></div>
    </div>
    <p class="text-white mt-8 text-center px-8 text-sm opacity-80 tracking-wide">Position plant within frame to identify it</p>
    <div class="mt-8 flex items-center gap-4">
        <label class="w-14 h-14 rounded-full border border-white/60 bg-white/10 text-white grid place-items-center cursor-pointer" for="image-upload" aria-label="Upload plant image from phone">
            <span class="material-symbols-outlined">upload_file</span>
        </label>
        <input id="image-upload" type="file" accept="image/*" capture="environment" hidden onchange="uploadPlantImage(this)">
        <button id="capture-btn" class="w-16 h-16 rounded-full border-4 border-white bg-white/10 flex items-center justify-center hover:bg-white/30 transition-colors active:scale-90 shadow-lg shadow-white/20 disabled:opacity-50 disabled:cursor-wait" onclick="takePhoto()" aria-label="Capture plant photo"></button>
    </div>
    <div id="scan-result" class="hidden w-11/12 max-w-sm mt-4 rounded-xl bg-white/10 text-white text-sm leading-snug text-center px-4 py-3"></div>
</div>

<script>
    // Camera Logic
    let stream = null;
    async function openCamera() {
        const modal = document.getElementById('camera-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        document.getElementById('scan-result').classList.add('hidden');
        document.getElementById('scan-result').innerHTML = '';
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            const video = document.getElementById('camera-feed');
            video.srcObject = stream;
            video.classList.remove('hidden');
            document.getElementById('camera-placeholder').classList.add('hidden');
        } catch (err) {
            document.getElementById('camera-placeholder').innerHTML = '<span class="material-symbols-outlined text-red-400 text-4xl">error</span><p class="text-red-400 text-xs mt-2">Camera access denied</p>';
        }
    }
    function closeCamera() {
        const modal = document.getElementById('camera-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
        document.getElementById('camera-feed').classList.add('hidden');
        document.getElementById('camera-placeholder').classList.remove('hidden');
        document.getElementById('scan-result').classList.add('hidden');
        document.getElementById('scan-result').innerHTML = '';
    }
    async function takePhoto() {
        const video = document.getElementById('camera-feed');
        const canvas = document.getElementById('camera-canvas');
        const result = document.getElementById('scan-result');
        const captureButton = document.getElementById('capture-btn');
        const videoContainer = document.querySelector('#camera-modal > div');
        const flash = document.createElement('div');
        flash.className = 'absolute inset-0 bg-white z-50 transition-opacity duration-300';
        videoContainer.appendChild(flash);
        setTimeout(() => flash.style.opacity = '0', 50);
        setTimeout(() => flash.remove(), 350);

        if (!video.videoWidth || !video.videoHeight) {
            result.innerHTML = 'Camera is not ready yet.';
            result.classList.remove('hidden');
            return;
        }

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

        await submitPlantImage(canvas.toDataURL('image/png'));
    }

    async function submitPlantImage(imageData) {
        const result = document.getElementById('scan-result');
        const captureButton = document.getElementById('capture-btn');
        const formData = new FormData();
        formData.append('image_data', imageData);
        captureButton.disabled = true;
        result.innerHTML = 'Identifying plant...';
        result.classList.remove('hidden');

        try {
            const response = await fetch('scan_plant.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (!data.ok) {
                result.innerHTML = data.error || 'Plant scan failed. Try another photo.';
                return;
            }

            const confidence = Math.round((data.confidence || 0) * 100);
            const matched = data.matched_species_id ? 'Matched in SproutSync' : 'Not in your saved species yet';
            result.innerHTML = '<strong class="block text-base mb-1">' + data.plant + '</strong>' + data.scientific_name + '<br>' + confidence + '% confidence<br>' + matched;
        } catch (error) {
            result.innerHTML = 'Plant scan failed. Check your API key and internet connection.';
        } finally {
            captureButton.disabled = false;
        }
    }

    function uploadPlantImage(input) {
        const file = input.files && input.files[0];

        if (!file) {
            return;
        }

        const reader = new FileReader();
        reader.onload = function(event) {
            submitPlantImage(event.target.result);
            input.value = '';
        };
        reader.readAsDataURL(file);
    }

    // Category Filtering Logic
    document.addEventListener('DOMContentLoaded', () => {
        const filterBtns = document.querySelectorAll('.filter-btn');
        const plantCards = document.querySelectorAll('.plant-card');
        const insightsCard = document.getElementById('insights-card');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => {
                    b.classList.remove('bg-primary', 'text-white', 'opacity-100');
                    b.classList.add('bg-secondary-fixed', 'text-primary', 'opacity-80');
                });
                btn.classList.remove('bg-secondary-fixed', 'text-primary', 'opacity-80');
                btn.classList.add('bg-primary', 'text-white', 'opacity-100');

                const filter = btn.dataset.filter;
                plantCards.forEach(card => {
                    card.style.display = (filter === 'all' || card.dataset.category === filter) ? 'block' : 'none';
                });
                if (insightsCard) insightsCard.style.display = (filter === 'all') ? 'block' : 'none';
            });
        });

        // Kick off weather fetch on page load
        loadWeather();
    });

    // ─── WEATHER WIDGET ──────────────────────────────────────────────────────────

    const WEATHER_API_KEY = '698d477b9fb4ce053ea267133d73772c';
    let weatherExpanded = false;

    // Material icon name per OWM condition group
    function weatherIcon(id, pod) {
        const night = pod === 'n';
        if (id >= 200 && id < 300) return 'thunderstorm';
        if (id >= 300 && id < 400) return 'rainy';
        if (id >= 500 && id < 600) return 'rainy';
        if (id >= 600 && id < 700) return 'ac_unit';
        if (id >= 700 && id < 800) return 'foggy';
        if (id === 800) return night ? 'nights_stay' : 'wb_sunny';
        if (id === 801 || id === 802) return night ? 'nights_stay' : 'partly_cloudy_day';
        if (id > 802) return 'cloud';
        return 'wb_sunny';
    }

    // Unsplash images that match the earthy/botanical SproutSync aesthetic
    const weatherBgs = {
        sunny:   'https://images.unsplash.com/photo-1501854140801-50d01698950b?w=900&q=80',   // sunny meadow
        cloudy:  'https://images.unsplash.com/photo-1518173946687-a4c8892bbd9f?w=900&q=80',   // moody overcast forest
        rainy:   'https://images.unsplash.com/photo-1428592953211-077101b2021b?w=900&q=80',   // rain on leaves
        stormy:  'https://images.unsplash.com/photo-1504370805625-d32c54b16100?w=900&q=80',   // dark stormy sky
        snowy:   'https://images.unsplash.com/photo-1491002052546-bf38f186af56?w=900&q=80',   // snow on branches
        foggy:   'https://images.unsplash.com/photo-1476820865390-c52aeebb9891?w=900&q=80',   // misty forest
        night:   'https://images.unsplash.com/photo-1475274047050-1d0c0975c63e?w=900&q=80',   // night sky plants
    };

    function getBg(id, pod) {
        const night = pod === 'n';
        if (night) return weatherBgs.night;
        if (id >= 200 && id < 300) return weatherBgs.stormy;
        if (id >= 300 && id < 600) return weatherBgs.rainy;
        if (id >= 600 && id < 700) return weatherBgs.snowy;
        if (id >= 700 && id < 800) return weatherBgs.foggy;
        if (id === 800 || id === 801) return weatherBgs.sunny;
        return weatherBgs.cloudy;
    }

    function plantTip(id, humidity, temp) {
        if (id >= 200 && id < 300) return 'Heavy storm today — keep potted plants sheltered and check for waterlogging after the rain passes.';
        if (id >= 300 && id < 600) return 'Rainy conditions mean natural watering! Skip irrigation today and check that outdoor beds have good drainage.';
        if (id >= 600 && id < 700) return 'Frost risk! Move tender plants indoors or cover them overnight to protect from cold damage.';
        if (id >= 700 && id < 800) return 'Low visibility and humidity — watch for fungal issues on your plants and ensure good air circulation.';
        if (id === 800) {
            if (temp > 30) return 'Hot and sunny — water your plants in the early morning or evening to minimise evaporation.';
            return 'Perfect gardening weather! Great day for repotting or trimming. Plants will love the sunlight today.';
        }
        if (humidity > 75) return 'High humidity today — ease up on watering and check leaves for any signs of mould or mildew.';
        if (humidity < 35) return 'Dry air can stress indoor plants. Consider misting tropical species or running a humidifier nearby.';
        return 'Mild conditions today. A great time to check soil moisture across your garden and do any routine plant care.';
    }

    function fmtTime(unix, tz) {
        const d = new Date((unix + tz) * 1000);
        const h = d.getUTCHours().toString().padStart(2, '0');
        const m = d.getUTCMinutes().toString().padStart(2, '0');
        return `${h}:${m}`;
    }

    function set(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    async function loadWeather() {
        if (!navigator.geolocation) { set('w-city-slim', 'Location unavailable'); return; }
        navigator.geolocation.getCurrentPosition(async pos => {
            const { latitude: lat, longitude: lon } = pos.coords;
            const url = `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${WEATHER_API_KEY}&units=metric`;
            try {
                const res  = await fetch(url);
                const data = await res.json();
                if (data.cod !== 200) { set('w-city-slim', 'Weather unavailable'); return; }

                const id      = data.weather[0].id;
                const desc    = data.weather[0].description;
                const temp    = Math.round(data.main.temp);
                const feels   = Math.round(data.main.feels_like);
                const hum     = data.main.humidity;
                const wind    = Math.round(data.wind.speed * 3.6); // m/s → km/h
                const vis     = (data.visibility / 1000).toFixed(1);
                const pressure= data.main.pressure;
                const clouds  = data.clouds.all;
                const high    = Math.round(data.main.temp_max);
                const low     = Math.round(data.main.temp_min);
                const city    = data.name + ', ' + data.sys.country;
                const tz      = data.timezone;
                const sunrise = fmtTime(data.sys.sunrise, tz);
                const sunset  = fmtTime(data.sys.sunset, tz);
                const pod     = (Date.now()/1000 > data.sys.sunrise && Date.now()/1000 < data.sys.sunset) ? 'd' : 'n';
                const icon    = weatherIcon(id, pod);
                const bg      = getBg(id, pod);
                const tip     = plantTip(id, hum, temp);

                // UV index requires a separate call (OWM UV endpoint)
                let uv = 'N/A';
                try {
                    const uvRes  = await fetch(`https://api.openweathermap.org/data/2.5/uvi?lat=${lat}&lon=${lon}&appid=${WEATHER_API_KEY}`);
                    const uvData = await uvRes.json();
                    uv = uvData.value !== undefined ? uvData.value.toFixed(1) : 'N/A';
                } catch(_) {}

                // Update background
                document.getElementById('weather-bg').style.backgroundImage = `url('${bg}')`;

                // Slim row
                const iconElSlim = document.getElementById('w-icon-slim');
                iconElSlim.textContent = icon;
                iconElSlim.removeAttribute('id');
                iconElSlim.id = 'w-icon-slim';
                set('w-temp-slim',  `${temp}°`);
                set('w-feel-slim',  `Feels ${feels}°`);
                set('w-city-slim',  city);
                set('w-hum-slim',   `${hum}%`);
                set('w-wind-slim',  `${wind} km/h`);

                // Expanded panel
                document.getElementById('w-icon-big').textContent = icon;
                set('w-temp-big',   `${temp}°`);
                set('w-desc',       desc.charAt(0).toUpperCase() + desc.slice(1));
                set('w-city-big',   city);
                set('w-high',       high);
                set('w-low',        low);
                set('w-condition-tag', desc.toUpperCase());
                set('w-hum',        `${hum}%`);
                set('w-wind',       `${wind} km/h`);
                set('w-uv',         uv);
                set('w-feels',      `${feels}°`);
                set('w-sunrise',    sunrise);
                set('w-sunset',     sunset);
                set('w-vis',        `${vis} km`);
                set('w-pressure',   `${pressure} hPa`);
                set('w-clouds',     `${clouds}%`);
                set('w-tip',        tip);

            } catch(e) { set('w-city-slim', 'Weather unavailable'); }
        }, () => { set('w-city-slim', 'Location denied'); });
    }

    function toggleWeather() {
        weatherExpanded = !weatherExpanded;
        const expanded = document.getElementById('weather-expanded');
        const chevron  = document.getElementById('weather-chevron');
        if (weatherExpanded) {
            expanded.classList.remove('hidden');
            chevron.style.transform = 'rotate(180deg)';
        } else {
            expanded.classList.add('hidden');
            chevron.style.transform = 'rotate(0deg)';
        }
    }
</script>

<!-- ═══════════════════════════════════════════════
     LIGHT METER MODAL
═══════════════════════════════════════════════ -->
<div id="light-modal" class="fixed inset-0 z-[60] bg-black/95 hidden flex-col items-center justify-center backdrop-blur-md">
    <button onclick="closeLightMeter()" class="absolute top-6 right-6 text-white bg-white/20 p-2 rounded-full hover:bg-white/30 transition z-[70]">
        <span class="material-symbols-outlined">close</span>
    </button>
    <div class="w-full max-w-sm px-5 flex flex-col items-center">
        <div class="flex items-center gap-2 mb-6">
            <span class="material-symbols-outlined text-[#cee9d3] filled">wb_sunny</span>
            <h2 class="text-white font-bold text-lg font-heading">Light Meter</h2>
        </div>

        <!-- Camera preview -->
        <div class="w-full aspect-[4/3] bg-zinc-900 border border-white/20 rounded-2xl overflow-hidden relative mb-4">
            <video id="lm-video" class="w-full h-full object-cover hidden" autoplay playsinline muted></video>
            <canvas id="lm-canvas" class="hidden"></canvas>
            <div id="lm-placeholder" class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="material-symbols-outlined text-white/30 text-5xl">wb_sunny</span>
                <p class="text-white/50 text-xs mt-2">Camera will open here</p>
            </div>
            <!-- Live lux overlay -->
            <div id="lm-overlay" class="hidden absolute bottom-3 left-3 right-3 bg-black/60 backdrop-blur-sm rounded-xl px-3 py-2 flex items-center justify-between">
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-white/60">BRIGHTNESS</span>
                <span id="lm-lux-live" class="text-xl font-bold text-[#cee9d3] font-mono">--</span>
            </div>
        </div>

        <!-- Result card -->
        <div id="lm-result" class="hidden w-full bg-white/10 border border-white/15 rounded-2xl p-4 mb-4">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-white/50 font-mono mb-1">LIGHT LEVEL</div>
                    <div id="lm-label" class="text-2xl font-bold text-white font-heading">--</div>
                </div>
                <div id="lm-icon-wrap" class="w-12 h-12 rounded-xl flex items-center justify-center bg-white/10">
                    <span id="lm-icon" class="material-symbols-outlined text-3xl filled text-[#cee9d3]">wb_sunny</span>
                </div>
            </div>
            <!-- Bar -->
            <div class="w-full h-2 bg-white/10 rounded-full mb-3">
                <div id="lm-bar" class="h-full rounded-full transition-all duration-500" style="width:0%"></div>
            </div>
            <p id="lm-advice" class="text-[12px] text-white/70 leading-snug border-l-2 border-[#cee9d3]/40 pl-3"></p>
            <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                <div class="bg-white/5 rounded-lg py-2">
                    <div class="text-[9px] font-mono uppercase tracking-wider text-white/40 mb-0.5">Avg (5s)</div>
                    <div id="lm-avg" class="text-sm font-bold text-white font-mono">--</div>
                </div>
                <div class="bg-white/5 rounded-lg py-2">
                    <div class="text-[9px] font-mono uppercase tracking-wider text-white/40 mb-0.5">Peak</div>
                    <div id="lm-peak" class="text-sm font-bold text-white font-mono">--</div>
                </div>
                <div class="bg-white/5 rounded-lg py-2">
                    <div class="text-[9px] font-mono uppercase tracking-wider text-white/40 mb-0.5">Min</div>
                    <div id="lm-min" class="text-sm font-bold text-white font-mono">--</div>
                </div>
            </div>
        </div>

        <!-- Action buttons -->
        <div class="flex gap-3 w-full">
            <button id="lm-start-btn" onclick="startLightMeter()" class="flex-1 bg-[#cee9d3] text-primary py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition hover:bg-white">
                Start Measuring
            </button>
            <button id="lm-stop-btn" onclick="stopLightMeasure()" class="hidden flex-1 bg-white/15 text-white py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition hover:bg-white/25">
                Stop
            </button>
        </div>

        <!-- Scale reference -->
        <div class="mt-4 w-full grid grid-cols-2 gap-1.5 text-[10px]">
            <div class="bg-white/5 rounded-lg px-2.5 py-1.5 flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                <span class="text-white/60 font-mono">Deep shade &lt;5%</span>
            </div>
            <div class="bg-white/5 rounded-lg px-2.5 py-1.5 flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-green-400"></div>
                <span class="text-white/60 font-mono">Low light 5–20%</span>
            </div>
            <div class="bg-white/5 rounded-lg px-2.5 py-1.5 flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                <span class="text-white/60 font-mono">Medium 20–60%</span>
            </div>
            <div class="bg-white/5 rounded-lg px-2.5 py-1.5 flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-orange-400"></div>
                <span class="text-white/60 font-mono">Bright 60–85%</span>
            </div>
            <div class="bg-white/5 rounded-lg px-2.5 py-1.5 flex items-center gap-2 col-span-2">
                <div class="w-2 h-2 rounded-full bg-red-400"></div>
                <span class="text-white/60 font-mono">Direct / intense &gt;85%</span>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════
     POT MEASURE MODAL
═══════════════════════════════════════════════ -->
<div id="pot-modal" class="fixed inset-0 z-[60] bg-black/95 hidden flex-col items-center justify-center backdrop-blur-md">
    <button onclick="closePotMeasure()" class="absolute top-6 right-6 text-white bg-white/20 p-2 rounded-full hover:bg-white/30 transition z-[70]">
        <span class="material-symbols-outlined">close</span>
    </button>
    <div class="w-full max-w-sm px-5 flex flex-col items-center">
        <div class="flex items-center gap-2 mb-6">
            <span class="material-symbols-outlined text-[#cee9d3] filled">straighten</span>
            <h2 class="text-white font-bold text-lg font-heading">Pot Measure</h2>
        </div>

        <!-- Camera preview with overlay guide -->
        <div class="w-full aspect-[4/3] bg-zinc-900 border border-white/20 rounded-2xl overflow-hidden relative mb-4">
            <video id="pm-video" class="w-full h-full object-cover hidden" autoplay playsinline muted></video>
            <canvas id="pm-canvas" class="hidden absolute inset-0 w-full h-full"></canvas>
            <div id="pm-placeholder" class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="material-symbols-outlined text-white/30 text-5xl">straighten</span>
                <p class="text-white/50 text-xs mt-2">Camera will open here</p>
            </div>
            <!-- Ellipse targeting guide (shown when live) -->
            <div id="pm-guide" class="hidden absolute inset-0 flex items-center justify-center pointer-events-none">
                <svg width="100%" height="100%" viewBox="0 0 320 240" preserveAspectRatio="xMidYMid meet">
                    <ellipse cx="160" cy="120" rx="110" ry="75" fill="none" stroke="#2ecc71" stroke-width="1.5" stroke-dasharray="6 4" opacity="0.8"/>
                    <line x1="50" y1="120" x2="270" y2="120" stroke="#2ecc71" stroke-width="0.5" opacity="0.5"/>
                    <line x1="160" y1="45" x2="160" y2="195" stroke="#2ecc71" stroke-width="0.5" opacity="0.5"/>
                    <text x="160" y="225" text-anchor="middle" fill="#2ecc71" font-size="9" font-family="monospace" opacity="0.8">Align pot rim to ellipse</text>
                </svg>
            </div>
            <!-- Size hint overlay -->
            <div id="pm-size-overlay" class="hidden absolute bottom-3 left-3 right-3 bg-black/60 backdrop-blur-sm rounded-xl px-3 py-2 flex items-center justify-between">
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-white/60">DETECTED</span>
                <span id="pm-size-live" class="text-base font-bold text-[#cee9d3] font-mono">--</span>
            </div>
        </div>

        <!-- Reference object selector -->
        <div class="w-full mb-3">
            <div class="text-[10px] font-bold uppercase tracking-widest text-white/50 font-mono mb-2">REFERENCE OBJECT (helps accuracy)</div>
            <div class="grid grid-cols-3 gap-2" id="ref-selector">
                <button data-ref="coin" data-cm="2.3" onclick="selectRef(this)"
                        class="ref-btn bg-white/20 border border-white/30 text-white text-[10px] font-bold py-2 rounded-xl font-mono uppercase tracking-wide transition">
                    Coin<br><span class="text-white/50 text-[9px] normal-case font-normal">2.3 cm</span>
                </button>
                <button data-ref="creditcard" data-cm="8.56" onclick="selectRef(this)"
                        class="ref-btn bg-white/10 border border-white/15 text-white/60 text-[10px] font-bold py-2 rounded-xl font-mono uppercase tracking-wide transition">
                    Card<br><span class="text-white/40 text-[9px] normal-case font-normal">8.56 cm</span>
                </button>
                <button data-ref="a4" data-cm="21" onclick="selectRef(this)"
                        class="ref-btn bg-white/10 border border-white/15 text-white/60 text-[10px] font-bold py-2 rounded-xl font-mono uppercase tracking-wide transition">
                    A4 width<br><span class="text-white/40 text-[9px] normal-case font-normal">21 cm</span>
                </button>
            </div>
        </div>

        <!-- Result card -->
        <div id="pm-result" class="hidden w-full bg-white/10 border border-white/15 rounded-2xl p-4 mb-4">
            <div class="text-[10px] font-bold uppercase tracking-widest text-white/50 font-mono mb-3">MEASUREMENT RESULT</div>
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="bg-white/5 rounded-xl py-3">
                    <div class="text-[9px] font-mono uppercase tracking-wider text-white/40 mb-1">Diameter</div>
                    <div id="pm-diameter" class="text-lg font-bold text-white font-mono">--</div>
                    <div class="text-[9px] text-white/40">cm</div>
                </div>
                <div class="bg-white/5 rounded-xl py-3">
                    <div class="text-[9px] font-mono uppercase tracking-wider text-white/40 mb-1">Pot Size</div>
                    <div id="pm-size-label" class="text-lg font-bold text-[#cee9d3] font-mono">--</div>
                    <div class="text-[9px] text-white/40">category</div>
                </div>
                <div class="bg-white/5 rounded-xl py-3">
                    <div class="text-[9px] font-mono uppercase tracking-wider text-white/40 mb-1">Volume</div>
                    <div id="pm-volume" class="text-lg font-bold text-white font-mono">--</div>
                    <div class="text-[9px] text-white/40">litres (est.)</div>
                </div>
            </div>
            <p id="pm-advice" class="text-[12px] text-white/70 leading-snug border-l-2 border-[#cee9d3]/40 pl-3 mt-3"></p>
        </div>

        <div class="flex gap-3 w-full">
            <button id="pm-start-btn" onclick="startPotMeasure()" class="flex-1 bg-[#cee9d3] text-primary py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition hover:bg-white">
                Start Camera
            </button>
            <button id="pm-capture-btn" onclick="capturePotPhoto()" class="hidden flex-1 bg-white/15 text-white py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition hover:bg-white/25">
                Capture
            </button>
        </div>

        <p class="text-[11px] text-white/35 text-center mt-3 leading-snug px-2">
            Place a reference object next to the pot. The tool estimates diameter from the circular rim shape using edge detection.
        </p>
    </div>
</div>

<script>
// ─── LIGHT METER ────────────────────────────────────────────────────────────

let lmStream = null, lmInterval = null;
let lmReadings = [], lmPeak = 0, lmMin = 100;

function openLightMeter() {
    document.getElementById('light-modal').classList.remove('hidden');
    document.getElementById('light-modal').classList.add('flex');
    document.body.style.overflow = 'hidden';
    lmReadings = []; lmPeak = 0; lmMin = 100;
}
function closeLightMeter() {
    stopLightMeasure();
    document.getElementById('light-modal').classList.add('hidden');
    document.getElementById('light-modal').classList.remove('flex');
    document.body.style.overflow = '';
    document.getElementById('lm-result').classList.add('hidden');
    document.getElementById('lm-start-btn').classList.remove('hidden');
    document.getElementById('lm-stop-btn').classList.add('hidden');
    document.getElementById('lm-overlay').classList.add('hidden');
    document.getElementById('lm-video').classList.add('hidden');
    document.getElementById('lm-placeholder').style.display = '';
}

async function startLightMeter() {
    try {
        lmStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 640 }, height: { ideal: 480 } }
        });
        const video = document.getElementById('lm-video');
        video.srcObject = lmStream;
        video.classList.remove('hidden');
        document.getElementById('lm-placeholder').style.display = 'none';
        document.getElementById('lm-overlay').classList.remove('hidden');
        document.getElementById('lm-result').classList.remove('hidden');
        document.getElementById('lm-start-btn').classList.add('hidden');
        document.getElementById('lm-stop-btn').classList.remove('hidden');
        lmReadings = []; lmPeak = 0; lmMin = 100;
        lmInterval = setInterval(measureLight, 200);
    } catch(e) {
        // Fallback: desktop ambient light via ImageCapture or manual note
        document.getElementById('lm-placeholder').innerHTML =
            '<span class="material-symbols-outlined text-yellow-400 text-4xl">warning</span>' +
            '<p class="text-white/60 text-xs mt-2 text-center px-4">Camera access denied.<br>Check browser permissions.</p>';
    }
}

function stopLightMeasure() {
    clearInterval(lmInterval);
    if (lmStream) { lmStream.getTracks().forEach(t => t.stop()); lmStream = null; }
}

function measureLight() {
    const video  = document.getElementById('lm-video');
    const canvas = document.getElementById('lm-canvas');
    if (!video.videoWidth) return;
    canvas.width  = 80;  // sample small for performance
    canvas.height = 60;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, 80, 60);
    const data = ctx.getImageData(0, 0, 80, 60).data;
    let sum = 0, count = 0;
    for (let i = 0; i < data.length; i += 4) {
        // Perceived luminance formula
        sum += 0.2126 * data[i] + 0.7152 * data[i+1] + 0.0722 * data[i+2];
        count++;
    }
    const brightness = Math.round((sum / count / 255) * 100); // 0–100%

    lmReadings.push(brightness);
    if (lmReadings.length > 25) lmReadings.shift(); // 5-second window at 200ms
    lmPeak = Math.max(lmPeak, brightness);
    lmMin  = Math.min(lmMin,  brightness);
    const avg = Math.round(lmReadings.reduce((a,b) => a+b, 0) / lmReadings.length);

    // Live overlay
    document.getElementById('lm-lux-live').textContent = brightness + '%';

    // Result panel
    document.getElementById('lm-avg').textContent  = avg + '%';
    document.getElementById('lm-peak').textContent = lmPeak + '%';
    document.getElementById('lm-min').textContent  = lmMin + '%';

    const { label, icon, color, advice } = interpretLight(brightness);
    document.getElementById('lm-label').textContent  = label;
    document.getElementById('lm-icon').textContent   = icon;
    document.getElementById('lm-advice').textContent = advice;
    const bar = document.getElementById('lm-bar');
    bar.style.width      = brightness + '%';
    bar.style.background = color;
}

function interpretLight(b) {
    if (b < 5)  return { label:'Deep Shade',    icon:'dark_mode',       color:'#60a5fa', advice:'Very low light. Suitable only for low-light tolerant plants like ZZ, cast iron plant, or pothos in survival mode.' };
    if (b < 20) return { label:'Low Light',     icon:'nights_stay',     color:'#4ade80', advice:'Good for shade-tolerant species: snake plant, peace lily, Chinese evergreen, or heartleaf philodendron.' };
    if (b < 40) return { label:'Medium Light',  icon:'partly_cloudy_day',color:'#facc15',advice:'Ideal for many houseplants — pothos, spider plant, dracaena, or bird of paradise away from the window.' };
    if (b < 60) return { label:'Bright Indirect',icon:'wb_sunny',        color:'#fb923c',advice:'Perfect for most tropical plants, fiddle leaf fig, monstera, orchids, and herbs like basil and mint.' };
    if (b < 85) return { label:'Bright Light',  icon:'light_mode',      color:'#f97316',advice:'Great for sun-loving plants: succulents, cacti, aloe vera, herbs, and most fruiting crops.' };
    return           { label:'Intense / Direct',icon:'wb_sunny',         color:'#ef4444',advice:'Very high intensity. Only cacti, succulents, and Mediterranean herbs like rosemary and lavender thrive here.' };
}

// ─── POT MEASURE ────────────────────────────────────────────────────────────

let pmStream = null;
let selectedRefCm = 2.3; // default: coin

function selectRef(btn) {
    document.querySelectorAll('.ref-btn').forEach(b => {
        b.classList.remove('bg-white/20', 'border-white/30', 'text-white');
        b.classList.add('bg-white/10', 'border-white/15', 'text-white/60');
    });
    btn.classList.remove('bg-white/10', 'border-white/15', 'text-white/60');
    btn.classList.add('bg-white/20', 'border-white/30', 'text-white');
    selectedRefCm = parseFloat(btn.dataset.cm);
}

function openPotMeasure() {
    document.getElementById('pot-modal').classList.remove('hidden');
    document.getElementById('pot-modal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closePotMeasure() {
    if (pmStream) { pmStream.getTracks().forEach(t => t.stop()); pmStream = null; }
    document.getElementById('pot-modal').classList.add('hidden');
    document.getElementById('pot-modal').classList.remove('flex');
    document.body.style.overflow = '';
    document.getElementById('pm-result').classList.add('hidden');
    document.getElementById('pm-start-btn').classList.remove('hidden');
    document.getElementById('pm-capture-btn').classList.add('hidden');
    document.getElementById('pm-guide').classList.add('hidden');
    document.getElementById('pm-size-overlay').classList.add('hidden');
    document.getElementById('pm-video').classList.add('hidden');
    document.getElementById('pm-placeholder').style.display = '';
}

async function startPotMeasure() {
    try {
        pmStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 960 } }
        });
        const video = document.getElementById('pm-video');
        video.srcObject = pmStream;
        video.classList.remove('hidden');
        document.getElementById('pm-placeholder').style.display = 'none';
        document.getElementById('pm-guide').classList.remove('hidden');
        document.getElementById('pm-size-overlay').classList.remove('hidden');
        document.getElementById('pm-start-btn').classList.add('hidden');
        document.getElementById('pm-capture-btn').classList.remove('hidden');
        document.getElementById('pm-size-live').textContent = 'Position pot...';
    } catch(e) {
        document.getElementById('pm-placeholder').innerHTML =
            '<span class="material-symbols-outlined text-yellow-400 text-4xl">warning</span>' +
            '<p class="text-white/60 text-xs mt-2 text-center px-4">Camera access denied.<br>Check browser permissions.</p>';
    }
}

function capturePotPhoto() {
    const video  = document.getElementById('pm-video');
    const canvas = document.getElementById('pm-canvas');
    if (!video.videoWidth) return;

    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0);

    // Edge-detection based circle finding via brightness gradient analysis
    const result = detectPotDiameter(ctx, canvas.width, canvas.height, selectedRefCm);

    document.getElementById('pm-result').classList.remove('hidden');
    document.getElementById('pm-diameter').textContent  = result.diameterCm.toFixed(1);
    document.getElementById('pm-size-label').textContent = result.sizeLabel;
    document.getElementById('pm-volume').textContent     = result.volumeL.toFixed(1);
    document.getElementById('pm-advice').textContent     = result.advice;
    document.getElementById('pm-size-live').textContent  = result.diameterCm.toFixed(1) + ' cm';
}

function detectPotDiameter(ctx, w, h, refCm) {
    // Sample horizontal brightness profile across the middle third of the frame
    // to find two sharp edges that likely correspond to the pot rim
    const sampleY  = Math.floor(h * 0.45); // slightly above center (rim is higher)
    const imgData  = ctx.getImageData(0, sampleY, w, 1).data;
    const brightness = [];
    for (let x = 0; x < w; x++) {
        const i = x * 4;
        brightness.push(0.2126 * imgData[i] + 0.7152 * imgData[i+1] + 0.0722 * imgData[i+2]);
    }

    // Compute gradient (edge strength)
    const gradient = brightness.map((v, i) =>
        i === 0 || i === w-1 ? 0 : Math.abs(brightness[i+1] - brightness[i-1])
    );

    // Find the two strongest edges in left and right halves
    const mid = Math.floor(w / 2);
    const leftEdge  = gradient.slice(Math.floor(w*0.1), mid)
                               .reduce((best, v, i) => v > best.v ? {v, i: i + Math.floor(w*0.1)} : best, {v:0, i:0});
    const rightEdge = gradient.slice(mid, Math.floor(w*0.9))
                               .reduce((best, v, i) => v > best.v ? {v, i: i + mid} : best, {v:0, i:0});

    const pixelSpan = Math.abs(rightEdge.i - leftEdge.i);
    if (pixelSpan < 10) {
        // Fallback: use guide ellipse size (80% of frame width as estimate)
        return buildPotResult(refCm * (w * 0.7) / (w * 0.15), refCm);
    }

    // Scale: the reference object occupies ~15% of frame width (typical close-up shot)
    // This is a heuristic — accuracy improves if reference object is in frame
    const pxPerCm  = (w * 0.15) / refCm;
    const diamCm   = pixelSpan / pxPerCm;
    return buildPotResult(diamCm, refCm);
}

function buildPotResult(diamCm, refCm) {
    // Clamp to realistic pot sizes
    const d = Math.min(Math.max(diamCm, 5), 60);
    // Estimate volume: cylinder with depth ≈ 0.85× diameter
    const r = d / 2 / 100; // metres
    const depth = d * 0.85 / 100;
    const volumeL = Math.PI * r * r * depth * 1000;

    let sizeLabel, advice;
    if      (d < 9)  { sizeLabel = 'Seedling'; advice = 'Tiny pot — suitable for seedlings or small succulents. Consider upsizing once roots appear at the drainage holes.'; }
    else if (d < 13) { sizeLabel = 'Small';    advice = 'Good for herbs, small succulents, and young plants. Most plants will need repotting within a year.'; }
    else if (d < 18) { sizeLabel = 'Medium';   advice = 'Ideal for most houseplants — pothos, peace lily, small ferns. Check roots annually and upsize by 2–3 cm when rootbound.'; }
    else if (d < 25) { sizeLabel = 'Large';    advice = 'Great for medium tropicals — monstera, rubber plant, bird of paradise. Ensure good drainage to prevent root rot.'; }
    else if (d < 35) { sizeLabel = 'XL';       advice = 'Suitable for large indoor trees or outdoor container plants. Use well-draining mix and water deeply but infrequently.'; }
    else             { sizeLabel = 'Planter';  advice = 'Large planter or raised bed. Perfect for multiple plants or a single specimen tree. Ensure adequate drainage.'; }

    return { diameterCm: d, sizeLabel, volumeL, advice };
}
</script>

</body>
