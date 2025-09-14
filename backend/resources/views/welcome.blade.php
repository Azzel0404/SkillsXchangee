<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SkillsXchange - Trade Your Skills</title>
  
  {{-- Try Vite first, fallback to built assets --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  
  {{-- Fallback for production if Vite fails --}}
  @if(app()->environment('production'))
    @php
      $manifestPath = public_path('build/manifest.json');
      if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
        $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
      }
    @endphp
    @if(isset($cssFile))
      <link rel="stylesheet" href="{{ asset('build/' . $cssFile) }}">
    @else
      {{-- Ultimate fallback - inline critical CSS --}}
      <style>
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        header { display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        header h3 { color: #007bff; margin: 0; font-size: 1.5rem; font-weight: 700; }
        header nav { display: flex; gap: 1rem; }
        header nav a { text-decoration: none; color: #333; font-weight: 500; padding: 0.5rem 1rem; border-radius: 0.375rem; }
        header nav a.signup { background: #007bff; color: #fff; }
        .hero { text-align: center; background: linear-gradient(135deg, #dbeafe 0%, #ede9fe 100%); padding: 4rem 1rem; min-height: 60vh; display: flex; flex-direction: column; justify-content: center; }
        .hero h1 { font-size: clamp(1.75rem, 4vw, 3rem); font-weight: 700; margin: 0 0 1rem 0; color: #1a202c; }
        .hero p { margin: 0 0 2rem 0; font-size: clamp(1rem, 2.5vw, 1.25rem); color: #4a5568; max-width: 600px; }
        .hero button { background: #007bff; color: #fff; border: none; padding: 0.75rem 2rem; border-radius: 0.5rem; font-size: 1rem; font-weight: 600; cursor: pointer; }
        .how-it-works { text-align: center; padding: 4rem 1rem; background: #fff; }
        .how-it-works h2 { margin-bottom: 3rem; font-size: clamp(1.5rem, 3vw, 2.5rem); font-weight: 700; color: #1a202c; }
        .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; max-width: 1200px; margin: 0 auto; }
        .feature { padding: 2rem 1rem; border-radius: 0.75rem; background: #f8f9fa; }
        .feature img { width: 60px; height: 60px; margin: 0 auto 1rem; display: block; }
        .feature h3 { margin: 0 0 1rem 0; font-size: 1.25rem; font-weight: 600; color: #1a202c; }
        .feature p { font-size: 1rem; color: #4a5568; line-height: 1.6; margin: 0; }
        @media (max-width: 640px) { .features { grid-template-columns: 1fr; } }
      </style>
    @endif
    @if(isset($jsFile))
      <script src="{{ asset('build/' . $jsFile) }}"></script>
    @endif
  @endif
</head>
<body>
  <header>
    <h3>SkillsXchange</h3>
    <nav>
      <a href="{{ route('login') }}">Login</a>
      <a href="{{ route('register') }}" class="signup">Sign Up</a>
    </nav>
  </header>

  <section class="hero">
    <h1>Trade Your Skills. Learn from Others.</h1>
    <p>A student-to-student platform to share and grow your skills together.</p>
    <button onclick="window.location.href='{{ route('register') }}'">Get Started</button>
  </section>

  <section class="how-it-works">
    <h2>How It Works</h2>
    <div class="features">
      <div class="feature">
        <img src="{{ asset('images/lens.png') }}" alt="Find a Skill">
        <h3>Find a Skill</h3>
        <p>Browse through the list of skills offered by other students.</p>
      </div>
      <div class="feature">
        <img src="{{ asset('images/handshake.png') }}" alt="Match and Trade">
        <h3>Match and Trade</h3>
        <p>Send a trade request and match based on availability and interest.</p>
      </div>
      <div class="feature">
        <img src="{{ asset('images/star.png') }}" alt="Rate and Review">
        <h3>Rate and Review</h3>
        <p>Leave feedback and build a trustworthy skill-sharing community.</p>
      </div>
    </div>
  </section>

  <footer>
    <p>&copy; {{ date('Y') }} SkillsXchange. All rights reserved.</p>
  </footer>
</body>
</html>