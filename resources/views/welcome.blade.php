<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SkillsXchange - Trade Your Skills</title>
  <meta name="description" content="Connect with students to trade skills and learn together. A peer-to-peer learning platform for skill exchange.">
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

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
  {{-- Modern SkillsXchange Landing Page Styles --}}
  <style>
    :root {
      --primary: #6366f1;
      --primary-dark: #4f46e5;
      --secondary: #f59e0b;
      --accent: #ec4899;
      --success: #10b981;
      --warning: #f59e0b;
      --error: #ef4444;
      --gray-50: #f9fafb;
      --gray-100: #f3f4f6;
      --gray-200: #e5e7eb;
      --gray-300: #d1d5db;
      --gray-400: #9ca3af;
      --gray-500: #6b7280;
      --gray-600: #4b5563;
      --gray-700: #374151;
      --gray-800: #1f2937;
      --gray-900: #111827;
      --white: #ffffff;
      --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
      --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
      --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
      --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
      --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      line-height: 1.6;
      color: var(--gray-800);
      background: var(--white);
      overflow-x: hidden;
    }

    /* Modern Header */
    .header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid var(--gray-200);
      transition: all 0.3s ease;
    }

    .header.scrolled {
      background: rgba(255, 255, 255, 0.98);
      box-shadow: var(--shadow-lg);
    }

    .header-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      text-decoration: none;
      font-weight: 800;
      font-size: 1.5rem;
      color: var(--primary);
      transition: all 0.3s ease;
    }

    .logo:hover {
      transform: scale(1.05);
    }

    .logo-icon {
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 900;
      font-size: 1.2rem;
    }

    .nav {
      display: flex;
      align-items: center;
      gap: 2rem;
    }

    .nav-links {
      display: flex;
      gap: 2rem;
      list-style: none;
    }

    .nav-links a {
      text-decoration: none;
      color: var(--gray-600);
      font-weight: 500;
      transition: all 0.3s ease;
      position: relative;
    }

    .nav-links a:hover {
      color: var(--primary);
    }

    .nav-links a::after {
      content: '';
      position: absolute;
      bottom: -4px;
      left: 0;
      width: 0;
      height: 2px;
      background: var(--primary);
      transition: width 0.3s ease;
    }

    .nav-links a:hover::after {
      width: 100%;
    }

    .nav-buttons {
      display: flex;
      gap: 1rem;
      align-items: center;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0.75rem 1.5rem;
      border-radius: 12px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
      font-size: 0.95rem;
    }

    .btn-outline {
      color: var(--gray-600);
      border: 2px solid var(--gray-200);
      background: transparent;
    }

    .btn-outline:hover {
      background: var(--gray-50);
      border-color: var(--gray-300);
      transform: translateY(-1px);
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: white;
      box-shadow: var(--shadow-md);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-lg);
    }

    /* Hero Section */
    .hero {
      min-height: 100vh;
      display: flex;
      align-items: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      position: relative;
      overflow: hidden;
      padding-top: 80px;
    }

    .hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
      opacity: 0.3;
    }

    .hero-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 2rem;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 4rem;
      align-items: center;
      position: relative;
      z-index: 2;
    }

    .hero-text {
      color: white;
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
      padding: 0.5rem 1rem;
      border-radius: 50px;
      font-size: 0.875rem;
      font-weight: 600;
      margin-bottom: 2rem;
      border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .hero h1 {
      font-size: clamp(2.5rem, 5vw, 4rem);
      font-weight: 900;
      line-height: 1.1;
      margin-bottom: 1.5rem;
      letter-spacing: -0.02em;
    }

    .hero p {
      font-size: 1.25rem;
      line-height: 1.6;
      margin-bottom: 2.5rem;
      opacity: 0.9;
      max-width: 500px;
    }

    .hero-buttons {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .btn-hero {
      padding: 1rem 2rem;
      font-size: 1.1rem;
      font-weight: 700;
    }

    .btn-hero-primary {
      background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
      color: white;
      box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
    }

    .btn-hero-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 35px rgba(255, 107, 107, 0.6);
    }

    .btn-hero-secondary {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border: 2px solid rgba(255, 255, 255, 0.3);
      backdrop-filter: blur(10px);
    }

    .btn-hero-secondary:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: translateY(-2px);
    }

    .hero-visual {
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
    }

    .hero-card {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 24px;
      padding: 2rem;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
      animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-20px); }
    }

    /* Features Section */
    .features {
      padding: 10rem 0;
      background: var(--gray-50);
      position: relative;
      scroll-margin-top: 50px;
    }

    .features-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 2rem;
    }

    .section-header {
      text-align: center;
      margin-bottom: 4rem;
    }

    .section-badge {
      display: inline-block;
      background: var(--primary);
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 50px;
      font-size: 0.875rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }

    .section-title {
      font-size: clamp(2rem, 4vw, 3rem);
      font-weight: 800;
      color: var(--gray-900);
      margin-bottom: 1rem;
      line-height: 1.2;
    }

    .section-description {
      font-size: 1.25rem;
      color: var(--gray-600);
      max-width: 600px;
      margin: 0 auto;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 2rem;
      margin-top: 4rem;
    }

    .feature-card {
      background: white;
      padding: 2.5rem;
      border-radius: 20px;
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--gray-200);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .feature-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
    }

    .feature-card:hover {
      transform: translateY(-8px);
      box-shadow: var(--shadow-xl);
    }

    .feature-icon {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1.5rem;
      box-shadow: var(--shadow-md);
    }

    .feature-icon svg {
      width: 28px;
      height: 28px;
      color: white;
    }

    .feature-title {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--gray-900);
      margin-bottom: 1rem;
    }

    .feature-description {
      color: var(--gray-600);
      line-height: 1.6;
    }

    /* Stats Section */
    .stats {
      padding: 4rem 0;
      background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
      color: white;
      scroll-margin-top: 80px;
    }

    .stats-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 2rem;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 2rem;
    }

    .stat-item {
      text-align: center;
    }

    .stat-number {
      font-size: 3rem;
      font-weight: 900;
      margin-bottom: 0.5rem;
      display: block;
    }

    .stat-label {
      font-size: 1.1rem;
      opacity: 0.9;
    }

    /* CTA Section */
    .cta {
      padding: 6rem 0;
      background: var(--white);
      text-align: center;
      scroll-margin-top: 80px;
    }

    .cta-content {
      max-width: 800px;
      margin: 0 auto;
      padding: 0 2rem;
    }

    .cta h2 {
      font-size: clamp(2rem, 4vw, 3rem);
      font-weight: 800;
      color: var(--gray-900);
      margin-bottom: 1.5rem;
    }

    .cta p {
      font-size: 1.25rem;
      color: var(--gray-600);
      margin-bottom: 2.5rem;
    }

    /* Footer */
    .footer {
      background: var(--gray-900);
      color: white;
      padding: 3rem 0 2rem;
    }

    .footer-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 2rem;
      text-align: center;
    }

    .footer-logo {
      font-size: 1.5rem;
      font-weight: 800;
      color: var(--primary);
        margin-bottom: 1rem;
      }

    .footer-text {
      color: var(--gray-400);
        margin-bottom: 2rem;
      }

    /* Mobile Responsive */
    @media (max-width: 768px) {
      .header-content {
        padding: 1rem;
      }

      .nav-links {
        display: none;
      }

      .hero-content {
        grid-template-columns: 1fr;
        gap: 2rem;
        text-align: center;
      }

      .hero-buttons {
        justify-content: center;
      }

      .features-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
      }

      .feature-card {
        padding: 2rem;
      }

      .stats-content {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 480px) {
      .hero {
        padding-top: 6rem;
      }

      .hero-buttons {
        flex-direction: column;
        align-items: center;
      }

      .btn-hero {
        width: 100%;
        max-width: 300px;
      }

      .stats-content {
        grid-template-columns: 1fr;
      }
    }

    /* Animations */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .animate-fade-in-up {
      animation: fadeInUp 0.6s ease-out;
    }

    /* Smooth scrolling */
    html {
      scroll-behavior: smooth;
    }
  </style>
  @endif
  @if(isset($jsFile))
  <script src="{{ asset('build/' . $jsFile) }}"></script>
  @endif
  @endif
