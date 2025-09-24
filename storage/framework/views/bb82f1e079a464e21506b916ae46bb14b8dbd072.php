<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SkillsXchange</title>
  <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
  
  
  <?php if(app()->environment('production')): ?>
    <?php
      $manifestPath = public_path('build/manifest.json');
      if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
        $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
      }
    ?>
    <?php if(isset($cssFile)): ?>
      <link rel="stylesheet" href="<?php echo e(asset('build/' . $cssFile)); ?>">
    <?php endif; ?>
    <?php if(isset($jsFile)): ?>
      <script src="<?php echo e(asset('build/' . $jsFile)); ?>"></script>
    <?php endif; ?>
  <?php endif; ?>
</head>
<body>
  <header>
    <h3>SkillsXchange</h3>
    <nav>
      <a href="<?php echo e(route('login')); ?>">Login</a>
      <a href="<?php echo e(route('register')); ?>" class="signup">Sign Up</a>
    </nav>
  </header>

  <section class="hero">
    <h1>Trade Your Skills. Learn from Others.</h1>
    <p>A student-to-student platform to share and grow your skills together.</p>
    <button onclick="window.location.href='<?php echo e(route('register')); ?>'">Get Started</button>
  </section>

  <section class="how-it-works">
    <h2>How It Works</h2>
    <div class="features">
      <div class="feature">
        <img src="<?php echo e(asset('images/lens.png')); ?>" alt="Find a Skill">
        <h3>Find a Skill</h3>
        <p>Browse through the list of skills offered by other students.</p>
      </div>
      <div class="feature">
        <img src="<?php echo e(asset('images/handshake.png')); ?>" alt="Match and Trade">
        <h3>Match and Trade</h3>
        <p>Send a trade request and match based on availability and interest.</p>
      </div>
      <div class="feature">
        <img src="<?php echo e(asset('images/star.png')); ?>" alt="Rate and Review">
        <h3>Rate and Review</h3>
        <p>Leave feedback and build a trustworthy skill-sharing community.</p>
      </div>
    </div>
  </section>
</body>
</html><?php /**PATH C:\xampp\htdocs\SkillsXchangee\resources\views/welcome.blade.php ENDPATH**/ ?>