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

        /* --- Custom Styles --- */
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
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>

            <h3 class="font-bold text-xl text-[#0d7f4e] mb-4 flex items-center gap-2">
                <span id="modalTitleIcon">✏️</span>
                <span id="modalTitle">แก้ไขข้อมูลวัสดุ</span>
            </h3>

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
                        <div><label class="text-xs text-gray-600">จำนวนรับ</label><input type="number" id="init_qty"
                                class="input input-sm input-bordered w-full" value="0" min="0"></div>
                        <div><label class="text-xs text-gray-600">ราคาต่อหน่วย</label><input type="number"
                                id="init_price" class="input input-sm input-bordered w-full" value="0.00" step="0.01">
                        </div>
                        <div class="col-span-2"><label class="text-xs text-gray-600">เลขที่เอกสาร</label><input
                                type="text" id="init_doc" class="input input-sm input-bordered w-full"
                                placeholder="ถ้ามี"></div>
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
        <div class="modal-box bg-white rounded-xl shadow-2xl max-w-3xl border-t-4 border-blue-600">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>

            <div class="flex items-center gap-3 mb-6 border-b pb-4">
                <div class="bg-blue-100 p-3 rounded-full text-blue-600"><svg xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg></div>
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
                สินค้า</h4>
            <div class="overflow-x-auto rounded-lg border border-gray-200 max-h-80 overflow-y-auto">
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
                        </tr>
                    </thead>
                    <tbody id="viewTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </dialog>

    <script>
        window.onload = fetchItems;

        async function fetchItems() {
            try {
                const res = await fetch('api.php?action=get_items');
                const items = await res.json();
                const tbody = document.getElementById('itemTableBody');
                tbody.innerHTML = '';

                if (items.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-gray-400">ไม่พบข้อมูลวัสดุ</td></tr>`;
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
                                    <button onclick="openViewModal('${item.itemid}')" class="btn btn-sm btn-square btn-ghost text-green-600 join-item hover:bg-green-50 tooltip tooltip-top" data-tip="ดูรายละเอียด Lot">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    </button>
                                    <button onclick='openModal("edit", ${JSON.stringify(item)})' class="btn btn-sm btn-square btn-ghost text-blue-600 join-item hover:bg-blue-50 tooltip tooltip-top" data-tip="แก้ไข">
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

        // ฟังก์ชันเปิด Modal ดูรายละเอียด Lot
        async function openViewModal(itemid) {
            const modal = document.getElementById('viewModal');

            // เคลียร์ค่าเก่าก่อน
            document.getElementById('view_itemname').innerText = 'กำลังโหลด...';
            document.getElementById('viewTableBody').innerHTML = '<tr><td colspan="7" class="text-center py-4">กำลังโหลดข้อมูล...</td></tr>';
            modal.showModal();

            try {
                const res = await fetch(`api.php?action=get_item_lots&itemid=${itemid}`);
                const data = await res.json();

                if (data.success) {
                    const item = data.item;
                    const lots = data.lots;

                    // ใส่ข้อมูล Header
                    document.getElementById('view_itemname').innerText = item.itemname;
                    document.getElementById('view_itemid').innerText = item.itemid;
                    document.getElementById('view_type').innerText = item.type;
                    document.getElementById('view_unit').innerText = item.unit;
                    document.getElementById('view_count_lots').innerText = lots.length;
                    document.getElementById('view_total_remain').innerText = item.qty; // ค่าจาก Cache

                    // สร้างตาราง Lot
                    const tbody = document.getElementById('viewTableBody');
                    tbody.innerHTML = '';

                    if (lots.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-gray-400">ยังไม่มีประวัติการรับเข้า (No Lot History)</td></tr>`;
                    } else {
                        lots.forEach(lot => {
                            const dateIn = new Date(lot.date_in).toLocaleDateString('th-TH', { day: 'numeric', month: 'short', year: '2-digit' });
                            const isSoldOut = parseInt(lot.qty_remain) === 0;

                            // สถานะ badge
                            const statusBadge = isSoldOut
                                ? `<span class="badge badge-xs bg-gray-200 text-gray-500 border-none font-medium py-2 px-3">หมดแล้ว</span>`
                                : `<span class="badge badge-xs bg-green-100 text-green-700 border-none font-medium py-2 px-3">ใช้งานอยู่</span>`;

                            // Row Style (ถ้าหมดแล้วให้ตัวจางๆ)
                            const rowClass = isSoldOut ? 'opacity-60 bg-gray-50' : 'hover:bg-blue-50 transition-colors';

                            tbody.innerHTML += `
                                <tr class="${rowClass}">
                                    <td class="font-mono text-xs">${lot.lot_id}</td>
                                    <td>${dateIn}</td>
                                    <td>${lot.doc_no || '-'}</td>
                                    <td class="text-right">${parseFloat(lot.lot_price).toFixed(2)}</td>
                                    <td class="text-right text-blue-600 font-medium">+${lot.qty_initial}</td>
                                    <td class="text-right font-bold ${isSoldOut ? 'text-gray-400' : 'text-green-600'}">${lot.qty_remain}</td>
                                    <td class="text-center">${statusBadge}</td>
                                </tr>
                            `;
                        });
                    }
                } else {
                    Swal.fire('Error', 'ไม่สามารถดึงข้อมูลได้', 'error');
                    modal.close();
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
                modal.close();
            }
        }

        function openModal(mode, item = null) {
            document.getElementById('mode').value = mode;
            const modal = document.getElementById('itemModal');

            if (mode === 'add') {
                document.getElementById('modalTitle').innerText = 'เพิ่มวัสดุใหม่';
                document.getElementById('modalTitleIcon').innerText = '📦';
                document.getElementById('formItem').reset();
                document.getElementById('itemid').disabled = false;
                document.getElementById('addStockSection').classList.remove('hidden');
                document.getElementById('idHint').classList.remove('hidden');
            } else {
                document.getElementById('modalTitle').innerText = 'แก้ไขข้อมูลวัสดุ';
                document.getElementById('modalTitleIcon').innerText = '✏️';
                document.getElementById('itemid').value = item.itemid;
                document.getElementById('itemid').disabled = true;
                document.getElementById('itemname').value = item.itemname;
                document.getElementById('type').value = item.type;
                document.getElementById('unit').value = item.unit;
                document.getElementById('addStockSection').classList.add('hidden');
                document.getElementById('idHint').classList.add('hidden');
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
            }

            try {
                const res = await fetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(itemData)
                });
                const result = await res.json();

                if (result.success) {
                    Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', timer: 1500, showConfirmButton: false });
                    document.getElementById('itemModal').close();
                    fetchItems();
                } else {
                    Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: result.error });
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้' });
            }
        });

        async function deleteItem(id) {
            const result = await Swal.fire({
                title: 'ยืนยันการลบ?',
                text: "หากลบแล้วจะไม่สามารถกู้คืนได้ (ต้องไม่มีสต็อกคงเหลือ)",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ลบรายการนี้',
                cancelButtonText: 'ยกเลิก'
            });

            if (result.isConfirmed) {
                try {
                    const res = await fetch('api.php?action=delete_item', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ itemid: id })
                    });
                    const resp = await res.json();

                    if (resp.success) {
                        Swal.fire('ลบสำเร็จ!', 'รายการถูกลบออกจากระบบแล้ว', 'success');
                        fetchItems();
                    } else {
                        Swal.fire('ลบไม่ได้', resp.error, 'error');
                    }
                } catch (err) {
                    console.error(err);
                }
            }
        }
    </script>
</body>

</html>