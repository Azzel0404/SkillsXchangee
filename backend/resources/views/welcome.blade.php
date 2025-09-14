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
        .hero { text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 6rem 1rem; min-height: 70vh; display: flex; flex-direction: column; justify-content: center; position: relative; }
        .hero-content { position: relative; z-index: 2; max-width: 800px; }
        .hero h1 { font-size: clamp(2.5rem, 5vw, 4rem); font-weight: 800; margin: 0 0 1.5rem 0; color: #ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
        .hero p { margin: 0 0 3rem 0; font-size: clamp(1.125rem, 2.5vw, 1.5rem); color: rgba(255, 255, 255, 0.9); max-width: 600px; text-shadow: 0 1px 2px rgba(0,0,0,0.2); }
        .hero button { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: #fff; border: none; padding: 1rem 2.5rem; border-radius: 50px; font-size: 1.125rem; font-weight: 700; cursor: pointer; box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4); text-transform: uppercase; letter-spacing: 0.5px; }
        .how-it-works { text-align: center; padding: 6rem 1rem; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); position: relative; }
        .how-it-works-content { position: relative; z-index: 2; max-width: 1200px; margin: 0 auto; }
        .how-it-works h2 { margin-bottom: 4rem; font-size: clamp(2rem, 4vw, 3rem); font-weight: 800; color: #1a202c; position: relative; }
        .how-it-works h2::after { content: ''; position: absolute; bottom: -1rem; left: 50%; transform: translateX(-50%); width: 80px; height: 4px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 2px; }
        .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 3rem; max-width: 1200px; margin: 0 auto; }
        .feature { padding: 3rem 2rem; border-radius: 1.5rem; background: #ffffff; box-shadow: 0 4px 20px rgba(0,0,0,0.08); position: relative; }
        .feature::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .feature-icon { width: 80px; height: 80px; margin: 0 auto 2rem; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3); }
        .feature-icon svg { width: 40px; height: 40px; color: white; }
        .feature h3 { margin: 0 0 1.5rem 0; font-size: 1.5rem; font-weight: 700; color: #1a202c; }
        .feature p { font-size: 1.125rem; color: #4a5568; line-height: 1.7; margin: 0; }
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
    <div class="hero-content">
      <h1>Trade Your Skills. Learn from Others.</h1>
      <p>A student-to-student platform to share and grow your skills together.</p>
      <button onclick="window.location.href='{{ route('register') }}'">Get Started</button>
    </div>
  </section>

  <section class="how-it-works">
    <div class="how-it-works-content">
      <h2>How It Works</h2>
      <div class="features">
        <div class="feature">
          <div class="feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <h3>Find a Skill</h3>
          <p>Browse through the list of skills offered by other students and discover what you want to learn.</p>
        </div>
        <div class="feature">
          <div class="feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
          </div>
          <h3>Match and Trade</h3>
          <p>Send a trade request and match based on availability and mutual interest in skill exchange.</p>
        </div>
        <div class="feature">
          <div class="feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
            </svg>
          </div>
          <h3>Rate and Review</h3>
          <p>Leave feedback and build a trustworthy skill-sharing community with honest reviews.</p>
        </div>
      </div>
    </div>
  </section>

  <footer>
    <p>&copy; {{ date('Y') }} SkillsXchange. All rights reserved.</p>
  </footer>
</body>
</html>