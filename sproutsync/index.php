<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SproutSync Intro</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&family=Work+Sans:wght@400;500;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-variant": "#e4e2e1",
                        "primary-container": "#1b4332",
                        "on-surface": "#1b1c1c",
                        "on-secondary-fixed-variant": "#354c3b",
                        "secondary-fixed-dim": "#b3cdb7",
                        "tertiary": "#3f1d00",
                        "outline-variant": "#c1c8c2",
                        "on-primary-fixed": "#002114",
                        "tertiary-fixed-dim": "#ffb780",
                        "surface-container-highest": "#e4e2e1",
                        "on-primary-container": "#86af99",
                        "surface-container-lowest": "#ffffff",
                        "on-error": "#ffffff",
                        "on-secondary": "#ffffff",
                        "secondary-container": "#cce6d0",
                        "on-tertiary-fixed-variant": "#6f3800",
                        "on-background": "#1b1c1c",
                        "on-tertiary": "#ffffff",
                        "surface-dim": "#dcd9d9",
                        "surface-container-low": "#f6f3f2",
                        "surface-container-high": "#eae7e7",
                        "surface-container": "#f0eded",
                        "primary": "#012d1d",
                        "surface-tint": "#3f6653",
                        "primary-fixed": "#c1ecd4",
                        "on-surface-variant": "#414844",
                        "primary-fixed-dim": "#a5d0b9",
                        "surface-bright": "#fcf9f8",
                        "tertiary-container": "#5f2f00",
                        "on-secondary-container": "#506856",
                        "outline": "#717973",
                        "inverse-primary": "#a5d0b9",
                        "secondary-fixed": "#cee9d3",
                        "on-tertiary-container": "#e39454",
                        "on-primary": "#ffffff",
                        "inverse-on-surface": "#f3f0f0",
                        "background": "#fcf9f8",
                        "inverse-surface": "#303030",
                        "on-secondary-fixed": "#092012",
                        "error-container": "#ffdad6",
                        "error": "#ba1a1a",
                        "tertiary-fixed": "#ffdcc4",
                        "surface": "#fcf9f8",
                        "on-tertiary-fixed": "#2f1400",
                        "on-error-container": "#93000a",
                        "secondary": "#4c6452",
                        "on-primary-fixed-variant": "#274e3d"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "margin-mobile": "16px",
                        "margin-desktop": "48px",
                        "gutter": "24px",
                        "base": "8px",
                        "container-max": "1280px"
                    },
                    "fontFamily": {
                        "headline-lg": ["Manrope"],
                        "headline-lg-mobile": ["Manrope"],
                        "body-md": ["Work Sans"],
                        "label-caps": ["Work Sans"],
                        "headline-md": ["Manrope"],
                        "body-sm": ["Work Sans"],
                        "label-data": ["JetBrains Mono"]
                    },
                    "fontSize": {
                        "headline-lg": ["40px", {"lineHeight": "48px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "headline-lg-mobile": ["30px", {"lineHeight": "36px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                        "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "label-caps": ["11px", {"lineHeight": "14px", "fontWeight": "700"}],
                        "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                        "body-sm": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "label-data": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "500"}]
                    }
                }
            }
        }
    </script>
    <style>
        .slide {
            transition: transform 0.5s ease-in-out, opacity 0.5s ease-in-out;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transform: translateX(100%);
            z-index: 10;
        }
        .slide.active {
            opacity: 1;
            transform: translateX(0);
            z-index: 20;
        }
        .slide.prev {
            transform: translateX(-100%);
        }
        
        .glass-overlay {
            background: linear-gradient(to top, rgba(1, 45, 29, 0.9) 0%, rgba(1, 45, 29, 0.4) 50%, rgba(1, 45, 29, 0.1) 100%);
        }
    </style>
</head>
<body class="bg-background text-on-background h-screen w-screen overflow-hidden font-body-md antialiased selection:bg-primary-container selection:text-on-primary-container">
<!-- DEBUG: YOU SHOULD SEE THIS IF index.php IS THE SLIDER -->
<div style="position:fixed; top:0; left:0; background:red; color:white; z-index:9999; padding:5px; font-size:10px;">INDEX.PHP (SLIDER) LOADED</div>

