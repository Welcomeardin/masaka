<?php
@include_once '../../API/config.php';
session_start();

if (!isset($_SESSION['user'])) {
    die("User not logged in.");
}

// Use first_name + second_name for display
$firstName = $_SESSION['user']['first_name'];
$secondName = $_SESSION['user']['second_name'];
$fullName = trim($firstName . ' ' . $secondName);

$email = $_SESSION['user']['email'];

// Generate initials from first + second name
$nameParts = explode(' ', $fullName);
$initials = '';
foreach ($nameParts as $part) {
    if ($part !== '') {
        $initials .= strtoupper($part[0]);
    }
}

// Optional: limit initials to 2 letters (e.g., John Doe → JD)
$initials = substr($initials, 0, 2);

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Minecloud — Dashboard (Tailwind HTML)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Tailwind custom config for fonts/colors similar to the design
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pcool: '#f3f4f6',
                        primary: '#2753ff',
                        panel: '#ffffff',
                        soft: '#f8fafb',
                        faint: '#9aa4b2'
                    },
                    boxShadow: {
                        smd: '0 6px 18px rgba(31,41,55,0.06)'
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial
        }

        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }

        .mobile-menu.open {
            transform: translateX(0);
        }

        .table-responsive {
            overflow-x: auto;
        }

        @media (max-width: 768px) {

            .table-header,
            .table-row {
                min-width: 700px;
            }
        }
    </style>
    <script src="https://unpkg.com/feather-icons"></script>
</head>

