<?php
$title = 'Dashboard';

ob_start();
?>

<div class="rounded-xl p-4 bg-white shadow-smd border">
    <!-- Quick Access -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold">Quick Access</h3>
        <div class="text-gray-400 text-sm">...</div>
    </div>

    <!-- TOP TEXT -->
    <div class="text-sm text-gray-500 mb-3">
        Dashboard › <span class="font-semibold text-gray-700">Donations</span>
    </div>



    <table class="w-full border border-gray-300 text-sm text-gray-700">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 border">Name of Donater</th>
                <th class="p-2 border">Email</th>
                <th class="p-2 border">Téléphone</th>
                <th class="p-2 border">Amount</th>
                <th class="p-2 border w-28">Actions</th>
            </tr>
        </thead>

        <tbody>
            <tr class="border-b">

                <td class="p-2 border">
                    Jean Claude Nduwimana
                </td>

                <td class="p-2 border">
                    jeanclaude@example.com
                </td>

                <td class="p-2 border">
                    +257 79 000 000
                </td>

                <td class="p-2 border">
                    1500
                </td>

                <!-- ACTIONS -->
                <td class="p-2 border text-center">
                    <div class="flex items-center justify-center gap-3">

                        <!-- VIEW BUTTON -->
                        <button class="text-blue-500 hover:text-blue-700" title="View">
                            <i data-feather="eye" class="w-5 h-5"></i>
                        </button>
                    </div>
                </td>

            </tr>
        </tbody>
    </table>



    <script>
        document.getElementById('imageInput').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('previewImage');

            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
            }
        });
    </script>




    <?php
    $content = ob_get_clean();

    include __DIR__ . '/template/index.php';
    ?>