<div class="relative w-full h-full" id="slider-container">
    <!-- Slide 1: Molecular Analysis -->
    <div class="slide active flex flex-col justify-end" id="slide-1">
        <div class="absolute inset-0 z-0">
            <img alt="Close-up of a leaf representing molecular analysis" class="w-full h-full object-cover" src="assets/intro/intro-leaf.png"/>
            <div class="absolute inset-0 glass-overlay"></div>
        </div>
        <div class="relative z-10 p-margin-mobile md:p-margin-desktop pb-32 text-center md:text-left">
            <h1 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-white mb-4 drop-shadow-md">Molecular Analysis</h1>
            <p class="font-body-md text-body-md text-surface-container-lowest opacity-90 max-w-md drop-shadow-sm mx-auto md:mx-0">Understand your plants at a cellular level.</p>
        </div>
    </div>

    <!-- Slide 2: Smart Growth Tracking -->
    <div class="slide flex flex-col justify-end" id="slide-2">
        <div class="absolute inset-0 z-0">
            <img alt="Scanning a plant with a smartphone" class="w-full h-full object-cover" src="assets/intro/intro-scan.png"/>
            <div class="absolute inset-0 glass-overlay"></div>
        </div>
        <div class="relative z-10 p-margin-mobile md:p-margin-desktop pb-32 text-center md:text-left">
            <h1 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-white mb-4 drop-shadow-md">Smart Growth Tracking</h1>
            <p class="font-body-md text-body-md text-surface-container-lowest opacity-90 max-w-md drop-shadow-sm mx-auto md:mx-0">Precision monitoring for every leaf.</p>
        </div>
    </div>

    <!-- Slide 3: Smart Soil Health -->
    <div class="slide flex flex-col justify-end" id="slide-3">
        <div class="absolute inset-0 z-0">
            <img alt="Soil moisture sensor monitoring" class="w-full h-full object-cover" src="assets/intro/intro-sensor.png"/>
            <div class="absolute inset-0 glass-overlay"></div>
        </div>
        <div class="relative z-10 p-margin-mobile md:p-margin-desktop pb-32 text-center md:text-left">
            <h1 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-white mb-4 drop-shadow-md">Smart Soil Health</h1>
            <p class="font-body-md text-body-md text-surface-container-lowest opacity-90 max-w-md drop-shadow-sm mx-auto md:mx-0">Monitor pH, nitrogen, and moisture levels in real-time.</p>
        </div>
    </div>

    <!-- Slide 4: Automated Care -->
    <div class="slide flex flex-col justify-end" id="slide-4">
        <div class="absolute inset-0 z-0">
            <img alt="Automated irrigation system" class="w-full h-full object-cover" src="assets/intro/intro-mist.png"/>
            <div class="absolute inset-0 glass-overlay"></div>
        </div>
        <div class="relative z-10 p-margin-mobile md:p-margin-desktop pb-32 text-center md:text-left">
            <h1 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-white mb-4 drop-shadow-md">Automated Care</h1>
            <p class="font-body-md text-body-md text-surface-container-lowest opacity-90 max-w-md drop-shadow-sm mx-auto md:mx-0">Schedule smart irrigation and misting to keep your botanical ecosystem thriving.</p>
        </div>
    </div>

    <!-- Slide 5: Call to Action -->
    <div class="slide flex flex-col items-center justify-center bg-surface" id="slide-5">
        <div class="absolute inset-0 z-0 opacity-10">
            <img alt="Background" class="w-full h-full object-cover" src="assets/intro/intro-main.png"/>
        </div>
        <div class="relative z-10 flex flex-col items-center justify-center w-full max-w-md p-margin-mobile md:p-margin-desktop text-center">
            <div class="flex items-center justify-center mb-8">
                <span class="material-symbols-outlined text-[48px] text-primary mr-3" style="font-variation-settings: 'FILL' 1;">eco</span>
                <span class="font-headline-lg-mobile text-headline-lg-mobile text-primary tracking-tight">SproutSync</span>
            </div>
            <h2 class="font-headline-md text-headline-md text-on-surface mb-4">Your urban jungle, synchronized.</h2>
            <p class="font-body-md text-body-md text-on-surface-variant mb-12">Join the next generation of botanical care.</p>
            <div class="w-full space-y-4">
                <button onclick="location.href='register.php'" class="w-full py-4 bg-primary text-on-primary font-headline-md text-headline-md rounded-full shadow-lg hover:bg-primary-container hover:shadow-xl transition-all duration-300 transform active:scale-95 flex justify-center items-center">
                    Register
                </button>
                <button onclick="location.href='login.php'" class="w-full py-4 bg-surface-container-low text-primary font-headline-md text-headline-md rounded-full border border-outline-variant hover:bg-surface-container-high transition-all duration-300 transform active:scale-95 flex justify-center items-center">
                    Login
                </button>
            </div>
        </div>
    </div>

    <!-- Pagination Controls -->
    <div class="absolute bottom-12 left-0 right-0 z-30 flex justify-center items-center space-x-3" id="pagination">
        <button aria-label="Go to slide 1" class="w-3 h-3 rounded-full bg-white shadow-sm transition-all duration-300"></button>
        <button aria-label="Go to slide 2" class="w-2 h-2 rounded-full bg-white/50 hover:bg-white/80 shadow-sm transition-all duration-300"></button>
        <button aria-label="Go to slide 3" class="w-2 h-2 rounded-full bg-white/50 hover:bg-white/80 shadow-sm transition-all duration-300"></button>
        <button aria-label="Go to slide 4" class="w-2 h-2 rounded-full bg-white/50 hover:bg-white/80 shadow-sm transition-all duration-300"></button>
        <button aria-label="Go to slide 5" class="w-2 h-2 rounded-full bg-white/50 hover:bg-white/80 shadow-sm transition-all duration-300"></button>
    </div>

    <!-- Navigation Arrows -->
    <button class="flex absolute top-1/2 left-4 -translate-y-1/2 z-30 w-12 h-12 bg-black/20 hover:bg-black/40 backdrop-blur-sm rounded-full text-white items-center justify-center transition-colors" id="prev-btn">
        <span class="material-symbols-outlined">chevron_left</span>
    </button>
    <button class="flex absolute top-1/2 right-4 -translate-y-1/2 z-30 w-12 h-12 bg-black/20 hover:bg-black/40 backdrop-blur-sm rounded-full text-white items-center justify-center transition-colors" id="next-btn">
        <span class="material-symbols-outlined">chevron_right</span>
    </button>
