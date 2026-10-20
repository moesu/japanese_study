<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'N5 漢字') — N5 Kanji</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Myanmar:wght@400;600;700&family=Noto+Serif+JP:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #1f1a17;
            --muted: #6b625b;
            --paper: #f4efe6;
            --card: #fffdf8;
            --line: #e4d9cc;
            --seal: #b42318;
            --seal-soft: #f8e8e4;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, #fff 0, transparent 42%),
                var(--paper);
            font-family: "Noto Sans Myanmar", "Hiragino Sans", sans-serif;
            line-height: 1.5;
        }
        a { color: inherit; }
        header {
            position: relative;
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
            padding: 1.25rem 6vw;
            border-bottom: 1px solid var(--line);
        }
        .brand { text-decoration: none; min-width: 0; }
        .brand strong {
            font-family: "Noto Serif JP", serif;
            font-size: 1.4rem;
            letter-spacing: 0.04em;
        }
        .brand span { display: block; color: var(--muted); font-size: 0.85rem; }
        .menu-toggle {
            display: grid;
            gap: 5px;
            width: 2.75rem;
            height: 2.75rem;
            place-content: center;
            padding: 0;
            background: var(--card);
            color: var(--ink);
            border: 1px solid var(--line);
            border-radius: 12px;
        }
        .menu-toggle span { display: block; width: 1.15rem; height: 2px; background: currentColor; }
        nav {
            display: none;
            position: absolute;
            z-index: 20;
            top: calc(100% + 0.4rem);
            right: 6vw;
            min-width: 12rem;
            padding: 0.4rem;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 14px;
            box-shadow: 0 14px 32px rgba(31, 26, 23, 0.1);
        }
        nav.open { display: grid; }
        nav a {
            text-decoration: none;
            padding: 0.7rem 0.85rem;
            border-radius: 10px;
        }
        nav a.active, nav a:hover { background: var(--paper); }
        .level-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(8.5rem, 1fr)); gap: 0.8rem; margin-top: 1.25rem; }
        .level {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 6.5rem;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: var(--card);
            font-family: "Noto Serif JP", serif;
            font-size: clamp(1.8rem, 6vw, 2.4rem);
            text-decoration: none;
        }
        a.level:hover { border-color: var(--ink); }
        .level.soon { color: var(--muted); }
        main { padding: 1.5rem 6vw 3rem; max-width: 1100px; margin: 0 auto; }
        .search {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin: 1rem 0 1.25rem;
        }
        input[type="search"], select.chapter {
            flex: 1;
            border: 1px solid var(--line);
            background: var(--card);
            border-radius: 12px;
            padding: 0.8rem 1rem;
            font: inherit;
            color: inherit;
        }
        select.chapter { max-width: 16rem; }
        .multi { position: relative; flex: 1; max-width: 28rem; }
        .multi-toggle {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            text-align: left;
            background: var(--card);
            color: var(--ink);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 0.8rem 1rem;
            min-height: 3.1rem;
        }
        .multi-toggle[aria-expanded="true"] { border-color: var(--ink); }
        .multi-caret { color: var(--muted); font-size: 0.8rem; }
        .multi-panel {
            position: absolute;
            z-index: 5;
            top: calc(100% + 0.35rem);
            left: 0;
            width: min(22rem, 80vw);
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 0.7rem;
            box-shadow: 0 14px 32px rgba(31, 26, 23, 0.1);
        }
        .multi-actions { display: flex; gap: 0.4rem; margin-bottom: 0.55rem; }
        .multi-actions button {
            background: transparent;
            color: var(--ink);
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 0.2rem 0.7rem;
            font-size: 0.85rem;
        }
        .multi-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 0.35rem; }
        .chip { position: relative; }
        .chip input { position: absolute; opacity: 0; }
        .chip span {
            display: block;
            text-align: center;
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 0.28rem 0.15rem;
            font-size: 0.82rem;
            cursor: pointer;
        }
        .chip input:checked + span { background: var(--ink); color: #fff; border-color: var(--ink); }
        .chip input:focus-visible + span { outline: 2px solid var(--ink); outline-offset: 2px; }
        button, .button {
            border: 0;
            background: var(--ink);
            color: #fff;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font: inherit;
            cursor: pointer;
            text-decoration: none;
        }
        button.ghost, .button.ghost { background: transparent; color: var(--ink); border: 1px solid var(--line); }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 0.8rem;
        }
        .card {
            display: block;
            text-decoration: none;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 1rem;
            min-height: 168px;
        }
        .card:hover { border-color: #c9b8a6; }
        .glyph {
            font-family: "Noto Serif JP", serif;
            font-size: 3rem;
            line-height: 1;
        }
        .my { font-weight: 700; margin: 0.35rem 0 0; }
        .en, .meta { color: var(--muted); font-size: 0.9rem; }
        .hero-kanji {
            font-family: "Noto Serif JP", serif;
            font-size: clamp(5rem, 16vw, 8rem);
            line-height: 1;
            margin: 0;
        }
        .panel {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 1.25rem;
        }
        .choices { display: grid; gap: 0.6rem; margin-top: 1rem; }
        .choices button { text-align: left; background: var(--card); color: var(--ink); border: 1px solid var(--line); }
        .choices button:hover { border-color: var(--ink); }
        .ok { color: #0f7a3a; }
        .no { color: var(--seal); }
        .seal {
            display: inline-block;
            background: var(--seal-soft);
            color: var(--seal);
            border-radius: 999px;
            padding: 0.15rem 0.55rem;
            font-size: 0.8rem;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 0.55rem 0; border-bottom: 1px solid var(--line); vertical-align: top; }
        .row { display: flex; justify-content: space-between; gap: 1rem; align-items: center; flex-wrap: wrap; }
        h3 { margin: 1.25rem 0 0.25rem; font-size: 1.05rem; }
        .examples { display: grid; gap: 0.8rem; }
        .example { border-top: 1px solid var(--line); padding-top: 0.8rem; }
        .breakdown { display: flex; flex-wrap: wrap; gap: 0.35rem 0.55rem; align-items: flex-start; }
        .unit { display: flex; flex-direction: column; align-items: center; max-width: 7.5rem; text-align: center; }
        .unit .glyph { font-size: 2rem; }
        .unit .my { font-size: 0.92rem; line-height: 1.35; }
        .unit .en { font-size: 0.75rem; }
        .unit.kana .glyph { color: var(--muted); font-size: 1.35rem; padding-top: 0.35rem; }
        .back-link { margin: 0 0 0.75rem; }
        .back-link a { text-decoration: none; color: var(--muted); font-size: 0.95rem; }
        .kanji-detail { display: grid; gap: 1rem; }
        .kanji-hero {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 1.1rem 1rem 1.25rem;
            text-align: center;
        }
        .kanji-hero .hero-kanji { margin: 0.35rem 0 0.15rem; }
        .kanji-my { font-weight: 700; font-size: clamp(1.35rem, 5vw, 1.8rem); margin: 0.2rem 0 0; }
        .kanji-en { color: var(--muted); font-size: 1.05rem; margin: 0.1rem 0 0.85rem; }
        .reading-block { display: grid; gap: 0.55rem; text-align: left; }
        .reading-row {
            display: grid;
            grid-template-columns: 2.6rem 1fr;
            gap: 0.55rem;
            align-items: start;
        }
        .reading-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 1.85rem;
            border-radius: 999px;
            background: var(--paper);
            border: 1px solid var(--line);
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--muted);
        }
        .reading-chips { display: flex; flex-wrap: wrap; gap: 0.35rem; }
        .chip {
            display: inline-block;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 0.28rem 0.7rem;
            font-family: "Noto Serif JP", serif;
            font-size: 0.98rem;
            line-height: 1.3;
        }
        .chip.muted { color: var(--muted); }
        .sense-list {
            list-style: none;
            margin: 0.9rem 0 0;
            padding: 0.7rem 0 0;
            border-top: 1px solid var(--line);
            display: grid;
            gap: 0.35rem;
            text-align: left;
        }
        .sense-list li { display: flex; flex-wrap: wrap; gap: 0.35rem 0.65rem; align-items: baseline; }
        .vocab-section {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 1rem 0.85rem 1.1rem;
        }
        .vocab-section h2 { margin: 0 0 0.2rem; font-size: 1.15rem; }
        .vocab-section h3 { margin: 1rem 0 0.45rem; font-size: 1rem; }
        .vocab-section > .meta { margin: 0 0 0.75rem; }
        .vocab-list { display: grid; gap: 0.65rem; }
        .vocab-item {
            background: linear-gradient(180deg, #fffefb 0%, #faf6f0 100%);
            border: 1px solid var(--line);
            border-left: 4px solid var(--seal);
            border-radius: 14px;
            padding: 0.75rem 0.8rem;
        }
        .vocab-item .reading {
            margin: 0.45rem 0 0.15rem;
            font-family: "Noto Serif JP", serif;
            font-size: 1.05rem;
            color: var(--muted);
        }
        .vocab-item .gloss { margin: 0; display: grid; gap: 0.1rem; }
        .vocab-item .gloss .my { margin: 0; font-size: 1.05rem; }
        .vocab-item .gloss .en { font-size: 0.88rem; }
        .unit.focus {
            background: var(--seal-soft);
            border: 1px solid #efc4bd;
            border-radius: 12px;
            padding: 0.25rem 0.35rem 0.4rem;
        }
        .unit.focus .glyph { color: var(--seal); }
        .unit.focus .my { color: var(--seal); }
        .kanji-pager {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            margin-top: 1rem;
            position: sticky;
            bottom: calc(0.75rem + env(safe-area-inset-bottom));
            z-index: 5;
        }
        .pager-btn {
            flex: 1 1 auto;
            max-width: 48%;
            text-align: center;
            min-height: 2.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--card);
            box-shadow: 0 8px 20px rgba(31, 26, 23, 0.08);
        }
        @media (min-width: 720px) {
            .vocab-list { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .kanji-hero { padding: 1.35rem 1.4rem 1.5rem; }
            .vocab-section { padding: 1.15rem 1.15rem 1.3rem; }
        }
        .filters { display: flex; flex-wrap: wrap; gap: 0.45rem; margin: 0 0 1rem; }
        .filters a {
            text-decoration: none;
            border: 1px solid var(--line);
            background: var(--card);
            border-radius: 999px;
            padding: 0.35rem 0.75rem;
            font-size: 0.92rem;
        }
        .filters a.active { background: var(--ink); color: #fff; border-color: var(--ink); }
        .filters a span { opacity: 0.7; margin-left: 0.25rem; }
        .goi-tools, .filter-sheet { display: contents; }
        body.filter-lock { overflow: hidden; }
        .filter-open, .filter-close { display: none; }
        .search .type-drop { margin: 0; flex: 0 1 16rem; align-self: stretch; }
        .search .type-drop summary {
            width: 100%;
            height: 100%;
            justify-content: space-between;
            background: var(--card);
            color: var(--ink);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 0.8rem 1rem;
            min-height: 3.1rem;
        }
        .type-drop { position: relative; margin: 0 0 1rem; }
        .type-drop summary {
            display: inline-flex;
            align-items: center;
            list-style: none;
            cursor: pointer;
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            background: var(--ink);
            color: #fff;
            border: 1px solid var(--ink);
            font-size: 0.92rem;
        }
        .type-drop summary::-webkit-details-marker { display: none; }
        .type-drop summary span { opacity: 0.7; margin-left: 0.35rem; }
        .type-drop .filters {
            position: absolute;
            z-index: 6;
            top: calc(100% + 0.35rem);
            left: 0;
            width: max-content;
            max-width: min(32rem, 86vw);
            margin: 0;
            padding: 0.55rem;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 14px;
            box-shadow: 0 14px 32px rgba(31, 26, 23, 0.1);
        }
        .vocab-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.65rem; }
        .vocab-card { position: relative; background: var(--card); border: 1px solid var(--line); border-radius: 12px; padding: 0.7rem 0.8rem; }
        .vocab-card .mark {
            position: absolute;
            top: 0.4rem;
            right: 0.4rem;
            width: 1.35rem;
            height: 1.35rem;
            padding: 0;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: transparent;
            color: transparent;
            font-size: 0.8rem;
            line-height: 1.2rem;
        }
        .vocab-card .mark.on { background: #e7f6ec; color: #0f7a3a; border-color: #b7e0c4; }
        .vocab-card:has(.mark) { padding-right: 2rem; }
        .vocab-card .word { font-family: "Noto Serif JP", serif; font-size: 1.35rem; line-height: 1.2; }
        .vocab-card .my { margin-top: 0.15rem; }
        .vocab-card button.learn {
            margin-top: 0.45rem;
            background: transparent;
            color: var(--ink);
            border: 1px solid var(--line);
            padding: 0.2rem 0.6rem;
            font-size: 0.78rem;
        }
        .vocab-card button.learn.on { background: #e7f6ec; color: #0f7a3a; border-color: #b7e0c4; }
        @media (max-width: 800px) {
            .vocab-grid { grid-template-columns: 1fr; }
        }
        .to-top {
            position: fixed;
            right: 1rem;
            bottom: calc(1rem + env(safe-area-inset-bottom));
            z-index: 22;
            width: 2.75rem;
            height: 2.75rem;
            padding: 0;
            border-radius: 999px;
            box-shadow: 0 10px 24px rgba(31, 26, 23, 0.16);
        }
        @media (max-width: 640px) {
            header, main { padding-left: 1rem; padding-right: 1rem; }
            .goi-intro { display: none; }
            nav { right: 1rem; left: 1rem; min-width: 0; }
            .search input[type="search"], .search select.chapter, .search button { width: 100%; }
            table { display: block; overflow-x: auto; }
            .filter-open {
                display: flex;
                position: sticky;
                top: 0;
                z-index: 23;
                width: 100%;
                justify-content: space-between;
                align-items: center;
                gap: 0.75rem;
                margin: 0 0 0.8rem;
                background: var(--card);
                color: var(--ink);
                border: 1px solid var(--line);
                box-shadow: 0 8px 16px rgba(31, 26, 23, 0.06);
            }
            .filter-open .meta { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .goi-tools { display: none; }
            .goi-tools.open {
                display: flex;
                align-items: flex-end;
                justify-content: center;
                position: fixed;
                inset: 0;
                z-index: 30;
                height: 100dvh;
                background: rgba(31, 26, 23, 0.45);
                padding: 0;
                overflow: hidden;
                touch-action: none;
            }
            .goi-tools.open .filter-sheet {
                display: block;
                width: min(100%, 32rem);
                max-height: 88dvh;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                overscroll-behavior: contain;
                touch-action: pan-y;
                background: var(--card);
                border-radius: 18px 18px 0 0;
                padding: 0.9rem 0.9rem calc(0.9rem + env(safe-area-inset-bottom));
            }
            .goi-tools.open .goi-search,
            .goi-tools.open .goi-filters {
                display: block;
                width: 100%;
                max-width: none;
                margin: 0;
                background: transparent;
            }
            .goi-tools.open .goi-search {
                flex-direction: column;
                align-items: stretch;
                border-radius: 0;
                padding: 0;
            }
            .goi-tools.open .filter-close {
                display: inline-flex;
                margin-bottom: 0.7rem;
                background: transparent;
                color: var(--ink);
                border: 1px solid var(--line);
            }
            .goi-tools.open .multi-toggle,
            .goi-tools.open .type-drop summary { display: none; }
            .goi-tools.open .multi,
            .goi-tools.open .type-drop { position: static; max-width: none; width: auto; }
            .goi-tools.open .multi-panel,
            .goi-tools.open .type-drop .filters {
                position: static !important;
                display: flex !important;
                width: auto;
                max-width: none;
                max-height: none;
                top: auto;
                margin: 0 0 0.8rem;
                padding: 0;
                border: 0;
                box-shadow: none;
                background: transparent;
            }
            .goi-tools.open .multi-panel { display: block !important; }
            .goi-tools.open .multi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .goi-tools.open .chip span { padding: 0.5rem 0.15rem; font-size: 0.92rem; }
            .goi-tools.open .goi-search button[type="submit"] { width: 100%; margin-top: 0.2rem; }
            .goi-tools.open .goi-filters {
                display: flex;
                border-radius: 0;
                padding: 0.2rem 0 0;
                flex-wrap: wrap;
                overflow: visible;
            }
        }
    </style>
</head>
<body>
    @unless (request()->routeIs('home'))
        <header>
            <a class="brand" href="{{ route('home') }}">
                <strong>JLPT</strong>
                <span>အဓိပ္ပာယ်နှင့် မြန်မာပြန်</span>
            </a>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="main-menu" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <nav id="main-menu">
                @if (request()->routeIs('n4.kotoba'))
                    <a href="{{ route('n4.kotoba') }}" class="active">Kotoba</a>
                @else
                    <a href="{{ route('kanji.index', [], false) }}" @class(['active' => request()->routeIs('kanji.index', 'kanji.show')])>N5 Kanji</a>
                    <a href="{{ route('goi.show') }}" @class(['active' => request()->routeIs('goi.show')])>Kotoba</a>
                @endif
            </nav>
        </header>
    @endunless
    <main>
        @yield('content')
    </main>
    <button type="button" class="to-top" aria-label="အပေါ်သို့" hidden>↑</button>
    <script>
        const toTop = document.querySelector('.to-top');
        const toggleToTop = () => {
            toTop.hidden = window.scrollY < 480;
        };
        window.addEventListener('scroll', toggleToTop, { passive: true });
        toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        const menuButton = document.querySelector('.menu-toggle');
        const mainMenu = document.querySelector('#main-menu');
        menuButton?.addEventListener('click', () => {
            const open = mainMenu.classList.toggle('open');
            menuButton.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.addEventListener('click', (event) => {
            if (mainMenu && menuButton && !mainMenu.contains(event.target) && !menuButton.contains(event.target)) {
                mainMenu.classList.remove('open');
                menuButton.setAttribute('aria-expanded', 'false');
            }
            if (document.querySelector('.goi-tools.open')) {
                return;
            }
            document.querySelectorAll('.type-drop[open]').forEach((menu) => {
                if (!menu.contains(event.target)) {
                    menu.removeAttribute('open');
                }
            });
        });
    </script>
</body>
</html>
