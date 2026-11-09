<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Financeiro</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --brand: #0f9f6e;
            --brand-dark: #0b8058;
            --brand-soft: #e9f8f2;
            --ink: #101828;
            --muted: #667085;
            --line: #e6eaf2;
            --surface: #ffffff;
            --bg: #f4f6fb;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(180deg, #f8fafc 0%, var(--bg) 100%);
            color: var(--ink);
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            font-size: .94rem;
        }

        a {
            text-decoration: none;
        }

        .app-header {
            position: sticky;
            top: 0;
            z-index: 1030;
            background: rgba(255, 255, 255, .9);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--line);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: .7rem;
            color: var(--ink);
            flex-shrink: 0;
        }

        .brand:hover {
            color: var(--ink);
        }

        .brand-mark {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            border-radius: 13px;
            background: linear-gradient(135deg, #12b981, #0a7f59);
            color: #fff;
            font-size: 1.15rem;
            box-shadow: 0 8px 18px rgba(15, 159, 110, .26);
        }

        .brand-text strong {
            display: block;
            font-size: 1.05rem;
            letter-spacing: -.02em;
        }

        .brand-text small {
            display: block;
            color: var(--muted);
            font-size: .72rem;
            margin-top: -2px;
        }

        .app-tabs {
            display: flex;
            gap: 4px;
            padding: 4px;
            border-radius: 12px;
            background: #f1f4f9;
        }

        .app-tab {
            display: flex;
            align-items: center;
            gap: .45rem;
            padding: .5rem .9rem;
            border-radius: 9px;
            color: var(--muted);
            font-size: .88rem;
            font-weight: 600;
            white-space: nowrap;
            transition: all .15s ease;
        }

        .app-tab:hover {
            color: var(--ink);
            background: rgba(255, 255, 255, .7);
        }

        .app-tab.active {
            color: var(--brand-dark);
            background: #fff;
            box-shadow: 0 2px 8px rgba(16, 24, 40, .08);
        }

        .avatar {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--ink);
            color: #fff;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .02em;
        }

        .page-eyebrow {
            color: var(--brand-dark);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .page-title {
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: -.035em;
        }

        .card-surface {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 16px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .03);
        }

        .stat-card {
            border-radius: 16px;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(16, 24, 40, .07);
        }

        .stat-label {
            color: var(--muted);
            font-size: .8rem;
            font-weight: 600;
            margin-bottom: .45rem;
        }

        .stat-value {
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -.035em;
            margin-bottom: .55rem;
        }

        .stat-icon {
            display: grid;
            place-items: center;
            width: 46px;
            height: 46px;
            border-radius: 13px;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .stat-meta {
            color: var(--muted);
            font-size: .78rem;
            margin: 0;
        }

        .stat-meta strong {
            color: var(--ink);
            font-weight: 600;
        }

        .section-title {
            font-size: 1.02rem;
            font-weight: 700;
            letter-spacing: -.015em;
            margin-bottom: .15rem;
        }

        .section-subtitle {
            color: var(--muted);
            font-size: .78rem;
            margin: 0;
        }

        .chart {
            display: flex;
            align-items: stretch;
            gap: .8rem;
            height: 238px;
        }

        .chart-compact {
            height: 100%;
            min-height: 205px;
        }

        .chart-col {
            display: flex;
            flex-direction: column;
            flex: 1;
            gap: .45rem;
            min-width: 0;
        }

        .chart-value {
            color: var(--muted);
            font-size: .7rem;
            font-weight: 600;
            text-align: center;
            white-space: nowrap;
        }

        .chart-track {
            position: relative;
            flex: 1;
            border-radius: 10px;
            background: linear-gradient(180deg, #f8fafc, #fff);
            border: 1px dashed #eef1f6;
        }

        .chart-bar {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: min(44px, 70%);
            min-height: 5px;
            border-radius: 8px 8px 4px 4px;
            background: linear-gradient(180deg, #34d399, #0f9f6e);
            transition: filter .15s ease;
        }

        .chart-bar:hover {
            filter: brightness(1.08);
        }

        .chart-label {
            color: var(--muted);
            font-size: .76rem;
            font-weight: 600;
            text-align: center;
        }

        .donut {
            position: relative;
            display: grid;
            place-items: center;
            width: 182px;
            height: 182px;
            margin: 0 auto;
            border-radius: 50%;
        }

        .donut-hole {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 116px;
            height: 116px;
            border-radius: 50%;
            background: #fff;
            box-shadow: inset 0 0 0 1px #f0f3f8;
            text-align: center;
        }

        .donut-hole span {
            color: var(--muted);
            font-size: .68rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .07em;
        }

        .donut-hole strong {
            font-size: .96rem;
            font-weight: 800;
            letter-spacing: -.03em;
        }

        .donut-sm {
            width: 158px;
            height: 158px;
        }

        .donut-sm .donut-hole {
            width: 98px;
            height: 98px;
        }

        .donut-sm .donut-hole strong {
            font-size: .9rem;
        }

        .donut-sm .donut-hole span {
            font-size: .62rem;
        }

        .legend-dot {
            display: inline-block;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            margin-right: .45rem;
            vertical-align: 1px;
        }

        .legend-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .5rem 0;
            border-bottom: 1px solid #f2f4f8;
            font-size: .82rem;
        }

        .legend-row:last-child {
            border-bottom: 0;
        }

        .list-row {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .65rem 0;
            border-bottom: 1px solid #f2f4f8;
        }

        .list-row:last-child {
            border-bottom: 0;
        }

        .list-icon {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            flex-shrink: 0;
            font-size: .9rem;
        }

        .rail-scroll {
            max-height: 176px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #d3dae7 transparent;
            overscroll-behavior: contain;
        }

        .rail-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .rail-scroll::-webkit-scrollbar-thumb {
            background: #d3dae7;
            border-radius: 999px;
        }

        .rail-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .rail-scroll:focus-visible {
            outline: 2px solid rgba(15, 159, 110, .35);
            outline-offset: 2px;
            border-radius: 8px;
        }

        .table-app thead th {
            padding: .8rem 1rem;
            background: #f8fafc;
            border-bottom: 1px solid var(--line);
            color: var(--muted);
            font-size: .69rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .table-app tbody td {
            padding: .9rem 1rem;
            border-color: #f1f3f8;
            vertical-align: middle;
        }

        .table-app tbody tr:last-child td {
            border-bottom: 0;
        }

        .table-app tbody tr:hover {
            background: #fbfcfe;
        }

        .client-avatar {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: var(--brand-soft);
            color: var(--brand-dark);
            font-size: .72rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .badge {
            padding: .4em .7em;
            font-size: .7rem;
            font-weight: 600;
        }

        .btn-brand {
            padding: .58rem 1rem;
            border: 1px solid var(--brand);
            border-radius: 10px;
            background: var(--brand);
            color: #fff;
            font-size: .88rem;
            font-weight: 600;
            box-shadow: 0 6px 14px rgba(15, 159, 110, .18);
        }

        .btn-brand:hover,
        .btn-brand:focus {
            border-color: var(--brand-dark);
            background: var(--brand-dark);
            color: #fff;
        }

        .btn-light-soft {
            padding: .58rem 1rem;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            color: #475467;
            font-size: .88rem;
            font-weight: 600;
        }

        .btn-light-soft:hover,
        .btn-light-soft:focus {
            border-color: #d3dae7;
            background: #f8fafc;
            color: var(--ink);
        }

        .btn-sm {
            padding: .35rem .55rem;
            border-radius: 8px;
            font-size: .8rem;
        }

        .form-label {
            margin-bottom: .35rem;
            color: #344054;
            font-size: .8rem;
            font-weight: 600;
        }

        .form-control,
        .form-select {
            min-height: 42px;
            border-color: #dfe4ee;
            border-radius: 10px;
            font-size: .88rem;
            color: var(--ink);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #8fd9c2;
            box-shadow: 0 0 0 .2rem rgba(15, 159, 110, .12);
        }

        .input-group-text {
            border-color: #dfe4ee;
            border-radius: 10px;
            background: #fff;
            color: var(--muted);
        }

        .input-group > .input-group-text {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .input-group > .form-control {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .empty-state {
            padding: 3.25rem 1rem;
            text-align: center;
        }

        .empty-state i {
            display: block;
            margin-bottom: .75rem;
            color: #c0c8d6;
            font-size: 2.25rem;
        }

        .empty-state h6 {
            font-weight: 700;
        }

        .empty-state p {
            color: var(--muted);
            font-size: .84rem;
        }

        .min-w-0 {
            min-width: 0;
        }

        .form-text {
            color: var(--muted);
            font-size: .74rem;
        }

        .pagination {
            margin-bottom: 0;
            gap: 3px;
        }

        .pagination .page-link {
            min-width: 33px;
            border: 1px solid #e7ebf3;
            border-radius: 8px;
            color: #475467;
            font-size: .8rem;
            text-align: center;
        }

        .pagination .page-item.active .page-link {
            border-color: var(--brand);
            background: var(--brand);
            color: #fff;
        }

        .pagination .page-link:hover {
            color: var(--brand-dark);
            background: var(--brand-soft);
        }

        .filter-total {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .48rem .8rem;
            border: 1px solid var(--line);
            border-radius: 9px;
            background: #fff;
            font-size: .8rem;
        }

        .modal-content {
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 26px 60px rgba(16, 24, 40, .18);
        }

        .modal-header {
            align-items: flex-start;
            padding: 1.1rem 1.4rem;
            border-bottom: 1px solid var(--line);
        }

        .modal-title {
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: -.015em;
        }

        .modal-body {
            padding: 1.4rem;
        }

        .modal-backdrop.show {
            opacity: .4;
        }

        footer {
            color: #98a2b3;
            font-size: .76rem;
        }

        @media (max-width: 1199.98px) {
            .rail-scroll {
                max-height: 232px;
            }
        }

        @media (max-width: 767.98px) {
            .header-inner {
                flex-wrap: wrap;
            }

            .app-tabs {
                order: 3;
                width: 100%;
                margin-left: 0 !important;
                margin-top: .35rem;
            }

            .app-tab {
                flex: 1;
                justify-content: center;
            }

            .page-title {
                font-size: 1.55rem;
            }

            .stat-value {
                font-size: 1.35rem;
            }

            .chart {
                gap: .35rem;
                height: 210px;
            }

            .chart-value {
                display: none;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="d-flex flex-column">

    <header class="app-header">
        <div class="container-xxl">
            <div class="header-inner d-flex align-items-center gap-3 py-2">
                <a href="{{ route('dashboard') }}" class="brand">
                    <span class="brand-mark"><i class="bi bi-graph-up-arrow"></i></span>
                    <span class="brand-text">
                        <strong>Financeiro</strong>
                        <small>Gestão de recebíveis</small>
                    </span>
                </a>

                <nav class="app-tabs ms-auto" aria-label="Navegação principal">
                    <a href="{{ route('dashboard') }}"
                       class="app-tab {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid-1x2"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('contas-receber.index') }}"
                       class="app-tab {{ request()->routeIs('contas-receber.*') ? 'active' : '' }}">
                        <i class="bi bi-receipt"></i>
                        <span>Contas a Receber</span>
                    </a>
                </nav>

                <div class="d-none d-md-flex align-items-center gap-2 ms-1">
                    <span class="avatar">GA</span>
                    <span class="d-none d-lg-block lh-sm">
                        <span class="d-block fw-semibold" style="font-size: .82rem;">Gabriel</span>
                        <span class="d-block text-muted" style="font-size: .72rem;">Administrador</span>
                    </span>
                </div>
            </div>
        </div>
    </header>

    <main class="container-xxl flex-grow-1 py-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 border-0 rounded-3 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show d-flex align-items-start gap-2 border-0 rounded-3 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                <div>{{ session('warning') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="container-xxl pb-4 text-center">
        Financeiro · {{ now()->format('Y') }} — Controle de contas a receber
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
