<!DOCTYPE html>
<html lang="th" data-theme="lofi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบพัสดุ FIFO + ทศนิยม</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Glassmorphism Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.17);
        }

        /* Custom Navbar Gradient */
        .custom-navbar {
            background: linear-gradient(to right, #667eea, #764ba2);
        }

        /* FIFO Box Styling */
        .fifo-box {
            font-size: 0.75rem;
            background: linear-gradient(45deg, #f0f4f8, #e2e8f0);
            padding: 8px 10px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            color: #2d3748;
            line-height: 1.4;
        }

        /* Hover Animation */
        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        /* Select with icon */
        .select-with-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        /* Table zebra modern */
        .table-zebra tbody tr:nth-child(even) {
            background-color: rgba(102, 126, 234, 0.05);
        }

        /* Loading pulse */
        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .loading-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>

<body class="min-h-screen">

    <!-- Navbar -->
    <div class="navbar custom-navbar text-white shadow-2xl rounded-2xl mx-4 mt-4">
        <div class="flex-1">
            <a class="btn btn-ghost text-2xl font-bold tracking-wider">
                <span class="text-3xl">🏛️</span> สำนักงานที่ดินจังหวัดอ่างทอง
            </a>
        </div>
        <div class="flex-none">
            <ul class="menu menu-horizontal px-1 gap-2">
                <li><a href="item.php" class="btn btn-neutral btn-sm hover:btn-primary shadow-lg">จัดการฐานข้อมูล</a>
                </li>
                <li><a onclick="switchTab('add')" class="btn btn-ghost btn-sm">รับเข้าสต็อก</a></li>
                <li><a onclick="switchTab('withdraw')" class="btn btn-active btn-primary btn-sm shadow-lg">เบิกจ่าย</a>
                </li>
                <li><a onclick="switchTab('report')" class="btn btn-ghost btn-sm">รายงาน FIFO</a></li>
                <li>
                    <a href="print_all_stock.php" target="_blank"
                        class="btn btn-success btn-sm shadow-lg flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        พิมพ์ทั้งหมด
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-6">

        <!-- เพิ่มพัสดุ -->
        <div id="page-add" class="hidden">
            <div class="glass-card card-hover rounded-3xl overflow-hidden">
                <div class="card-body p-8">
                    <h2 class="card-title text-3xl text-success font-bold mb-6 flex items-center gap-3">
                        <span class="text-4xl">📦</span> เพิ่มวัสดุเข้าสต็อก
                    </h2>
                    <form id="formAdd" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <input type="text" id="add_itemid" placeholder="รหัสวัสดุ" class="input input-bordered input-lg"
                            required />
                        <input type="text" id="add_itemname" placeholder="ชื่อรายการ"
                            class="input input-bordered input-lg" required />
                        <input type="text" id="add_unit" placeholder="หน่วยนับ" class="input input-bordered input-lg"
                            required />
                        <select id="add_type" class="select select-bordered input-lg select-with-icon">
                            <option>วัสดุสำนักงาน</option>
                            <option>วัสดุคอมพิวเตอร์</option>
                            <option>วัสดุครัวเรือน</option>
                        </select>
                        <input type="text" id="add_doc" placeholder="เลขที่เอกสาร/ใบส่งของ"
                            class="input input-bordered input-lg" required />
                        <input type="number" id="add_qty" class="input input-bordered input-lg" value="1" min="1"
                            placeholder="จำนวนรับ" required />
                        <input type="number" step="0.01" id="add_price" class="input input-bordered input-lg"
                            placeholder="ราคาต่อหน่วย (บาท)" required />
                        <div class="lg:col-span-2">
                            <input type="date" id="add_custom_date" class="input input-bordered input-lg w-full" />
                            <label class="label-text text-sm opacity-70 ml-2">วันที่บันทึก (เว้นว่าง = วันนี้)</label>
                        </div>
                        <button type="submit"
                            class="btn btn-success btn-lg text-white col-span-full shadow-xl hover:shadow-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            บันทึกเข้าสต็อก
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- เบิกจ่าย -->
        <div id="page-withdraw">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="glass-card card-hover rounded-3xl overflow-hidden">
                        <div class="card-body p-8">
                            <h2 class="card-title text-3xl text-error font-bold mb-6 flex items-center gap-3">
                                <span class="text-5xl">📤</span> เบิกวัสดุ
                            </h2>
                            <form id="formWithdraw" class="space-y-6">
                                <select id="wd_member" class="select select-bordered select-lg w-full select-with-icon"
                                    required></select>
                                <select id="wd_item" class="select select-bordered select-lg w-full select-with-icon"
                                    required onchange="showItemDetails()"></select>
                                <input type="number" id="wd_qty" min="1" value="1" class="input input-bordered input-lg"
                                    placeholder="จำนวนที่เบิก" required />
                                <input type="text" id="wd_doc" class="input input-bordered input-lg"
                                    placeholder="เลขที่ใบเบิก เช่น บ.10/2568" required />
                                <div>
                                    <input type="date" id="wd_custom_date"
                                        class="input input-bordered input-lg w-full" />
                                    <label class="label-text text-sm opacity-70 ml-2">วันที่เบิก (เว้นว่าง =
                                        วันนี้)</label>
                                </div>
                                <button type="submit"
                                    class="btn btn-error btn-lg w-full text-white shadow-xl hover:shadow-2xl">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    ยืนยันการเบิกวัสดุ
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ข้อมูลสต็อกข้าง ๆ -->
                <div class="glass-card card-hover rounded-3xl h-fit">
                    <div class="card-body p-8 bg-gradient-to-br from-blue-50 to-indigo-100">
                        <h3 class="text-xl font-bold text-gray-700 mb-4 flex items-center gap-2">
                            <span class="text-3xl">📊</span> ข้อมูลสต็อก (FIFO)
                        </h3>
                        <div id="itemDetailsContent" class="hidden space-y-4 mt-4">
                            <div class="text-2xl font-bold text-primary" id="info_name">-</div>
                            <div class="text-lg">
                                คงเหลือ: <span class="text-3xl font-bold text-success" id="info_stock">0</span>
                            </div>
                            <div class="text-lg">
                                ราคาล็อตเก่าสุด: <span class="text-2xl font-bold text-blue-600"
                                    id="info_price">0.00</span> บาท
                            </div>
                            <div class="text-sm text-gray-600 mt-4 p-3 bg-white bg-opacity-60 rounded-xl">
                                <strong>ระบบ FIFO</strong><br>
                                ตัดสต็อกจากล็อตเก่าสุดก่อนเสมอ
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- รายงาน -->
        <div id="page-report" class="hidden">
            <div class="glass-card card-hover rounded-3xl overflow-hidden">
                <div class="card-body p-8">
                    <h2 class="card-title text-3xl font-bold mb-6 flex items-center gap-3">
                        <span class="text-4xl">📈</span> รายงานการเบิกจ่าย (FIFO Log)
                    </h2>
                    <div class="overflow-x-auto rounded-2xl">
                        <table class="table table-zebra w-full text-sm">
                            <thead class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                                <tr>
                                    <th class="text-left">วันที่</th>
                                    <th class="text-left">ผู้เบิก</th>
                                    <th class="text-left">รายการ</th>
                                    <th class="text-right">จำนวน</th>
                                    <th class="text-right">มูลค่ารวม</th>
                                    <th>FIFO Details</th>
                                </tr>
                            </thead>
                            <tbody id="reportTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // โค้ด JavaScript เดิมทั้งหมด (ไม่เปลี่ยนแปลง)
        window.onload = loadDropdowns;
        let currentItems = [];

        async function loadDropdowns() {
            const [memRes, itemRes] = await Promise.all([
                fetch('api.php?action=get_members'),
                fetch('api.php?action=get_items')
            ]);
            const members = await memRes.json();
            currentItems = await itemRes.json();

            const mSelect = document.getElementById('wd_member');
            mSelect.innerHTML = '<option value="" disabled selected>-- เลือกผู้เบิก --</option>';
            members.forEach(m => mSelect.innerHTML += `<option value="${m.eid}">${m.name}</option>`);

            const iSelect = document.getElementById('wd_item');
            iSelect.innerHTML = '<option value="" disabled selected>-- เลือกวัสดุ --</option>';
            currentItems.forEach(i => iSelect.innerHTML += `<option value="${i.itemid}">${i.itemname}</option>`);
        }

        function showItemDetails() {
            const item = currentItems.find(i => i.itemid === document.getElementById('wd_item').value);
            if (item) {
                document.getElementById('itemDetailsContent').classList.remove('hidden');
                document.getElementById('info_name').innerText = item.itemname;
                document.getElementById('info_stock').innerText = item.qty;
                let price = parseFloat(item.current_price || 0).toFixed(2);
                document.getElementById('info_price').innerText = price;
            }
        }

        document.getElementById('formAdd').addEventListener('submit', async function (e) {
            e.preventDefault();
            const res = await fetch('api.php?action=add_item', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    itemid: document.getElementById('add_itemid').value,
                    itemname: document.getElementById('add_itemname').value,
                    unit: document.getElementById('add_unit').value,
                    type: document.getElementById('add_type').value,
                    doc_no: document.getElementById('add_doc').value,
                    qty: document.getElementById('add_qty').value,
                    price: document.getElementById('add_price').value,
                    custom_date: document.getElementById('add_custom_date').value
                })
            });
            const result = await res.json();

            if (result.success) {
                Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', text: 'นำเข้าสต็อกเรียบร้อยแล้ว', timer: 1500, showConfirmButton: false });
                this.reset();
                loadDropdowns();
            } else {
                Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: result.error });
            }
        });

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
                Swal.fire({ icon: 'success', title: 'เบิกจ่ายสำเร็จ', text: 'ตัดยอดคงเหลือเรียบร้อยแล้ว', timer: 1500, showConfirmButton: false });
                this.reset();
                loadDropdowns();
                document.getElementById('itemDetailsContent').classList.add('hidden');
            } else {
                Swal.fire({ icon: 'error', title: 'เบิกไม่สำเร็จ', text: result.error });
            }
        });

        async function renderReport() {
            const res = await fetch('api.php?action=get_history');
            const data = await res.json();
            const tbody = document.getElementById('reportTableBody');
            tbody.innerHTML = '';

            data.forEach(t => {
                tbody.innerHTML += `
                    <tr class="hover:bg-base-200 transition-all align-top">
                        <td class="font-medium">${t.trx_date}</td>
                        <td><div class="font-bold">${t.member_name}</div><div class="text-xs opacity-60">${t.department}</div></td>
                        <td>${t.itemname} <span class="badge badge-primary badge-sm">${t.type}</span></td>
                        <td class="text-right font-bold text-error">-${t.qty_withdraw}</td>
                        <td class="text-right font-semibold">${parseFloat(t.total_cost).toLocaleString('th-TH', { minimumFractionDigits: 2 })} ฿</td>
                        <td><div class="fifo-box">${t.fifo_details || '-'}</div></td>
                    </tr>
                `;
            });
        }

        function switchTab(tab) {
            document.getElementById('page-add').classList.add('hidden');
            document.getElementById('page-withdraw').classList.add('hidden');
            document.getElementById('page-report').classList.add('hidden');
            if (tab === 'add') document.getElementById('page-add').classList.remove('hidden');
            if (tab === 'withdraw') document.getElementById('page-withdraw').classList.remove('hidden');
            if (tab === 'report') { document.getElementById('page-report').classList.remove('hidden'); renderReport(); }
        }
    </script>
</body>

</html>