</head>

<body>
  <!-- Modern Header -->
  <header class="header" id="header">
    <div class="header-content">
    <a href="/" class="logo">
        <img src="{{ asset('logo.png') }}" alt="SkillsXchange Logo" class="logo-image">
        <span>SkillsXchange</span>
      </a>
      
      <nav class="nav">
        <ul class="nav-links">
          <li><a href="#features">Features</a></li>
          <li><a href="#how-it-works">How it Works</a></li>
          <li><a href="#about">About</a></li>
        </ul>
        
        <div class="nav-buttons">
          <a href="{{ route('login') }}" class="btn btn-outline">Login</a>
          <a href="{{ route('register') }}" class="btn btn-primary">Sign Up</a>
        </div>
    </nav>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-content">
      <div class="hero-text">
        <div class="hero-badge">
          <span>✨</span>
          <span>Join 1000+ students already trading skills</span>
        </div>
        
        <h1>Trade Your Skills.<br>Learn from Others.</h1>
        <p>Connect with fellow students to exchange skills, learn together, and grow your expertise in a collaborative learning environment.</p>
        
        <div class="hero-buttons">
          <a href="{{ route('register') }}" class="btn btn-hero btn-hero-primary" id="getStartedBtn">Get Started</a>
          <a href="#how-it-works" class="btn btn-hero btn-hero-secondary">Learn More</a>
        </div>
      </div>
      
      <div class="hero-visual">
        <div class="hero-card">
          <div style="text-align: center; color: white;">
            <h3 style="margin-bottom: 1rem; font-size: 1.5rem;">Skill Exchange in Action</h3>
            <div style="display: flex; gap: 1rem; justify-content: center; margin-bottom: 1rem;">
              <div style="background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.9rem;">
                📚 Math Tutoring
              </div>
              <div style="background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.9rem;">
                🎨 Design Skills
              </div>
            </div>
            <p style="opacity: 0.8; font-size: 0.9rem;">Connect • Learn • Grow</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section class="features" id="features">
    <div class="features-content">
      <div class="section-header">
        <span></span>
        <h2 class="section-title">Everything you need to trade skills</h2>
        <p class="section-description">Our platform makes it easy to connect with students, exchange skills, and build meaningful learning relationships.</p>
      </div>
      
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <h3 class="feature-title">Discover Skills</h3>
          <p class="feature-description">Browse through a diverse range of skills offered by students. From academic subjects to creative arts, find exactly what you want to learn.</p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
          </div>
          <h3 class="feature-title">Smart Matching</h3>
          <p class="feature-description">Our intelligent system matches you with students who have complementary skills and similar learning goals for optimal skill exchanges.</p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
          </div>
          <h3 class="feature-title">Real-time Chat</h3>
          <p class="feature-description">Communicate seamlessly with your skill exchange partners through our integrated chat system with video call capabilities.</p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
            </svg>
          </div>
          <h3 class="feature-title">Trust & Reviews</h3>
          <p class="feature-description">Build a reputation through honest reviews and ratings. Our community-driven trust system ensures quality skill exchanges.</p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h3 class="feature-title">Flexible Scheduling</h3>
          <p class="feature-description">Schedule your skill exchange sessions at times that work for both you and your partner. Learn at your own pace.</p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h3 class="feature-title">Progress Tracking</h3>
          <p class="feature-description">Track your learning progress and skill development. Set goals and celebrate achievements with your learning community.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- How It Works Section -->
  <section class="features" id="how-it-works">
    <div class="features-content">
      <div class="section-header">
        <span></span>
        <h2 class="section-title">How it works</h2>
        <p class="section-description">Get started with skill trading in just a few simple steps.</p>
      </div>
      
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
          </div>
          <h3 class="feature-title">1. Create Your Profile</h3>
          <p class="feature-description">Sign up and create your student profile. List the skills you can teach and the skills you want to learn.</p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <h3 class="feature-title">2. Find Your Match</h3>
          <p class="feature-description">Browse available skills or search for specific topics. Find students whose skills complement your learning goals.</p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
          </div>
          <h3 class="feature-title">3. Start Learning</h3>
          <p class="feature-description">Connect with your skill exchange partner, schedule sessions, and begin your collaborative learning journey.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="cta">
    <div class="cta-content">
      <h2>Ready to start trading skills?</h2>
      <p>Join thousands of students who are already learning and teaching through skill exchanges.</p>
      <div class="hero-buttons">
        <a href="{{ route('register') }}" class="btn btn-hero btn-hero-primary">Sign Up</a>
        <a href="{{ route('login') }}" class="btn btn-hero btn-hero-secondary">Already have an account?</a>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-content">
      <div class="footer-logo">SkillsXchange</div>
      <p class="footer-text">Connecting students through skill exchange and collaborative learning.</p>
    <p>&copy; {{ date('Y') }} SkillsXchange. All rights reserved.</p>
    </div>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Header scroll effect
      const header = document.getElementById('header');
      window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
          header.classList.add('scrolled');
        } else {
          header.classList.remove('scrolled');
        }
      });

      // Smooth scrolling for anchor links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute('href'));
          if (target) {
            target.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          }
        });
      });

      // Get started button functionality
      const getStartedBtn = document.getElementById('getStartedBtn');
      if (getStartedBtn) {
        getStartedBtn.addEventListener('click', function(e) {
          e.preventDefault();
          window.location.href = '/register';
        });
      }

      // Add animation classes on scroll
      const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      };

      const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('animate-fade-in-up');
          }
        });
      }, observerOptions);

      // Observe feature cards
      document.querySelectorAll('.feature-card').forEach(card => {
        observer.observe(card);
      });
    });
  </script>
</body>

</html>