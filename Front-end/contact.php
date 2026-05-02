<?php
require_once __DIR__ . '/lang_init.php';
require_once __DIR__ . '/../API/config.php';

// Fetch settings
$settings_query = $conn->query("SELECT * FROM settings LIMIT 1");
$settings = $settings_query->fetch_assoc();

// Initialize contact info from settings
$contact_info = [
    'address' => $settings['address'] ?? '',
    'phone' => $settings['phone'] ?? '',
    'email' => $settings['email'] ?? '',
    'facebook' => $settings['facebook'] ?? '',
    'instagram' => $settings['instagram'] ?? '',
    'twitter' => $settings['twitter'] ?? '',
    'youtube' => $settings['youtube'] ?? ''
];

// Initialize success message
$success_msg = $_SESSION['contact_success'] ?? null;
unset($_SESSION['contact_success']);

// Translation array
$text = [
    1 => [
        'page_title' => 'Contact Us',
        'hero_title' => 'Contact Us',
        'hero_subtitle' => 'We\'d love to hear from you',
        'get_in_touch' => 'Get in Touch',
        'send_message' => 'Send us a message',
        'full_name' => 'Full Name',
        'email' => 'Email',
        'subject' => 'Subject',
        'message' => 'Message',
        'submit' => 'Send Message',
        'visit' => 'Visit Us',
        'call' => 'Call Us',
        'email_us' => 'Email Us',
        'follow_us' => 'Follow Us',
        'success' => 'Thank you! Your message has been sent.'
    ],
    2 => [
        'page_title' => 'Wasiliana Nasi',
        'hero_title' => 'Wasiliana Nasi',
        'hero_subtitle' => 'Tungependa kusikia kutoka kwako',
        'get_in_touch' => 'Wasiliana',
        'send_message' => 'Tutumie ujumbe',
        'full_name' => 'Jina Kamili',
        'email' => 'Barua Pepe',
        'subject' => 'Mada',
        'message' => 'Ujumbe Wako',
        'submit' => 'Tuma Ujumbe',
        'visit' => 'Tutembelee',
        'call' => 'Tupigie',
        'email_us' => 'Tutumie Email',
        'follow_us' => 'Tufuate',
        'success' => 'Asante! Ujumbe wako umetumwa.'
    ],
    3 => [
        'page_title' => 'Contactez-Nous',
        'hero_title' => 'Contactez-Nous',
        'hero_subtitle' => 'Nous serions ravis de vous entendre',
        'get_in_touch' => 'Restez en Contact',
        'send_message' => 'Envoyez-nous un message',
        'full_name' => 'Nom Complet',
        'email' => 'Email',
        'subject' => 'Sujet',
        'message' => 'Message',
        'submit' => 'Envoyer',
        'visit' => 'Visitez-Nous',
        'call' => 'Appelez-Nous',
        'email_us' => 'Email',
        'follow_us' => 'Suivez-Nous',
        'success' => 'Merci! Votre message a été envoyé.'
    ]
];

$t = $text[$language_id] ?? $text[1];

ob_start();
?>
<!-- Hero Section -->
<section class="relative h-96 bg-dark-blue flex items-center justify-center text-white">
    <div class="text-center z-10">
        <h1 class="text-4xl lg:text-5xl font-bold mb-4"><?php echo $t['hero_title']; ?></h1>
        <div class="w-24 h-1 bg-primary-gold mx-auto mb-6 rounded-full"></div>
        <p class="text-xl text-gray-200 max-w-2xl mx-auto"><?php echo $t['hero_subtitle']; ?></p>
    </div>
</section>

