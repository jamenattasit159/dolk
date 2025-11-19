<!DOCTYPE html>
<html lang="th" data-theme="light">

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
            background-color: #f8faf7;
            min-height: 100vh;
        }

        /* --- Custom Styles --- */
        .glass-card {
            background: #ffffff;
            border: 1px solid #e5ebe4;
            box-shadow: 0 4px 20px rgba(0, 51, 20, 0.05);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            box-shadow: 0 8px 24px rgba(0, 51, 20, 0.1);
            transform: translateY(-2px);
        }

        .custom-navbar {
            background: linear-gradient(135deg, #0d7f4e 0%, #0a5f3c 100%);
            box-shadow: 0 4px 12px rgba(13, 127, 78, 0.2);
        }

        .fifo-box {
            font-size: 0.75rem;
            background: #f0f5f1;
            padding: 8px 10px;
            border-radius: 6px;
            border-left: 3px solid #0d7f4e;
            color: #1a4d31;
            line-height: 1.4;
        }

        .select-with-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%230d7f4e' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        .input-bordered,
        .select-bordered {
            border-color: #d9e4d6;
            background-color: #fafbf9;
        }

        .input-bordered:focus,
        .select-bordered:focus {
            border-color: #0d7f4e;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(13, 127, 78, 0.1);
            outline: none;
        }

        .btn-primary {
            background-color: #0d7f4e;
            border-color: #0d7f4e;
        }

        .btn-primary:hover {
            background-color: #0a5f3c;
            border-color: #0a5f3c;
        }

        .text-primary {
            color: #0d7f4e;
        }

        .rounded-3xl {
            border-radius: 16px;
        }

        .table thead {
            background: linear-gradient(135deg, #0d7f4e 0%, #0a5f3c 100%);
            color: white;
        }
    </style>
</head>

<body class="min-h-screen pb-10">

    <div class="navbar custom-navbar text-white shadow-lg rounded-b-2xl mb-8 sticky top-0 z-50">
        <div class="flex-1">
            <a class="btn btn-ghost text-xl font-bold tracking-wider">
                <span class="text-2xl">🏛️</span> สำนักงานที่ดินจังหวัดอ่างทอง
            </a>
        </div>
        <div class="flex-none hidden lg:block">
            <ul class="menu menu-horizontal px-1 gap-2">
                <li><a href="item.php"
                        class="btn btn-neutral btn-sm border-none bg-white/20 text-white hover:bg-white/30">จัดการฐานข้อมูล</a>
                </li>
                <li><a onclick="switchTab('add')" class="btn btn-ghost btn-sm">รับเข้าสต็อก</a></li>
                <li><a onclick="switchTab('withdraw')" class="btn btn-ghost btn-sm">เบิกจ่าย</a></li>
                <li><a onclick="switchTab('report')" class="btn btn-ghost btn-sm">รายงาน FIFO</a></li>
                <li>
                    <a href="print_all_stock.php" target="_blank"
                        class="btn btn-sm bg-white text-green-800 hover:bg-green-50 border-none shadow-md font-bold gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        <div id="page-add" class="hidden">
            <div class="glass-card rounded-3xl overflow-hidden">
                <div class="card-body p-8">
                    <h2 class="text-2xl font-bold mb-6 flex items-center gap-3 text-green-800">
                        <span class="text-3xl">📦</span> เพิ่มวัสดุเข้าสต็อก
                    </h2>
                    <form id="formAdd" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="form-control"><label class="label-text mb-1 ml-1">รหัสวัสดุ</label><input
                                type="text" id="add_itemid" class="input input-bordered" required /></div>
                        <div class="form-control"><label class="label-text mb-1 ml-1">ชื่อรายการ</label><input
                                type="text" id="add_itemname" class="input input-bordered" required /></div>
                        <div class="form-control"><label class="label-text mb-1 ml-1">หน่วยนับ</label><input type="text"
                                id="add_unit" class="input input-bordered" required /></div>

                        <div class="form-control"><label class="label-text mb-1 ml-1">ประเภท</label>
                            <select id="add_type" class="select select-bordered select-with-icon">
                                <option>วัสดุสำนักงาน</option>
                                <option>วัสดุคอมพิวเตอร์</option>
                                <option>วัสดุครัวเรือน</option>
                            </select>
                        </div>
                        <div class="form-control"><label class="label-text mb-1 ml-1">เลขที่เอกสาร</label><input
                                type="text" id="add_doc" class="input input-bordered" required /></div>
                        <div class="form-control"><label class="label-text mb-1 ml-1">จำนวนรับ</label><input
                                type="number" id="add_qty" class="input input-bordered" value="1" min="1" required />
                        </div>

                        <div class="form-control"><label class="label-text mb-1 ml-1">ราคาต่อหน่วย (บาท)</label><input
                                type="number" step="0.01" id="add_price" class="input input-bordered" placeholder="0.00"
                                required /></div>
                        <div class="form-control lg:col-span-2"><label class="label-text mb-1 ml-1">วันที่บันทึก
                                (ย้อนหลังได้)</label><input type="date" id="add_custom_date"
                                class="input input-bordered w-full" /></div>

                        <button type="submit"
                            class="btn btn-primary text-white col-span-full shadow-lg hover:shadow-xl mt-2 text-lg">
                            บันทึกเข้าสต็อก
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div id="page-withdraw">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <div class="glass-card rounded-3xl overflow-hidden">
                        <div class="card-body p-8">
                            <h2 class="text-2xl font-bold mb-6 flex items-center gap-3 text-red-700">
                                <span class="text-3xl">📤</span> เบิกวัสดุ
                            </h2>
                            <form id="formWithdraw" class="space-y-5">
                                <div class="form-control">
                                    <label class="label-text mb-1 ml-1 font-bold">ผู้เบิก</label>
                                    <select id="wd_member" class="select select-bordered w-full select-with-icon"
                                        required></select>
                                </div>
                                <div class="form-control">
                                    <label class="label-text mb-1 ml-1 font-bold">เลือกวัสดุ</label>
                                    <select id="wd_item" class="select select-bordered w-full select-with-icon" required
                                        onchange="showItemDetails()"></select>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="form-control">
                                        <label class="label-text mb-1 ml-1">จำนวน</label>
                                        <input type="number" id="wd_qty" min="1" value="1" class="input input-bordered"
                                            required />
                                    </div>
                                    <div class="form-control">
                                        <label class="label-text mb-1 ml-1">เลขที่ใบเบิก</label>
                                        <input type="text" id="wd_doc" class="input input-bordered"
                                            placeholder="เช่น บ.10/2568" required />
                                    </div>
                                </div>
                                <div class="form-control">
                                    <label class="label-text mb-1 ml-1 text-gray-500">วันที่เบิก (เว้นว่าง =
                                        วันนี้)</label>
                                    <input type="date" id="wd_custom_date" class="input input-bordered w-full" />
                                </div>
                                <button type="submit"
                                    class="btn btn-error w-full text-white shadow-lg hover:shadow-xl text-lg mt-2">
                                    ยืนยันการเบิกวัสดุ
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="glass-card rounded-3xl h-fit bg-green-50 border-green-100">
                        <div class="card-body p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="text-2xl">📊</span> ข้อมูลสต็อก (FIFO)
                            </h3>
                            <div id="itemInfoDisplay" class="text-center py-10 text-gray-400">กรุณาเลือกวัสดุ</div>
                            <div id="itemDetailsContent" class="hidden space-y-4">
                                <div class="text-xl font-bold text-primary break-words" id="info_name">-</div>
                                <div class="flex justify-between items-center p-3 bg-white rounded-xl shadow-sm">
                                    <span class="text-gray-600">คงเหลือ:</span>
                                    <span class="text-3xl font-bold text-primary" id="info_stock">0</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-white rounded-xl shadow-sm">
                                    <span class="text-gray-600">ราคาต้นทุน:</span>
                                    <div><span class="text-xl font-bold text-gray-800" id="info_price">0.00</span> <span
                                            class="text-xs">บาท</span></div>
                                </div>
                                <div class="text-xs text-gray-500 text-center mt-2">
                                    * ระบบตัดจากล็อตเก่าสุดก่อน (FIFO)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="page-report" class="hidden">
            <div class="glass-card rounded-3xl overflow-hidden">
                <div class="card-body p-8">
                    <h2 class="text-2xl font-bold mb-6 flex items-center gap-3 text-gray-800">
                        <span class="text-3xl">📈</span> รายงานการเบิกจ่าย (FIFO Log)
                    </h2>
                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="table table-zebra w-full text-sm">
                            <thead>
                                <tr>
                                    <th width="15%">วันที่</th>
                                    <th width="20%">ผู้เบิก</th>
                                    <th width="20%">รายการ</th>
                                    <th width="10%" class="text-right">จำนวน</th>
                                    <th width="10%" class="text-right">มูลค่ารวม</th>
                                    <th width="20%">รายละเอียด FIFO</th>
                                    <th width="5%" class="text-center">จัดการ</th>
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
        window.onload = loadDropdowns;
        let currentItems = [];

        async function loadDropdowns() {
            try {
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
            } catch (e) { console.error(e); }
        }

        function showItemDetails() {
            const item = currentItems.find(i => i.itemid === document.getElementById('wd_item').value);
            document.getElementById('itemInfoDisplay').classList.add('hidden');

            if (item) {
                document.getElementById('itemDetailsContent').classList.remove('hidden');
                document.getElementById('info_name').innerText = item.itemname;
                document.getElementById('info_stock').innerText = item.qty;
                document.getElementById('info_price').innerText = parseFloat(item.current_price || 0).toFixed(2);
            }
        }

        // === Add Item ===
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
                this.reset(); loadDropdowns();
            } else {
                Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: result.error });
            }
        });

        // === Withdraw ===
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
                this.reset(); loadDropdowns();
                document.getElementById('itemDetailsContent').classList.add('hidden');
                document.getElementById('itemInfoDisplay').classList.remove('hidden');
            } else {
                Swal.fire({ icon: 'error', title: 'เบิกไม่สำเร็จ', text: result.error });
            }
        });

        // === Render Report with Cancel Button ===
        async function renderReport() {
            const res = await fetch('api.php?action=get_history');
            const data = await res.json();
            const tbody = document.getElementById('reportTableBody');
            tbody.innerHTML = '';

            data.forEach(t => {
                tbody.innerHTML += `
                    <tr class="hover:bg-green-50 transition-all align-top">
                        <td class="font-medium">${t.trx_date}</td>
                        <td>
                            <div class="font-bold text-gray-800">${t.member_name}</div>
                            <div class="text-xs opacity-60">${t.department}</div>
                            <div class="text-xs text-blue-600 mt-1">เอกสาร: ${t.doctax_no || '-'}</div>
                        </td>
                        <td>${t.itemname} <br><span class="badge badge-ghost badge-xs">${t.type}</span></td>
                        <td class="text-right font-bold text-error">-${t.qty_withdraw}</td>
                        <td class="text-right font-medium">${parseFloat(t.total_cost).toLocaleString('th-TH', { minimumFractionDigits: 2 })} ฿</td>
                        <td><div class="fifo-box">${t.fifo_details || '-'}</div></td>
                        <td class="text-center">
                            <button onclick="cancelTransaction(${t.tid})" class="btn btn-sm btn-square btn-ghost text-error hover:bg-red-50 tooltip tooltip-left" data-tip="ยกเลิก/ลบรายการ">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }

        // === Cancel Transaction (SweetAlert2) ===
        async function cancelTransaction(tid) {
            const result = await Swal.fire({
                title: 'ยืนยันการยกเลิก?',
                text: "รายการนี้จะถูกลบ และสินค้าจะถูกคืนกลับเข้าสต็อก (คืนยอด Lot เดิม)!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c03030',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'ใช่, ลบรายการเลย',
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
                    } else {
                        Swal.fire('ผิดพลาด', resp.error, 'error');
                    }
                } catch (err) {
                    Swal.fire('Error', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error');
                }
            }
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