<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <!-- Select2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            html, body {
                height: 100%;
                font-family: 'Figtree', sans-serif;
            }

            /* Sidebar Layout */
            .app-wrapper {
                display: flex;
                min-height: 100vh;
            }

            .main-content {
                flex: 1;
                margin-left: 260px; /* Same as sidebar width */
                background-color: #f3f4f6;
                min-height: 100vh;
                transition: margin-left 0.3s ease;
            }

            .main-content.expanded {
                margin-left: 0;
            }

            .content-wrapper {
                padding: 24px 30px;
                max-width: 100%;
            }

            .page-header {
                background: white;
                padding: 20px 30px;
                border-bottom: 1px solid #e5e7eb;
                margin-bottom: 24px;
                box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            }

            .page-header h1 {
                font-size: 1.5rem;
                font-weight: 600;
                color: #111827;
                margin: 0;
            }

            .card {
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                border: none;
            }

            .table-container {
                overflow-x: auto;
            }

            table {
                width: 100%;
                white-space: nowrap;
            }

            .btn-group-sm .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            /* Mobile toggle button */
            .sidebar-toggle {
                display: none;
                position: fixed;
                top: 12px;
                left: 12px;
                z-index: 1060;
                background: #1a1f36;
                color: white;
                border: none;
                padding: 8px 12px;
                border-radius: 6px;
                font-size: 18px;
                cursor: pointer;
            }

            .sidebar-toggle:hover {
                background: #2d3555;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .sidebar-toggle {
                    display: block;
                }

                .main-content {
                    margin-left: 0;
                    padding-top: 60px;
                }

                .main-content.sidebar-open {
                    margin-left: 260px;
                }

                .content-wrapper {
                    padding: 16px;
                }

                .page-header {
                    padding: 16px 20px;
                }

                .page-header h1 {
                    font-size: 1.25rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="app-wrapper">
            <!-- Sidebar Navigation -->
            @include('layouts.navigation')

            <!-- Mobile Toggle -->
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Main Content -->
            <div class="main-content" id="mainContent">
                <!-- Page Header -->
                @isset($header)
                    <div class="page-header">
                        <h1>{{ $header }}</h1>
                    </div>
                @endisset

                <!-- Page Content -->
                <div class="content-wrapper">
                    @yield('content')
                </div>
            </div>
        </div>

        <script>
            // Mobile sidebar toggle
            document.getElementById('sidebarToggle')?.addEventListener('click', function() {
                const sidebar = document.querySelector('.sidebar');
                const mainContent = document.getElementById('mainContent');
                
                sidebar.classList.toggle('show');
                mainContent.classList.toggle('sidebar-open');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                const sidebar = document.querySelector('.sidebar');
                const toggle = document.getElementById('sidebarToggle');
                
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                        sidebar.classList.remove('show');
                        document.getElementById('mainContent').classList.remove('sidebar-open');
                    }
                }
            });

            // Company switcher function - make it globally accessible
            window.switchCompany = function(companyId, companyName) {
                console.log('Switching to company:', companyId, companyName);
                
                fetch('/company/switch/' + companyId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        document.getElementById('currentCompanyName').textContent = companyName;
                        // Reload the page to refresh all data
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to switch company');
                    }
                })
                .catch(error => {
                    console.error('Error switching company:', error);
                    alert('Error switching company. Please try again.');
                });
            };
        </script>
    </body>
</html>