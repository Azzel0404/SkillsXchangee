<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SkillsXchange</title>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
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
</body>
</html>