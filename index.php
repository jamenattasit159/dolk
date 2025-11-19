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
                <li><a href="dashboard.php" class="btn btn-ghost btn-sm">📊 ภาพรวม</a></li>
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
        <div id="alert-stock-container"
            class="fixed bottom-4 left-4 z-50 w-96 hidden transition-all duration-500 transform translate-y-0"></div>
        <div id="page-add" class="hidden animate-fade-in">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-2">
                    <div class="glass-card rounded-2xl overflow-hidden border-t-4 border-[#0d7f4e] shadow-lg">
                        <div class="p-8">
                            <div class="flex items-center justify-between mb-8">
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                                        <span class="bg-green-100 text-green-700 p-3 rounded-xl shadow-sm">📦</span>
                                        บันทึกรับเข้าสต็อก
                                    </h2>
                                    <p class="text-gray-400 text-sm mt-1 ml-1">
                                        กรอกข้อมูลเพื่อนำวัสดุใหม่เข้าสู่คลังพัสดุ</p>
                                </div>
                            </div>

                            <form id="formAdd" class="space-y-8">
                                <div class="relative">
                                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                        <div class="w-full border-t border-gray-200"></div>
                                    </div>
                                    <div class="relative flex justify-start">
                                        <span
                                            class="pr-3 bg-white text-sm font-bold text-gray-500 uppercase tracking-wider">
                                            1. ข้อมูลสินค้า (Item Details)
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-6 rounded-xl border border-gray-100 hover:border-gray-300 transition-colors">
                                    <div class="form-control">
                                        <label class="label-text font-bold mb-2 text-gray-700">รหัสวัสดุ <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" id="add_itemid"
                                            class="input input-bordered focus:border-[#0d7f4e] focus:ring-4 focus:ring-green-50 bg-white w-full"
                                            placeholder="เช่น A001" required />
                                        <label class="label"><span class="label-text-alt text-gray-400">รหัสอ้างอิง หรือ
                                                Barcode</span></label>
                                    </div>
                                    <div class="form-control">
                                        <label class="label-text font-bold mb-2 text-gray-700">ชื่อรายการ <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" id="add_itemname"
                                            class="input input-bordered focus:border-[#0d7f4e] focus:ring-4 focus:ring-green-50 bg-white w-full"
                                            placeholder="ระบุชื่อวัสดุ" required />
                                    </div>
                                    <div class="form-control">
                                        <label class="label-text font-bold mb-2 text-gray-700">ประเภท</label>
                                        <select id="add_type"
                                            class="select select-bordered focus:border-[#0d7f4e] focus:ring-4 focus:ring-green-50 bg-white w-full">
                                            <option>วัสดุสำนักงาน</option>
                                            <option>วัสดุคอมพิวเตอร์</option>
                                            <option>วัสดุครัวเรือน</option>
                                            <option>อื่นๆ</option>
                                        </select>
                                    </div>
                                    <div class="form-control">
                                        <label class="label-text font-bold mb-2 text-gray-700">หน่วยนับ</label>
                                        <input type="text" id="add_unit"
                                            class="input input-bordered focus:border-[#0d7f4e] focus:ring-4 focus:ring-green-50 bg-white w-full"
                                            placeholder="เช่น ชิ้น, กล่อง, เล่ม" required />
                                    </div>
                                </div>

                                <div class="relative">
                                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                        <div class="w-full border-t border-gray-200"></div>
                                    </div>
                                    <div class="relative flex justify-start">
                                        <span
                                            class="pr-3 bg-white text-sm font-bold text-[#0d7f4e] uppercase tracking-wider">
                                            2. รายละเอียด Lot (Stock Info)
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="bg-green-50/50 p-6 rounded-xl border border-green-100 hover:border-green-300 transition-colors">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div class="form-control">
                                            <label class="label-text font-bold mb-2 text-gray-700">เลขที่เอกสาร</label>
                                            <input type="text" id="add_doc"
                                                class="input input-bordered focus:border-[#0d7f4e] bg-white w-full"
                                                placeholder="ใบส่งของ/ใบเสร็จ" required />
                                        </div>
                                        <div class="form-control">
                                            <label class="label-text font-bold mb-2 text-gray-700">จำนวนรับ <span
                                                    class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <input type="number" id="add_qty"
                                                    class="input input-bordered focus:border-[#0d7f4e] bg-white w-full pr-10 font-bold text-lg text-[#0d7f4e]"
                                                    value="1" min="1" required />
                                                <div
                                                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-400 text-sm">หน่วย</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-control">
                                            <label class="label-text font-bold mb-2 text-gray-700">ราคาต่อหน่วย</label>
                                            <div class="relative">
                                                <input type="number" step="0.01" id="add_price"
                                                    class="input input-bordered focus:border-[#0d7f4e] bg-white w-full pr-10"
                                                    placeholder="0.00" required />
                                                <div
                                                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-400 text-sm">฿</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-control md:col-span-3">
                                            <label class="label-text font-bold mb-2 text-gray-700">วันที่บันทึก</label>
                                            <input type="date" id="add_custom_date"
                                                class="input input-bordered focus:border-[#0d7f4e] bg-white w-full cursor-pointer" />
                                            <label class="label">
                                                <span class="label-text-alt text-gray-400">📅 หากเว้นว่าง
                                                    ระบบจะใช้วันที่ปัจจุบัน</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end items-center gap-4 pt-4 border-t border-gray-100">
                                    <button type="reset"
                                        class="btn btn-ghost text-gray-500 hover:bg-gray-100">ล้างข้อมูล</button>
                                    <button type="submit"
                                        class="btn bg-[#0d7f4e] hover:bg-[#0a5f3c] text-white px-8 h-12 text-lg shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all rounded-xl flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        ยืนยันนำเข้า
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="hidden lg:block space-y-6">
                    <div
                        class="glass-card rounded-2xl p-6 bg-gradient-to-br from-[#0d7f4e] to-[#065f3a] text-white shadow-lg relative overflow-hidden">
                        <div
                            class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl">
                        </div>
                        <h3 class="font-bold text-lg mb-3 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            ข้อแนะนำ
                        </h3>
                        <ul class="text-sm space-y-3 opacity-90 list-disc pl-5 leading-relaxed">
                            <li>ตรวจสอบ <b>รหัสวัสดุ</b> ให้ถูกต้อง หากมีรหัสเดิมระบบจะอัปเดตจำนวนเพิ่มให้</li>
                            <li>ระบบ FIFO จะตัดสต็อกตาม <b>ราคาต่อหน่วย</b> ของแต่ละล็อต</li>
                            <li>สามารถบันทึกย้อนหลังได้โดยเลือกวันที่ในช่อง <b>วันที่บันทึก</b></li>
                        </ul>
                    </div>

                    <div class="glass-card rounded-2xl p-6 border border-gray-100 shadow-sm">
                        <h3 class="font-bold text-gray-700 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            เพิ่มล่าสุด (Session นี้)
                        </h3>
                        <ul class="space-y-3" id="recentAddedList">
                            <li
                                class="text-center text-gray-400 text-sm py-6 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                                ยังไม่มีรายการใหม่
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div id="page-withdraw" class="hidden animate-fade-in">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-2">
                    <div class="glass-card rounded-2xl overflow-hidden border-t-4 border-red-500 shadow-lg">
                        <div class="p-8">
                            <div class="flex items-center justify-between mb-8">
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                                        <span class="bg-red-100 text-red-600 p-3 rounded-xl shadow-sm">📤</span>
                                        เบิกจ่ายวัสดุ
                                    </h2>
                                    <p class="text-gray-400 text-sm mt-1 ml-1">ตัดยอดสต็อกตามระบบ FIFO
                                        (เข้าก่อน-ออกก่อน)</p>
                                </div>
                            </div>

                            <form id="formWithdraw" class="space-y-6">
                                <div class="bg-red-50/50 p-6 rounded-xl border border-red-100">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="form-control md:col-span-2">
                                            <label class="label-text font-bold mb-2 text-gray-700">ผู้เบิก <span
                                                    class="text-red-500">*</span></label>
                                            <select id="wd_member"
                                                class="select select-bordered focus:border-red-500 focus:ring-4 focus:ring-red-50 bg-white w-full"
                                                required>
                                            </select>
                                        </div>

                                        <div class="form-control md:col-span-2">
                                            <label class="label-text font-bold mb-2 text-gray-700">เลือกวัสดุ <span
                                                    class="text-red-500">*</span></label>
                                            <select id="wd_item"
                                                class="select select-bordered focus:border-red-500 focus:ring-4 focus:ring-red-50 bg-white w-full"
                                                required onchange="showItemDetails()">
                                            </select>
                                        </div>

                                        <div class="form-control">
                                            <label class="label-text font-bold mb-2 text-gray-700">จำนวนที่เบิก <span
                                                    class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <input type="number" id="wd_qty" min="1" value="1"
                                                    class="input input-bordered focus:border-red-500 bg-white w-full pr-10 font-bold text-lg text-red-600"
                                                    required oninput="validateStock()" />
                                                <div
                                                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-400 text-sm">หน่วย</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-control">
                                            <label class="label-text font-bold mb-2 text-gray-700">เลขที่ใบเบิก</label>
                                            <input type="text" id="wd_doc"
                                                class="input input-bordered focus:border-red-500 bg-white w-full"
                                                placeholder="เช่น บ.10/2568" required />
                                        </div>

                                        <div class="form-control md:col-span-2">
                                            <label class="label-text font-bold mb-2 text-gray-700">วันที่เบิก</label>
                                            <input type="date" id="wd_custom_date"
                                                class="input input-bordered focus:border-red-500 bg-white w-full" />
                                            <label class="label"><span class="label-text-alt text-gray-400">📅
                                                    หากเว้นว่าง ระบบจะใช้วันที่ปัจจุบัน</span></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end pt-2">
                                    <button type="submit" id="btnWithdraw"
                                        class="btn bg-red-600 hover:bg-red-700 text-white px-8 h-12 text-lg shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all rounded-xl flex items-center gap-2 w-full md:w-auto">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        ยืนยันการเบิก
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="hidden lg:block">
                    <div id="stockStatusCard"
                        class="glass-card rounded-2xl p-8 h-full border border-gray-100 flex flex-col justify-center items-center text-center transition-all">

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
                            <p class="text-sm text-gray-400 mt-2">
                                กรุณาเลือกรายการจากเมนูด้านซ้าย<br>เพื่อตรวจสอบยอดคงเหลือ</p>
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

                            <div class="flex justify-between items-center text-sm text-gray-500 px-2">
                                <span>ราคาเฉลี่ย/หน่วย:</span>
                                <span class="font-bold text-gray-700" id="disp_price">0.00 ฿</span>
                            </div>

                            <div id="stock-alert"
                                class="hidden mt-4 p-3 bg-red-100 text-red-700 text-sm rounded-lg font-bold flex items-center gap-2 justify-center animate-pulse">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                จำนวนเบิกเกินสต็อกที่มี!
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
                checkLowStock(currentItems);

                const mSelect = document.getElementById('wd_member');
                mSelect.innerHTML = '<option value="" disabled selected>-- เลือกผู้เบิก --</option>';
                members.forEach(m => mSelect.innerHTML += `<option value="${m.eid}">${m.name}</option>`);

                const iSelect = document.getElementById('wd_item');
                iSelect.innerHTML = '<option value="" disabled selected>-- เลือกวัสดุ --</option>';
                currentItems.forEach(i => iSelect.innerHTML += `<option value="${i.itemid}">${i.itemname}</option>`);
            } catch (e) { console.error(e); }
        }

        function showItemDetails() {
            const itemId = document.getElementById('wd_item').value;
            const item = currentItems.find(i => i.itemid === itemId);

            const emptyState = document.getElementById('state-empty');
            const selectedState = document.getElementById('state-selected');
            const card = document.getElementById('stockStatusCard');

            if (item) {
                // ซ่อนสถานะว่าง -> โชว์ข้อมูล
                emptyState.classList.add('hidden');
                selectedState.classList.remove('hidden');

                // ใส่ข้อมูล
                document.getElementById('disp_id').innerText = item.itemid;
                document.getElementById('disp_name').innerText = item.itemname;
                document.getElementById('disp_type').innerText = item.type;
                document.getElementById('disp_stock').innerText = item.qty;
                document.getElementById('disp_unit').innerText = item.unit;
                document.getElementById('disp_price').innerText = parseFloat(item.current_price || 0).toFixed(2) + ' ฿';

                // เปลี่ยนสีการ์ดตามสถานะสต็อก
                if (parseInt(item.qty) === 0) {
                    card.classList.remove('border-gray-100', 'border-green-200');
                    card.classList.add('bg-gray-50', 'border-gray-200');
                    document.getElementById('disp_stock').classList.replace('text-green-600', 'text-gray-400');
                } else {
                    card.classList.remove('bg-gray-50', 'border-gray-200');
                    card.classList.add('border-green-200');
                    document.getElementById('disp_stock').classList.replace('text-gray-400', 'text-green-600');
                }

                validateStock(); // เช็คยอดเบิกทันที
            } else {
                emptyState.classList.remove('hidden');
                selectedState.classList.add('hidden');
            }
        }

        // ฟังก์ชันตรวจสอบยอดเบิก (Real-time Validation)
        function validateStock() {
            const itemId = document.getElementById('wd_item').value;
            const item = currentItems.find(i => i.itemid === itemId);
            const withdrawQty = parseInt(document.getElementById('wd_qty').value) || 0;
            const btn = document.getElementById('btnWithdraw');
            const alertBox = document.getElementById('stock-alert');

            if (!item) return;

            const stockQty = parseInt(item.qty);

            if (withdrawQty > stockQty) {
                // เบิกเกิน -> แจ้งเตือน
                alertBox.classList.remove('hidden');
                btn.disabled = true;
                btn.classList.add('btn-disabled', 'opacity-50');
                document.getElementById('wd_qty').classList.add('text-red-600', 'border-red-500');
            } else {
                // ปกติ
                alertBox.classList.add('hidden');
                btn.disabled = false;
                btn.classList.remove('btn-disabled', 'opacity-50');
                document.getElementById('wd_qty').classList.remove('text-red-600', 'border-red-500');
            }
        }

        // === Add Item ===
        document.getElementById('formAdd').addEventListener('submit', async function (e) {
            e.preventDefault();

            // เก็บค่าไว้ก่อน reset
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
                Swal.fire({
                    icon: 'success',
                    title: 'บันทึกสำเร็จ',
                    text: 'นำเข้าสต็อกเรียบร้อยแล้ว',
                    timer: 1500,
                    showConfirmButton: false
                });

                this.reset();
                loadDropdowns();

                // [เพิ่มส่วนนี้] อัปเดตรายการด้านขวา (Recent Added List)
                const list = document.getElementById('recentAddedList');
                if (list.querySelector('li.text-center')) list.innerHTML = ''; // ลบข้อความ "ยังไม่มีรายการ" ออก

                const newItemHTML = `
                    <li class="flex items-center gap-3 p-3 bg-green-50 rounded-lg border border-green-100 animate-fade-in">
                        <div class="bg-green-200 text-green-700 p-2 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-800">${itemName}</div>
                            <div class="text-xs text-green-600">+${qty} ${unit}</div>
                        </div>
                        <div class="ml-auto text-xs text-gray-400">เมื่อสักครู่</div>
                    </li>
                `;
                list.insertAdjacentHTML('afterbegin', newItemHTML);

                // ตัดให้เหลือแค่ 5 รายการล่าสุด
                if (list.children.length > 5) list.lastElementChild.remove();

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
        function checkLowStock(items) {
            const lowStockItems = items.filter(i => parseInt(i.qty) < 5);
            const container = document.getElementById('alert-stock-container');

            if (lowStockItems.length > 0) {
                let listHtml = '';
                // แสดงแค่ 3 รายการแรก เพื่อไม่ให้กล่องยาวเกินไป (ถ้ามีเยอะ)
                const showLimit = 3;
                lowStockItems.slice(0, showLimit).forEach(i => {
                    listHtml += `<li class="flex justify-between items-center">
                        <span>${i.itemname}</span>
                        <span class="badge badge-sm badge-error text-white">${i.qty} ${i.unit}</span>
                    </li>`;
                });

                // ถ้ามีมากกว่า 3 รายการ ให้บอกจำนวนที่เหลือ
                if (lowStockItems.length > showLimit) {
                    listHtml += `<li class="text-xs text-center mt-1 opacity-75">และอีก ${lowStockItems.length - showLimit} รายการ...</li>`;
                }

                container.innerHTML = `
                    <div class="card bg-base-100 shadow-2xl border-l-4 border-warning animate-bounce-in">
                        <div class="card-body p-4">
                            <div class="flex justify-between items-start">
                                <h3 class="font-bold text-warning flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    วัสดุใกล้หมด (${lowStockItems.length})
                                </h3>
                                <button onclick="document.getElementById('alert-stock-container').classList.add('hidden')" class="btn btn-xs btn-square btn-ghost">✕</button>
                            </div>
                            <ul class="text-sm mt-2 space-y-1 text-gray-600">
                                ${listHtml}
                            </ul>
                        </div>
                    </div>
                `;
                container.classList.remove('hidden');
            } else {
                container.innerHTML = '';
                container.classList.add('hidden');
            }
        }
    </script>
</body>

</html>