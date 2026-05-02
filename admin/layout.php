<?php
// Admin Layout Template
require_once __DIR__ . '/../auth/require_login.php';
require_once __DIR__ . '/../API/config.php';

// Get languages
$languages = $conn->query("SELECT id, code, name FROM languages WHERE is_active = 1");
$langs = [];
while ($lang = $languages->fetch_assoc()) {
    $langs[$lang['id']] = $lang;
}

// Get default language
$default_lang = $conn->query("SELECT id FROM languages WHERE is_default = 1");
$defaultLangId = $default_lang->fetch_assoc()['id'] ?? 1;

// Get current language from session or default
$currentLangId = $_SESSION['current_lang_id'] ?? $defaultLangId;
$currentLang = $langs[$currentLangId] ?? $langs[$defaultLangId];

$pageTitle = $pageTitle ?? 'Masaka Admin';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | Masaka Admin</title>
    <link rel="icon" type="image/png" href="../assert/favicon.jpg">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Feather Icons -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>

    <!-- Custom Tailwind Config Override -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        },
                    },
                    boxShadow: {
                        'card': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                        'card-hover': '0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025)',
                        'sidebar': '4px 0 10px -4px rgba(0, 0, 0, 0.02)',
                    },
                    transitionProperty: {
                        'width': 'width',
                        'spacing': 'margin, padding',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.2s ease-in-out',
                        'slide-in': 'slideIn 0.3s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': {
                                opacity: '0'
                            },
                            '100%': {
                                opacity: '1'
                            },
                        },
                        slideIn: {
                            '0%': {
                                transform: 'translateX(-10px)',
                                opacity: '0'
                            },
                            '100%': {
                                transform: 'translateX(0)',
                                opacity: '1'
                            },
                        },
                    },
                }
            }
        }
    </script>

    <style>
        /* Custom Scrollbar */
        .sidebar-custom-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-custom-scroll::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }

        .sidebar-custom-scroll::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }

        .sidebar-custom-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .sidebar-item-transition {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .mobile-menu-btn span {
            transition: all 0.3s ease-in-out;
        }

        .spinner {
            border: 2px solid rgba(100, 116, 139, 0.1);
            border-radius: 50%;
            border-top: 2px solid #64748b;
            width: 20px;
            height: 20px;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .hover-lift:hover {
            transform: translateY(-1px);
        }

        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background-color: #ef4444;
            color: white;
            border-radius: 9999px;
            font-size: 9px;
            font-weight: 600;
            padding: 2px 5px;
            min-width: 14px;
            text-align: center;
            line-height: 1;
        }

        .main-content-wrapper {
            min-height: 100vh;
            background-color: #f9fafb;
        }

        /* Dropdown transition */
        .dropdown-transition {
            transition: all 0.2s ease-out;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
        }

        .dropdown-transition.open {
            max-height: 500px;
            opacity: 1;
        }
    </style>

    <?php if (isset($additionalCSS)): ?>
        <?php echo $additionalCSS; ?>
    <?php endif; ?>
</head>

<body class="bg-gray-100 font-sans antialiased">

    <!-- Mobile Menu Overlay -->
    <div id="mobileMenuOverlay" class="fixed inset-0 bg-black bg-opacity-30 z-20 hidden transition-opacity duration-300 lg:hidden"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-72 bg-white border-r border-gray-200 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out shadow-sidebar">
        <!-- Sidebar Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center shadow-sm">
                    <i data-feather="cpu" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h2 class="text-gray-800 text-lg font-bold tracking-tight">Masaka<span class="text-primary-600">Admin</span></h2>
                    <p class="text-gray-400 text-xs">Content Management System</p>
                </div>
            </div>
            <button id="closeMobileMenu" class="lg:hidden text-gray-400 hover:text-gray-600 transition-colors">
                <i data-feather="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="flex-1 overflow-y-auto sidebar-custom-scroll h-full pb-20">
            <!-- Management Section -->
            <div class="px-4 pt-6 pb-2">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wider mb-3 px-3">Management</p>
                <a href="index.php" class="sidebar-item-transition flex items-center space-x-3 px-3 py-2.5 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 group <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'bg-gray-100 text-gray-900' : ''; ?>">
                    <i data-feather="home" class="w-5 h-5"></i>
                    <span class="text-sm font-medium">Dashboard</span>
                    <?php if (basename($_SERVER['PHP_SELF']) == 'index.php'): ?>
                        <span class="ml-auto w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- Content Section with Dropdowns -->
            <div class="px-4 pt-4 pb-2">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wider mb-3 px-3">Content Management</p>

                <!-- About Dropdown -->
                <div class="mb-1">
                    <button onclick="toggleDropdown('aboutDropdown')" class="w-full sidebar-item-transition flex items-center justify-between px-3 py-2.5 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 group">
                        <div class="flex items-center space-x-3">
                            <i data-feather="file-text" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Pages</span>
                        </div>
                        <i data-feather="chevron-down" class="w-4 h-4 transition-transform duration-200" id="aboutDropdownIcon"></i>
                    </button>
                    <div id="aboutDropdown" class="dropdown-transition ml-6 mt-1 space-y-1">
                        <a href="about.php" class="flex items-center space-x-3 px-3 py-2 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 text-sm <?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'bg-gray-100 text-gray-900' : ''; ?>">
                            <i data-feather="info" class="w-4 h-4"></i>
                            <span>About Page</span>
                        </a>
                        <!-- <a href="privacy.php" class="flex items-center space-x-3 px-3 py-2 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 text-sm">
                            <i data-feather="lock" class="w-4 h-4"></i>
                            <span>Privacy Policy</span>
                        </a>
                        <a href="terms.php" class="flex items-center space-x-3 px-3 py-2 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 text-sm">
                            <i data-feather="file" class="w-4 h-4"></i>
                            <span>Terms of Service</span>
                        </a> -->
                    </div>
                </div>

                <!-- Media Dropdown -->
                <div class="mb-1 mt-1">
                    <button onclick="toggleDropdown('mediaDropdown')" class="w-full sidebar-item-transition flex items-center justify-between px-3 py-2.5 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 group">
                        <div class="flex items-center space-x-3">
                            <i data-feather="image" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Media</span>
                        </div>
                        <i data-feather="chevron-down" class="w-4 h-4 transition-transform duration-200" id="mediaDropdownIcon"></i>
                    </button>
                    <div id="mediaDropdown" class="dropdown-transition ml-6 mt-1 space-y-1">
                        <a href="slides.php" class="flex items-center space-x-3 px-3 py-2 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 text-sm <?php echo (basename($_SERVER['PHP_SELF']) == 'slides.php') ? 'bg-gray-100 text-gray-900' : ''; ?>">
                            <i data-feather="layers" class="w-4 h-4"></i>
                            <span>Slideshow</span>
                        </a>
                        <a href="gallery.php" class="flex items-center space-x-3 px-3 py-2 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 text-sm <?php echo (basename($_SERVER['PHP_SELF']) == 'gallery.php') ? 'bg-gray-100 text-gray-900' : ''; ?>">
                            <i data-feather="grid" class="w-4 h-4"></i>
                            <span>Gallery</span>
                        </a>
                    </div>
                </div>

                <!-- Team & Events Dropdown -->
                <div class="mb-1 mt-1">
                    <button onclick="toggleDropdown('teamEventsDropdown')" class="w-full sidebar-item-transition flex items-center justify-between px-3 py-2.5 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 group">
                        <div class="flex items-center space-x-3">
                            <i data-feather="users" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">People & Events</span>
                        </div>
                        <i data-feather="chevron-down" class="w-4 h-4 transition-transform duration-200" id="teamEventsDropdownIcon"></i>
                    </button>
                    <div id="teamEventsDropdown" class="dropdown-transition ml-6 mt-1 space-y-1">
                        <a href="team.php" class="flex items-center space-x-3 px-3 py-2 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 text-sm <?php echo (basename($_SERVER['PHP_SELF']) == 'team.php') ? 'bg-gray-100 text-gray-900' : ''; ?>">
                            <i data-feather="user-plus" class="w-4 h-4"></i>
                            <span>Team Members</span>
                        </a>
                        <a href="events.php" class="flex items-center space-x-3 px-3 py-2 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 text-sm <?php echo (basename($_SERVER['PHP_SELF']) == 'events.php') ? 'bg-gray-100 text-gray-900' : ''; ?>">
                            <i data-feather="calendar" class="w-4 h-4"></i>
                            <span>Events</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Data & Forms Section with Dropdown -->
            <div class="px-4 pt-4 pb-2">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wider mb-3 px-3">Data & Forms</p>

                <div class="mb-1">
                    <button onclick="toggleDropdown('formsDropdown')" class="w-full sidebar-item-transition flex items-center justify-between px-3 py-2.5 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 group">
                        <div class="flex items-center space-x-3">
                            <i data-feather="inbox" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Submissions</span>
                        </div>
                        <i data-feather="chevron-down" class="w-4 h-4 transition-transform duration-200" id="formsDropdownIcon"></i>
                    </button>
                    <div id="formsDropdown" class="dropdown-transition ml-6 mt-1 space-y-1">
                        <a href="donations.php" class="flex items-center space-x-3 px-3 py-2 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 text-sm <?php echo (basename($_SERVER['PHP_SELF']) == 'donations.php') ? 'bg-gray-100 text-gray-900' : ''; ?>">
                            <i data-feather="heart" class="w-4 h-4"></i>
                            <span>Donations</span>
                        </a>
                        <a href="contact.php" class="flex items-center space-x-3 px-3 py-2 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 text-sm <?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'bg-gray-100 text-gray-900' : ''; ?>">
                            <i data-feather="message-circle" class="w-4 h-4"></i>
                            <span>Contact Messages</span>
                        </a>
                        <a href="newsletter.php" class="flex items-center space-x-3 px-3 py-2 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 text-sm <?php echo (basename($_SERVER['PHP_SELF']) == 'newsletter.php') ? 'bg-gray-100 text-gray-900' : ''; ?>">
                            <i data-feather="mail" class="w-4 h-4"></i>
                            <span>Newsletter</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Settings Section -->
            <div class="px-4 pt-4 pb-2">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wider mb-3 px-3">System</p>

                <a href="settings.php" class="sidebar-item-transition flex items-center space-x-3 px-3 py-2.5 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 group <?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'bg-gray-100 text-gray-900' : ''; ?>">
                    <i data-feather="settings" class="w-5 h-5"></i>
                    <span class="text-sm font-medium">Site Settings</span>
                </a>

                <a href="users.php" class="sidebar-item-transition flex items-center space-x-3 px-3 py-2.5 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 group mt-1 <?php echo (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'bg-gray-100 text-gray-900' : ''; ?>">
                    <i data-feather="shield" class="w-5 h-5"></i>
                    <span class="text-sm font-medium">User Management</span>
                </a>
            </div>

            <!-- Sidebar Footer -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 bg-white">
                <div class="flex items-center space-x-3 px-2 py-2 rounded-lg bg-gray-50">
                    <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center">
                        <span class="text-white text-xs font-bold"><?php echo strtoupper(substr(htmlspecialchars($_SESSION['user_name']), 0, 1)); ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-800 text-sm font-medium truncate"><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                        <p class="text-gray-400 text-xs">Administrator</p>
                    </div>
                    <button onclick="confirmLogout()" class="text-gray-400 hover:text-red-500 transition-colors p-2 rounded-lg hover:bg-gray-100" title="Logout">
                        <i data-feather="log-out" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </nav>
    </aside>

    <!-- Main Content Area - Fluid Container -->
    <main class="lg:ml-72 main-content-wrapper">
        <!-- Top Navigation Bar -->
        <header class="sticky top-0 z-20 bg-white border-b border-gray-200 shadow-sm">
            <div class="px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <!-- Left section -->
                    <div class="flex items-center space-x-4">
                        <button id="mobileMenuToggle" class="lg:hidden text-gray-500 hover:text-gray-700 transition-colors p-2 rounded-lg hover:bg-gray-100">
                            <i data-feather="menu" class="w-6 h-6"></i>
                        </button>
                        <div class="hidden lg:block">
                            <h1 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($pageTitle); ?></h1>
                            <p class="text-sm text-gray-500 mt-0.5">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                        </div>
                        <div class="lg:hidden">
                            <h1 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($pageTitle); ?></h1>
                        </div>
                    </div>

                    <!-- Right section -->
                    <div class="flex items-center space-x-3">
                        <!-- Language Selector -->
                        <div class="relative">
                            <select id="langSelector" onchange="changeLanguage(this.value)" class="appearance-none bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-full focus:ring-2 focus:ring-primary-400 focus:border-primary-400 block px-3 py-2 pr-8 cursor-pointer hover:bg-gray-100 transition-colors">
                                <?php foreach ($langs as $langId => $lang): ?>
                                    <option value="<?php echo $langId; ?>" <?php echo ($langId == $currentLangId) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($lang['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400 rounded-full">
                                <i data-feather="chevron-down" class="w-4 h-4"></i>
                            </div>
                        </div>

                        <!-- Notification Bell -->
                        <button class="relative text-gray-500 hover:text-gray-700 transition-colors p-2 rounded-full bg-gray-100">
                            <i data-feather="bell" class="w-5 h-5"></i>
                            <span class="notification-badge hidden">3</span>
                        </button>

                        <!-- User Dropdown -->
                        <div class="relative">
                            <button id="userMenuButton" class="flex items-center space-x-3 text-gray-700 hover:text-gray-900 transition-colors p-1 rounded-full bg-gray-100">
                                <div class="w-9 h-9 rounded-full bg-primary-600 flex items-center justify-center shadow-sm">
                                    <span class="text-white text-sm font-bold"><?php echo strtoupper(substr(htmlspecialchars($_SESSION['user_name']), 0, 1)); ?></span>
                                </div>
                                <!-- <span class="hidden sm:inline text-sm font-medium"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span> -->
                                <!-- <i data-feather="chevron-down" class="hidden sm:block w-4 h-4"></i> -->
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content Container - Fluid with max-w-none for full width usage -->
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-none">
            <!-- Alert Messages -->
            <?php if (isset($successMessage) && !empty($successMessage)): ?>
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 rounded-md shadow-sm overflow-hidden animate-fade-in">
                    <div class="p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i data-feather="check-circle" class="w-5 h-5 text-green-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700 font-medium"><?php echo htmlspecialchars($successMessage); ?></p>
                            </div>
                            <div class="ml-auto">
                                <button class="text-green-500 hover:text-green-700" onclick="this.closest('.bg-green-50').remove()">
                                    <i data-feather="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($errorMessage) && !empty($errorMessage)): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-md shadow-sm overflow-hidden animate-fade-in">
                    <div class="p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i data-feather="alert-circle" class="w-5 h-5 text-red-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700 font-medium"><?php echo htmlspecialchars($errorMessage); ?></p>
                            </div>
                            <div class="ml-auto">
                                <button class="text-red-500 hover:text-red-700" onclick="this.closest('.bg-red-50').remove()">
                                    <i data-feather="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Dynamic Page Content - Full width fluid -->
            <div class="w-full">
                <?php echo $content ?? ''; ?>
            </div>
        </div>
    </main>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Dropdown toggle function
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const icon = document.getElementById(dropdownId + 'Icon');

            if (dropdown.classList.contains('open')) {
                dropdown.classList.remove('open');
                if (icon) icon.style.transform = 'rotate(0deg)';
            } else {
                dropdown.classList.add('open');
                if (icon) icon.style.transform = 'rotate(180deg)';
            }
        }

        // Logout confirmation
        function confirmLogout() {
            Swal.fire({
                title: 'Logout?',
                text: 'Are you sure you want to logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create and submit a form to logout
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'logout.php';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Initialize Feather Icons and setup
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();

            // Check URL hash to open dropdowns if needed
            const currentPage = window.location.pathname.split('/').pop();

            // Map pages to dropdowns
            const pageDropdownMap = {
                'about.php': 'aboutDropdown',
                'privacy.php': 'aboutDropdown',
                'terms.php': 'aboutDropdown',
                'slides.php': 'mediaDropdown',
                'gallery.php': 'mediaDropdown',
                'team.php': 'teamEventsDropdown',
                'events.php': 'teamEventsDropdown',
                'donations.php': 'formsDropdown',
                'contact.php': 'formsDropdown',
                'newsletter.php': 'formsDropdown'
            };

            if (pageDropdownMap[currentPage]) {
                const dropdown = document.getElementById(pageDropdownMap[currentPage]);
                const icon = document.getElementById(pageDropdownMap[currentPage] + 'Icon');
                if (dropdown && !dropdown.classList.contains('open')) {
                    dropdown.classList.add('open');
                    if (icon) icon.style.transform = 'rotate(180deg)';
                }
            }

            // Mobile menu toggle
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('sidebar');
            const closeMobileMenu = document.getElementById('closeMobileMenu');
            const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');

            function openMobileMenu() {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                mobileMenuOverlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeMobileMenuFunc() {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                mobileMenuOverlay.classList.add('hidden');
                document.body.style.overflow = '';
            }

            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', openMobileMenu);
            }
            if (closeMobileMenu) {
                closeMobileMenu.addEventListener('click', closeMobileMenuFunc);
            }
            if (mobileMenuOverlay) {
                mobileMenuOverlay.addEventListener('click', closeMobileMenuFunc);
            }

            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    closeMobileMenuFunc();
                }
            });

            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.bg-green-50, .bg-red-50');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert && alert.parentElement) {
                        alert.style.opacity = '0';
                        setTimeout(() => {
                            if (alert.parentElement) alert.remove();
                        }, 300);
                    }
                }, 5000);
            });
        });

        // Language Change Function
        function changeLanguage(langId) {
            const url = new URL(window.location.href);
            url.searchParams.set('lang', langId);
            window.location.href = url.toString();
        }

        // Display SweetAlert messages from session
        <?php if (isset($_SESSION['swal_message'])): ?>
            const swalMessage = <?php echo json_encode($_SESSION['swal_message']); ?>;
            if (swalMessage) {
                Swal.fire({
                    title: swalMessage.title || 'Notification',
                    text: swalMessage.text || '',
                    icon: swalMessage.icon || 'info',
                    confirmButtonColor: '#64748b',
                    confirmButtonText: 'OK'
                });
            }
            <?php unset($_SESSION['swal_message']); ?>
        <?php endif; ?>
    </script>

    <?php if (isset($additionalJS)): ?>
        <?php echo $additionalJS; ?>
    <?php endif; ?>
</body>

</html>