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
$success_msg = isset($_GET['success']) ? true : false;

// Fetch slides for hero section
$slides_query = fetchContent($conn, 'slides', "status = 'active' AND language_id = $language_id", 'sort_order ASC', 0);

// Fetch about content
$about_query = fetchContent($conn, 'about', "status = 'active' AND language_id = $language_id", '', 1);
$about = $about_query->fetch_assoc();

// If no about content in current language, try to get fallback
if (!$about) {
    $about_query = fetchContent($conn, 'about', "status = 'active' AND language_id = 1", '', 1);
    $about = $about_query->fetch_assoc();
}

// Fetch events
$events_query = fetchContent($conn, 'events', "status IN ('upcoming', 'ongoing') AND language_id = $language_id", 'event_date ASC', 3);

// If no events in current language, try to get fallback
if (!$events_query || $events_query->num_rows == 0) {
    $events_query = fetchContent($conn, 'events', "status IN ('upcoming', 'ongoing') AND language_id = 1", 'event_date ASC', 3);
}

// Fetch team members
$team_query = $conn->query("SELECT * FROM team WHERE status = 'active' ORDER BY sort_order ASC, created_at DESC LIMIT 4");

// Fetch testimonials
$testimonials_query = $conn->query("
    SELECT git.*, gi.media_url 
    FROM gallery_items_translations git
    JOIN gallery_items gi ON git.gallery_item_id = gi.id
    WHERE git.language_id = $language_id AND gi.status = 'active'
    ORDER BY gi.sort_order ASC
    LIMIT 3
");

if (!$testimonials_query || $testimonials_query->num_rows == 0) {
    $testimonials_query = $conn->query("
        SELECT git.*, gi.media_url 
        FROM gallery_items_translations git
        JOIN gallery_items gi ON git.gallery_item_id = gi.id
        WHERE git.language_id = 1 AND gi.status = 'active'
        ORDER BY gi.sort_order ASC
        LIMIT 3
    ");
}

// Fetch gallery items for homepage
$gallery_query = $conn->query("
    SELECT git.*, gi.media_url, gi.media_type, git.link_url as youtube_url
    FROM gallery_items_translations git
    JOIN gallery_items gi ON git.gallery_item_id = gi.id
    WHERE git.language_id = $language_id AND gi.status = 'active'
    ORDER BY gi.sort_order ASC
    LIMIT 6
");

if (!$gallery_query || $gallery_query->num_rows == 0) {
    $gallery_query = $conn->query("
        SELECT git.*, gi.media_url, gi.media_type, git.link_url as youtube_url
        FROM gallery_items_translations git
        JOIN gallery_items gi ON git.gallery_item_id = gi.id
        WHERE git.language_id = 1 AND gi.status = 'active'
        ORDER BY gi.sort_order ASC
        LIMIT 6
    ");
}

// Fetch stats
$stats = [];
$stats['missions'] = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'completed'")->fetch_assoc()['count'] ?? 45;
$stats['countries'] = $conn->query("SELECT COUNT(DISTINCT location) as count FROM events")->fetch_assoc()['count'] ?? 20;
$stats['lives'] = $conn->query("SELECT SUM(amount) as total FROM donations WHERE status = 'completed'")->fetch_assoc()['total'] ?? 25000;
$stats['members'] = $conn->query("SELECT COUNT(*) as count FROM team WHERE status = 'active'")->fetch_assoc()['count'] ?? 12;

// Translations - Indexed array pattern like events.php
$t = [
    1 => [ // English
        'Home', 'Join Us', 'Donate', 'Who We Are', 'A missionary group dedicated to evangelism and humanitarian service', 'Our Mission', 'See More', 'Missions Completed', 'Countries Visited', 'Lives Touched', 'Missionaries', 'Our Team', 'Meet our dedicated missionaries', 'Team members coming soon...', 'View All Team', 'Our Mission', 'Our Vision', 'Gallery', 'View Full Gallery', 'Watch Video', 'Close', 'Previous', 'Next', 'Events & Actions', 'Discover our past and upcoming events', 'See All Events', 'Testimonials', 'Those who experienced the missionary journey and testify their faith', 'Support Our Mission', 'Your generosity helps change lives and spread the love of Christ', 'Make a Donation', 'Secure payment', 'Tax receipt available', 'Contact Us', 'Have a question? Want to join our mission? Write to us!', 'Full Name', 'Email', 'Phone', 'Subject', 'Message', 'Send Message', 'Address', 'Phone', 'Email', 'Follow Us', 'Email Us', 'Call Us', 'Visit Us', 'Send Message', 'Message sent successfully!', 'Mon-Fri: 9AM - 6PM', 'Testimony', 'Missionary', 'No events scheduled at the moment. Check back soon!', 'Coming Soon', 'Our team will be displayed here soon.'
    ],
    2 => [ // Kiswahili
        'Nyumbani', 'Jiunge Nasi', 'Changia', 'Sisi Ni Nani', 'Kikundi cha kimisionari kinachojitolea kwa uinjilisti na huduma ya kibinadamu', 'Dhamira Yetu', 'Ona Zaidi', 'Majukumu Yaliyokamilika', 'Nchi Zilizotembelewa', 'Maisha Yaliyoguswa', 'Wamisionari', 'Timu Yetu', 'Wamisionari wanaoijitolea katika utumishi wa Mungu na jamii', 'Ona Timu Yote', 'Dhamira Yetu', 'Hadhi Yetu', 'Nyumba ya Sanaa', 'Ona Nyumba ya Sanaa Yote', 'Ona Video', 'Funga', 'Iliyopita', 'Ifuatayo', 'Matukio na Vitendo', 'Gundua matukio yetu ya zamani na yajayo', 'Ona Matukio Yote', 'Ushuhuda', 'Wale waliopitia safari ya kimisionari na kushuhudia imani yao', 'Tusaidie katika Dhamira Yetu', 'Ukali wako wa kufadhili unasaidia kuokoa maisha na kusambaza upendo wa Kristo', 'Changia Sasa', 'Malipo salama', 'Risiti ya kodi inapatikana', 'Wasiliana Nasi', 'Una swali? Unataka kujiunga na dhamira yetu? Tuandikie!', 'Jina Kamili', 'Barua Pepe', 'Simu', 'Mada', 'Ujumbe', 'Tuma Ujumbe', 'Anwani', 'Simu', 'Barua Pepe', 'Tufuatilie', 'Tutumie Barua Pepe', 'Pigia Simu', 'Tutembelee', 'Tuma Ujumbe', 'Ujumbe umetumwa kwa mafanikio!', 'Jumatatu-Ijumaa: 9AM - 6PM', 'Ushuhuda', 'Mmissionari', 'Hakuna matukio yaliyopangwa kwa sasa. Rudia hivi karibuni!', 'Inakuja Hivi Karibuni', 'Timu yetu itaonyeshwa hapa hivi karibuni.'
    ],
    3 => [ // French
        'Accueil', 'Nous Rejoindre', 'Faire un Don', 'Qui Sommes-Nous', 'Un groupe missionnaire dédié à l\'évangélisation et au service humanitaire', 'Notre Mission', 'Voir Plus', 'Missions Accomplies', 'Pays Visités', 'Vies Touchées', 'Missionnaires', 'Notre Équipe', 'Des missionnaires dévoués au service de Dieu et des communautés', 'Voir toute l\'équipe', 'Notre Mission', 'Notre Vision', 'Galerie', 'Voir la Galerie Complète', 'Regarder la Vidéo', 'Fermer', 'Précédent', 'Suivant', 'Événements & Actions', 'Découvrez nos événements passés et à venir', 'Voir tous les événements', 'Témoignages', 'Ceux qui ont vécu l\'expérience missionnaire et témoignent de leur foi', 'Soutenez Notre Mission', 'Votre générosité permet de changer des vies et de répandre l\'amour du Christ', 'Faire un Don', 'Paiement sécurisé', 'Reçu fiscal disponible', 'Contactez-Nous', 'Une question ? Envie de rejoindre notre mission ? Écrivez-nous !', 'Nom Complet', 'Email', 'Téléphone', 'Sujet', 'Message', 'Envoyer un message', 'Adresse', 'Téléphone', 'Email', 'Suivez-Nous', 'Email', 'Appelez-Nous', 'Visitez-nous', 'Envoyer un message', 'Message envoyé avec succès!', 'Lun-Ven: 9h00 - 18h00', 'Témoignage', 'Missionnaire', 'Aucun événement prévu pour le moment. Revenez bientôt !', 'Bientôt Disponible', 'Notre équipe sera bientôt affichée ici.'
    ]
];

$l = $t[$language_id] ?? $t[1];

ob_start();
?>
<!DOCTYPE html>
<html lang="<?php echo $language_code; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['site_title'] ?? 'Masaka Initiative'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-gold': '#C9A962',
                        'dark-blue': '#2C3E50',
                        'light-gray': '#F8F9FA'
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-in-out',
                        'slide-up': 'slideUp 0.8s ease-out'
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeIn 0.6s ease-in-out; }
        .slide-up { animation: slideUp 0.8s ease-out; }
        .perspective-1000 { perspective: 1000px; }
        .border-3 { border-width: 3px; }
    </style>
</head>
<body>

<style>
        .hero-slider {
            height: 85vh;
            min-height: 600px;
            position: relative;
            overflow: hidden;
            background: #2C3E50;
        }

    .slides {
        display: flex;
        width: 300%;
        height: 100%;
        transition: transform 0.6s ease;
    }

    .slide {
        width: 33.3333%;
        height: 100%;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .slide .overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(44, 62, 80, 0.85), rgba(44, 62, 80, 0.7));
    }

    .slide .content {
        z-index: 10;
        text-align: center;
        max-width: 900px;
        padding: 0 2rem;
        color: white;
    }

    .slide .content h1 {
        font-size: 3.5rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        letter-spacing: -0.02em;
        text-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }

    .slide .content p {
        font-size: 1.25rem;
        margin-bottom: 2rem;
        opacity: 0.9;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem 2rem;
        background: var(--primary-gold);
        color: white;
        border-radius: 50px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(201, 169, 98, 0.4);
    }

    .btn-primary:hover {
        background: #b8963d;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(201, 169, 98, 0.5);
    }

    .btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem 2rem;
        background: transparent;
        color: white;
        border: 2px solid white;
        border-radius: 50px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background: white;
        color: var(--dark-blue);
    }

    .slider-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        z-index: 20;
    }

    .slider-btn:hover {
        background: var(--primary-gold);
        border-color: var(--primary-gold);
    }

    #prev-slide { left: 2rem; }
    #next-slide { right: 2rem; }

    .dots {
        position: absolute;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 0.75rem;
        z-index: 20;
    }

    .dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255,255,255,0.4);
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
    }

    .dot.active {
        background: var(--primary-gold);
        transform: scale(1.2);
    }

    .section-header {
        text-align: center;
        margin-bottom: 4rem;
    }

    .section-header h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--dark-blue);
        margin-bottom: 1rem;
    }

    .section-header p {
        color: #6b7280;
        font-size: 1.125rem;
        max-width: 600px;
        margin: 0 auto;
    }

    .gold-line {
        width: 60px;
        height: 4px;
        background: var(--primary-gold);
        margin: 1rem auto;
        border-radius: 2px;
    }

    .about-section {
        padding: 6rem 0;
        background: var(--light-gray);
    }

    .about-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4rem;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .about-content h3 {
        font-size: 2rem;
        font-weight: 600;
        color: var(--primary-gold);
        margin-bottom: 1.5rem;
    }

    .about-content p {
        color: #4b5563;
        line-height: 1.8;
        margin-bottom: 2rem;
    }

    .about-image {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(44, 62, 80, 0.15);
    }

    .about-image img {
        width: 100%;
        height: 450px;
        object-fit: cover;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
        max-width: 1000px;
        margin: 4rem auto 0;
        padding: 0 2rem;
    }

    .stat-card {
        text-align: center;
        padding: 2rem;
        background: white;
        border-radius: 8px;
        /* box-shadow: 0 4px 20px rgba(0,0,0,0.08); */
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-gold);
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: #6b7280;
        font-size: 0.9rem;
    }

    .team-section {
        padding: 6rem 0;
        background: white;
    }

    .team-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .team-card {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        text-align: center;
        transition: all 0.3s ease;
        border: 1px solid rgba(201, 169, 98, 0.1);
    }
    
    .team-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 35px rgba(201, 169, 98, 0.15);
        border-color: rgba(201, 169, 98, 0.3);
    }
    
    .team-image-container {
        margin-bottom: 1.5rem;
        perspective: 1000px;
    }

    .team-image {
        width: 140px;
        height: 140px;
        margin: 0 auto;
        border-radius: 20px;
        overflow: hidden;
        position: relative;
        transform-style: preserve-3d;
        transition: transform 0.3s ease;
        border: 3px solid var(--primary-gold);
        box-shadow: 0 8px 20px rgba(201, 169, 98, 0.3);
    }
    
    .team-card:hover .team-image {
        transform: rotateY(5deg) rotateX(-5deg);
    }

    .team-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .team-image img:hover {
        transform: scale(1.05);
    }
    
    .team-content {
        padding: 0;
    }

    .team-card h4 {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--dark-blue);
        margin-bottom: 0.5rem;
        transition: color 0.3s ease;
    }
    
    .team-card:hover h4 {
        color: var(--primary-gold);
    }

    .team-role {
        color: var(--primary-gold) !important;
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 1rem !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .team-bio {
        color: #6b7280 !important;
        font-size: 0.9rem !important;
        line-height: 1.5;
        margin: 0;
        margin-top: 0.5rem;
        color: #6b7280;
    }
    
    /* Mission & Vision Section */
    .mission-vision-section {
        padding: 6rem 0;
        background: var(--light-gray);
    }
    
    .mission-vision-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 2rem;
    }
    
    .mission-card, .vision-card {
        background: white;
        padding: 2.5rem;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .mission-card:hover, .vision-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 40px rgba(201, 169, 98, 0.15);
    }
    
    .mission-image, .vision-image {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
        border-radius: 16px;
        overflow: hidden;
        border: 2px solid var(--primary-gold);
    }
    
    .mission-image img, .vision-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .icon-wrapper {
        width: 80px;
        height: 80px;
        margin: 0 auto 2rem;
        background: var(--primary-gold);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    
    .mission-card h3, .vision-card h3 {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--dark-blue);
        margin-bottom: 1rem;
    }
    
    .mission-card p, .vision-card p {
        color: #4b5563;
        line-height: 1.6;
    }
    
    /* Gallery Section */
    .gallery-section {
        padding: 6rem 0;
        background: white;
    }
    
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }
    
    .gallery-item {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        transition: transform 0.3s ease;
        aspect-ratio: 1;
    }
    
    .gallery-item:hover {
        transform: scale(1.05);
    }
    
    .gallery-image {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
    
    .gallery-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0,0,0,0.8));
        color: white;
        padding: 1.5rem;
        transform: translateY(100%);
        transition: transform 0.3s ease;
    }
    
    .gallery-item:hover .gallery-overlay {
        transform: translateY(0);
    }
    
    .gallery-overlay h4 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .video-btn {
        background: var(--primary-gold);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.9rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background 0.3s ease;
    }
    
    .video-btn:hover {
        background: #b8963d;
    }
    
    /* Lightbox */
    .lightbox {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.95);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }
    
    .lightbox img {
        max-width: 90%;
        max-height: 80vh;
        border-radius: 8px;
    }
    
    .lightbox-close {
        position: absolute;
        top: 20px;
        right: 40px;
        color: white;
        font-size: 40px;
        cursor: pointer;
    }
    
    .lightbox-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: var(--primary-gold);
        color: white;
        border: none;
        padding: 1rem;
        cursor: pointer;
        border-radius: 50%;
        font-size: 1rem;
        transition: background 0.3s ease;
    }
    
    .lightbox-nav:hover {
        background: #b8963d;
    }
    
    .lightbox-nav.prev {
        left: 20px;
    }
    
    .lightbox-nav.next {
        right: 20px;
    }
    
    #lightbox-caption {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        color: white;
        text-align: center;
        font-size: 1.1rem;
    }

    .events-section {
        padding: 6rem 0;
        background: var(--dark-blue);
        color: white;
    }

    .events-section .section-header h2,
    .events-section .section-header p {
        color: white;
    }

    .events-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .event-card {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .event-card:hover {
        background: rgba(255,255,255,0.1);
        transform: translateY(-4px);
    }

    .event-image {
        height: 200px;
        background-size: cover;
        background-position: center;
    }

    .event-content {
        padding: 1.5rem;
    }

    .event-content h4 {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }

    .event-content p {
        color: rgba(255,255,255,0.7);
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .event-date {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--primary-gold);
        font-weight: 500;
        font-size: 0.9rem;
    }

    .donate-section {
        padding: 6rem 0;
        background: linear-gradient(135deg, var(--dark-blue), #34495e);
        color: white;
        text-align: center;
    }

    .donate-section h2 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .donate-section p {
        opacity: 0.9;
        max-width: 600px;
        margin: 0 auto 3rem;
    }

    .amount-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 2rem;
    }

    .amount-btn {
        padding: 0.75rem 1.5rem;
        border: 2px solid rgba(255,255,255,0.3);
        background: transparent;
        color: white;
        border-radius: 50px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .amount-btn:hover,
    .amount-btn.active {
        background: var(--primary-gold);
        border-color: var(--primary-gold);
    }

    .contact-section {
        padding: 6rem 0;
        background: var(--light-gray);
    }

    .contact-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 4rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .contact-form {
        background: white;
        padding: 2.5rem;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        font-weight: 500;
        color: var(--dark-blue);
        margin-bottom: 0.5rem;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary-gold);
    }

    .contact-info {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .info-card {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .info-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: var(--primary-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }

    .info-content h4 {
        font-weight: 600;
        color: var(--dark-blue);
        margin-bottom: 0.25rem;
    }

    .info-content p {
        color: #6b7280;
        font-size: 0.9rem;
    }

    .fade-in {
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.6s ease;
    }

    .fade-in.visible {
        opacity: 1;
        transform: translateY(0);
    }

    @media (max-width: 1024px) {
        .about-grid,
        .contact-grid {
            grid-template-columns: 1fr;
        }
        .team-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .mission-vision-grid {
            grid-template-columns: 1fr;
        }
        .gallery-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .events-grid {
            grid-template-columns: 1fr;
        }
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .hero-slider {
            height: 70vh;
            min-height: 500px;
        }
        .slide .content h1 {
            font-size: 2rem;
        }
        .slide .content p {
            font-size: 1rem;
        }
        .section-header h2 {
            font-size: 1.75rem;
        }
        .team-grid,
        .events-grid,
        .stats-grid {
            grid-template-columns: 1fr;
        }
        .slider-btn {
            display: none;
        }
    }
</style>

<!-- HERO SLIDER -->
<section class="hero-slider">
    <div class="slides" id="slides">
        <?php if ($slides_query && $slides_query->num_rows > 0): ?>
            <?php while ($slide = $slides_query->fetch_assoc()): ?>
                <div class="slide" style="background-image: url('../<?php echo $slide['image_url']; ?>'); background-size: cover; background-position: center;">
                    <div class="overlay"></div>
                    <div class="content">
                        <h1><?php echo htmlspecialchars($slide['title']); ?></h1>
                        <p><?php echo htmlspecialchars($slide['content']); ?></p>
                        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                            <a href="<?php echo getLanguageUrl('contact.php'); ?>" class="btn-primary"><?php echo $l[1]; ?></a>
                            <a href="<?php echo getLanguageUrl('donations.php'); ?>" class="btn-secondary"><?php echo $l[2]; ?></a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="slide" style="background: linear-gradient(135deg, var(--dark-blue), #34495e);">
                <div class="overlay"></div>
                <div class="content">
                    <h1>Masaka Initiative</h1>
                    <p><?php echo $l[4]; ?></p>
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="<?php echo getLanguageUrl('contact.php'); ?>" class="btn-primary"><?php echo $l[1]; ?></a>
                        <a href="<?php echo getLanguageUrl('donations.php'); ?>" class="btn-secondary"><?php echo $l[2]; ?></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <button id="prev-slide" class="slider-btn" style="left: 2rem;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </button>
    <button id="next-slide" class="slider-btn" style="right: 2rem;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </button>

    <div class="dots" id="dots">
        <?php 
        $total_slides = $slides_query ? $slides_query->num_rows : 1;
        for ($i = 0; $i < max(1, $total_slides); $i++): 
        ?>
            <button class="dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></button>
        <?php endfor; ?>
    </div>
</section>

<!-- ABOUT SECTION -->
<section class="about-section">
    <div class="section-header">
        <h2><?php echo $l[3]; ?></h2>
        <div class="gold-line"></div>
        <p><?php echo $l[4]; ?></p>
    </div>

    <div class="about-grid">
        <div class="about-content fade-in">
            <h3><?php echo htmlspecialchars($about['mission_title'] ?? $l[6]); ?></h3>
            <p><?php echo nl2br(htmlspecialchars($about['content'] ?? '')); ?></p>
            <a href="<?php echo getLanguageUrl('about.php'); ?>" class="btn-primary" style="padding: 0.75rem 1.5rem; font-size: 0.9rem;"><?php echo $l[6]; ?></a>
        </div>
        <div class="about-image fade-in">
            <img src="../<?php echo $about['image_url'] ?? 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=1200'; ?>" alt="About" onerror="this.src='https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=1200'">
        </div>
    </div>

 <div class="stats-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8 max-w-6xl mx-auto">
    <div class="stat-card fade-in text-center bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
        <div class="stat-number text-4xl lg:text-5xl font-bold text-dark-blue mb-2" data-target="<?php echo $stats['missions']; ?>">0</div>
        <div class="stat-label text-gray-600 font-medium"><?php echo $l[7]; ?></div>
    </div>
    <div class="stat-card fade-in text-center bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
        <div class="stat-number text-4xl lg:text-5xl font-bold text-dark-blue mb-2" data-target="<?php echo $stats['countries']; ?>">0</div>
        <div class="stat-label text-gray-600 font-medium"><?php echo $l[8]; ?></div>
    </div>
    <div class="stat-card fade-in text-center bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
        <div class="stat-number text-4xl lg:text-5xl font-bold text-dark-blue mb-2" data-target="<?php echo $stats['lives']; ?>">0</div>
        <div class="stat-label text-gray-600 font-medium"><?php echo $l[9]; ?></div>
    </div>
    <div class="stat-card fade-in text-center bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
        <div class="stat-number text-4xl lg:text-5xl font-bold text-dark-blue mb-2" data-target="<?php echo $stats['members']; ?>">0</div>
        <div class="stat-label text-gray-600 font-medium"><?php echo $l[10]; ?></div>
    </div>
</div>
</section>

<!-- MISSION & VISION SECTION -->
<section class="py-14 bg-light-gray">
    <div class="max-w-6xl mx-auto px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <h2 class="text-4xl lg:text-5xl font-bold text-dark-blue mb-4">Our Mission & Vision</h2>
            <div class="w-24 h-1 bg-primary-gold mx-auto rounded-full"></div>
            <p class="text-gray-600 mt-6 max-w-2xl mx-auto">Guided by faith and purpose, we strive to make a lasting impact in our communities and beyond.</p>
        </div>

        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">
            <!-- Mission Card -->
            <div class="group bg-white rounded-lg shadow-lg hover:shadow-lg transition-all duration-300 hover:-translate-y-2 overflow-hidden">
                <!-- Mission Image -->
                <?php if (!empty($about['mission_image'])): ?>
                    <div class="relative h-56 w-full overflow-hidden bg-gray-100">
                        <img 
                            src="../<?php echo htmlspecialchars($about['mission_image']); ?>" 
                            alt="Mission illustration" 
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            onerror="this.src='https://placehold.co/800x400/f3f4f6/9ca3af?text=Mission'"
                            loading="lazy"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>
                    </div>
                <?php endif; ?>
                
                <!-- Mission Content -->
                <div class="p-8">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-primary-gold/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-gold" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                            </svg>
                        </div>
                        <span class="text-primary-gold font-semibold text-sm uppercase tracking-wider">Our Commitment</span>
                    </div>
                    <h4 class="text-xl lg:text-xl font-bold text-dark-blue mb-4 group-hover:text-primary-gold transition-colors duration-300">
                        <?php echo htmlspecialchars($about['mission_title'] ?? $l[6] ?? 'Our Mission'); ?>
                    </h4>
                    <div class="w-12 h-0.5 bg-primary-gold/40 mb-5 rounded-full"></div>
                    <p class="text-gray-600 leading-relaxed text-base lg:text-lg">
                        <?php 
                            $mission_text = trim($about['mission_text'] ?? '');
                            if (empty($mission_text)) {
                                $mission_text = 'To spread the love of Christ through evangelism, humanitarian service, and community development, making a positive impact in the lives of people around the world.';
                            }
                            echo nl2br(htmlspecialchars($mission_text));
                        ?>
                    </p>
                </div>
            </div>

            <!-- Vision Card -->
            <div class="group bg-white rounded-lg shadow-lg hover:shadow-lg transition-all duration-300 hover:-translate-y-2 overflow-hidden">
                <!-- Vision Image -->
                <?php if (!empty($about['vision_image'])): ?>
                    <div class="relative h-56 w-full overflow-hidden bg-gray-100">
                        <img 
                            src="../<?php echo htmlspecialchars($about['vision_image']); ?>" 
                            alt="Vision illustration" 
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            onerror="this.src='https://placehold.co/800x400/f3f4f6/9ca3af?text=Vision'"
                            loading="lazy"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>
                    </div>
                <?php endif; ?>
                
                <!-- Vision Content -->
                <div class="p-8">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-primary-gold/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-gold" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 4C7 4 2.73 7.11 1 11.5 2.73 15.89 7 19 12 19s9.27-3.11 11-7.5C21.27 7.11 17 4 12 4zm0 12.5c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                            </svg>
                        </div>
                        <span class="text-primary-gold font-semibold text-sm uppercase tracking-wider">Our Aspiration</span>
                    </div>
                    <h4 class="text-xl lg:text-xl font-bold text-dark-blue mb-4 group-hover:text-primary-gold transition-colors duration-300">
                        <?php echo htmlspecialchars($about['vision_title'] ?? $l[8] ?? 'Our Vision'); ?>
                    </h4>
                    <div class="w-12 h-0.5 bg-primary-gold/40 mb-5 rounded-full"></div>
                    <p class="text-gray-600 leading-relaxed text-base lg:text-lg">
                        <?php 
                            $vision_text = trim($about['vision_text'] ?? '');
                            if (empty($vision_text)) {
                                $vision_text = 'To be a beacon of hope and transformation, creating communities where faith flourishes, needs are met, and people experience the unconditional love of God.';
                            }
                            echo nl2br(htmlspecialchars($vision_text));
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- EVENTS SECTION -->
<section class="events-section py-24 bg-light-gray">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
<!-- Section Header - Heading and button on the same line -->
<div class="flex flex-wrap justify-between items-center gap-4 mb-16">
    <h2 class="text-4xl lg:text-5xl font-bold text-white">
        <?php echo htmlspecialchars($l[22]); ?>
    </h2>
    <div class="flex-shrink-0">
        <a href="<?php echo htmlspecialchars(getLanguageUrl('events.php')); ?>" 
           class="btn-secondary inline-flex items-center gap-2 bg-transparent border-2 border-primary-gold text-primary-gold font-semibold px-6 py-3 rounded-lg hover:bg-primary-gold hover:text-dark-blue transition-all duration-300 whitespace-nowrap">
            <span><?php echo htmlspecialchars($l[23]); ?></span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
    </div>
</div>



        <!-- Events Grid -->
        <div class="events-grid grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if ($events_query && $events_query->num_rows > 0): ?>
                <?php while ($event = $events_query->fetch_assoc()): ?>
                    <div class="event-card group bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-2 fade-in">
                        <!-- Event Image with Click to Expand -->
                        <div class="event-image-wrapper relative overflow-hidden cursor-pointer h-56" 
                             onclick="openImageModal('../<?php echo htmlspecialchars($event['image_url'] ?? ''); ?>', '<?php echo htmlspecialchars($event['title']); ?>')">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10"></div>
                            <img 
                                src="../<?php echo htmlspecialchars($event['image_url'] ?? ''); ?>" 
                                alt="<?php echo htmlspecialchars($event['title']); ?>"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                onerror="this.src='https://placehold.co/800x600/f3f4f6/9ca3af?text=Event+Image'"
                                loading="lazy"
                            >
                            <!-- Zoom Icon Overlay -->
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-20">
                                <div class="bg-black/50 rounded-full p-3">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Event Content -->
                        <div class="event-content p-6">
                            <h4 class="text-xl font-bold text-white mb-3 group-hover:text-primary-gold transition-colors duration-300 line-clamp-1">
                                <?php echo htmlspecialchars($event['title']); ?>
                            </h4>
                            
                            <p class="text-gray-600 text-sm leading-relaxed mb-4 line-clamp-3">
                                <?php echo htmlspecialchars(substr($event['description'] ?? '', 0, 120)) . '...'; ?>
                            </p>
                            
                            <!-- Event Date -->
                            <div class="event-date flex items-center gap-2 text-gray-500 text-sm mb-4">
                                <svg class="w-4 h-4 text-primary-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                <span><?php echo date('d M Y', strtotime($event['event_date'])); ?></span>
                            </div>
                            
                            <!-- YouTube Button (if video URL exists) -->
                            <?php if (!empty($event['video_url'])): ?>
                                <button onclick="openVideoModal('<?php echo htmlspecialchars($event['video_url']); ?>', '<?php echo htmlspecialchars($event['title']); ?>')"
                                        class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg transition-all duration-300 text-sm">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M10 15l5-3-5-3v6zm9-11H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2zm0 14H5V6h14v12z"/>
                                    </svg>
                                    Watch on YouTube
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="col-span-full">
                    <div class="text-center py-16 px-6 bg-white rounded-2xl">
                        <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <p class="text-gray-500 text-lg"><?php echo $l[35]; ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Image Lightbox Modal -->
<div id="imageModal" class="fixed inset-0 bg-black/90 z-50 hidden items-center justify-center p-4" onclick="closeImageModal()">
    <div class="relative max-w-5xl w-full max-h-[90vh]" onclick="event.stopPropagation()">
        <button onclick="closeImageModal()" class="absolute -top-12 right-0 text-white hover:text-primary-gold transition-colors">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <img id="modalImage" src="" alt="" class="w-full h-auto rounded-lg shadow-2xl">
        <p id="modalCaption" class="text-white text-center mt-4 text-lg"></p>
    </div>
</div>

<!-- YouTube Video Modal -->
<div id="videoModal" class="fixed inset-0 bg-black/90 z-50 hidden items-center justify-center p-4" onclick="closeVideoModal()">
    <div class="relative w-full max-w-5xl" onclick="event.stopPropagation()">
        <button onclick="closeVideoModal()" class="absolute -top-12 right-0 text-white hover:text-primary-gold transition-colors">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="relative pt-[56.25%]">
            <iframe id="videoFrame" class="absolute top-0 left-0 w-full h-full rounded-lg shadow-2xl" 
                    frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen>
            </iframe>
        </div>
        <p id="videoCaption" class="text-white text-center mt-4 text-lg"></p>
    </div>
</div>

<!-- Modal JavaScript -->
<script>
// Image Modal Functions
function openImageModal(imageUrl, title) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    const caption = document.getElementById('modalCaption');
    
    modalImg.src = imageUrl;
    caption.textContent = title;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    document.getElementById('modalImage').src = '';
    document.body.style.overflow = 'auto';
}

// YouTube Video Modal Functions
function openVideoModal(videoUrl, title) {
    const modal = document.getElementById('videoModal');
    const iframe = document.getElementById('videoFrame');
    const caption = document.getElementById('videoCaption');
    
    // Extract YouTube video ID from various URL formats
    let videoId = '';
    if (videoUrl.includes('youtube.com/watch?v=')) {
        videoId = videoUrl.split('v=')[1].split('&')[0];
    } else if (videoUrl.includes('youtu.be/')) {
        videoId = videoUrl.split('youtu.be/')[1].split('?')[0];
    } else if (videoUrl.includes('youtube.com/embed/')) {
        videoId = videoUrl.split('embed/')[1].split('?')[0];
    } else {
        // If direct embed URL or ID
        videoId = videoUrl;
    }
    
    iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0&modestbranding=1`;
    caption.textContent = title;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeVideoModal() {
    const modal = document.getElementById('videoModal');
    const iframe = document.getElementById('videoFrame');
    
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    iframe.src = ''; // Stop video playback
    document.body.style.overflow = 'auto';
}

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
        closeVideoModal();
    }
});
</script>

<!-- Optional: Add CSS for line-clamp if not using Tailwind typography plugin -->
<style>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>



<!-- TEAM SECTION -->
<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-20">
            <h2 class="text-4xl lg:text-5xl font-bold text-dark-blue mb-4">
                <?php echo $l[11]; ?>
            </h2>
            <div class="w-24 h-1 bg-primary-gold mx-auto mb-6 rounded-full"></div>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto leading-relaxed">
                <?php echo $l[12]; ?>
            </p>
        </div>

        <!-- Team Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
            <?php if ($team_query && $team_query->num_rows > 0): ?>
                <?php while ($member = $team_query->fetch_assoc()): ?>
                    <div class="group bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-2 border border-gray-100 overflow-hidden">
                        <!-- Image Container -->
                        <div class="relative aspect-[4/3] w-full overflow-hidden bg-gray-100">
                            <img 
                                src="../<?php echo htmlspecialchars($member['image_url'] ?? ''); ?>" 
                                alt="<?php echo htmlspecialchars($member['name']); ?>" 
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($member['name']); ?>&background=C9A962&color=fff&size=400&bold=true'"
                                loading="lazy"
                            >
                            <!-- Optional: Overlay gradient -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/100 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-6 lg:p-8">
                            <h3 class="text-xl lg:text-2xl font-bold text-dark-blue group-hover:text-primary-gold transition-colors duration-300 mb-1">
                                <?php echo htmlspecialchars($member['name']); ?>
                            </h3>
                            <p class="text-primary-gold font-semibold text-sm uppercase tracking-wider mb-4">
                                <?php echo htmlspecialchars($member['role']); ?>
                            </p>
                            <p class="text-gray-600 text-sm leading-relaxed">
                                <?php 
                                    $bio = trim($member['bio'] ?? '');
                                    if (!empty($bio)) {
                                        echo htmlspecialchars(strlen($bio) > 100 ? substr($bio, 0, 100) . '...' : $bio);
                                    } else {
                                        echo '<span class="italic text-gray-400">No bio available</span>';
                                    }
                                ?>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="col-span-full">
                    <div class="text-center py-16 px-6 bg-gray-50 rounded-2xl">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2"><?php echo $l[36]; ?></h3>
                        <p class="text-gray-500 max-w-sm mx-auto">Our team member profiles are being prepared. Please check back soon.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
    <button class="lightbox-nav prev" onclick="navigateLightbox(-1)"><?php echo $l[20]; ?></button>
    <button class="lightbox-nav next" onclick="navigateLightbox(1)"><?php echo $l[21]; ?></button>
    <img id="lightbox-img" src="" alt="">
    <div id="lightbox-caption"></div>
</div>

<!-- DONATE SECTION -->
<section class="donate-section">
    <h2><?php echo $l[29]; ?></h2>
    <p><?php echo $l[30]; ?></p>

    <div class="amount-buttons">
        <button class="amount-btn">$20</button>
        <button class="amount-btn">$50</button>
        <button class="amount-btn">$100</button>
        <button class="amount-btn">$200</button>
    </div>

    <a href="<?php echo getLanguageUrl('donations.php'); ?>" class="btn-primary" style="padding: 1rem 3rem;">
        <?php echo $l[31]; ?>
    </a>

    <div style="margin-top: 2rem; font-size: 0.85rem; opacity: 0.8; display: flex; gap: 2rem; justify-content: center; flex-wrap: wrap;">
        <span style="display: flex; align-items: center; gap: 0.5rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
            <?php echo $l[32]; ?>
        </span>
        <span style="display: flex; align-items: center; gap: 0.5rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            <?php echo $l[33]; ?>
        </span>
    </div>
</section>

<!-- CONTACT SECTION -->
<section class="contact-section py-24 bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <h2 class="text-4xl lg:text-5xl font-bold text-dark-blue mb-4">
                <?php echo htmlspecialchars($l[34]); ?>
            </h2>
            <div class="w-24 h-1 bg-primary-gold mx-auto rounded-full mb-6"></div>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">
                <?php echo htmlspecialchars($l[35]); ?>
            </p>
        </div>

        

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
                        <h4 class="text-lg font-semibold text-dark-blue mb-2"><?php echo $l[38]; ?></h4>
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
                        <h4 class="text-lg font-semibold text-dark-blue mb-2"><?php echo $l[39]; ?></h4>
                        <p class="text-gray-600">
                            <a href="tel:<?php echo htmlspecialchars($contact_info['phone']); ?>" class="hover:text-primary-gold transition-colors duration-300">
                                <?php echo htmlspecialchars($contact_info['phone']); ?>
                            </a>
                        </p>
                        <p class="text-sm text-gray-500 mt-1"><?php echo $l[40]; ?></p>
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
                        <h4 class="text-lg font-semibold text-dark-blue mb-2"><?php echo $l[41]; ?></h4>
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
                <h4 class="text-lg font-semibold text-dark-blue mb-4"><?php echo $l[42]; ?></h4>
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
            <h3 class="text-2xl font-bold text-dark-blue mb-6"><?php echo $l[43]; ?></h3>
            
            <?php if (isset($success_msg) && $success_msg): ?>
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                    <?php echo $l[44]; ?>
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
// Slider functionality
(function() {
    const slidesEl = document.getElementById('slides');
    const dots = Array.from(document.querySelectorAll('.dot'));
    const total = dots.length;
    let index = 0;
    let slideInterval;
    
    if (total > 1 && slidesEl) {
        function showSlide(i) {
            index = (i + total) % total;
            slidesEl.style.transform = `translateX(-${index * (100/total)}%)`;
            dots.forEach((d, idx) => d.classList.toggle('active', idx === index));
        }
        
        const prevBtn = document.getElementById('prev-slide');
        const nextBtn = document.getElementById('next-slide');
        
        if (prevBtn) prevBtn.addEventListener('click', () => { showSlide(index - 1); resetInterval(); });
        if (nextBtn) nextBtn.addEventListener('click', () => { showSlide(index + 1); resetInterval(); });
        
        dots.forEach(d => {
            d.addEventListener('click', (e) => {
                showSlide(parseInt(d.dataset.index));
                resetInterval();
            });
        });
        
        function startInterval() { slideInterval = setInterval(() => showSlide(index + 1), 5000); }
        function resetInterval() { clearInterval(slideInterval); startInterval(); }
        startInterval();
    }
})();

// Stats counter animation
const statElements = Array.from(document.querySelectorAll('.stat-number'));
const statsObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const el = entry.target;
            const target = parseInt(el.dataset.target) || 0;
            let curr = 0;
            const step = Math.max(1, Math.floor(target / 60));
            const timer = setInterval(() => {
                curr += step;
                if (curr >= target) {
                    el.textContent = target.toLocaleString() + (target > 1000 ? '+' : '');
                    clearInterval(timer);
                } else {
                    el.textContent = curr.toLocaleString();
                }
            }, 25);
            statsObserver.unobserve(el);
        }
    });
}, { threshold: 0.5 });
statElements.forEach(e => statsObserver.observe(e));

// Donation amount selection
document.querySelectorAll('.amount-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('bg-primary-gold', 'text-white'));
        btn.classList.add('bg-primary-gold', 'text-white');
    });
});

// Fade-in animation
const fadeElements = document.querySelectorAll('.fade-in');
const fadeObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) entry.target.classList.add('visible');
    });
}, { threshold: 0.1 });
fadeElements.forEach(el => fadeObserver.observe(el));

// Gallery lightbox functionality
let currentImageIndex = 0;
let galleryImages = [];

// Collect gallery images
function initGallery() {
    const items = document.querySelectorAll('.gallery-item');
    galleryImages = Array.from(items).map(item => ({
        src: item.querySelector('.gallery-image').style.backgroundImage.slice(5, -2),
        title: item.querySelector('.gallery-overlay h4')?.textContent || '',
        type: item.dataset.type,
        youtube: item.dataset.youtube
    }));
}

function openLightbox(index) {
    currentImageIndex = index;
    const lightbox = document.getElementById('lightbox');
    const img = document.getElementById('lightbox-img');
    const caption = document.getElementById('lightbox-caption');
    
    const current = galleryImages[index];
    
    if (current.type === 'video' && current.youtube) {
        // For videos, show YouTube embed
        img.style.display = 'none';
        const iframe = document.createElement('iframe');
        iframe.src = current.youtube.replace('watch?v=', 'embed/') + '?autoplay=1';
        iframe.width = '800';
        iframe.height = '450';
        iframe.style.borderRadius = '8px';
        img.parentNode.insertBefore(iframe, img.nextSibling);
    } else {
        img.src = current.src;
        img.style.display = 'block';
    }
    
    caption.textContent = current.title;
    lightbox.style.display = 'flex';
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    lightbox.style.display = 'none';
    
    // Remove any iframes
    const iframes = lightbox.querySelectorAll('iframe');
    iframes.forEach(iframe => iframe.remove());
}

function navigateLightbox(direction) {
    currentImageIndex += direction;
    if (currentImageIndex < 0) currentImageIndex = galleryImages.length - 1;
    if (currentImageIndex >= galleryImages.length) currentImageIndex = 0;
    
    const img = document.getElementById('lightbox-img');
    const caption = document.getElementById('lightbox-caption');
    
    // Remove any existing iframes
    const iframes = document.querySelectorAll('.lightbox iframe');
    iframes.forEach(iframe => iframe.remove());
    
    const current = galleryImages[currentImageIndex];
    
    if (current.type === 'video' && current.youtube) {
        img.style.display = 'none';
        const iframe = document.createElement('iframe');
        iframe.src = current.youtube.replace('watch?v=', 'embed/') + '?autoplay=1';
        iframe.width = '800';
        iframe.height = '450';
        iframe.style.borderRadius = '8px';
        img.parentNode.insertBefore(iframe, img.nextSibling);
    } else {
        img.src = current.src;
        img.style.display = 'block';
    }
    
    caption.textContent = current.title;
}

// Initialize gallery when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initGallery();
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (document.getElementById('lightbox').style.display === 'flex') {
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') navigateLightbox(-1);
            if (e.key === 'ArrowRight') navigateLightbox(1);
        }
    });
});
</script>

<?php
$content = ob_get_clean();
$page_titles = [
    1 => 'Home - Masaka Initiative',
    2 => 'Nyumbani - Masaka Initiative',
    3 => 'Accueil - Masaka Initiative'
];
$page_title = $page_titles[$language_id] ?? $page_titles[1];
require_once 'layout.php';
?>