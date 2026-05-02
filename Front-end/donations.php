<?php
require_once __DIR__ . '/lang_init.php';
require_once __DIR__ . '/../API/config.php';

// Fetch settings
$settings_query = $conn->query("SELECT * FROM settings LIMIT 1");
$settings = $settings_query->fetch_assoc();

// Translation array
$text = [
    1 => [
        'page_title' => 'Make a Donation',
        'hero_title' => 'Make a Donation',
        'hero_subtitle' => 'Support our mission with your contribution',
        'support_mission' => 'Support Our Mission',
        'contribution_text' => 'Your contribution helps us make a difference in the world',
        'amount' => 'Amount',
        'full_name' => 'Full Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'message' => 'Message (Optional)',
        'payment_method' => 'Payment Method',
        'credit_card' => 'Credit Card',
        'mobile_money' => 'Mobile Money',
        'bank_transfer' => 'Bank Transfer',
        'submit' => 'Submit Donation',
        'secure' => 'Secure Payment',
        'tax_receipt' => 'Tax Receipt Available'
    ],
    2 => [
        'page_title' => 'Changia',
        'hero_title' => 'Changia',
        'hero_subtitle' => 'Saidia dhamira yetu kwa mchango wako',
        'support_mission' => 'Support Dhamira Yetu',
        'contribution_text' => 'Mchango wako unatusaidia kufanya tofauti duniani',
        'amount' => 'Kiasi',
        'full_name' => 'Jina Kamili',
        'email' => 'Barua Pepe',
        'phone' => 'Simu',
        'message' => 'Ujumbe (Si lazima)',
        'payment_method' => 'Njia ya Malipo',
        'credit_card' => 'Kadi ya Mkopo',
        'mobile_money' => 'Pesa ya Simu',
        'bank_transfer' => 'Benki',
        'submit' => 'Tuma Mchango',
        'secure' => 'Malipo Salama',
        'tax_receipt' => 'Risiti ya Kodi Inapatikana'
    ],
    3 => [
        'page_title' => 'Faire un Don',
        'hero_title' => 'Faire un Don',
        'hero_subtitle' => 'Soutenez notre mission avec votre contribution',
        'support_mission' => 'Soutenez Notre Mission',
        'contribution_text' => 'Votre contribution nous aide à faire la différence dans le monde',
        'amount' => 'Montant',
        'full_name' => 'Nom Complet',
        'email' => 'Email',
        'phone' => 'Téléphone',
        'message' => 'Message (Optionnel)',
        'payment_method' => 'Mode de Paiement',
        'credit_card' => 'Carte de Crédit',
        'mobile_money' => 'Mobile Money',
        'bank_transfer' => 'Virement',
        'submit' => 'Soumettre',
        'secure' => 'Paiement Sécurisé',
        'tax_receipt' => 'Reçu Fiscal Disponible'
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


<!-- Donate Section -->
<section class="py-24 bg-light-gray">
    <div class="max-w-4xl mx-auto px-6 lg:px-8">
      

        <!-- Donation Card -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h3 class="text-2xl font-bold text-dark-blue text-center mb-2">
                <?php echo $t['hero_title']; ?>
            </h3>
            <p class="text-gray-600 text-center mb-8">
                <?php echo $t['contribution_text']; ?>
            </p>
            
            <form method="POST" action="process_donation.php" class="space-y-6">
                <!-- Amount Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-4">
                        <?php echo $t['amount']; ?> *
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4">
                        <button type="button" onclick="setAmount(25)" class="amount-btn py-3 px-4 border-2 border-gray-300 rounded-lg hover:border-primary-gold hover:bg-primary-gold hover:text-white transition-all duration-300 font-medium">
                            $25
                        </button>
                        <button type="button" onclick="setAmount(50)" class="amount-btn py-3 px-4 border-2 border-gray-300 rounded-lg hover:border-primary-gold hover:bg-primary-gold hover:text-white transition-all duration-300 font-medium">
                            $50
                        </button>
                        <button type="button" onclick="setAmount(100)" class="amount-btn py-3 px-4 border-2 border-primary-gold bg-primary-gold text-white rounded-lg transition-all duration-300 font-medium">
                            $100
                        </button>
                        <button type="button" onclick="setAmount(250)" class="amount-btn py-3 px-4 border-2 border-gray-300 rounded-lg hover:border-primary-gold hover:bg-primary-gold hover:text-white transition-all duration-300 font-medium">
                            $250
                        </button>
                        <button type="button" onclick="setAmount(500)" class="amount-btn py-3 px-4 border-2 border-gray-300 rounded-lg hover:border-primary-gold hover:bg-primary-gold hover:text-white transition-all duration-300 font-medium">
                            $500
                        </button>
                    </div>
                    <input type="number" id="amount" name="amount" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-gold focus:border-transparent transition-all duration-300"
                           placeholder="Custom amount" min="1" step="0.01">
                </div>
                
                <!-- Personal Information -->
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo $t['full_name']; ?> *
                        </label>
                        <input type="text" id="full_name" name="full_name" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-gold focus:border-transparent transition-all duration-300"
                               placeholder="<?php echo $t['full_name']; ?>">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo $t['email']; ?> *
                        </label>
                        <input type="email" id="email" name="email" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-gold focus:border-transparent transition-all duration-300"
                               placeholder="email@example.com">
                    </div>
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo $t['phone']; ?>
                    </label>
                    <input type="tel" id="phone" name="phone" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-gold focus:border-transparent transition-all duration-300"
                           placeholder="<?php echo $t['phone']; ?>">
                </div>
                
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo $t['message']; ?>
                    </label>
                    <textarea id="message" name="message" rows="4" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-gold focus:border-transparent transition-all duration-300 resize-none"
                              placeholder="<?php echo $t['message']; ?>"></textarea>
                </div>
                
                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo $t['payment_method']; ?> *
                    </label>
                    <div class="grid md:grid-cols-3 gap-4">
                        <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-primary-gold transition-all duration-300">
                            <input type="radio" name="payment_method" value="credit_card" required class="mr-3">
                            <span><?php echo $t['credit_card']; ?></span>
                        </label>
                        <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-primary-gold transition-all duration-300">
                            <input type="radio" name="payment_method" value="mobile_money" required class="mr-3">
                            <span><?php echo $t['mobile_money']; ?></span>
                        </label>
                        <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-primary-gold transition-all duration-300">
                            <input type="radio" name="payment_method" value="bank_transfer" required class="mr-3">
                            <span><?php echo $t['bank_transfer']; ?></span>
                        </label>
                    </div>
                </div>
                
                <input type="hidden" name="lang" value="<?php echo $lang_code; ?>">
                
                <!-- Submit Button -->
                <div class="text-center pt-4">
                    <button type="submit" class="bg-primary-gold hover:bg-primary-gold/90 text-white font-bold py-4 px-8 rounded-lg transition-all duration-300 transform hover:-translate-y-1 text-lg">
                        <?php echo $t['submit']; ?>
                    </button>
                    <p class="text-sm text-gray-500 mt-3 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <?php echo $t['secure']; ?>
                    </p>
                    <p class="text-xs text-gray-400 mt-2">
                        <?php echo $t['tax_receipt']; ?>
                    </p>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
function setAmount(amount) {
    document.getElementById('amount').value = amount;
    // Update button styles
    document.querySelectorAll('.amount-btn').forEach(btn => {
        btn.classList.remove('border-primary-gold', 'bg-primary-gold', 'text-white');
        btn.classList.add('border-gray-300');
    });
    event.target.classList.remove('border-gray-300');
    event.target.classList.add('border-primary-gold', 'bg-primary-gold', 'text-white');
}

// Fade in animation
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
