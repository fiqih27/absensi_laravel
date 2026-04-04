<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Absensi Alfaiz - Dashboard Modern</title>
    <!-- Google Fonts & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #fef2f2;
            color: #2d1a1a;
            overflow-x: hidden;
        }

        /* Nuansa Merah Modern */
        :root {
            --red-primary: #c41e3a;
            --red-dark: #9b1d2c;
            --red-soft: #fce7e7;
            --red-glow: rgba(196, 30, 58, 0.25);
            --gray-bg: #fff9f9;
            --shadow-sm: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            --shadow-md: 0 20px 25px -12px rgba(196, 30, 58, 0.15);
        }

        /* Navbar Premium */
        .navbar-modern {
            background: linear-gradient(135deg, #a81c34 0%, #7a1428 100%);
            backdrop-filter: blur(2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            padding: 0.85rem 1.5rem;
            transition: all 0.3s ease;
        }

        .navbar-modern .navbar-brand {
            font-weight: 800;
            font-size: 1.6rem;
            letter-spacing: -0.3px;
            background: linear-gradient(120deg, #fff, #ffe0e0);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-modern .nav-link {
            font-weight: 500;
            margin: 0 4px;
            border-radius: 40px;
            transition: all 0.2s ease;
            color: rgba(255,255,255,0.9) !important;
        }

        .navbar-modern .nav-link:hover, .navbar-modern .nav-link.active {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
            color: white !important;
        }

        /* Card Modern */
        .card-modern {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(2px);
            border: none;
            border-radius: 28px;
            box-shadow: var(--shadow-sm);
            transition: all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            overflow: hidden;
        }

        .card-modern:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-md);
            border-bottom: 2px solid var(--red-primary);
        }

        /* Stats Cards dengan aksen merah */
        .stat-card {
            border-radius: 28px;
            border-left: 5px solid var(--red-primary);
            background: white;
            transition: all 0.25s;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(196,30,58,0.08) 0%, rgba(255,255,255,0) 80%);
            pointer-events: none;
        }

        .stat-card:hover {
            transform: scale(1.02);
            border-left-width: 8px;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            background: var(--red-soft);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--red-primary);
            font-size: 1.8rem;
        }

        /* Badge Status */
        .status-present {
            background: #dcfce7;
            color: #15803d;
            border-radius: 40px;
            padding: 0.25rem 0.9rem;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .status-late {
            background: #fff3e3;
            color: #b45309;
            border-radius: 40px;
            padding: 0.25rem 0.9rem;
            font-weight: 600;
        }
        .status-absent {
            background: #fee2e2;
            color: #b91c1c;
            border-radius: 40px;
            padding: 0.25rem 0.9rem;
            font-weight: 600;
        }
        .badge-sent {
            background: #15803d;
            box-shadow: 0 2px 5px rgba(21,128,61,0.2);
        }
        .badge-pending {
            background: #eab308;
            color: #2c2b2b;
        }

        /* Table Modern */
        .table-modern {
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        .table-modern thead th {
            background: transparent;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #4b2e2e;
            border-bottom: 2px solid #f0cfcf;
            padding: 1rem 0.75rem;
        }
        .table-modern tbody tr {
            background: white;
            border-radius: 18px;
            transition: all 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .table-modern tbody tr:hover {
            background: #fffafa;
            transform: scale(1.01);
            box-shadow: 0 8px 18px rgba(196,30,58,0.08);
        }

        /* Button Red Elegant */
        .btn-red-modern {
            background: linear-gradient(95deg, #c41e3a, #a51e36);
            border: none;
            border-radius: 40px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s;
            color: white;
            box-shadow: 0 2px 5px rgba(196,30,58,0.3);
        }
        .btn-red-modern:hover {
            transform: translateY(-2px);
            background: linear-gradient(95deg, #b11a34, #8f162c);
            box-shadow: 0 10px 20px -5px rgba(196,30,58,0.5);
            color: white;
        }

        /* Animation Elements */
        .animated-gradient-bg {
            background: linear-gradient(-45deg, #fff5f5, #ffffff, #ffe6e6);
            background-size: 200% 200%;
            animation: subtleShift 10s ease infinite;
        }

        @keyframes subtleShift {
            0% { background-position: 0% 50%;}
            50% { background-position: 100% 50%;}
            100% { background-position: 0% 50%;}
        }

        /* Floating animation for icons */
        .float-icon {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px);}
            50% { transform: translateY(-6px);}
            100% { transform: translateY(0px);}
        }

        /* Sidebar style (karena di layout tidak pakai sidebar terpisah, tapi nav sudah cukup) */
        .main-content {
            padding: 2rem 1.8rem;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-modern {
            border-radius: 20px;
            border-left: 5px solid;
            backdrop-filter: blur(4px);
            font-weight: 500;
        }

        /* dataTable customization */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--red-primary);
            color: white !important;
            border-radius: 30px;
            border: none;
        }
        .dataTables_filter input {
            border-radius: 40px;
            border: 1px solid #f0c0c0;
            padding: 0.4rem 1rem;
        }

.navbar-modern .navbar-brand,
.navbar-modern .navbar-brand i {
    color: white !important;
}

.navbar-modern .navbar-brand span {
    color: white;
}
        @media (max-width: 768px) {
            .main-content { padding: 1rem; }
            .navbar-modern .navbar-brand { font-size: 1.2rem; }
        }
    </style>
    @stack('styles')
</head>
<body class="animated-gradient-bg">

    <!-- Navbar Red Nuansa Modern -->
    <nav class="navbar navbar-expand-lg navbar-modern sticky-top">
        <div class="container-fluid px-3">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-fingerprint me-2 float-icon"></i> Absensi Alfaiz
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarModern">
                <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarModern">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center gap-1">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
    <a class="nav-link" href="{{ route('whatsapp.chat') }}">
        <i class="fab fa-whatsapp"></i> WhatsApp Chat
    </a>
</li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="attDrop" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-calendar-check me-1"></i> Absensi
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 mt-2">
                            <li><a class="dropdown-item rounded-3" href="{{ route('attendance.index') }}"><i class="fas fa-check-circle text-danger me-2"></i> Hari Ini</a></li>
                            <li><a class="dropdown-item rounded-3" href="{{ route('reports.attendance') }}"><i class="fas fa-chart-line text-danger me-2"></i> Laporan</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('students.index') }}"><i class="fas fa-user-graduate me-1"></i> Siswa</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('parents.index') }}"><i class="fas fa-users me-1"></i> Orang Tua</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('devices.index') }}"><i class="fas fa-microchip me-1"></i> Device</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('notifications.index') }}"><i class="fas fa-bell me-1"></i> Notifikasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('settings.index') }}"><i class="fas fa-cog me-1"></i> Pengaturan</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-0">
        <div class="row g-0">
            <main class="col-12 main-content">
                <!-- Alert Session dengan Animasi -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show alert-modern shadow-sm mb-4" role="alert" style="border-left-color: #198754; background: #e9f7eb;">
                        <i class="fas fa-check-circle me-2 fa-fw"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show alert-modern shadow-sm mb-4" role="alert" style="border-left-color: #dc3545;">
                        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Inisialisasi AOS untuk animasi scroll halus
        AOS.init({
            duration: 800,
            once: true,
            offset: 10
        });

        // Tooltip & hover efek tambahan
        $(document).ready(function() {
            // animasi tambahan untuk card ketika load
            $('.stat-card, .card-modern').each(function(i) {
                $(this).css('animation-delay', (i * 0.05) + 's');
            });

            // Optional: menambahkan efek klik pada navlink active state
            $('.nav-link').on('click', function() {
                $(this).addClass('active').parent().siblings().find('.nav-link').removeClass('active');
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
