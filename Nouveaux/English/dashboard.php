<?php
$title = 'Dashboard';

ob_start();

require_once __DIR__ . '/../../API/config.php';
$totalUsers = 0; // default in case query fails

if (isset($conn)) {
    $result = $conn->query("SELECT COUNT(*) AS total FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        $totalUsers = $row['total'];
    }
}
?>
<div class="rounded-xl p-4 bg-white shadow-smd border">
    <!-- Quick Access -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold">Quick Access</h3>
        <div class="text-gray-400 text-sm">...</div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="p-3 rounded-lg border bg-white">
            <div class="flex items-center gap-2"><i data-feather="home"></i><span class="text-sm font-medium">Home</span></div>
            <div class="flex items-center justify-between mt-2 text-xs text-gray-400">
                <!-- Left side: info -->

                <!-- Right side: buttons -->
                <div class="flex items-center gap-2">
                    <!-- List Button -->
                    <a href="list_home.php" class="flex items-center gap-1 px-2 py-1 bg-gray-100 rounded-full hover:bg-gray-200 text-gray-700">
                        <i data-feather="list" class="w-4 h-4"></i>
                        List
                    </a>

                    <!-- Create Button -->
                    <a href="create_home.php" class="flex items-center gap-1 px-2 py-1 bg-green-400 rounded-full hover:bg-blue-600 text-white">
                        <i data-feather="plus-circle" class="w-4 h-4"></i>
                        Create
                    </a>
                </div>
            </div>
        </div>
        <div class="p-3 rounded-lg border bg-white">
            <div class="flex items-center gap-2"><i data-feather="calendar"></i><span class="text-sm font-medium">Events</span></div>
            <div class="flex items-center justify-between mt-2 text-xs text-gray-400">
                <!-- Right side: buttons -->
                <div class="flex items-center gap-2">
                    <!-- List Button -->
                    <a href="list_events.php" class="flex items-center gap-1 px-2 py-1 bg-gray-100 rounded-full hover:bg-gray-200 text-gray-700">
                        <i data-feather="list" class="w-4 h-4"></i>
                        List
                    </a>

                    <!-- Create Button -->
                    <a href="create_events.php" class="flex items-center gap-1 px-2 py-1 bg-yellow-400 rounded-full hover:bg-blue-600 text-white">
                        <i data-feather="plus-circle" class="w-4 h-4"></i>
                        Create
                    </a>
                </div>
            </div>
        </div>
        <div class="p-3 rounded-lg border bg-white">
            <div class="flex items-center gap-2"><i data-feather="heart"></i><span class="text-sm font-medium">Donations</span></div>
            <div class="flex items-center justify-between mt-2 text-xs text-gray-400">
                <!-- Right side: buttons -->
                <div class="flex items-center gap-2">
                    <!-- List Button -->
                    <a href="list_donations.php" class="flex items-center gap-1 px-2 py-1 bg-pink-300 rounded-full hover:bg-gray-200 text-white">
                        <i data-feather="list" class="w-4 h-4"></i>
                        List
                    </a>
                </div>
            </div>
        </div>
        <div class="p-3 rounded-lg border bg-white">
            <div class="flex items-center gap-2"><i data-feather="users"></i><span class="text-sm font-medium">Staff</span></div>
            <div class="text-xs text-gray-400 mt-2">
                <?= htmlspecialchars($totalUsers) ?> users
            </div>

        </div>
    </div>

    <!-- File table -->
    <div class="mt-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-3 gap-2">
            <div class="text-sm text-gray-500">Home › <span class="font-semibold text-gray-700">Staff</span></div>
            <div class="flex items-center gap-2">
                <button onclick="openUserModal()"
                    class="px-2 py-1 bg-green-400 text-white rounded-full text-sm flex items-center gap-2">
                    <i data-feather="user-plus" class="w-4 h-4"></i>
                    Add New
                </button>
            </div>


            <!-- Modal Background -->
            <div id="userModal" class="fixed inset-0 bg-black/50 hidden z-50 flex justify-center items-center">
                <!-- Modal Box -->
                <div class="bg-white w-[700px] rounded shadow-lg overflow-hidden">

                    <!-- Header with Green Background -->
                    <div class="bg-green-600 text-white px-2 py-1 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <i data-feather="user-plus" class="w-5 h-5"></i>
                            <h2 class="text-md font-semibold">Register New User</h2>
                        </div>

                        <!-- Close Button -->
                        <button onclick="closeUserModal()" class="hover:text-gray-200 transition">
                            <i data-feather="x" class="w-5 h-5"></i>
                        </button>
                    </div>


                    <!-- Form -->
                    <div class="p-6">
                        <form action="save_user.php" method="POST" class="grid grid-cols-2 gap-4">

                            <!-- First Name -->
                            <div>
                                <label class="text-sm text-gray-600">First Name</label>
                                <div class="flex items-center gap-2 border rounded px-2 py-1 text-sm">
                                    <i data-feather="user" class="w-4 h-4 text-green-400"></i>
                                    <input
                                        type="text"
                                        name="first_name"
                                        class="w-full outline-none text-sm py-1"
                                        required>
                                </div>

                            </div>

                            <!-- Second Name -->
                            <div>
                                <label class="text-sm text-gray-600">Second Name</label>
                                <div class="flex items-center gap-2 border rounded px-2 py-1 text-sm">
                                    <i data-feather="user" class="w-4 h-4 text-green-400"></i>
                                    <input type="text" name="second_name" class="w-full outline-none text-sm py-1" required>
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="text-sm text-gray-600">Email</label>
                                <div class="flex items-center gap-2 border rounded px-2 py-1 text-sm">
                                    <i data-feather="mail" class="w-4 h-4 text-green-400"></i>
                                    <input type="email" name="email" class="w-full outline-none text-sm py-1" required>
                                </div>
                            </div>

                            <!-- Telephone -->
                            <div>
                                <label class="text-sm text-gray-600">Telephone</label>
                                <div class="flex items-center gap-2 border rounded px-2 py-1 text-sm">
                                    <i data-feather="phone" class="w-4 h-4 text-green-400"></i>
                                    <input type="text" name="telephone" class="w-full outline-none text-sm py-1" required>
                                </div>
                            </div>

                            <!-- Identifiant -->
                            <div>
                                <label class="text-sm text-gray-600">Identifiant</label>
                                <div class="flex items-center gap-2 border rounded px-2 py-1 text-sm">
                                    <i data-feather="hash" class="w-4 h-4 text-green-400"></i>
                                    <input type="text" name="identifiant" class="w-full outline-none text-sm py-1" required>
                                </div>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="text-sm text-gray-600">Status</label>
                                <div class="flex items-center gap-2 border rounded px-2 py-1 text-sm">
                                    <i data-feather="activity" class="w-4 h-4 text-green-400"></i>
                                    <select name="status" class="w-full outline-none text-sm py-1">
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Type User -->
                            <div>
                                <label class="text-sm text-gray-600">Type User</label>
                                <div class="flex items-center gap-2 border rounded px-2 py-1 text-sm">
                                    <i data-feather="users" class="w-4 h-4 text-green-400"></i>
                                    <select name="type_user" class="w-full outline-none text-sm py-1">
                                        <option value="Admin">Admin</option>
                                        <option value="Editor">Editor</option>
                                        <option value="Viewer">Viewer</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Password -->
                            <div>
                                <label class="text-sm text-gray-600">Password</label>
                                <div class="flex items-center gap-2 border rounded px-2 py-1 text-sm">
                                    <i data-feather="lock" class="w-4 h-4 text-green-400"></i>
                                    <input type="password" name="password" class="w-full outline-none text-sm py-1" required>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-span-2 flex justify-end mt-2">
                                <button class="bg-green-400 px-2 py-1 text-white rounded-full flex items-center gap-2">
                                    <i data-feather="download" class="w-4 h-4"></i>
                                    Save User
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>


        </div>
        <?php
        // Fetch users from database
        $query = $conn->query("SELECT * FROM users ORDER BY id DESC");
        ?>

        <div class="border rounded-lg overflow-hidden table-responsive">
            <!-- Table Header -->
            <div class="table-header grid grid-cols-12 gap-4 p-3 text-xs text-gray-500 bg-gray-50">
                <div class="col-span-2">First Name</div>
                <div class="col-span-2">Second Name</div>
                <div class="col-span-3">Email</div>
                <div class="col-span-2">Telephone</div>
                <div class="col-span-2">Status</div>
                <div class="col-span-1 text-center">Action</div>
            </div>

            <div class="divide-y">
                <?php while ($u = $query->fetch_assoc()): ?>
                    <div class="table-row grid grid-cols-12 gap-4 p-3 items-center hover:bg-blue-50/30">

                        <div class="col-span-2"><?= htmlspecialchars($u['first_name']); ?></div>
                        <div class="col-span-2"><?= htmlspecialchars($u['second_name']); ?></div>
                        <div class="col-span-3"><?= htmlspecialchars($u['email']); ?></div>
                        <div class="col-span-2"><?= htmlspecialchars($u['telephone']); ?></div>

                        <div class="col-span-2">
                            <?php if ($u['status'] == 'Active'): ?>
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-600">Active</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-600">Inactive</span>
                            <?php endif; ?>
                        </div>

                        <!-- Edit / Delete -->
                        <div class="col-span-1 flex justify-center gap-3">
                            <a href="edit_user.php?id=<?= $u['id']; ?>"
                                class="text-green-600 hover:text-green-800" title="Edit">
                                <i data-feather="edit-2"></i>
                            </a>

                            <a href="delete_user.php?id=<?= $u['id']; ?>"
                                onclick="return confirm('Delete this user?');"
                                class="text-red-600 hover:text-red-800" title="Delete">
                                <i data-feather="trash-2"></i>
                            </a>
                        </div>

                    </div>
                <?php endwhile; ?>
            </div>
        </div>


    </div>

</div>


<script>
    function openUserModal() {
        document.getElementById("userModal").classList.remove("hidden");
        feather.replace();
    }

    function closeUserModal() {
        document.getElementById("userModal").classList.add("hidden");
    }
</script>




<?php
$content = ob_get_clean();

include __DIR__ . '/template/index.php';
