{{-- Letterhead pad image (public/img/esdo-pad-bg.png). Replace that PNG to change the pad. --}}
@if (file_exists(public_path('img/esdo-pad-bg.png')))
    <img class="esdo-pad-bg" src="{{ public_path('img/esdo-pad-bg.png') }}">
@endif
