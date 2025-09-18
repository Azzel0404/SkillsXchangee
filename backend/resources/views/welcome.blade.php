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
      {{-- Fallback CSS for production --}}
      <link rel="stylesheet" href="{{ asset('css/fallback.css') }}">
      {{-- SkillsXchange style CSS matching the images --}}
      <style>
        * { box-sizing: border-box; }
        body { 
          margin: 0; 
          font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
          line-height: 1.6; 
          background: #fff;
          color: #333;
        }
        
        /* Clean Header */
        header { 
          display: flex; 
          justify-content: space-between; 
          align-items: center; 
          padding: 1rem 2rem; 
          background: #fff; 
          border-bottom: 1px solid #e9ecef;
        }
        .logo { 
          color: #007bff; 
          margin: 0; 
          font-size: 1.5rem; 
          font-weight: 700; 
          text-decoration: none;
        }
        header nav { display: flex; gap: 1rem; align-items: center; }
        header nav a { 
          text-decoration: none; 
          color: #007bff; 
          font-weight: 500; 
          padding: 0.5rem 1rem; 
          border-radius: 0.375rem; 
          transition: all 0.2s ease;
        }
        header nav a:hover { background-color: #f8f9fa; }
        header nav a.signup { 
          background: #007bff; 
          color: #fff; 
        }
        header nav a.signup:hover { 
          background: #0056b3;
        }
        
        /* Hero Section - Light Blue Background */
        .hero { 
          text-align: center; 
          background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); 
          padding: 6rem 1rem; 
          min-height: 70vh; 
          display: flex; 
          flex-direction: column; 
          justify-content: center; 
          align-items: center;
        }
        .hero-content { 
          max-width: 800px; 
          margin: 0 auto;
        }
        .hero h1 { 
          font-size: clamp(2.5rem, 5vw, 4rem); 
          font-weight: 800; 
          margin: 0 0 1.5rem 0; 
          color: #333; 
          line-height: 1.1;
        }
        .hero p { 
          margin: 0 0 3rem 0; 
          font-size: clamp(1.125rem, 2.5vw, 1.5rem); 
          color: #666; 
          max-width: 600px; 
          margin-left: auto;
          margin-right: auto;
          line-height: 1.6;
        }
        .hero button { 
          background: #007bff; 
          color: #fff; 
          border: none; 
          padding: 1rem 2.5rem; 
          border-radius: 0.375rem; 
          font-size: 1.125rem; 
          font-weight: 600; 
          cursor: pointer; 
          transition: all 0.2s ease;
        }
        .hero button:hover { 
          background: #0056b3;
          transform: translateY(-2px);
        }
        
        /* How It Works Section - White Background */
        .how-it-works { 
          text-align: center; 
          padding: 6rem 1rem; 
          background: #fff; 
        }
        .how-it-works-content { 
          max-width: 1200px; 
          margin: 0 auto; 
        }
        .how-it-works h2 { 
          margin-bottom: 4rem; 
          font-size: clamp(2rem, 4vw, 3rem); 
          font-weight: 800; 
          color: #333; 
        }
        .features { 
          display: grid; 
          grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
          gap: 3rem; 
          max-width: 1200px; 
          margin: 0 auto; 
        }
        .feature { 
          padding: 3rem 2rem; 
          border-radius: 1rem; 
          background: #fff; 
          box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
          transition: all 0.3s ease;
        }
        .feature:hover { 
          transform: translateY(-5px);
          box-shadow: 0 8px 30px rgba(0,0,0,0.12); 
        }
        .feature-icon { 
          width: 80px; 
          height: 80px; 
          margin: 0 auto 2rem; 
          display: flex; 
          align-items: center; 
          justify-content: center; 
          border-radius: 50%; 
          background: #007bff; 
          box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3); 
        }
        .feature-icon svg { width: 40px; height: 40px; color: white; }
        .feature h3 { 
          margin: 0 0 1.5rem 0; 
          font-size: 1.5rem; 
          font-weight: 700; 
          color: #333; 
        }
        .feature p { 
          font-size: 1.125rem; 
          color: #666; 
          line-height: 1.7; 
          margin: 0; 
        }
        
        /* Footer */
        footer {
          text-align: center;
          padding: 2rem 1rem;
          background: #f8f9fa;
          border-top: 1px solid #e9ecef;
          color: #666;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
          header { padding: 1rem; }
          .logo { font-size: 1.25rem; }
          header nav { gap: 0.5rem; }
          header nav a { padding: 0.375rem 0.75rem; font-size: 0.875rem; }
          .hero { padding: 4rem 1rem; min-height: 60vh; }
          .hero h1 { margin-bottom: 1rem; }
          .hero p { margin-bottom: 2rem; }
          .hero button { padding: 0.875rem 2rem; font-size: 1rem; }
          .how-it-works { padding: 4rem 1rem; }
          .how-it-works h2 { margin-bottom: 3rem; }
          .features { grid-template-columns: 1fr; gap: 2rem; }
          .feature { padding: 2rem 1.5rem; }
          .feature-icon { width: 70px; height: 70px; }
          .feature-icon svg { width: 35px; height: 35px; }
          .feature h3 { font-size: 1.25rem; }
          .feature p { font-size: 1rem; }
        }
        
        @media (max-width: 480px) {
          header { flex-wrap: wrap; }
          header nav { width: 100%; justify-content: center; margin-top: 0.5rem; }
          .hero { padding: 3rem 1rem; }
          .hero button { padding: 0.75rem 1.5rem; font-size: 0.875rem; }
          .how-it-works { padding: 3rem 1rem; }
          .feature { padding: 1.5rem 1rem; }
        }
      </style>
    @endif
    @if(isset($jsFile))
      <script src="{{ asset('build/' . $jsFile) }}"></script>
    @endif
  @endif
</head>
<body>
  <header>
    <a href="/" class="logo">SkillsXchange</a>
    <nav>
      <a href="{{ route('login') }}">Login</a>
      <a href="{{ route('register') }}" class="signup">Sign Up</a>
    </nav>
  </header>

  <section class="hero">
    <div class="hero-content">
      <h1>Trade Your Skills. Learn from Others.</h1>
      <p>A student-to-student platform to share and grow your skills together.</p>
      <button id="getStartedBtn">Get Started</button>
    </div>
  </section>

  <section class="how-it-works">
    <div class="how-it-works-content">
      <h2>How It Works</h2>
      <div class="features">
        <div class="feature">
          <div class="feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <h3>Find a Skill</h3>
          <p>Browse through the list of skills offered by other students.</p>
        </div>
        <div class="feature">
          <div class="feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
          </div>
          <h3>Match and Trade</h3>
          <p>Send a trade request and match based on availability and interest.</p>
        </div>
        <div class="feature">
          <div class="feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
            </svg>
          </div>
          <h3>Rate and Review</h3>
          <p>Leave feedback and build a trustworthy skill-sharing community.</p>
        </div>
      </div>
    </div>
  </section>

  <footer>
    <p>&copy; {{ date('Y') }} SkillsXchange. All rights reserved.</p>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const getStartedBtn = document.getElementById('getStartedBtn');
      if (getStartedBtn) {
        getStartedBtn.addEventListener('click', function() {
          window.location.href = '/register';
        });
      }
    });
  </script>
</body>
</html>