<body>
    <!-- Mobile Menu Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden mobile-menu-overlay hidden"></div>

    <!-- Mobile Sidebar -->
    <div class="fixed top-0 left-0 h-full w-64 bg-white z-50 p-4 mobile-menu md:hidden">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-gradient-to-tr from-green-600 to-indigo-400 rounded flex items-center justify-center text-white font-bold">m</div>
                <div class="text-lg font-semibold">Masaka</div>
            </div>
            <button class="mobile-menu-close p-2 rounded-full hover:bg-gray-100">
                <i data-feather="x"></i>
            </button>
        </div>

        <nav class="mb-8">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Menu</div>
            <div class="space-y-1">
                <button class="w-full text-left px-3 py-2 rounded-md bg-white text-primary border border-transparent shadow-sm">Files</button>
                <button class="w-full text-left px-3 py-2 rounded-md hover:bg-gray-50">Activity</button>
                <button class="w-full text-left px-3 py-2 rounded-md hover:bg-gray-50">Calendar</button>
                <button class="w-full text-left px-3 py-2 rounded-md hover:bg-gray-50">Contact</button>
            </div>
        </nav>

        <div class="mb-8">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Categories</div>
            <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-center gap-3"><i data-feather="clock" class="w-4 h-4"></i> Recent</li>
                <li class="flex items-center gap-3"><i data-feather="heart" class="w-4 h-4"></i> Favorites</li>
                <li class="flex items-center gap-3"><i data-feather="share-2" class="w-4 h-4"></i> Shared</li>
                <li class="flex items-center gap-3"><i data-feather="tag" class="w-4 h-4"></i> Tags</li>
            </ul>
        </div>

        <div class="mt-auto">
            <div class="text-sm">
                <div class="flex items-center justify-between text-gray-500 mb-3"><span>Storage</span><span class="font-semibold">42 GB</span></div>
                <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                    <div class="h-2 bg-blue-400 rounded-full" style="width:16%"></div>
                </div>
                <div class="mt-2 text-xs text-gray-400">42 GB used from 256 GB</div>
            </div>
        </div>
    </div>

    <!-- Main container -->
    <div class="max-w-[100%] mx-auto bg-transparent h-screen">
        <div class="rounded-md bg-white/90  p-4 md:p-6 relative overflow-hidden ">

            <!-- Topbar -->
            <header class="flex items-center gap-4">
                <div class="flex items-center gap-3">
                    <button class="md:hidden mobile-menu-toggle p-2 rounded-md hover:bg-gray-100">
                        <i data-feather="menu"></i>
                    </button>
                    <div class="w-9 h-9 bg-gradient-to-tr from-green-600 to-green-400 rounded flex items-center justify-center text-white font-bold">m</div>
                    <div class="text-lg font-semibold">Masaka</div>
                </div>

                <nav class="ml-6 hidden md:flex gap-3 items-center text-sm text-gray-600">
                    <button class="px-3 py-2 rounded-md bg-white text-green-700 border border-transparent shadow-sm">English</button>
                    <button class="px-3 py-2 rounded-md hover:bg-gray-50">Francais</button>
                    <button class="px-3 py-2 rounded-md hover:bg-gray-50">Swahili</button>
                </nav>

                <div class="flex-1"></div>

                <div class="flex items-center gap-3">
                    <div class="hidden sm:block w-64">
                        <div class="relative">
                            <input class="w-full border border-gray-200 rounded-full pl-4 pr-10 py-2 text-sm bg-white" placeholder="Search anything...">
                            <div class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400"> <i data-feather="search"></i></div>
                        </div>
                    </div>
                    <button class="md:hidden search-toggle p-2 rounded-full hover:bg-gray-100" aria-label="search">
                        <i data-feather="search"></i>
                    </button>
                    <button class="p-2 rounded-full bg-gray-100" aria-label="notifications"><i data-feather="bell"></i></button>
                    <button id="logoutBtn1" class="w-9 h-9 rounded-full bg-gradient-to-tr from-green-500 to-green-400 flex items-center justify-center">
                        <i data-feather="power" class="w-4 h-4 text-white"></i>
                    </button>

                </div>
            </header>

            <!-- Mobile Search Bar -->
            <div class="mt-4 sm:hidden mobile-search hidden">
                <div class="relative">
                    <input class="w-full border border-gray-200 rounded-full pl-4 pr-10 py-2 text-sm bg-white" placeholder="Search anything...">
                    <div class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400"> <i data-feather="search"></i></div>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-12 gap-6">
                <!-- Left Sidebar -->
                <aside class="md:col-span-3 bg-transparent hidden md:block">
                    <div class="rounded-xl p-4 bg-white shadow-smd border">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <a href="dashboard.php" class=" flex items-center gap-2">
                                    <div class="w-8 h-8 rounded bg-green-50 flex items-center justify-center text-green-700"><i data-feather="grid"></i></div>
                                    <div class="text-sm font-semibold">Dashboard</div>
                                </a>
                            </div>
                            <i data-feather="chevron-down" class="text-gray-400"></i>
                        </div>

                        <ul class="space-y-2 text-sm text-gray-600">
                            <li>
                                <a href="home.php" class="flex items-center gap-3 hover:text-green-700">
                                    <i data-feather="home" class="w-4 h-4 text-green-700"></i>
                                    Home
                                </a>
                            </li>

                            <li>
                                <a href="about.php" class="flex items-center gap-3 hover:text-green-700">
                                    <i data-feather="info" class="w-4 h-4 text-green-700"></i>
                                    About us
                                </a>
                            </li>

                            <li>
                                <a href="missions.php" class="flex items-center gap-3 hover:text-green-700">
                                    <i data-feather="flag" class="w-4 h-4 text-green-700"></i>
                                    Missions
                                </a>
                            </li>

                            <li>
                                <a href="events.php" class="flex items-center gap-3 hover:text-green-700">
                                    <i data-feather="calendar" class="w-4 h-4 text-green-700"></i>
                                    Events
                                </a>
                            </li>

                            <li>
                                <a href="donations.php" class="flex items-center gap-3 hover:text-green-700">
                                    <i data-feather="heart" class="w-4 h-4 text-green-700"></i>
                                    Donations
                                </a>
                            </li>

                            <li>
                                <a href="contacts.php" class="flex items-center gap-3 hover:text-green-700">
                                    <i data-feather="phone" class="w-4 h-4 text-green-700"></i>
                                    Contacts
                                </a>
                            </li>

                            <li>
                                <a href="general.php" class="flex items-center gap-3 hover:text-green-700">
                                    <i data-feather="folder" class="w-4 h-4 text-green-700"></i>
                                    General
                                </a>
                            </li>

                            <!-- <li>
                                <a href="settings.php" class="flex items-center gap-3 hover:text-green-700">
                                    <i data-feather="settings" class="w-4 h-4 text-green-700"></i>
                                    Settings
                                </a>
                            </li> -->
                        </ul>


                        <div class="mt-20 text-sm">
                            <div class="flex items-center justify-between text-gray-500 mb-3 cursor-pointer" id="logoutBtn">
                                <span>Signout</span>
                                <span class="font-semibold">
                                    <i data-feather="log-out" class="w-4 h-4 text-red-600"></i>
                                </span>
                            </div>

                            <!-- User Info Block -->
                            <div class="flex items-center gap-4 p-3 bg-gray-100 rounded-xl">

                                <!-- Initials Circle -->
                                <div class="w-12 h-12 rounded-full bg-green-600 text-white flex items-center justify-center text-lg font-semibold">
                                    <?= htmlspecialchars($initials) ?>
                                </div>

                                <!-- User Info -->
                                <div>
                                    <div class="font-semibold text-gray-700">
                                        <?= htmlspecialchars($fullName) ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?= htmlspecialchars($email) ?>
                                    </div>
                                </div>
                            </div>

                        </div>


                    </div>
                </aside>

                <!-- Main Content -->
                <main class="md:col-span-8">

                    <?php
                    // Check if $content is defined, then include it
                    if (isset($content)) {
                        echo $content;
                    }
                    ?>

                </main>

                <!-- Right Panel
                <aside class="md:col-span-3">
                    <div class="rounded-xl p-4 bg-white shadow-smd border">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="text-sm font-semibold">Source</div>
                                <div class="text-xs text-gray-400 mt-1">1.2 MB • Yesterday • 11ten</div>
                            </div>
                            <div class="text-gray-400"> <i data-feather="x"></i></div>
                        </div>

                        <div class="mt-4">
                            <div class="text-xs text-gray-400">Tags</div>
                            <div class="flex gap-2 mt-2 flex-wrap">
                                <span class="text-xs px-2 py-1 bg-blue-50 rounded">Work</span>
                                <span class="text-xs px-2 py-1 bg-gray-50 rounded">Source</span>
                                <span class="text-xs px-2 py-1 bg-gray-50 rounded">Font</span>
                            </div>
                        </div>

                        <div class="mt-4 border-t pt-3 text-sm text-gray-500">
                            <div class="font-semibold mb-2">Sharing</div>
                            <div class="flex -space-x-3 items-center">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-400 border-2 border-white"></div>
                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-green-500 to-teal-400 border-2 border-white"></div>
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs border">+3</div>
                            </div>
                        </div>

                        <div class="mt-4 border-t pt-3 text-xs text-gray-400">
                            <div class="font-semibold text-sm mb-2">Activity</div>
                            <div class="space-y-3">
                                <div>
                                    <div class="text-xs text-gray-500">Yesterday</div>
                                    <div class="text-sm">You shared edit access to <span class="font-semibold">Miko</span></div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Apr 1, 2022</div>
                                    <div class="text-sm">You changed <span class="font-semibold">Maszeh.glyph</span></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </aside> -->

            </div>

        </div>
    </div>

    <script>
        feather.replace();

        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenu = document.querySelector('.mobile-menu');
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const mobileMenuClose = document.querySelector('.mobile-menu-close');
            const mobileMenuOverlay = document.querySelector('.mobile-menu-overlay');
            const mobileSearch = document.querySelector('.mobile-search');
            const searchToggle = document.querySelector('.search-toggle');

            // Toggle mobile menu
            mobileMenuToggle.addEventListener('click', function() {
                mobileMenu.classList.add('open');
                mobileMenuOverlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });

            // Close mobile menu
            function closeMobileMenu() {
                mobileMenu.classList.remove('open');
                mobileMenuOverlay.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }

            mobileMenuClose.addEventListener('click', closeMobileMenu);
            mobileMenuOverlay.addEventListener('click', closeMobileMenu);

            // Toggle mobile search
            searchToggle.addEventListener('click', function() {
                mobileSearch.classList.toggle('hidden');
            });
        });
    </script>

    <script>
        // Activate feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        document.getElementById('logoutBtn').addEventListener('click', function() {
            Swal.fire({
                title: 'Are you sure you want to logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Logout',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-md', // rounded popup
                    title: 'text-sm ', // smaller title
                    icon: 'text-md', // smaller icon
                    confirmButton: 'bg-green-500 hover:bg-green-600 text-white rounded-full px-4 py-2', // green rounded button
                    cancelButton: 'bg-red-500 hover:bg-red-600 text-white rounded-full px-4 py-2' // red rounded button
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to logout page
                    window.location.href = '../../../API/logout.php';
                }
            });
        });

        document.getElementById('logoutBtn1').addEventListener('click', function() {
            Swal.fire({
                title: 'Are you sure you want to logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Logout',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-md', // rounded popup
                    title: 'text-sm ', // smaller title
                    icon: 'text-md', // smaller icon
                    confirmButton: 'bg-green-500 hover:bg-green-600 text-white rounded-full px-4 py-2', // green rounded button
                    cancelButton: 'bg-red-500 hover:bg-red-600 text-white rounded-full px-4 py-2' // red rounded button
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to logout page
                    window.location.href = '../../../API/logout.php';
                }
            });
        });
    </script>
    <!-- Include SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>