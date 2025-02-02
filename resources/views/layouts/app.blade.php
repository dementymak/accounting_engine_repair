<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Engine Repair Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .btn-icon {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .table-actions {
            white-space: nowrap;
        }
        .nav-link.active {
            font-weight: bold;
        }
        .language-switcher .nav-link {
            padding: 0.5rem 0.75rem;
            color: rgba(255,255,255,0.7);
        }
        .language-switcher .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('repair-cards.index') }}">Engine Repair</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('repair-cards.*') ? 'active' : '' }}" 
                           href="{{ route('repair-cards.index') }}">{{ __('messages.repair_cards') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('wire-inventory.*') ? 'active' : '' }}" 
                           href="{{ route('wire-inventory.index') }}">{{ __('messages.wire_inventory') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('scrap.*') ? 'active' : '' }}" 
                           href="{{ route('scrap.index') }}">{{ __('messages.scrap_inventory') }}</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <!-- Language Switcher -->
                    <li class="nav-item language-switcher me-3">
                        <div class="btn-group" role="group">
                            <a href="{{ route('language.switch', 'en') }}" class="nav-link {{ app()->getLocale() == 'en' ? 'active' : '' }}">EN</a>
                            <a href="{{ route('language.switch', 'uk') }}" class="nav-link {{ app()->getLocale() == 'uk' ? 'active' : '' }}">UK</a>
                            <a href="{{ route('language.switch', 'pl') }}" class="nav-link {{ app()->getLocale() == 'pl' ? 'active' : '' }}">PL</a>
                        </div>
                    </li>
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('messages.login') }}</a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                {{ Auth::user()->name }}
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">{{ __('messages.logout') }}</button>
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom validation messages -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set custom validation messages for all required inputs
            document.querySelectorAll('input[required], select[required], textarea[required]').forEach(function(element) {
                element.oninvalid = function(e) {
                    if (e.target.validity.valueMissing) {
                        e.target.setCustomValidity('{{ __('messages.fill_this_field') }}');
                    }
                };
                element.oninput = function(e) {
                    e.target.setCustomValidity('');
                };
            });

            // Set custom validation messages for number inputs
            document.querySelectorAll('input[type="number"]').forEach(function(element) {
                element.addEventListener('invalid', function(e) {
                    if (e.target.validity.badInput) {
                        e.target.setCustomValidity('{{ __('messages.numeric') }}');
                    }
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html> 













