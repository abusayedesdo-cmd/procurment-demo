@extends('layouts.app')

@section('title', $title ?? 'Module')

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap');

    :root {
        --ink: #0F172A;
        --paper: #FFFFFF;
        --surface: #F8FAFC;
        --line: #E2E8F0;
        --muted: #64748B;
        --accent: #0D9488;
        --accent-dark: #0F766E;
        --red: #991B1B;
        --red-bg: #FEF2F2;
        --red-line: #FECACA;
    }

    body { font-family: 'Inter', system-ui, sans-serif; }

    .shell {
        max-width: 1080px;
        margin: 0 auto;
        padding: 2.5rem 2rem 4rem;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 1.5rem;
        padding-bottom: 1.75rem;
        margin-bottom: 2rem;
        border-bottom: 1px solid var(--line);
        flex-wrap: wrap;
    }

    .eyebrow {
        font-family: 'JetBrains Mono', monospace;
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--muted);
        margin: 0 0 .5rem;
    }

    .page-header h1 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: var(--ink);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: transparent;
        color: var(--ink);
        border: 1px solid var(--line);
        border-radius: 7px;
        padding: .6rem 1.1rem;
        cursor: pointer;
        text-decoration: none;
        font-size: .85rem;
        font-weight: 600;
        font-family: inherit;
        transition: background .15s ease, border-color .15s ease;
    }
    .btn:hover { background: var(--surface); border-color: #CBD5E1; }

    .state-panel {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 2rem 1.5rem;
        text-align: center;
        color: var(--muted);
        font-size: .9rem;
    }

    .error-box {
        background: var(--red-bg);
        color: var(--red);
        border: 1px solid var(--red-line);
        padding: .75rem 1rem;
        border-radius: 8px;
        font-size: .88rem;
    }

    @media (max-width: 560px) {
        .shell { padding: 1.5rem 1.1rem 3rem; }
        .page-header { flex-direction: column; align-items: flex-start; }
    }
</style>
@endsection

@section('content')
    <div class="shell">
        <div class="page-header">
            <div>
                <p class="eyebrow">Module</p>
                <h1 id="moduleTitle">Loading…</h1>
            </div>
            <a href="{{ route('modules.index') }}" class="btn">← All Modules</a>
        </div>

        <div id="resourceRoot">
            <div class="state-panel">Loading…</div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('js/module-configs.js') }}"></script>
<script src="{{ asset('js/resource-ui.js') }}"></script>
<script>
    window.currentUserId = {{ auth()->id() }};

    const slug = @json($slug);
    const config = MODULE_CONFIGS[slug];
    const titleEl = document.getElementById('moduleTitle');

    if (!config) {
        titleEl.textContent = 'Unknown module';
        document.getElementById('resourceRoot').innerHTML =
            `<div class="error-box">Unknown module: ${slug}</div>`;
    } else {
        titleEl.textContent = config.title;
        initResourcePage(config);
    }
</script>
@endsection