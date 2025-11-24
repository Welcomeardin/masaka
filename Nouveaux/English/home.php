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
        Dashboard › <span class="font-semibold text-gray-700">Home</span>
    </div>

    <!-- BUTTONS TOGETHER ON THE RIGHT -->
    <div class="flex justify-end mb-4 gap-2">

        <!-- LIST BUTTON -->
        <button
            onclick="openUserModal()"
            class="px-3 py-1 bg-gray-400 text-white rounded-full text-sm flex items-center gap-2">
            <i data-feather="list" class="w-4 h-4"></i>
            List
        </button>

        <!-- SUBMIT BUTTON -->
        <button
            type="submit"
            form="myForm"
            class="px-3 py-1 bg-green-500 text-white rounded-full text-sm flex items-center gap-2">
            <i data-feather="download" class="w-4 h-4"></i>
            Submit
        </button>

    </div>


    <!-- FORM CONTENT -->
    <form id="myForm" action="save_data.php" method="POST" enctype="multipart/form-data">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- LEFT SIDE -->
            <div class="space-y-3">

                <!-- Title -->
                <div>
                    <label class="text-sm text-gray-600">Title</label>
                    <input
                        type="text"
                        name="title"
                        class="w-full border rounded px-2 py-1 text-sm outline-none"
                        placeholder="Enter title..."
                        required>
                </div>

                <!-- Description -->
                <div>
                    <label class="text-sm text-gray-600">Description</label>
                    <textarea
                        name="description"
                        rows="6"
                        class="w-full border rounded px-2 py-1 text-sm outline-none"
                        placeholder="Write something..."
                        required></textarea>
                </div>

            </div>


            <!-- RIGHT SIDE -->
            <div class="space-y-3">

                <!-- Upload Image -->
                <div>
                    <label class="text-sm text-gray-600">Upload Image</label>
                    <input
                        type="file"
                        name="image"
                        accept="image/*"
                        id="imageInput"
                        class="w-full border rounded px-2 py-1 text-sm"
                        required>
                </div>

                <!-- Preview Box -->
                <div class="border rounded p-2 w-full h-60 flex items-center justify-center bg-gray-100">
                    <img id="previewImage" class="max-h-full max-w-full object-contain hidden">
                </div>

            </div>

        </div>

    </form>


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