<!DOCTYPE html>
<html lang="th" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบบริหารคลังพัสดุ - สำนักงานที่ดินจังหวัดอ่างทอง</title>

    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f0f4f8;
            /* พื้นหลังสีเทาอมฟ้าอ่อนๆ ให้ดู Modern */
            min-height: 100vh;
            padding-bottom: 50px;
        }

        /* --- Custom Glassmorphism & Cards --- */
        .glass-card {
            background: #ffffff;
            border: 1px solid #eef2f6;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .nav-gradient {
            background: linear-gradient(135deg, #0d7f4e 0%, #055c36 100%);
            box-shadow: 0 4px 20px rgba(13, 127, 78, 0.25);
        }

        /* Table Styles */
        .table-header-modern {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.05em;
        }

        /* Input Focus States */
        .input:focus,
        .select:focus {
            outline: none;
            border-color: #0d7f4e;
            box-shadow: 0 0 0 4px rgba(13, 127, 78, 0.1);
        }

        /* Animations */
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-bounce-in {
            animation: bounceIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3) translateY(50px);
            }

            50% {
                opacity: 1;
                transform: scale(1.05);
            }

            70% {
                transform: scale(0.9);
            }

            100% {
                transform: scale(1);
                translateY(0);
            }
        }
    </style>
</head>

