<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>ESDO Procurement — লগ-ইন</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, sans-serif; background: #f5f6fa; margin: 0; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .box { background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.08); width: 320px; }
        .box h1 { font-size: 1.25rem; margin: 0 0 1.25rem; }
        label { display: block; font-size: .85rem; color: #6b7280; margin-bottom: .25rem; }
        input { width: 100%; padding: .5rem .6rem; margin-bottom: 1rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: .6rem; background: #1f2937; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
        .error { color: #b91c1c; font-size: .85rem; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <form class="box" method="POST" action="<?php echo e(route('login')); ?>">
        <?php echo csrf_field(); ?>
        <h1>ESDO Procurement</h1>

        <?php if($errors->any()): ?>
            <div class="error"><?php echo e($errors->first()); ?></div>
        <?php endif; ?>

        <label for="email">ইমেইল</label>
        <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus>

        <label for="password">পাসওয়ার্ড</label>
        <input id="password" type="password" name="password" required>

        <button type="submit">লগ-ইন</button>
    </form>
</body>
</html>
<?php /**PATH D:\New Poject\New folder\procurment\resources\views/auth/login.blade.php ENDPATH**/ ?>