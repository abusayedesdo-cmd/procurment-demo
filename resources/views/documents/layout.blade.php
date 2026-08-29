<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <style>
        @page { margin: 25px 30px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 15px; text-align: center; text-decoration: underline; margin: 4px 0; }
        h2 { font-size: 13px; text-decoration: underline; margin: 14px 0 6px; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .muted { color: #555; }
        .meta p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin: 6px 0 12px; }
        table.bordered th, table.bordered td { border: 1px solid #333; padding: 4px 6px; font-size: 10.5px; vertical-align: top; }
        table.bordered th { background: #eee; }
        table.plain td { border: none; padding: 2px 0; }
        ol.terms { margin: 4px 0 0 18px; padding: 0; }
        ol.terms li { margin-bottom: 4px; }
        .sig-block { margin-top: 30px; }
        .page-break { page-break-before: always; }
        .doc-footer { text-align: center; margin-top: 20px; font-size: 9.5px; color: #444; }
        .doc-footer .disclaimer { font-style: italic; margin-top: 3px; }
    </style>
</head>
<body>
      @yield('content')
    <div class="doc-footer">
        <!-- Eso-Social Development Organization (ESDO) — Procurement Management System -->
        <div class="disclaimer">(This is a system-generated document; signature is not required. The document is ready only after verification by the concerned official.)</div>
    </div>
</body>
</html>