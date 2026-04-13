<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tokaku — Toko kamu, lebih mudah.</title>
    <meta name="description" content="Tokaku adalah aplikasi kasir & manajemen UMKM all-in-one. Kelola penjualan, stok, laporan, dan tim toko dari satu platform yang simpel.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --green:     #0F6E56;
            --green-mid: #1A8F6F;
            --green-lt:  #E8F5F0;
            --green-xl:  #F2FAF7;
            --ink:       #0D1F1A;
            --ink-mid:   #2E4840;
            --muted:     #7A9E92;
            --border:    #D4EAE2;
            --white:     #FFFFFF;
            --cream:     #FAF9F6;
            --serif:     'Instrument Serif', Georgia, serif;
            --sans:      'DM Sans', system-ui, sans-serif;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: var(--sans);
            background: var(--cream);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        /* ── NAV ── */
        nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 5vw; height: 64px;
            background: rgba(250,249,246,0.85);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--border);
            transition: box-shadow 0.3s;
        }
        nav.scrolled { box-shadow: 0 2px 24px rgba(15,110,86,0.08); }
        .nav-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .nav-logo-mark {
            width: 34px; height: 34px; background: var(--green); border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .nav-logo-text { font-family: var(--serif); font-size: 22px; color: var(--green); letter-spacing: -0.3px; }
        .nav-links { display: flex; align-items: center; gap: 32px; }
        .nav-links a { font-size: 14px; font-weight: 500; color: var(--ink-mid); text-decoration: none; transition: color 0.15s; }
        .nav-links a:hover { color: var(--green); }
        .nav-cta {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--green); color: var(--white);
            font-family: var(--sans); font-size: 14px; font-weight: 500;
            padding: 9px 20px; border-radius: 99px; text-decoration: none;
            transition: all 0.2s; box-shadow: 0 2px 12px rgba(15,110,86,0.25);
        }
        .nav-cta:hover { background: #085041; transform: translateY(-1px); box-shadow: 0 4px 20px rgba(15,110,86,0.35); }

        /* ── HERO ── */
        .hero {
            min-height: 100vh;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-align: center; padding: 100px 5vw 80px;
            position: relative; overflow: hidden;
        }
        .hero-bg {
            position: absolute; inset: 0; z-index: 0;
            background:
                radial-gradient(ellipse 60% 50% at 20% 60%, rgba(15,110,86,0.08) 0%, transparent 70%),
                radial-gradient(ellipse 50% 40% at 80% 30%, rgba(15,110,86,0.06) 0%, transparent 70%),
                var(--cream);
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 7px;
            background: var(--green-lt); color: var(--green);
            font-size: 12.5px; font-weight: 600; letter-spacing: 0.4px;
            padding: 6px 16px; border-radius: 99px; margin-bottom: 28px;
            border: 1px solid var(--border);
            animation: fadeUp 0.6s ease both;
        }
        .hero-badge-dot { width: 6px; height: 6px; background: var(--green); border-radius: 50%; animation: pulse 2s infinite; }
        .hero h1 {
            font-family: var(--serif);
            font-size: clamp(42px, 7vw, 80px);
            font-weight: 400; line-height: 1.1;
            color: var(--ink); letter-spacing: -1.5px;
            max-width: 800px; margin-bottom: 22px;
            animation: fadeUp 0.6s 0.1s ease both;
        }
        .hero h1 em { color: var(--green); font-style: italic; }
        .hero p {
            font-size: clamp(16px, 2vw, 18px); color: var(--muted);
            max-width: 480px; line-height: 1.7; margin-bottom: 40px;
            animation: fadeUp 0.6s 0.2s ease both;
        }
        .hero-actions {
            display: flex; align-items: center; gap: 14px; flex-wrap: wrap; justify-content: center;
            animation: fadeUp 0.6s 0.3s ease both;
        }
        .btn-hero {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--green); color: var(--white);
            font-family: var(--sans); font-size: 15px; font-weight: 600;
            padding: 14px 28px; border-radius: 99px; text-decoration: none;
            transition: all 0.2s; box-shadow: 0 4px 20px rgba(15,110,86,0.3);
        }
        .btn-hero:hover { background: #085041; transform: translateY(-2px); box-shadow: 0 8px 32px rgba(15,110,86,0.35); }
        .btn-ghost {
            display: inline-flex; align-items: center; gap: 8px;
            color: var(--ink-mid); font-family: var(--sans); font-size: 15px; font-weight: 500;
            padding: 14px 28px; border-radius: 99px; text-decoration: none;
            border: 1.5px solid var(--border); background: var(--white);
            transition: all 0.2s;
        }
        .btn-ghost:hover { border-color: var(--green); color: var(--green); }

        /* Hero mockup */
        .hero-mockup {
            margin-top: 60px; width: 100%; max-width: 860px;
            background: var(--white); border-radius: 20px;
            border: 1px solid var(--border);
            box-shadow: 0 20px 80px rgba(15,110,86,0.12), 0 2px 8px rgba(0,0,0,0.04);
            overflow: hidden;
            animation: fadeUp 0.8s 0.4s ease both;
        }
        .mockup-bar {
            background: #F7F7F5; border-bottom: 1px solid #EEECEA;
            padding: 12px 16px; display: flex; align-items: center; gap: 8px;
        }
        .dot { width: 12px; height: 12px; border-radius: 50%; }
        .mockup-content {
            display: grid; grid-template-columns: 200px 1fr;
            min-height: 320px;
        }
        .mockup-sidebar {
            background: var(--white); border-right: 1px solid #F0F0EE;
            padding: 16px 12px; display: flex; flex-direction: column; gap: 4px;
        }
        .mockup-logo { display: flex; align-items: center; gap: 8px; padding: 8px 10px; margin-bottom: 8px; }
        .mockup-logo-icon { width: 26px; height: 26px; background: var(--green); border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .mockup-logo-text { font-family: var(--serif); font-size: 16px; color: var(--green); }
        .mockup-link {
            display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 8px;
            font-size: 12px; font-weight: 500; color: #888; transition: all 0.15s;
        }
        .mockup-link.active { background: var(--green); color: var(--white); }
        .mockup-link svg { flex-shrink: 0; }
        .mockup-main { padding: 20px; display: flex; flex-direction: column; gap: 14px; }
        .mockup-title { font-size: 14px; font-weight: 700; color: var(--ink); }
        .mockup-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .mockup-card {
            background: var(--green-xl); border: 1px solid var(--border);
            border-radius: 10px; padding: 12px;
        }
        .mockup-card-label { font-size: 10px; color: var(--muted); margin-bottom: 6px; }
        .mockup-card-val { font-size: 16px; font-weight: 700; color: var(--green); }
        .mockup-card-sub { font-size: 9px; color: var(--muted); margin-top: 2px; }
        .mockup-chart {
            background: var(--green-xl); border: 1px solid var(--border);
            border-radius: 10px; padding: 14px; display: flex; align-items: flex-end; gap: 6px; height: 80px;
        }
        .bar {
            flex: 1; background: var(--green); border-radius: 4px 4px 0 0; opacity: 0.7;
            transition: opacity 0.2s;
        }
        .bar:hover { opacity: 1; }

        /* ── LOGOS ── */
        .logos-section {
            padding: 40px 5vw; text-align: center; border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border); background: var(--white);
        }
        .logos-label { font-size: 12px; font-weight: 600; color: var(--muted); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 24px; }
        .logos-row { display: flex; align-items: center; justify-content: center; gap: 40px; flex-wrap: wrap; }
        .logo-pill {
            display: flex; align-items: center; gap: 8px;
            font-size: 14px; font-weight: 600; color: var(--muted);
            padding: 8px 18px; border-radius: 99px; border: 1px solid var(--border);
        }

        /* ── SECTION GENERIC ── */
        section { padding: 80px 5vw; }
        .section-tag {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 11.5px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase;
            color: var(--green); margin-bottom: 16px;
        }
        .section-title {
            font-family: var(--serif); font-size: clamp(32px, 5vw, 52px);
            font-weight: 400; line-height: 1.15; letter-spacing: -0.8px;
            color: var(--ink); max-width: 560px; margin-bottom: 16px;
        }
        .section-title em { color: var(--green); font-style: italic; }
        .section-sub { font-size: 16px; color: var(--muted); line-height: 1.7; max-width: 480px; margin-bottom: 48px; }

        /* ── FEATURES ── */
        .features-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;
        }
        .feature-card {
            background: var(--white); border: 1px solid var(--border);
            border-radius: 18px; padding: 28px; transition: all 0.25s;
            position: relative; overflow: hidden;
        }
        .feature-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: var(--green); transform: scaleX(0); transform-origin: left;
            transition: transform 0.3s ease;
        }
        .feature-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(15,110,86,0.1); border-color: var(--green); }
        .feature-card:hover::before { transform: scaleX(1); }
        .feature-icon {
            width: 44px; height: 44px; background: var(--green-lt); border-radius: 12px;
            display: flex; align-items: center; justify-content: center; margin-bottom: 18px;
        }
        .feature-title { font-size: 16px; font-weight: 600; color: var(--ink); margin-bottom: 8px; }
        .feature-desc { font-size: 14px; color: var(--muted); line-height: 1.6; }

        /* ── HOW IT WORKS ── */
        .how-bg { background: var(--ink); }
        .how-bg .section-tag { color: #5DCAA5; }
        .how-bg .section-title { color: var(--white); }
        .how-bg .section-title em { color: #5DCAA5; }
        .how-bg .section-sub { color: #7A9E92; }
        .steps { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2px; }
        .step {
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px; padding: 28px; position: relative; overflow: hidden;
            transition: background 0.2s;
        }
        .step:hover { background: rgba(255,255,255,0.07); }
        .step-num {
            font-family: var(--serif); font-size: 52px; font-weight: 400;
            color: rgba(255,255,255,0.06); position: absolute; top: 16px; right: 20px;
            line-height: 1;
        }
        .step-icon {
            width: 40px; height: 40px; background: rgba(93,202,165,0.15); border-radius: 10px;
            display: flex; align-items: center; justify-content: center; margin-bottom: 16px;
        }
        .step-title { font-size: 15px; font-weight: 600; color: var(--white); margin-bottom: 8px; }
        .step-desc { font-size: 13.5px; color: #7A9E92; line-height: 1.6; }

        /* ── PRICING ── */
        .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; max-width: 900px; }
        .pricing-card {
            background: var(--white); border: 1.5px solid var(--border);
            border-radius: 20px; padding: 32px; transition: all 0.25s; position: relative;
        }
        .pricing-card.featured {
            background: var(--green); border-color: var(--green);
            transform: scale(1.03);
            box-shadow: 0 20px 60px rgba(15,110,86,0.25);
        }
        .pricing-badge {
            display: inline-block; background: rgba(255,255,255,0.2); color: var(--white);
            font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;
            padding: 4px 12px; border-radius: 99px; margin-bottom: 20px;
        }
        .pricing-name { font-size: 16px; font-weight: 600; color: var(--ink); margin-bottom: 8px; }
        .pricing-card.featured .pricing-name { color: rgba(255,255,255,0.8); }
        .pricing-price {
            font-family: var(--serif); font-size: 44px; font-weight: 400;
            color: var(--ink); line-height: 1; margin-bottom: 4px; letter-spacing: -1px;
        }
        .pricing-card.featured .pricing-price { color: var(--white); }
        .pricing-period { font-size: 13px; color: var(--muted); margin-bottom: 24px; }
        .pricing-card.featured .pricing-period { color: rgba(255,255,255,0.6); }
        .pricing-features { list-style: none; display: flex; flex-direction: column; gap: 10px; margin-bottom: 28px; }
        .pricing-features li {
            display: flex; align-items: center; gap: 10px;
            font-size: 14px; color: var(--ink-mid);
        }
        .pricing-card.featured .pricing-features li { color: rgba(255,255,255,0.85); }
        .pricing-features li svg { flex-shrink: 0; }
        .btn-pricing {
            display: block; text-align: center; font-family: var(--sans);
            font-size: 14px; font-weight: 600; padding: 13px; border-radius: 12px;
            text-decoration: none; transition: all 0.2s;
        }
        .btn-pricing.outline {
            border: 1.5px solid var(--border); color: var(--ink);
            background: var(--white);
        }
        .btn-pricing.outline:hover { border-color: var(--green); color: var(--green); }
        .btn-pricing.solid {
            background: var(--white); color: var(--green);
        }
        .btn-pricing.solid:hover { background: var(--green-lt); }

        /* ── TESTIMONIALS ── */
        .testimonials-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; }
        .testimonial-card {
            background: var(--white); border: 1px solid var(--border);
            border-radius: 16px; padding: 24px; transition: all 0.2s;
        }
        .testimonial-card:hover { box-shadow: 0 8px 30px rgba(15,110,86,0.08); transform: translateY(-2px); }
        .stars { display: flex; gap: 3px; margin-bottom: 14px; }
        .testimonial-text { font-size: 14.5px; color: var(--ink-mid); line-height: 1.7; margin-bottom: 18px; font-style: italic; }
        .testimonial-author { display: flex; align-items: center; gap: 10px; }
        .author-avatar {
            width: 36px; height: 36px; border-radius: 50%; background: var(--green-lt);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: var(--green);
        }
        .author-name { font-size: 13.5px; font-weight: 600; color: var(--ink); }
        .author-role { font-size: 12px; color: var(--muted); }

        /* ── CTA ── */
        .cta-section {
            background: var(--green); padding: 80px 5vw; text-align: center;
            position: relative; overflow: hidden;
        }
        .cta-section::before {
            content: ''; position: absolute;
            top: -60%; left: -20%; width: 60%; height: 200%;
            background: rgba(255,255,255,0.04); border-radius: 50%;
        }
        .cta-section::after {
            content: ''; position: absolute;
            bottom: -60%; right: -20%; width: 60%; height: 200%;
            background: rgba(255,255,255,0.04); border-radius: 50%;
        }
        .cta-tag { color: rgba(255,255,255,0.6); font-size: 12px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 16px; }
        .cta-title {
            font-family: var(--serif); font-size: clamp(32px, 5vw, 56px);
            color: var(--white); font-weight: 400; letter-spacing: -1px; margin-bottom: 16px;
        }
        .cta-sub { font-size: 16px; color: rgba(255,255,255,0.7); margin-bottom: 36px; max-width: 440px; margin-left: auto; margin-right: auto; }
        .btn-cta-white {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--white); color: var(--green);
            font-family: var(--sans); font-size: 15px; font-weight: 600;
            padding: 14px 32px; border-radius: 99px; text-decoration: none;
            transition: all 0.2s; box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }
        .btn-cta-white:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(0,0,0,0.18); }

        /* ── FOOTER ── */
        footer {
            background: var(--ink); padding: 60px 5vw 32px;
        }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 40px; margin-bottom: 48px; }
        .footer-brand { }
        .footer-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
        .footer-logo-mark { width: 32px; height: 32px; background: var(--green); border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .footer-logo-text { font-family: var(--serif); font-size: 20px; color: var(--white); }
        .footer-desc { font-size: 14px; color: #5A7A70; line-height: 1.7; max-width: 220px; }
        .footer-col h4 { font-size: 12px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; color: #5A7A70; margin-bottom: 16px; }
        .footer-col a { display: block; font-size: 14px; color: #4A6A60; text-decoration: none; margin-bottom: 10px; transition: color 0.15s; }
        .footer-col a:hover { color: #5DCAA5; }
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.06); padding-top: 24px;
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
        }
        .footer-bottom p { font-size: 13px; color: #3A5A50; }

        /* ── ANIMATIONS ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.5; transform: scale(0.85); }
        }

        .fade-in {
            opacity: 0; transform: translateY(24px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .fade-in.visible { opacity: 1; transform: none; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .nav-links { display: none; }
            .mockup-content { grid-template-columns: 1fr; }
            .mockup-sidebar { display: none; }
            .mockup-cards { grid-template-columns: repeat(2, 1fr); }
            .footer-grid { grid-template-columns: 1fr 1fr; }
            .pricing-card.featured { transform: none; }
            .steps { grid-template-columns: 1fr; }
        }
        @media (max-width: 480px) {
            .footer-grid { grid-template-columns: 1fr; }
            .hero-actions { flex-direction: column; width: 100%; }
            .btn-hero, .btn-ghost { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

<!-- NAV -->
<nav id="navbar">
    <a href="#" class="nav-logo">
        <div class="nav-logo-mark">
            <svg width="16" height="16" viewBox="0 0 18 18" fill="none"><path d="M3 5h12M3 9h8M3 13h5" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <span class="nav-logo-text">Tokaku</span>
    </a>
    <div class="nav-links">
        <a href="#fitur">Fitur</a>
        <a href="#cara-kerja">Cara Kerja</a>
        <a href="#harga">Harga</a>
        <a href="#testimoni">Testimoni</a>
    </div>
    <a href="{{ route('login') }}" class="nav-cta">
        Mulai Gratis
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
    </a>
</nav>

<!-- HERO -->
<section class="hero" style="position:relative;">
    <div class="hero-bg"></div>
    <div style="position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;">
        <div class="hero-badge">
            <span class="hero-badge-dot"></span>
            Aplikasi Kasir & UMKM All-in-One
        </div>
        <h1>Toko kamu,<br><em>lebih mudah.</em></h1>
        <p>Kelola kasir, stok, laporan, dan tim toko dalam satu platform yang simpel — cocok untuk UMKM F&B, retail, dan jasa.</p>
        <div class="hero-actions">
            <a href="{{ route('login') }}" class="btn-hero">
                Coba Gratis 14 Hari
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
            <a href="#fitur" class="btn-ghost">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Lihat Demo
            </a>
        </div>

        <!-- Mockup -->
        <div class="hero-mockup">
            <div class="mockup-bar">
                <div class="dot" style="background:#FF5F57;"></div>
                <div class="dot" style="background:#FFBD2E;"></div>
                <div class="dot" style="background:#28C840;"></div>
                <div style="flex:1;background:#EEECEA;border-radius:4px;height:18px;margin:0 12px;display:flex;align-items:center;justify-content:center;">
                    <span style="font-size:11px;color:#AAA;">tokaku.id/dashboard</span>
                </div>
            </div>
            <div class="mockup-content">
                <div class="mockup-sidebar">
                    <div class="mockup-logo">
                        <div class="mockup-logo-icon">
                            <svg width="12" height="12" viewBox="0 0 18 18" fill="none"><path d="M3 5h12M3 9h8M3 13h5" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
                        </div>
                        <span class="mockup-logo-text">Tokaku</span>
                    </div>
                    @foreach([['Dashboard','M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',true],['Kasir','M9 7H6a2 2 0 00-2 2v9a2 2 0 002 2h9a2 2 0 002-2v-3M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2M9 7h6',false],['Produk','M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',false],['Laporan','M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10',false],['Stok','M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172',false]] as [$label,$path,$active])
                    <div class="mockup-link {{ $active ? 'active' : '' }}">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                        {{ $label }}
                    </div>
                    @endforeach
                </div>
                <div class="mockup-main">
                    <p class="mockup-title">Dashboard — Warung Budi</p>
                    <div class="mockup-cards">
                        <div class="mockup-card">
                            <div class="mockup-card-label">Omzet Hari Ini</div>
                            <div class="mockup-card-val">Rp 847k</div>
                            <div class="mockup-card-sub">↑ 12% dari kemarin</div>
                        </div>
                        <div class="mockup-card">
                            <div class="mockup-card-label">Transaksi</div>
                            <div class="mockup-card-val">34</div>
                            <div class="mockup-card-sub">transaksi hari ini</div>
                        </div>
                        <div class="mockup-card">
                            <div class="mockup-card-label">Produk Aktif</div>
                            <div class="mockup-card-val">128</div>
                            <div class="mockup-card-sub">3 stok menipis</div>
                        </div>
                    </div>
                    <div class="mockup-chart">
                        @foreach([35,55,42,70,48,85,62,90,55,75,88,65] as $h)
                        <div class="bar" style="height:{{ $h }}%;"></div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- LOGOS -->
<div class="logos-section">
    <p class="logos-label">Dipercaya oleh UMKM dari berbagai industri</p>
    <div class="logos-row">
        @foreach(['🍜 F&B & Kuliner','🛒 Retail & Minimarket','💈 Salon & Jasa','🏪 Toko Kelontong','🧁 Bakery & Kafe'] as $l)
        <div class="logo-pill">{{ $l }}</div>
        @endforeach
    </div>
</div>

<!-- FITUR -->
<section id="fitur" style="background:var(--white);">
    <div class="fade-in">
        <div class="section-tag">
            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3l14 9-14 9V3z"/></svg>
            Fitur
        </div>
        <h2 class="section-title">Semua yang kamu butuhkan,<br><em>dalam satu tempat</em></h2>
        <p class="section-sub">Tidak perlu banyak aplikasi. Tokaku menggabungkan kasir, stok, laporan, dan manajemen tim dalam satu platform yang mudah dipakai.</p>
    </div>
    <div class="features-grid fade-in">
        @foreach([
            ['Kasir Digital','Catat transaksi dengan cepat. Multi metode bayar — tunai, QRIS, transfer. Struk bisa dicetak atau download PDF.','M9 7H6a2 2 0 00-2 2v9a2 2 0 002 2h9a2 2 0 002-2v-3M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2M9 7h6'],
            ['Manajemen Stok','Pantau stok real-time. Notifikasi otomatis saat stok menipis. Riwayat perubahan stok tercatat lengkap.','M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172'],
            ['Laporan Penjualan','Grafik omzet harian, produk terlaris, summary per metode bayar. Export ke Excel satu klik.','M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10'],
            ['Manajemen Tim','Tambah kasir dan admin. Atur hak akses per role. Reset password kapan saja.','M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['Multi Tenant','Satu akun untuk banyak toko. Cocok untuk pemilik bisnis yang punya lebih dari satu cabang.','M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
            ['Akses Mobile','Bisa diakses dari HP, tablet, atau laptop. Tidak perlu install aplikasi — cukup buka browser.','M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'],
        ] as [$title,$desc,$icon])
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--green)" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
            </div>
            <p class="feature-title">{{ $title }}</p>
            <p class="feature-desc">{{ $desc }}</p>
        </div>
        @endforeach
    </div>
</section>

<!-- CARA KERJA -->
<section id="cara-kerja" class="how-bg">
    <div class="fade-in" style="max-width:1200px;margin:0 auto;">
        <div class="section-tag">Cara Kerja</div>
        <h2 class="section-title">Mulai dalam <em>3 langkah</em> saja</h2>
        <p class="section-sub" style="color:#5A8A7A;">Tidak perlu training panjang. Tokaku dirancang agar bisa langsung dipakai oleh siapa pun.</p>
        <div class="steps">
            @foreach([
                ['Daftar & Setup Toko','Buat akun, isi nama toko, tambah produk dan kategori. Selesai dalam 10 menit.','M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                ['Ajak Tim Kamu','Tambahkan kasir dan admin. Atur role masing-masing. Semua langsung bisa login.','M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857'],
                ['Mulai Berjualan','Buka halaman kasir, klik produk, proses transaksi. Semudah itu setiap hari.','M9 7H6a2 2 0 00-2 2v9a2 2 0 002 2h9a2 2 0 002-2v-3M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2M9 7h6'],
            ] as $i=>[$title,$desc,$icon])
            <div class="step">
                <span class="step-num">0{{ $i+1 }}</span>
                <div class="step-icon">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#5DCAA5" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                </div>
                <p class="step-title">{{ $title }}</p>
                <p class="step-desc">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- HARGA -->
<section id="harga" style="background:var(--cream);">
    <div class="fade-in" style="text-align:center;margin-bottom:48px;">
        <div class="section-tag" style="justify-content:center;">Harga</div>
        <h2 class="section-title" style="margin:0 auto 16px;text-align:center;">Harga yang <em>transparan</em></h2>
        <p class="section-sub" style="margin:0 auto;text-align:center;">Coba gratis 14 hari tanpa kartu kredit. Upgrade kapan saja.</p>
    </div>
    <div class="pricing-grid fade-in" style="margin:0 auto;">
        @foreach([
            ['Starter','Untuk toko baru yang baru mulai','Rp 0','/bulan, gratis selamanya',['1 toko','1 user (owner)','Kasir digital','Laporan dasar','Stok sederhana'],false,'outline','Mulai Gratis'],
            ['Pro','Paling populer untuk UMKM aktif','Rp XXX.000','/bulan',['1 toko','Unlimited user','Kasir + QRIS','Laporan lengkap + export','Manajemen stok penuh','Struk PDF','Prioritas support'],true,'solid','Coba 14 Hari Gratis'],
            ['Business','Untuk multi-cabang','Rp XXX.000','/bulan',['Unlimited toko','Unlimited user','Semua fitur Pro','Laporan lintas cabang','Dedicated support','Custom subdomain'],false,'outline','Hubungi Kami'],
        ] as [$name,$tagline,$price,$period,$features,$featured,$btnClass,$btnLabel])
        <div class="pricing-card {{ $featured ? 'featured' : '' }}">
            @if($featured)
            <div class="pricing-badge">⭐ Paling Populer</div>
            @endif
            <p class="pricing-name">{{ $name }}</p>
            <p style="font-size:13px;color:{{ $featured?'rgba(255,255,255,0.6)':'var(--muted)' }};margin-bottom:16px;">{{ $tagline }}</p>
            <p class="pricing-price">{{ $price }}</p>
            <p class="pricing-period">{{ $period }}</p>
            <ul class="pricing-features">
                @foreach($features as $f)
                <li>
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="{{ $featured ? 'rgba(255,255,255,0.8)' : 'var(--green)' }}" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    {{ $f }}
                </li>
                @endforeach
            </ul>
            <a href="{{ route('login') }}" class="btn-pricing {{ $btnClass }}">{{ $btnLabel }}</a>
        </div>
        @endforeach
    </div>
</section>

<!-- TESTIMONI -->
<section id="testimoni" style="background:var(--white);">
    <div class="fade-in" style="text-align:center;margin-bottom:48px;">
        <div class="section-tag" style="justify-content:center;">Testimoni</div>
        <h2 class="section-title" style="margin:0 auto;text-align:center;">Kata mereka yang sudah<br><em>pakai Tokaku</em></h2>
    </div>
    <div class="testimonials-grid fade-in">
        @foreach([
            ['Sebelum pakai Tokaku, saya hitung uang masuk pakai buku manual. Sekarang laporan langsung ada, tinggal lihat di HP.','BU','Budi Santoso','Owner Warung Budi'],
            ['Kasirnya cepet banget. Klik produk, proses, selesai. Customer tidak perlu nunggu lama. Cocok untuk warung kami yang ramai.','AN','Ani Lestari','Owner Toko Ani'],
            ['Yang saya suka itu fitur stok-nya. Langsung tau kalau barang mau habis, jadi bisa restock sebelum kehabisan.','RT','Rini Tanjung','Owner Bakery Rini'],
        ] as [$text,$init,$name,$role])
        <div class="testimonial-card">
            <div class="stars">
                @for($i=0;$i<5;$i++)<svg width="14" height="14" viewBox="0 0 24 24" fill="#F59E0B"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>@endfor
            </div>
            <p class="testimonial-text">"{{ $text }}"</p>
            <div class="testimonial-author">
                <div class="author-avatar">{{ $init }}</div>
                <div>
                    <p class="author-name">{{ $name }}</p>
                    <p class="author-role">{{ $role }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <p class="cta-tag">Mulai Sekarang</p>
    <h2 class="cta-title">Siap bawa tokomu<br>ke level berikutnya?</h2>
    <p class="cta-sub">Daftar gratis, tidak perlu kartu kredit. Trial 14 hari penuh tanpa batas fitur.</p>
    <a href="{{ route('login') }}" class="btn-cta-white">
        Mulai Gratis Sekarang
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
    </a>
</section>

<!-- FOOTER -->
<footer>
    <div class="footer-grid">
        <div class="footer-brand">
            <div class="footer-logo">
                <div class="footer-logo-mark">
                    <svg width="14" height="14" viewBox="0 0 18 18" fill="none"><path d="M3 5h12M3 9h8M3 13h5" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <span class="footer-logo-text">Tokaku</span>
            </div>
            <p class="footer-desc">Aplikasi kasir & manajemen UMKM yang simpel, cepat, dan terjangkau.</p>
            <p style="font-size:12px;color:#3A5A50;margin-top:16px;">by <a href="https://1017studios.id" style="color:#5DCAA5;text-decoration:none;">1017studios.id</a></p>
        </div>
        <div class="footer-col">
            <h4>Produk</h4>
            <a href="#fitur">Fitur</a>
            <a href="#harga">Harga</a>
            <a href="#cara-kerja">Cara Kerja</a>
        </div>
        <div class="footer-col">
            <h4>Industri</h4>
            <a href="#">F&B & Kuliner</a>
            <a href="#">Retail</a>
            <a href="#">Salon & Jasa</a>
            <a href="#">Toko Kelontong</a>
        </div>
        <div class="footer-col">
            <h4>Bantuan</h4>
            <a href="#">Panduan Penggunaan</a>
            <a href="#">Kontak Support</a>
            <a href="https://wa.me/6281234567890">WhatsApp</a>
            <a href="#">Kebijakan Privasi</a>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} Tokaku by 1017studios.id. Semua hak dilindungi.</p>
        <p>Made with ♥ in Indonesia 🇮🇩</p>
    </div>
</footer>

<script>
// Nav scroll effect
var navbar = document.getElementById('navbar');
window.addEventListener('scroll', function() {
    navbar.classList.toggle('scrolled', window.scrollY > 20);
});

// Scroll fade-in
var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

document.querySelectorAll('.fade-in').forEach(function(el) {
    observer.observe(el);
});

// Smooth scroll untuk nav links
document.querySelectorAll('a[href^="#"]').forEach(function(a) {
    a.addEventListener('click', function(e) {
        var target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>
</body>
</html>