<!-- CONTACT SECTION -->
<section class="contact-section py-24 bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
  <div class="grid lg:grid-cols-2 gap-12 lg:gap-16">
        <!-- Contact Info -->
        <div class="space-y-8">
            <?php if (!empty($contact_info['address'])): ?>
            <div class="group bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-primary-gold/10 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-primary-gold/20 transition-colors duration-300">
                        <svg class="w-6 h-6 text-primary-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-lg font-semibold text-dark-blue mb-2"><?php echo $t['visit'] ?? 'Visit Us'; ?></h4>
                        <p class="text-gray-600 leading-relaxed"><?php echo nl2br(htmlspecialchars($contact_info['address'])); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($contact_info['phone'])): ?>
            <div class="group bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-primary-gold/10 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-primary-gold/20 transition-colors duration-300">
                        <svg class="w-6 h-6 text-primary-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-lg font-semibold text-dark-blue mb-2"><?php echo $t['call'] ?? 'Call Us'; ?></h4>
                        <p class="text-gray-600">
                            <a href="tel:<?php echo htmlspecialchars($contact_info['phone']); ?>" class="hover:text-primary-gold transition-colors duration-300">
                                <?php echo htmlspecialchars($contact_info['phone']); ?>
                            </a>
                        </p>
                        <p class="text-sm text-gray-500 mt-1"><?php echo $t['mon_fri'] ?? 'Mon-Fri: 9AM - 6PM'; ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($contact_info['email'])): ?>
            <div class="group bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-primary-gold/10 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-primary-gold/20 transition-colors duration-300">
                        <svg class="w-6 h-6 text-primary-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-lg font-semibold text-dark-blue mb-2"><?php echo $t['email_us'] ?? 'Email Us'; ?></h4>
                        <p class="text-gray-600">
                            <a href="mailto:<?php echo htmlspecialchars($contact_info['email']); ?>" class="hover:text-primary-gold transition-colors duration-300">
                                <?php echo htmlspecialchars($contact_info['email']); ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Social Media -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h4 class="text-lg font-semibold text-dark-blue mb-4"><?php echo $t['follow_us'] ?? 'Follow Us'; ?></h4>
                <div class="flex gap-3">
                    <?php if (!empty($contact_info['facebook'])): ?>
                        <a href="<?php echo htmlspecialchars($contact_info['facebook']); ?>" target="_blank" class="w-10 h-10 bg-gray-100 hover:bg-primary-gold hover:text-white rounded-lg flex items-center justify-center transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                            </svg>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($contact_info['instagram'])): ?>
                        <a href="<?php echo htmlspecialchars($contact_info['instagram']); ?>" target="_blank" class="w-10 h-10 bg-gray-100 hover:bg-primary-gold hover:text-white rounded-lg flex items-center justify-center transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                            </svg>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($contact_info['twitter'])): ?>
                        <a href="<?php echo htmlspecialchars($contact_info['twitter']); ?>" target="_blank" class="w-10 h-10 bg-gray-100 hover:bg-primary-gold hover:text-white rounded-lg flex items-center justify-center transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
                            </svg>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($contact_info['youtube'])): ?>
                        <a href="<?php echo htmlspecialchars($contact_info['youtube']); ?>" target="_blank" class="w-10 h-10 bg-gray-100 hover:bg-primary-gold hover:text-white rounded-lg flex items-center justify-center transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
                                <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Contact Form -->
        <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-2xl font-bold text-dark-blue mb-6"><?php echo $t['send_message'] ?? 'Send Message'; ?></h3>
            
            <?php if (isset($success_msg) && $success_msg): ?>
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                    <?php echo $t['success'] ?? 'Message sent successfully!'; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="process_contact.php" class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo $t['full_name'] ?? 'Full Name'; ?> *
                        </label>
                        <input type="text" id="name" name="name" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-gold focus:border-transparent transition-all duration-300"
                               placeholder="<?php echo $t['full_name'] ?? 'Full Name'; ?>">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo $t['email'] ?? 'Email'; ?> *
                        </label>
                        <input type="email" id="email" name="email" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-gold focus:border-transparent transition-all duration-300"
                               placeholder="email@example.com">
                    </div>
                </div>
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo $t['subject'] ?? 'Subject'; ?> *
                    </label>
                    <input type="text" id="subject" name="subject" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-gold focus:border-transparent transition-all duration-300"
                           placeholder="<?php echo $t['subject'] ?? 'Subject'; ?>">
                </div>
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo $t['message'] ?? 'Message'; ?> *
                    </label>
                    <textarea id="message" name="message" rows="5" required 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-gold focus:border-transparent transition-all duration-300 resize-none"
                              placeholder="<?php echo $t['message'] ?? 'Message'; ?>"></textarea>
                </div>
                <input type="hidden" name="lang" value="<?php echo $lang_code; ?>">
                <button type="submit" class="w-full bg-primary-gold hover:bg-primary-gold/90 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:-translate-y-0.5">
                    <?php echo $t['send_message'] ?? 'Send Message'; ?>
                </button>
            </form>
        </div>
    </div>

            </div>
        </div>
    </div>
    
</section>
<!-- Map Section -->
        <div class="map-section ">
            <div class="map-container overflow-hidden shadow-lg">
                <div class="map-wrapper relative" style="padding-bottom: 35%;">
                    <iframe 
                        src="https://maps.google.com/maps?q=<?php echo urlencode($contact_info['address'] ?? $settings['address'] ?? 'Kigali, Rwanda'); ?>&output=embed&z=15"
                        class="absolute top-0 left-0 w-full h-full"
                        style="border:0;" 
                        allowfullscreen="" 
                        >
                    </iframe>
                </div>
            </div>
        </div>




    <script>
        document.querySelectorAll('.fade-in').forEach(el => {
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) entry.target.classList.add('visible');
                });
            }, { threshold: 0.1 });
            observer.observe(el);
        });
    </script>

<?php
$content = ob_get_clean();
$page_title = $t['page_title'] . ' - Masaka Initiative';
require_once 'layout.php';
?>
