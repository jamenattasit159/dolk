<!DOCTYPE html>
<html lang="th" data-theme="lofi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการฐานข้อมูลวัสดุ - สำนักงานที่ดินฯ</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }
    </style>
</head>

<body class="bg-base-200 min-h-screen p-4">

    <div class="navbar bg-base-100 shadow-lg rounded-box mb-6">
        <div class="flex-1">
            <a href="index.php" class="btn btn-ghost text-xl">⬅ กลับหน้าหลัก</a>
            <span class="text-xl font-bold text-primary ml-2">| จัดการฐานข้อมูลวัสดุ (Master Data)</span>
        </div>
    </div>

    <div class="max-w-6xl mx-auto">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">

                <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-4">
                    <h2 class="card-title text-2xl">รายชื่อวัสดุทั้งหมด</h2>
                    <div class="form-control w-full max-w-xs">
                        <input type="text" id="searchInput" onkeyup="filterItems()" placeholder="🔍 ค้นหาชื่อ, รหัส..."
                            class="input input-bordered w-full" />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead class="bg-base-200 text-base">
                            <tr>
                                <th>รหัสวัสดุ</th>
                                <th>ชื่อรายการ</th>
                                <th>ประเภท</th>

                                <th class="text-right">คงเหลือ</th>
                                <th>หน่วยนับ</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="itemTableBody">
                            <tr>
                                <td colspan="6" class="text-center">กำลังโหลดข้อมูล...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <dialog id="edit_modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">แก้ไขข้อมูลวัสดุ</h3>
            <form id="formEdit" class="space-y-3">
                <div class="form-control">
                    <label class="label font-bold">รหัสวัสดุ</label>
                    <input type="text" id="edit_itemid" class="input input-bordered bg-gray-100" readonly />
                </div>
                <div class="form-control">
                    <label class="label font-bold">ชื่อรายการ</label>
                    <input type="text" id="edit_itemname" class="input input-bordered" required />
                </div>
                <div class="form-control">
                    <label class="label font-bold">ประเภท</label>
                    <select id="edit_type" class="select select-bordered">
                        <option>วัสดุสำนักงาน</option>
                        <option>วัสดุคอมพิวเตอร์</option>
                        <option>วัสดุครัวเรือน</option>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label font-bold">หน่วยนับ</label>
                    <input type="text" id="edit_unit" class="input input-bordered" required />
                </div>

                <div class="modal-action">
                    <button type="button" class="btn"
                        onclick="document.getElementById('edit_modal').close()">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary text-white">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </dialog>

    <script>
        // 1. โหลดข้อมูลเมื่อเปิดหน้าเว็บ
        window.onload = loadItems;

        async function loadItems() {
            try {
                const res = await fetch('api.php?action=get_items');
                const data = await res.json();
                renderTable(data);
            } catch (error) {
                console.error('Error:', error);
                alert('โหลดข้อมูลไม่สำเร็จ');
            }
        }

        // 2. สร้างตาราง
        function renderTable(data) {
            const tbody = document.getElementById('itemTableBody');
            tbody.innerHTML = '';

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">ไม่พบข้อมูล</td></tr>';
                return;
            }

            data.forEach(item => {
                const unit = item.unit ? item.unit : '-';
                const stockClass = item.qty > 0 ? 'text-success font-bold' : 'text-error font-bold';

                tbody.innerHTML += `
                    <tr class="hover">
                        <td class="font-mono font-bold">${item.itemid}</td>
                        <td class="text-lg">${item.itemname}</td>
                        <td><span class="badge badge-ghost">${item.type}</span></td>
                        
                        <td class="text-right ${stockClass} text-lg">${numberWithCommas(item.qty)}</td>
                        <td>${item.unit}</td>
                        <td class="text-center">
                            <div class="join">
                                <button onclick="openEdit('${item.itemid}', '${item.itemname}', '${item.type}', '${unit}')" class="btn btn-warning btn-sm join-item text-white">แก้ไข</button>
                                <button onclick="deleteItem('${item.itemid}')" class="btn btn-error btn-sm join-item text-white">ลบ</button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }

        // 3. ฟังก์ชันค้นหา (Filter)
        function filterItems() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('itemTableBody');
            const tr = table.getElementsByTagName('tr');

            for (let i = 0; i < tr.length; i++) {
                const tdId = tr[i].getElementsByTagName('td')[0];
                const tdName = tr[i].getElementsByTagName('td')[1];
                if (tdId || tdName) {
                    const txtId = tdId.textContent || tdId.innerText;
                    const txtName = tdName.textContent || tdName.innerText;
                    if (txtId.toUpperCase().indexOf(filter) > -1 || txtName.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        // 4. เปิด Modal แก้ไข
        function openEdit(id, name, type, unit) {
            document.getElementById('edit_itemid').value = id;
            document.getElementById('edit_itemname').value = name;
            document.getElementById('edit_type').value = type;
            document.getElementById('edit_unit').value = (unit === '-' ? '' : unit);
            document.getElementById('edit_modal').showModal();
        }

        // 5. บันทึกการแก้ไข (Submit Edit)
        document.getElementById('formEdit').addEventListener('submit', async function (e) {
            e.preventDefault();
            const payload = {
                itemid: document.getElementById('edit_itemid').value,
                itemname: document.getElementById('edit_itemname').value,
                type: document.getElementById('edit_type').value,
                unit: document.getElementById('edit_unit').value
            };

            const res = await fetch('api.php?action=update_item', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.success) {
                alert('บันทึกเรียบร้อย');
                document.getElementById('edit_modal').close();
                loadItems(); // รีโหลดตาราง
            } else {
                alert('Error: ' + result.error);
            }
        });

        // 6. ลบรายการ
        async function deleteItem(id) {
            if (!confirm(`ต้องการลบรายการรหัส ${id} ใช่หรือไม่?\n(ลบได้เฉพาะรายการใหม่ที่ยังไม่มีการเคลื่อนไหว)`)) return;

            const res = await fetch('api.php?action=delete_item', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ itemid: id })
            });
            const result = await res.json();
            if (result.success) {
                alert('ลบสำเร็จ');
                loadItems();
            } else {
                alert('ลบไม่ได้: ' + result.error);
            }
        }

        // Helper: ใส่ลูกน้ำให้ตัวเลข
        function numberWithCommas(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
    </script>
</body>

</html>