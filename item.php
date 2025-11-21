<!DOCTYPE html>
<html lang="th" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการฐานข้อมูลวัสดุ - สำนักงานที่ดินจังหวัดอ่างทอง</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f8faf7;
            min-height: 100vh;
        }

        .glass-card {
            background: #ffffff;
            border: 1px solid #e5ebe4;
            box-shadow: 0 4px 20px rgba(0, 51, 20, 0.05);
            border-radius: 16px;
        }

        .custom-navbar {
            background: linear-gradient(135deg, #0d7f4e 0%, #0a5f3c 100%);
            box-shadow: 0 4px 12px rgba(13, 127, 78, 0.2);
        }

        .table-header-formal {
            background: linear-gradient(135deg, #0d7f4e 0%, #0a5f3c 100%);
            color: white;
            font-size: 0.95rem;
        }

        .table-row-hover:hover td {
            background-color: #f0f7f4;
        }

        .badge-type {
            font-weight: 500;
            border-radius: 4px;
        }

        .btn-action {
            border-radius: 8px;
        }
    </style>
</head>

<body class="pb-10">

    <div class="navbar custom-navbar text-white shadow-lg rounded-b-xl mb-8 sticky top-0 z-50">
        <div class="flex-1">
            <a href="index.php" class="btn btn-ghost text-xl font-bold tracking-wider gap-3">
                <span class="text-2xl">🏛️</span> สำนักงานที่ดินจังหวัดอ่างทอง
            </a>
        </div>
        <div class="flex-none">
            <ul class="menu menu-horizontal px-1">
                <li><a href="index.php" class="btn btn-ghost btn-sm text-white hover:bg-white/20 font-normal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        กลับหน้าหลัก
                    </a></li>
            </ul>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span class="text-primary">📚</span> จัดการฐานข้อมูลวัสดุ
                </h1>
                <p class="text-sm text-gray-500 mt-1">เพิ่ม แก้ไข และตรวจสอบประวัติ Lot วัสดุทั้งหมด</p>
            </div>

            <button onclick="openModal('add')"
                class="btn btn-primary bg-[#0d7f4e] border-none shadow-md hover:bg-[#0a5f3c] text-white gap-2 px-6 btn-action">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                เพิ่มรายการใหม่
            </button>
        </div>

        <div
            class="glass-card p-4 mb-6 flex flex-col md:flex-row gap-4 items-center justify-between bg-white/80 backdrop-blur-sm sticky top-20 z-40">
            <div class="flex items-center gap-2 w-full md:w-auto">
                <span class="text-sm font-bold text-gray-600">🔍 ค้นหา:</span>
                <div class="relative w-full md:w-64">
                    <input type="text" id="searchInput" oninput="fetchItems()"
                        class="input input-bordered input-sm w-full pr-8" placeholder="ชื่อวัสดุ หรือ รหัส...">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <span class="text-sm font-bold text-gray-600">📂 กรองประเภท:</span>
                <select id="typeFilter" onchange="fetchItems()" class="select select-bordered select-sm w-full md:w-48">
                    <option value="">ทั้งหมด (All Types)</option>
                    <option value="วัสดุสำนักงาน">วัสดุสำนักงาน</option>
                    <option value="วัสดุคอมพิวเตอร์">วัสดุคอมพิวเตอร์</option>
                    <option value="วัสดุครัวเรือน">วัสดุครัวเรือน</option>
                    <option value="อื่นๆ">อื่นๆ</option>
                </select>

                <div class="join">
                    <button onclick="resetSearch()" class="btn btn-sm btn-ghost text-gray-500 join-item tooltip"
                        data-tip="รีค่าค้นหา">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>

                    <button onclick="printFilteredReport()"
                        class="btn btn-sm bg-white text-[#0d7f4e] border border-[#0d7f4e] hover:bg-[#e8f5e9] join-item gap-2 tooltip tooltip-left"
                        data-tip="พิมพ์รายงานสรุป (ตามที่กรอง)">
                        พิมพ์สรุป
                    </button>
                </div>
            </div>
        </div>

        <div class="glass-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="table-header-formal">
                        <tr>
                            <th class="w-24 text-center">รหัส</th>
                            <th>ชื่อวัสดุ</th>
                            <th>ประเภท</th>
                            <th class="text-center">หน่วยนับ</th>
                            <th class="text-right">คงเหลือ (Cache)</th>
                            <th class="text-center w-40">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="itemTableBody" class="text-gray-700">
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-400">กำลังโหลดข้อมูล...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <dialog id="itemModal" class="modal">
        <div class="modal-box bg-white rounded-xl shadow-2xl max-w-lg border-t-4 border-[#0d7f4e]">
            <form method="dialog"><button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="font-bold text-xl text-[#0d7f4e] mb-4 flex items-center gap-2"><span
                    id="modalTitleIcon">✏️</span> <span id="modalTitle">แก้ไขข้อมูลวัสดุ</span></h3>

            <form id="formItem" class="space-y-4">
                <input type="hidden" id="mode" value="add">

                <div class="form-control">
                    <label class="label font-bold text-gray-600 text-sm">รหัสวัสดุ <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="itemid"
                        class="input input-bordered bg-gray-50 focus:bg-white focus:border-[#0d7f4e]" required />
                    <span class="text-xs text-gray-400 mt-1" id="idHint">รหัสห้ามซ้ำ (เช่น P001, C002)</span>
                </div>

                <div class="form-control">
                    <label class="label font-bold text-gray-600 text-sm">ชื่อรายการ <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="itemname"
                        class="input input-bordered bg-gray-50 focus:bg-white focus:border-[#0d7f4e]" required />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label font-bold text-gray-600 text-sm">ประเภท</label>
                        <select id="type"
                            class="select select-bordered bg-gray-50 focus:bg-white focus:border-[#0d7f4e]">
                            <option>วัสดุสำนักงาน</option>
                            <option>วัสดุคอมพิวเตอร์</option>
                            <option>วัสดุครัวเรือน</option>
                            <option>อื่นๆ</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label font-bold text-gray-600 text-sm">หน่วยนับ</label>
                        <input type="text" id="unit"
                            class="input input-bordered bg-gray-50 focus:bg-white focus:border-[#0d7f4e]"
                            placeholder="เช่น ชิ้น, เล่ม" required />
                    </div>
                </div>

                <div id="addStockSection" class="hidden p-3 bg-green-50 rounded-lg border border-green-200 mt-2">
                    <h4 class="text-sm font-bold text-green-800 mb-2">ตั้งต้นสต็อก (รับเข้าครั้งแรก)</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-600">จำนวนรับ</label>
                            <input type="number" id="init_qty" class="input input-sm input-bordered w-full" value="0"
                                min="0">
                        </div>
                        <div>
                            <label class="text-xs text-gray-600">ราคาต่อหน่วย</label>
                            <input type="number" id="init_price" class="input input-sm input-bordered w-full"
                                value="0.00" step="0.01">
                        </div>
                        <div class="col-span-2">
                            <label class="text-xs text-gray-600">เลขที่เอกสาร</label>
                            <input type="text" id="init_doc" class="input input-sm input-bordered w-full"
                                placeholder="ถ้ามี">
                        </div>
                    </div>
                </div>

                <div id="editStockSection" class="hidden p-3 bg-orange-50 rounded-lg border border-orange-200 mt-2">
                    <h4 class="text-sm font-bold text-orange-800 mb-2">🛠️ แก้ไขจำนวนคงเหลือรวม</h4>
                    <div class="form-control">
                        <label class="text-xs text-gray-600 mb-1">จำนวนคงเหลือที่ถูกต้อง</label>
                        <input type="number" id="edit_qty" class="input input-sm input-bordered w-full bg-white"
                            placeholder="ระบุจำนวนที่ถูกต้อง">
                        <div class="text-xs text-gray-400 mt-1">* หากต้องการแก้เฉพาะ Lot ให้ไปที่ปุ่มแว่นขยาย (🔍)</div>
                    </div>
                </div>

                <div class="modal-action mt-6">
                    <button type="button" onclick="document.getElementById('itemModal').close()"
                        class="btn btn-ghost">ยกเลิก</button>
                    <button type="submit"
                        class="btn btn-primary bg-[#0d7f4e] border-none text-white px-6">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </dialog>

    <dialog id="viewModal" class="modal">
        <div class="modal-box bg-white rounded-xl shadow-2xl max-w-4xl border-t-4 border-blue-600">
            <form method="dialog"><button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <div class="flex items-center gap-3 mb-6 border-b pb-4">
                <div class="bg-blue-100 p-3 rounded-full text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-xl text-gray-800" id="view_itemname">โหลดข้อมูล...</h3>
                    <div class="text-sm text-gray-500">รหัส: <span id="view_itemid" class="font-mono font-bold">-</span>
                        | ประเภท: <span id="view_type">-</span></div>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-gray-50 p-3 rounded-lg text-center border">
                    <div class="text-xs text-gray-500">จำนวน Lot ที่รับเข้า</div>
                    <div class="text-xl font-bold text-gray-800" id="view_count_lots">0</div>
                </div>
                <div class="bg-green-50 p-3 rounded-lg text-center border border-green-100">
                    <div class="text-xs text-green-600">คงเหลือรวม (ปัจจุบัน)</div>
                    <div class="text-xl font-bold text-green-700" id="view_total_remain">0</div>
                </div>
                <div class="bg-blue-50 p-3 rounded-lg text-center border border-blue-100">
                    <div class="text-xs text-blue-600">หน่วยนับ</div>
                    <div class="text-xl font-bold text-blue-700" id="view_unit">-</div>
                </div>
            </div>
            <h4 class="font-bold text-gray-700 mb-2 flex items-center gap-2"><span class="text-sm">📋</span> ประวัติ Lot
                สินค้า (แก้ไขราย Lot ได้ที่นี่)</h4>
            <div class="overflow-x-auto rounded-lg border border-gray-200 max-h-96 overflow-y-auto">
                <table class="table table-sm w-full header-sticky">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th>Lot ID</th>
                            <th>วันที่รับเข้า</th>
                            <th>เลขที่เอกสาร</th>
                            <th class="text-right">ราคา/หน่วย</th>
                            <th class="text-right">รับเข้า</th>
                            <th class="text-right">คงเหลือ</th>
                            <th class="text-center">สถานะ</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="viewTableBody"></tbody>
                </table>
            </div>
        </div>
    </dialog>

    <dialog id="editLotModal" class="modal">
        <div class="modal-box bg-white rounded-xl shadow-lg border border-gray-200 w-96">
            <h3 class="font-bold text-lg text-gray-800 mb-4">แก้ไขข้อมูล Lot: <span id="edit_lot_id_display"
                    class="text-blue-600"></span></h3>
            <form id="formEditLot" class="space-y-3">
                <input type="hidden" id="edit_lot_id">
                <input type="hidden" id="edit_lot_itemid">

                <input type="hidden" id="original_initial_qty">
                <input type="hidden" id="original_remain_qty">

                <div class="form-control">
                    <label class="label-text text-xs font-bold text-gray-500">วันที่รับเข้า</label>
                    <input type="date" id="edit_lot_date" class="input input-sm input-bordered w-full">
                </div>

                <div class="form-control">
                    <label class="label-text text-xs font-bold text-gray-500">เลขที่เอกสาร</label>
                    <input type="text" id="edit_lot_doc" class="input input-sm input-bordered w-full">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="form-control">
                        <label class="label-text text-xs font-bold text-gray-500">ราคาต่อหน่วย</label>
                        <input type="number" id="edit_lot_price" step="0.01"
                            class="input input-sm input-bordered w-full">
                    </div>
                    <div class="form-control">
                        <label class="label-text text-xs font-bold text-gray-500 text-blue-600">จำนวนรับเข้า
                            (Initial)</label>
                        <input type="number" id="edit_lot_initial"
                            class="input input-sm input-bordered w-full border-blue-200 focus:border-blue-500">
                    </div>
                </div>

                <div class="form-control">
                    <label class="label-text text-xs font-bold text-gray-500 text-red-600">คงเหลือปัจจุบัน
                        (Remain)</label>
                    <input type="number" id="edit_lot_remain"
                        class="input input-sm input-bordered w-full border-red-200 focus:border-red-500">
                    <span class="text-[10px] text-gray-400 mt-1 text-orange-500">*
                        เปลี่ยนอัตโนมัติตามยอดรับเข้าใหม่</span>
                </div>

                <div class="modal-action">
                    <button type="button" onclick="document.getElementById('editLotModal').close()"
                        class="btn btn-sm btn-ghost">ยกเลิก</button>
                    <button type="submit"
                        class="btn btn-sm btn-primary bg-blue-600 border-none text-white">บันทึก</button>
                </div>
            </form>
        </div>
    </dialog>

    <script>
        window.onload = fetchItems;

        function resetSearch() {
            document.getElementById('searchInput').value = '';
            document.getElementById('typeFilter').value = '';
            fetchItems();
        }

        // --- ฟังก์ชันที่ปรับปรุงใหม่ (Popup เลือกวันที่) ---
        async function printFilteredReport() {
            const search = document.getElementById('searchInput').value;
            const type = document.getElementById('typeFilter').value;

            // คำนวณวันที่เริ่มต้น (วันที่ 1 ของเดือนปัจจุบัน) และวันที่ปัจจุบัน
            const now = new Date();
            const today = now.toISOString().split('T')[0];
            const firstDay = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];

            // แสดง SweetAlert ให้เลือกวันที่
            const { value: formValues } = await Swal.fire({
                title: 'กรองช่วงเวลาออกรายงาน',
                html: `
                    <div class="text-left space-y-3 px-2">
                        <div class="form-control">
                            <label class="label text-sm font-bold text-gray-600 pb-1">ตั้งแต่วันที่ (Start Date)</label>
                            <input id="swal-start" type="date" class="input input-bordered w-full bg-gray-50" value="${firstDay}">
                        </div>
                        <div class="form-control">
                            <label class="label text-sm font-bold text-gray-600 pb-1">ถึงวันที่ (End Date)</label>
                            <input id="swal-end" type="date" class="input input-bordered w-full bg-gray-50" value="${today}">
                        </div>
                    </div>
                `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: '🖨️ พิมพ์รายงาน',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#0d7f4e',
                cancelButtonColor: '#9ca3af',
                preConfirm: () => {
                    return [
                        document.getElementById('swal-start').value,
                        document.getElementById('swal-end').value
                    ]
                }
            });

            if (formValues) {
                const [startDate, endDate] = formValues;
                if (!startDate || !endDate) {
                    Swal.fire('กรุณาเลือกวันที่ให้ครบถ้วน', '', 'warning');
                    return;
                }
                // ส่งค่าไปยัง print_stock_summary.php
                const url = `print_stock_summary.php?search=${encodeURIComponent(search)}&type=${encodeURIComponent(type)}&start_date=${startDate}&end_date=${endDate}`;
                window.open(url, '_blank');
            }
        }
        // ---------------------------------------------------

        async function fetchItems() {
            const search = document.getElementById('searchInput').value;
            const type = document.getElementById('typeFilter').value;

            const tbody = document.getElementById('itemTableBody');
            if (search || type) tbody.innerHTML = `<tr><td colspan="6" class="text-center py-10 text-gray-400">กำลังค้นหา "${search}"...</td></tr>`;

            try {
                const res = await fetch(`api.php?action=get_items&search=${encodeURIComponent(search)}&type=${encodeURIComponent(type)}`);
                const items = await res.json();

                tbody.innerHTML = '';

                if (items.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-gray-400">ไม่พบข้อมูลที่ค้นหา</td></tr>`;
                    return;
                }

                items.forEach(item => {
                    let badgeColor = 'badge-ghost';
                    if (item.type.includes('สำนักงาน')) badgeColor = 'bg-blue-100 text-blue-800 border-blue-200';
                    else if (item.type.includes('คอมพิวเตอร์')) badgeColor = 'bg-purple-100 text-purple-800 border-purple-200';
                    else if (item.type.includes('ครัวเรือน')) badgeColor = 'bg-orange-100 text-orange-800 border-orange-200';

                    tbody.innerHTML += `
                        <tr class="table-row-hover border-b border-gray-100">
                            <td class="text-center font-mono text-sm font-bold text-gray-500">${item.itemid}</td>
                            <td class="font-medium text-gray-800">${item.itemname}</td>
                            <td><span class="badge ${badgeColor} badge-type border py-3">${item.type}</span></td>
                            <td class="text-center text-gray-500">${item.unit}</td>
                            <td class="text-right font-bold ${item.qty > 0 ? 'text-[#0d7f4e]' : 'text-red-500'}">${item.qty}</td>
                            <td class="text-center">
                                <div class="join shadow-sm border rounded-lg">
                                    <a href="print_stock_item.php?itemid=${item.itemid}" target="_blank" class="btn btn-sm btn-square btn-ghost text-orange-600 join-item hover:bg-orange-50 tooltip tooltip-top" data-tip="พิมพ์บัญชีวัสดุ">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                    </a>
                                    
                                    <button onclick="openViewModal('${item.itemid}')" class="btn btn-sm btn-square btn-ghost text-green-600 join-item hover:bg-green-50 tooltip tooltip-top" data-tip="ดูรายละเอียด/แก้ไข Lot">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    </button>
                                    <button onclick='openModal("edit", ${JSON.stringify(item)})' class="btn btn-sm btn-square btn-ghost text-blue-600 join-item hover:bg-blue-50 tooltip tooltip-top" data-tip="แก้ไขข้อมูลหลัก">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" /></svg>
                                    </button>
                                    <button onclick="deleteItem('${item.itemid}')" class="btn btn-sm btn-square btn-ghost text-red-600 join-item hover:bg-red-50 tooltip tooltip-top" data-tip="ลบ">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            } catch (e) { console.error(e); }
        }

        async function openViewModal(itemid) {
            const modal = document.getElementById('viewModal');
            document.getElementById('view_itemname').innerText = 'กำลังโหลด...';
            document.getElementById('viewTableBody').innerHTML = '<tr><td colspan="8" class="text-center py-4">กำลังโหลดข้อมูล...</td></tr>';
            modal.showModal();
            try {
                const res = await fetch(`api.php?action=get_item_lots&itemid=${itemid}`);
                const data = await res.json();
                if (data.success) {
                    const item = data.item;
                    const lots = data.lots;
                    document.getElementById('view_itemname').innerText = item.itemname;
                    document.getElementById('view_itemid').innerText = item.itemid;
                    document.getElementById('view_type').innerText = item.type;
                    document.getElementById('view_unit').innerText = item.unit;
                    document.getElementById('view_count_lots').innerText = lots.length;
                    document.getElementById('view_total_remain').innerText = item.qty;

                    const tbody = document.getElementById('viewTableBody');
                    tbody.innerHTML = '';
                    if (lots.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-gray-400">ยังไม่มีประวัติการรับเข้า</td></tr>`;
                    } else {
                        lots.forEach(lot => {
                            const dateIn = new Date(lot.date_in).toLocaleDateString('th-TH', { day: 'numeric', month: 'short', year: '2-digit' });
                            const isSoldOut = parseInt(lot.qty_remain) === 0;
                            const statusBadge = isSoldOut ? `<span class="badge badge-xs bg-gray-200 text-gray-500 border-none font-medium py-2 px-3">หมดแล้ว</span>` : `<span class="badge badge-xs bg-green-100 text-green-700 border-none font-medium py-2 px-3">ใช้งานอยู่</span>`;
                            const rowClass = isSoldOut ? 'opacity-60 bg-gray-50' : 'hover:bg-blue-50 transition-colors';

                            const lotStr = JSON.stringify(lot).replace(/"/g, '&quot;');

                            tbody.innerHTML += `
                                <tr class="${rowClass}">
                                    <td class="font-mono text-xs">${lot.lot_id}</td>
                                    <td>${dateIn}</td>
                                    <td>${lot.doc_no || '-'}</td>
                                    <td class="text-right">${parseFloat(lot.lot_price).toFixed(2)}</td>
                                    <td class="text-right text-blue-600 font-medium">+${lot.qty_initial}</td>
                                    <td class="text-right font-bold ${isSoldOut ? 'text-gray-400' : 'text-green-600'}">${lot.qty_remain}</td>
                                    <td class="text-center">${statusBadge}</td>
                                    <td class="text-center">
                                        <button onclick="openEditLotModal(${lotStr})" class="btn btn-xs btn-ghost text-blue-600 tooltip" data-tip="แก้ไข Lot">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>`;
                        });
                    }
                } else { Swal.fire('Error', 'ไม่สามารถดึงข้อมูลได้', 'error'); modal.close(); }
            } catch (err) { console.error(err); Swal.fire('Error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error'); modal.close(); }
        }

        function openEditLotModal(lot) {
            document.getElementById('edit_lot_id').value = lot.lot_id;
            document.getElementById('edit_lot_itemid').value = lot.itemid;
            document.getElementById('edit_lot_id_display').innerText = lot.lot_id;

            const d = new Date(lot.date_in);
            const dateStr = d.toISOString().split('T')[0];
            document.getElementById('edit_lot_date').value = dateStr;

            document.getElementById('edit_lot_doc').value = lot.doc_no;
            document.getElementById('edit_lot_price').value = lot.lot_price;

            // ใส่ค่าจำนวนรับเข้า และ คงเหลือ
            document.getElementById('edit_lot_initial').value = lot.qty_initial;
            document.getElementById('edit_lot_remain').value = lot.qty_remain;

            // เก็บค่าเดิมไว้คำนวณ
            document.getElementById('original_initial_qty').value = lot.qty_initial;
            document.getElementById('original_remain_qty').value = lot.qty_remain;

            document.getElementById('editLotModal').showModal();
        }

        // Logic คำนวณคงเหลืออัตโนมัติเมื่อแก้รับเข้า
        document.getElementById('edit_lot_initial').addEventListener('input', function () {
            const newInit = parseInt(this.value) || 0;
            const oldInit = parseInt(document.getElementById('original_initial_qty').value) || 0;
            const oldRemain = parseInt(document.getElementById('original_remain_qty').value) || 0;

            let newRemain = oldRemain + (newInit - oldInit);
            if (newRemain < 0) newRemain = 0;

            document.getElementById('edit_lot_remain').value = newRemain;
        });

        document.getElementById('formEditLot').addEventListener('submit', async function (e) {
            e.preventDefault();
            const lotData = {
                lot_id: document.getElementById('edit_lot_id').value,
                itemid: document.getElementById('edit_lot_itemid').value,
                date_in: document.getElementById('edit_lot_date').value,
                doc_no: document.getElementById('edit_lot_doc').value,
                lot_price: document.getElementById('edit_lot_price').value,
                qty_initial: document.getElementById('edit_lot_initial').value,
                qty_remain: document.getElementById('edit_lot_remain').value
            };

            try {
                const res = await fetch('api.php?action=update_lot', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(lotData)
                });
                const result = await res.json();

                if (result.success) {
                    Swal.fire({ icon: 'success', title: 'แก้ไข Lot สำเร็จ', timer: 1000, showConfirmButton: false });
                    document.getElementById('editLotModal').close();
                    openViewModal(lotData.itemid);
                    fetchItems();
                } else {
                    Swal.fire('Error', result.error, 'error');
                }
            } catch (err) {
                console.error(err);
            }
        });

        function openModal(mode, item = null) {
            document.getElementById('mode').value = mode;
            const modal = document.getElementById('itemModal');

            const addSec = document.getElementById('addStockSection');
            const editSec = document.getElementById('editStockSection');
            const idHint = document.getElementById('idHint');

            if (mode === 'add') {
                document.getElementById('modalTitle').innerText = 'เพิ่มวัสดุใหม่'; document.getElementById('modalTitleIcon').innerText = '📦';
                document.getElementById('formItem').reset(); document.getElementById('itemid').disabled = false;
                addSec.classList.remove('hidden');
                editSec.classList.add('hidden');
                idHint.classList.remove('hidden');
            } else {
                document.getElementById('modalTitle').innerText = 'แก้ไขข้อมูลวัสดุ'; document.getElementById('modalTitleIcon').innerText = '✏️';
                document.getElementById('itemid').value = item.itemid; document.getElementById('itemid').disabled = true;
                document.getElementById('itemname').value = item.itemname; document.getElementById('type').value = item.type; document.getElementById('unit').value = item.unit;
                addSec.classList.add('hidden');
                editSec.classList.remove('hidden');
                idHint.classList.add('hidden');
                document.getElementById('edit_qty').value = item.qty;
            }
            modal.showModal();
        }

        document.getElementById('formItem').addEventListener('submit', async function (e) {
            e.preventDefault();
            const mode = document.getElementById('mode').value;
            const itemData = {
                itemid: document.getElementById('itemid').value,
                itemname: document.getElementById('itemname').value,
                type: document.getElementById('type').value,
                unit: document.getElementById('unit').value
            };
            let apiUrl = '';
            if (mode === 'add') {
                apiUrl = 'api.php?action=add_item';
                itemData.qty = document.getElementById('init_qty').value;
                itemData.price = document.getElementById('init_price').value;
                itemData.doc_no = document.getElementById('init_doc').value;
            } else {
                apiUrl = 'api.php?action=update_item';
                itemData.qty = document.getElementById('edit_qty').value;
            }
            try {
                const res = await fetch(apiUrl, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(itemData) });
                const result = await res.json();
                if (result.success) { Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', timer: 1500, showConfirmButton: false }); document.getElementById('itemModal').close(); fetchItems(); }
                else { Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: result.error }); }
            } catch (err) { Swal.fire({ icon: 'error', title: 'Error', text: 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้' }); }
        });

        async function deleteItem(id) {
            const result = await Swal.fire({
                title: 'ยืนยันการลบรายการ?',
                html: `รายการนี้อาจมีประวัติสต็อกอยู่<br>หากต้องการลบจริงๆ ให้พิมพ์ <b>angthongdol</b> เพื่อยืนยัน`,
                icon: 'warning',
                input: 'text',
                inputPlaceholder: 'พิมพ์รหัสยืนยันที่นี่...',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ยืนยันลบ',
                cancelButtonText: 'ยกเลิก',
                preConfirm: (inputValue) => {
                    if (!inputValue) { Swal.showValidationMessage('กรุณาพิมพ์รหัสยืนยัน') }
                    return inputValue
                }
            });

            if (result.isConfirmed) {
                try {
                    const res = await fetch('api.php?action=delete_item', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ itemid: id, confirm_code: result.value })
                    });
                    const resp = await res.json();
                    if (resp.success) { Swal.fire('ลบสำเร็จ!', 'รายการและข้อมูลที่เกี่ยวข้องถูกลบแล้ว', 'success'); fetchItems(); }
                    else { Swal.fire('ลบไม่ได้', resp.error, 'error'); }
                } catch (err) { console.error(err); Swal.fire('Error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error'); }
            }
        }
    </script>
</body>

</html>