<body>

    <div class="navbar nav-gradient text-white shadow-lg rounded-b-2xl mb-8 sticky top-0 z-50 px-4 lg:px-8">
        <div class="flex-1">
            <a href="index.php" class="btn btn-ghost text-xl font-bold tracking-wide gap-3 hover:bg-white/10">
                <span class="text-2xl bg-white/20 p-1 rounded-lg">🏛️</span>
                <span class="hidden sm:inline">สำนักงานที่ดินจังหวัดอ่างทอง</span>
                <span class="sm:hidden">สนง.ที่ดินอ่างทอง</span>
            </a>
        </div>
        <div class="flex-none hidden lg:block">
            <ul class="menu menu-horizontal px-1 gap-1">
                <li><a href="dashboard.php"
                        class="btn btn-ghost btn-sm font-normal opacity-90 hover:opacity-100 hover:bg-white/20">📊
                        ภาพรวม (Dashboard)</a></li>




                <li><a onclick="switchTab('add')" class="btn btn-ghost btn-sm font-normal hover:bg-white/20"
                        id="menu-add">📦 รับเข้าสต็อก</a></li>
                <li><a onclick="switchTab('withdraw')" class="btn btn-ghost btn-sm font-normal hover:bg-white/20"
                        id="menu-withdraw">📤 เบิกจ่าย</a></li>
                <li><a onclick="switchTab('report')" class="btn btn-ghost btn-sm font-normal hover:bg-white/20"
                        id="menu-report">📋 รายงาน</a></li>
                <li><a href="print_all_stock.php" target="_blank" class="btn btn-ghost btn-sm font-normal hover:bg-white/20"
                        id="menu-add">📋 พิมพ์รายงานทั้งหมด</a></li>

                <li>
                    <a href="item.php"
                        class="btn btn-sm bg-white text-[#0d7f4e] hover:bg-green-50 border-none font-bold ml-2 shadow-sm gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        จัดการฐานข้อมูล
                    </a>
                </li>
            </ul>
        </div>
        <div class="flex-none lg:hidden">
            <button class="btn btn-square btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    class="inline-block w-6 h-6 stroke-current">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                    </path>
                </svg>
            </button>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        <div id="page-add" class="hidden animate-fade-in">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="glass-card rounded-2xl overflow-hidden border-t-4 border-[#0d7f4e]">
                        <div class="p-8">
                            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3 mb-8">
                                <span class="bg-green-100 text-green-700 p-3 rounded-xl">📦</span>
                                บันทึกรับเข้าสต็อก
                            </h2>
                            <form id="formAdd" class="space-y-8">
                                <div class="relative">
                                    <div class="absolute inset-0 flex items-center">
                                        <div class="w-full border-t border-gray-200"></div>
                                    </div>
                                    <div class="relative flex justify-start"><span
                                            class="pr-3 bg-white text-sm font-bold text-gray-500 uppercase tracking-wider">1.
                                            ข้อมูลสินค้า</span></div>
                                </div>
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-6 rounded-xl border border-gray-100">
                                    <div class="form-control">
                                        <label class="label-text font-bold mb-2 text-gray-700">รหัสวัสดุ <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" id="add_itemid" class="input input-bordered bg-white w-full"
                                            placeholder="เช่น A001" required />
                                    </div>
                                    <div class="form-control">
                                        <label class="label-text font-bold mb-2 text-gray-700">ชื่อรายการ <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" id="add_itemname"
                                            class="input input-bordered bg-white w-full" required />
                                    </div>
                                    <div class="form-control">
                                        <label class="label-text font-bold mb-2 text-gray-700">ประเภท</label>
                                        <select id="add_type" class="select select-bordered bg-white w-full">
                                            <option>วัสดุสำนักงาน</option>
                                            <option>วัสดุคอมพิวเตอร์</option>
                                            <option>วัสดุครัวเรือน</option>
                                            <option>อื่นๆ</option>
                                        </select>
                                    </div>
                                    <div class="form-control">
                                        <label class="label-text font-bold mb-2 text-gray-700">หน่วยนับ</label>
                                        <input type="text" id="add_unit" class="input input-bordered bg-white w-full"
                                            required />
                                    </div>
                                </div>

                                <div class="relative">
                                    <div class="absolute inset-0 flex items-center">
                                        <div class="w-full border-t border-gray-200"></div>
                                    </div>
                                    <div class="relative flex justify-start"><span
                                            class="pr-3 bg-white text-sm font-bold text-[#0d7f4e] uppercase tracking-wider">2.
                                            รายละเอียด Lot</span></div>
                                </div>
                                <div class="bg-green-50/50 p-6 rounded-xl border border-green-100">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div class="form-control">
                                            <label class="label-text font-bold mb-2 text-gray-700">เลขที่เอกสาร</label>
                                            <input type="text" id="add_doc" class="input input-bordered bg-white w-full"
                                                placeholder="ใบส่งของ/ใบเสร็จ" required />
                                        </div>
                                        <div class="form-control">
                                            <label class="label-text font-bold mb-2 text-gray-700">จำนวนรับ <span
                                                    class="text-red-500">*</span></label>
                                            <input type="number" id="add_qty"
                                                class="input input-bordered bg-white w-full font-bold text-[#0d7f4e]"
                                                value="1" min="1" required />
                                        </div>
                                        <div class="form-control">
                                            <label class="label-text font-bold mb-2 text-gray-700">ราคาต่อหน่วย</label>
                                            <input type="number" step="0.01" id="add_price"
                                                class="input input-bordered bg-white w-full" placeholder="0.00"
                                                required />
                                        </div>
                                        <div class="form-control md:col-span-3">
                                            <label class="label-text font-bold mb-2 text-gray-700">วันที่บันทึก</label>
                                            <input type="date" id="add_custom_date"
                                                class="input input-bordered bg-white w-full" />
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-4 pt-4">
                                    <button type="reset" class="btn btn-ghost text-gray-500">ล้างค่า</button>
                                    <button type="submit"
                                        class="btn bg-[#0d7f4e] hover:bg-[#0a5f3c] text-white px-8 rounded-xl shadow-lg">ยืนยันนำเข้า</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="hidden lg:block space-y-6">
                    <div
                        class="glass-card rounded-2xl p-6 bg-gradient-to-br from-[#0d7f4e] to-[#065f3a] text-white shadow-lg">
                        <h3 class="font-bold text-lg mb-3 flex items-center gap-2">📝 ข้อแนะนำ</h3>
                        <ul class="text-sm space-y-2 opacity-90 list-disc pl-5">
                            <li>ระบบ FIFO จะตัดสต็อกตามราคาของแต่ละ Lot</li>
                            <li>หากมีรหัสวัสดุเดิม ระบบจะเพิ่มยอดให้ทันที</li>
                        </ul>
                    </div>
                    <div class="glass-card rounded-2xl p-6 border border-gray-100">
                        <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">รายการล่าสุด</h3>
                        <ul class="space-y-3" id="recentAddedList">
                            <li class="text-center text-gray-400 text-sm py-6">ยังไม่มีรายการใหม่</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div id="page-withdraw" class="hidden animate-fade-in">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="glass-card rounded-2xl overflow-hidden border-t-4 border-red-500">
                        <div class="p-8">
                            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3 mb-8">
                                <span class="bg-red-100 text-red-600 p-3 rounded-xl">📤</span>
                                เบิกจ่ายวัสดุ
                            </h2>
                            <form id="formWithdraw" class="space-y-6">
                                <div class="bg-red-50/50 p-6 rounded-xl border border-red-100">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="form-control md:col-span-2">
                                            <label class="label-text font-bold mb-2 text-gray-700">ผู้เบิก <span
                                                    class="text-red-500">*</span></label>
                                            <select id="wd_member" class="select select-bordered bg-white w-full"
                                                required></select>
                                        </div>
                                        <div class="form-control md:col-span-2">
                                            <label class="label-text font-bold mb-2 text-gray-700">เลือกวัสดุ <span
                                                    class="text-red-500">*</span></label>
                                            <select id="wd_item" class="select select-bordered bg-white w-full" required
                                                onchange="showItemDetails()"></select>
                                        </div>
                                        <div class="form-control">
                                            <label class="label-text font-bold mb-2 text-gray-700">จำนวน <span
                                                    class="text-red-500">*</span></label>
                                            <input type="number" id="wd_qty" min="1" value="1"
                                                class="input input-bordered bg-white w-full font-bold text-red-600"
                                                required oninput="validateStock()" />
                                        </div>
                                        <div class="form-control">
                                            <label class="label-text font-bold mb-2 text-gray-700">เลขที่ใบเบิก</label>
                                            <input type="text" id="wd_doc" class="input input-bordered bg-white w-full"
                                                placeholder="เช่น บ.10/2568" required />
                                        </div>
                                        <div class="form-control md:col-span-2">
                                            <label class="label-text font-bold mb-2 text-gray-700">วันที่เบิก</label>
                                            <input type="date" id="wd_custom_date"
                                                class="input input-bordered bg-white w-full" />
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" id="btnWithdraw"
                                        class="btn bg-red-600 hover:bg-red-700 text-white px-8 rounded-xl shadow-lg w-full md:w-auto">ยืนยันการเบิก</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="hidden lg:block">
                    <div id="stockStatusCard"
                        class="glass-card rounded-2xl p-8 h-full flex flex-col justify-center items-center text-center">
                        <div id="state-empty" class="opacity-50">
                            <div
                                class="bg-gray-100 p-4 rounded-full w-20 h-20 mx-auto mb-4 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-600">รอการเลือกวัสดุ</h3>
                            <p class="text-sm text-gray-400 mt-2">เลือกรายการด้านซ้ายเพื่อตรวจสอบยอด</p>
                        </div>
                        <div id="state-selected" class="hidden w-full">
                            <div class="badge badge-outline text-gray-400 mb-4" id="disp_id">ID: -</div>
                            <h3 class="text-xl font-bold text-gray-800 mb-1 break-words" id="disp_name">-</h3>
                            <p class="text-sm text-gray-500 mb-6" id="disp_type">-</p>

                            <div class="bg-green-50 rounded-xl p-6 border border-green-100 mb-6">
                                <p class="text-xs text-green-600 font-bold uppercase tracking-wider">คงเหลือในคลัง</p>
                                <div class="text-5xl font-bold text-green-600 my-2" id="disp_stock">0</div>
                                <p class="text-sm text-green-700 font-medium" id="disp_unit">หน่วย</p>
                            </div>

                            <div id="stock-alert"
                                class="hidden mt-4 p-3 bg-red-100 text-red-700 text-sm rounded-lg font-bold animate-pulse">
                                ⚠️ จำนวนเบิกเกินสต็อกที่มี!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="page-report" class="hidden animate-fade-in">
            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                            <span class="bg-blue-100 text-blue-600 p-3 rounded-xl">📈</span>
                            รายงานการเบิกจ่าย (FIFO Log)
                        </h2>
                        <div class="flex gap-2">
                            <a href="print_all_stock.php" target="_blank" class="btn btn-sm btn-outline gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                พิมพ์รายงาน
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="table w-full">
                            <thead class="table-header-modern">
                                <tr>
                                    <th class="py-4 pl-6">วันที่ / เวลา</th>
                                    <th>ผู้เบิก</th>
                                    <th>รายการวัสดุ</th>
                                    <th class="text-right">จำนวน</th>
                                    <th class="text-right">มูลค่ารวม</th>
                                    <th class="pl-6">FIFO Details</th>
                                    <th class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="reportTableBody" class="bg-white">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div id="alert-stock-container"
        class="fixed bottom-4 left-4 z-50 w-96 hidden transition-all duration-500 transform translate-y-0"></div>

    <script>
        window.onload = function () {
            loadDropdowns();
            // Default Tab
            switchTab('add');
        };

        let currentItems = [];

        async function loadDropdowns() {
            try {
                const [memRes, itemRes] = await Promise.all([
                    fetch('api.php?action=get_members'),
                    fetch('api.php?action=get_items')
                ]);
                const members = await memRes.json();
                currentItems = await itemRes.json();

                checkLowStock(currentItems); // Trigger Alert

                const mSelect = document.getElementById('wd_member');
                mSelect.innerHTML = '<option value="" disabled selected>-- เลือกผู้เบิก --</option>';
                members.forEach(m => mSelect.innerHTML += `<option value="${m.eid}">${m.name}</option>`);

                const iSelect = document.getElementById('wd_item');
                iSelect.innerHTML = '<option value="" disabled selected>-- เลือกวัสดุ --</option>';
                currentItems.forEach(i => iSelect.innerHTML += `<option value="${i.itemid}">${i.itemname}</option>`);
            } catch (e) { console.error(e); }
        }

        // === Stock Logic ===
        function showItemDetails() {
            const itemId = document.getElementById('wd_item').value;
            const item = currentItems.find(i => i.itemid === itemId);
            const emptyState = document.getElementById('state-empty');
            const selectedState = document.getElementById('state-selected');
            const card = document.getElementById('stockStatusCard');

            if (item) {
                emptyState.classList.add('hidden');
                selectedState.classList.remove('hidden');

                document.getElementById('disp_id').innerText = item.itemid;
                document.getElementById('disp_name').innerText = item.itemname;
                document.getElementById('disp_type').innerText = item.type;
                document.getElementById('disp_stock').innerText = item.qty;
                document.getElementById('disp_unit').innerText = item.unit;

                // Style Check
                if (parseInt(item.qty) === 0) {
                    document.getElementById('disp_stock').classList.replace('text-green-600', 'text-gray-400');
                } else {
                    document.getElementById('disp_stock').classList.replace('text-gray-400', 'text-green-600');
                }
                validateStock();
            } else {
                emptyState.classList.remove('hidden');
                selectedState.classList.add('hidden');
            }
        }

        function validateStock() {
            const itemId = document.getElementById('wd_item').value;
            const item = currentItems.find(i => i.itemid === itemId);
            const withdrawQty = parseInt(document.getElementById('wd_qty').value) || 0;
            const btn = document.getElementById('btnWithdraw');
            const alertBox = document.getElementById('stock-alert');

            if (!item) return;
            if (withdrawQty > parseInt(item.qty)) {
                alertBox.classList.remove('hidden');
                btn.disabled = true;
                btn.classList.add('opacity-50');
                document.getElementById('wd_qty').classList.add('text-red-600', 'border-red-500');
            } else {
                alertBox.classList.add('hidden');
                btn.disabled = false;
                btn.classList.remove('opacity-50');
                document.getElementById('wd_qty').classList.remove('text-red-600', 'border-red-500');
            }
        }

        // === Add Item Logic ===
        document.getElementById('formAdd').addEventListener('submit', async function (e) {
            e.preventDefault();
            const itemName = document.getElementById('add_itemname').value;
            const qty = document.getElementById('add_qty').value;
            const unit = document.getElementById('add_unit').value;

            const res = await fetch('api.php?action=add_item', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    itemid: document.getElementById('add_itemid').value,
                    itemname: itemName,
                    unit: unit,
                    type: document.getElementById('add_type').value,
                    doc_no: document.getElementById('add_doc').value,
                    qty: qty,
                    price: document.getElementById('add_price').value,
                    custom_date: document.getElementById('add_custom_date').value
                })
            });
            const result = await res.json();

            if (result.success) {
                Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', timer: 1500, showConfirmButton: false });
                this.reset();
                loadDropdowns();

                // Update Recent List
                const list = document.getElementById('recentAddedList');
                if (list.querySelector('li.text-center')) list.innerHTML = '';
                const newItem = `
                    <li class="flex items-center gap-3 p-3 bg-green-50 rounded-xl border border-green-100 animate-bounce-in">
                        <div class="bg-green-200 text-green-700 p-2 rounded-full"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" /></svg></div>
                        <div>
                            <div class="text-sm font-bold text-gray-800">${itemName}</div>
                            <div class="text-xs text-green-600">+${qty} ${unit}</div>
                        </div>
                    </li>`;
                list.insertAdjacentHTML('afterbegin', newItem);
            } else {
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: result.error });
            }
        });

        // === Withdraw Logic ===
        document.getElementById('formWithdraw').addEventListener('submit', async function (e) {
            e.preventDefault();
            const res = await fetch('api.php?action=withdraw', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    eid: document.getElementById('wd_member').value,
                    itemid: document.getElementById('wd_item').value,
                    qty: document.getElementById('wd_qty').value,
                    doctax_no: document.getElementById('wd_doc').value,
                    custom_date: document.getElementById('wd_custom_date').value
                })
            });
            const result = await res.json();
            if (result.success) {
                Swal.fire({ icon: 'success', title: 'เบิกจ่ายสำเร็จ', timer: 1500, showConfirmButton: false });
                this.reset();
                loadDropdowns();
                document.getElementById('state-selected').classList.add('hidden');
                document.getElementById('state-empty').classList.remove('hidden');
            } else {
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: result.error });
            }
        });

        // === Report Logic ===
        async function renderReport() {
            const res = await fetch('api.php?action=get_history');
            const data = await res.json();
            const tbody = document.getElementById('reportTableBody');
            tbody.innerHTML = '';

            data.forEach(t => {
                const initial = t.member_name.charAt(0);
                tbody.innerHTML += `
                    <tr class="hover:bg-gray-50 transition border-b border-gray-50">
                        <td class="font-mono text-xs text-gray-500 py-4 pl-6">${t.trx_date}</td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar placeholder">
                                    <div class="bg-neutral text-neutral-content rounded-full w-8 h-8">
                                        <span class="text-xs">${initial}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-bold text-sm text-gray-800">${t.member_name}</div>
                                    <div class="text-xs text-gray-400">${t.department}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="text-sm font-bold text-gray-700">${t.itemname}</div>
                            <div class="badge badge-xs badge-ghost mt-1">${t.type}</div>
                        </td>
                        <td class="text-right font-bold text-red-600">-${t.qty_withdraw}</td>
                        <td class="text-right text-sm text-gray-600">${parseFloat(t.total_cost).toLocaleString('th-TH', { minimumFractionDigits: 2 })} ฿</td>
                        <td class="pl-6">
                            <div class="text-xs bg-gray-50 p-2 rounded border border-gray-100 leading-relaxed text-gray-500">${t.fifo_details || '-'}</div>
                        </td>
                        <td class="text-center">
                             <button onclick="cancelTransaction(${t.tid})" class="btn btn-sm btn-square btn-ghost text-red-400 hover:bg-red-50 hover:text-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }

        async function cancelTransaction(tid) {
            const result = await Swal.fire({
                title: 'ยืนยันการยกเลิก?',
                text: "รายการนี้จะถูกลบ และสินค้าจะถูกคืนกลับเข้าสต็อก",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'ลบรายการ',
                cancelButtonText: 'ยกเลิก'
            });
            if (result.isConfirmed) {
                try {
                    const res = await fetch('api.php?action=cancel_withdraw', {
                        method: 'POST', headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ tid: tid })
                    });
                    const resp = await res.json();
                    if (resp.success) {
                        Swal.fire('เรียบร้อย', 'คืนยอดสต็อกแล้ว', 'success');
                        renderReport();
                        loadDropdowns();
                    } else Swal.fire('ผิดพลาด', resp.error, 'error');
                } catch (err) { Swal.fire('Error', 'เชื่อมต่อไม่ได้', 'error'); }
            }
        }

        // === UI Helpers ===
        function switchTab(tab) {
            ['add', 'withdraw', 'report'].forEach(t => {
                document.getElementById(`page-${t}`).classList.add('hidden');
                // Optional: Toggle Active Class on Navbar (if needed)
            });
            document.getElementById(`page-${tab}`).classList.remove('hidden');
            if (tab === 'report') renderReport();
        }

        function checkLowStock(items) {
            const lowStockItems = items.filter(i => parseInt(i.qty) < 5);
            const container = document.getElementById('alert-stock-container');
            if (lowStockItems.length > 0) {
                let listHtml = '';
                lowStockItems.slice(0, 3).forEach(i => {
                    listHtml += `<li class="flex justify-between items-center py-1 border-b border-orange-100 last:border-0">
                        <span class="text-gray-700">${i.itemname}</span>
                        <span class="badge badge-sm badge-warning text-white">${i.qty} ${i.unit}</span>
                    </li>`;
                });
                container.innerHTML = `
                    <div class="card bg-white shadow-2xl border-l-4 border-warning animate-bounce-in overflow-hidden">
                        <div class="bg-orange-50 p-3 flex justify-between items-center">
                            <h3 class="font-bold text-orange-800 text-sm flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                วัสดุใกล้หมด (${lowStockItems.length})
                            </h3>
                            <button onclick="this.closest('#alert-stock-container').classList.add('hidden')" class="btn btn-xs btn-circle btn-ghost text-orange-800">✕</button>
                        </div>
                        <div class="p-4 pt-2"><ul class="text-sm mt-2 space-y-1">${listHtml}</ul></div>
                    </div>`;
                container.classList.remove('hidden');
            } else container.classList.add('hidden');
        }
    </script>
</body>

</html>