<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SkillsXchange') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        {{-- Fallback CSS for production --}}
        @if(app()->environment('production'))
            <link rel="stylesheet" href="{{ asset('css/fallback.css') }}">
        @endif
        
        <!-- Clean minimalist styles for auth pages -->
        <style>
            .auth-container {
                background: #f5f5f5;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 2rem 1rem;
            }
            
            .auth-logo {
                width: 60px;
                height: 60px;
                background: #fff;
                border-radius: 8px;
                margin-bottom: 2rem;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }
            
            .auth-card {
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                padding: 2rem;
                width: 100%;
                max-width: 400px;
            }
            
            .form-input {
                width: 100%;
                padding: 0.75rem;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 1rem;
                transition: border-color 0.2s ease;
            }
            
            .form-input:focus {
                outline: none;
                border-color: #007bff;
                box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
            }
            
            .form-input.border-red-500 {
                border-color: #dc3545;
            }
            
            .form-input.border-green-500 {
                border-color: #28a745;
            }
            
            .btn-primary {
                background: #333;
                color: white;
                border: none;
                padding: 0.75rem 1.5rem;
                border-radius: 4px;
                font-weight: 600;
                font-size: 0.875rem;
                text-transform: uppercase;
                cursor: pointer;
                transition: background-color 0.2s ease;
            }
            
            .btn-primary:hover {
                background: #555;
            }
            
            .btn-primary:disabled {
                opacity: 0.6;
                cursor: not-allowed;
            }
            
            .form-label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: 500;
                color: #333;
            }
            
            .form-group {
                margin-bottom: 1rem;
            }
            
            .checkbox-group {
                display: flex;
                align-items: center;
                margin-bottom: 1rem;
            }
            
            .checkbox-group input[type="checkbox"] {
                margin-right: 0.5rem;
            }
            
            .form-footer {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 1.5rem;
            }
            
            .form-footer a {
                color: #007bff;
                text-decoration: none;
                font-size: 0.875rem;
            }
            
            .form-footer a:hover {
                text-decoration: underline;
            }
            
            @media (max-width: 640px) {
                .auth-container {
                    padding: 1rem;
                }
                
                .auth-card {
                    padding: 1.5rem;
                }
                
                .auth-logo {
                    width: 50px;
                    height: 50px;
                }
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="auth-container">
            <div class="auth-logo">
                <img src="{{ asset('logo.png') }}" alt="SkillsXchange Logo" style="width: 100%; height: 100%; object-fit: contain;">
            </div>

            <div class="auth-card">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
