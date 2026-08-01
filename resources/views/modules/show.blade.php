@extends('layouts.app')

@section('title', $title ?? 'Module')

@section('content')
    <div id="resourceRoot">
        <p class="muted">লোড হচ্ছে...</p>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('js/module-configs.js') }}"></script>
<script src="{{ asset('js/resource-ui.js') }}"></script>
<script>
    window.currentUserId = {{ auth()->id() }};

    const slug = @json($slug);
    const config = MODULE_CONFIGS[slug];

    if (!config) {
        document.getElementById('resourceRoot').innerHTML =
            '<div class="error-box">অজানা module: ' + slug + '</div>';
    } else {
        initResourcePage(config);
    }
</script>
@endsection
