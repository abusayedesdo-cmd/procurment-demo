<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo $__env->yieldContent('title'); ?></title>
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
    </style>
</head>
<body>
    <?php echo $__env->yieldContent('content'); ?>
</body>
</html>
<?php /**PATH D:\New Poject\New folder\procurment\resources\views/documents/layout.blade.php ENDPATH**/ ?>