</div>

<script>
    function showNotification(message, icon, type = 'warning') {
        let container = document.getElementById('notification-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'fixed top-6 right-6 z-[9999] space-y-3 pointer-events-none max-w-sm w-full px-4 md:px-0';
            document.body.appendChild(container);
        }
        
        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto flex items-center gap-3 bg-white border border-outline-variant/30 rounded-2xl p-4 shadow-xl translate-x-12 opacity-0 transition-all duration-300 ease-out';
        
        const iconColor = type === 'warning' ? 'text-[#ba1a1a]' : 'text-primary';
        const iconBg = type === 'warning' ? 'bg-[#ffdad6]' : 'bg-[#cce6d0]';
        
        toast.innerHTML = `
            <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center ${iconBg}">
                <span class="material-symbols-outlined ${iconColor} text-xl" style="font-variation-settings: 'FILL' 1;">${icon}</span>
            </div>
            <div class="flex-1">
                <h4 class="font-heading font-bold text-sm text-primary">Alert Notification</h4>
                <p class="text-xs text-on-surface-variant leading-relaxed mt-0.5">${message}</p>
            </div>
            <button class="text-on-surface-variant/40 hover:text-on-surface-variant transition-colors flex items-center" onclick="this.parentElement.remove()">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        `;
        
        container.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => {
            toast.classList.remove('translate-x-12', 'opacity-0');
        }, 10);
        
        // Auto-remove after 6 seconds
        setTimeout(() => {
            toast.classList.add('translate-x-12', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 6000);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('#pagination button');
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        
        let currentSlide = 0;
        const totalSlides = slides.length;

        function updateSlider(index) {
            if (index < 0) index = totalSlides - 1;
            if (index >= totalSlides) index = 0;
            
            currentSlide = index;

            slides.forEach((slide, i) => {
                slide.classList.remove('active', 'prev');
                if (i === currentSlide) {
                    slide.classList.add('active');
                    slide.style.transform = 'translateX(0)';
                    slide.style.opacity = '1';
                } else if (i < currentSlide) {
                    slide.style.transform = 'translateX(-100%)';
                    slide.style.opacity = '0';
                } else {
                    slide.style.transform = 'translateX(100%)';
                    slide.style.opacity = '0';
                }
            });

            dots.forEach((dot, i) => {
                if (i === currentSlide) {
                    dot.classList.replace('w-2', 'w-3');
                    dot.classList.replace('h-2', 'h-3');
                    dot.classList.remove('bg-white/50', 'hover:bg-white/80');
                    dot.classList.add('bg-white');
                    
                    if(currentSlide === 4) {
                         dot.classList.replace('bg-white', 'bg-primary');
                    } else {
                         dot.classList.remove('bg-primary');
                         dot.classList.add('bg-white');
                    }
                } else {
                    dot.classList.replace('w-3', 'w-2');
                    dot.classList.replace('h-3', 'h-2');
                    
                    if(currentSlide === 4) {
                        dot.classList.remove('bg-white', 'bg-white/50', 'hover:bg-white/80');
                        dot.classList.add('bg-primary/30', 'hover:bg-primary/60');
                    } else {
                        dot.classList.remove('bg-primary', 'bg-primary/30', 'hover:bg-primary/60');
                        dot.classList.add('bg-white/50', 'hover:bg-white/80');
                    }
                }
            });
        }

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => updateSlider(index));
        });

        if(prevBtn) prevBtn.addEventListener('click', () => updateSlider(currentSlide - 1));
        if(nextBtn) nextBtn.addEventListener('click', () => updateSlider(currentSlide + 1));

        // Swipe support
        let touchStartX = 0;
        let touchEndX = 0;
        const sliderContainer = document.getElementById('slider-container');
        
        sliderContainer.addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
        }, {passive: true});

        sliderContainer.addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            if (touchEndX < touchStartX - 50) updateSlider(currentSlide + 1);
            if (touchEndX > touchStartX + 50) updateSlider(currentSlide - 1);
        }, {passive: true});
        
        // Auto-play
        setInterval(() => {
            if (currentSlide < 4) updateSlider(currentSlide + 1);
        }, 5000);

        // Show alerts if logged in
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        if (isLoggedIn) {
            setTimeout(() => {
                showNotification("Plants are not watered!", "water_drop", "warning");
            }, 1000);
            setTimeout(() => {
                showNotification("Plants don't have enough light!", "wb_sunny", "warning");
            }, 2200);
        }
    });
</script>
</body>
</html>
