<!DOCTYPE html>
<html lang="th" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Dashboard - สำนักงานที่ดินจังหวัดอ่างทอง</title>

    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f0f4f8;
            min-height: 100vh;
        }

        /* Card Design */
        .dashboard-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s;
            border: 1px solid #eef2f6;
        }

        .dashboard-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        /* Navbar Gradient */
        .nav-gradient {
            background: linear-gradient(120deg, #0d7f4e 0%, #159957 100%);
        }

        /* Avatar Placeholder */
        .avatar-placeholder {
            background-color: #e2e8f0;
            color: #475569;
            font-weight: 600;
        }

        .animate-slide-up {
            animation: slideUp 0.6s ease-out forwards;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="pb-12">

    <div class="navbar nav-gradient text-white shadow-lg rounded-b-2xl mb-8 sticky top-0 z-50 px-4 lg:px-8">
        <div class="flex-1">
            <a href="dashboard.php" class="btn btn-ghost text-xl font-bold tracking-wide gap-3">
                <span class="text-2xl">🏛️</span> สำนักงานที่ดินจังหวัดอ่างทอง
            </a>
        </div>
        <div class="flex-none gap-2">
            <a href="index.php" class="btn btn-sm bg-white/20 border-none text-white hover:bg-white/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                ไปหน้าทำงาน
            </a>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 animate-slide-up">

        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">ภาพรวมคลังพัสดุ</h1>
                <p class="text-gray-500 mt-1">ข้อมูลสถานะปัจจุบันและการเคลื่อนไหวล่าสุด</p>
            </div>
            <div class="flex gap-3">
                <a href="index.php"
                    class="btn bg-white text-gray-700 border-gray-200 hover:bg-gray-50 hover:border-gray-300 shadow-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z" />
                    </svg>
                    เบิกจ่ายด่วน
                </a>
                <a href="item.php" class="btn bg-[#0d7f4e] text-white border-none hover:bg-[#0a5f3c] shadow-md gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    เพิ่มสินค้าใหม่
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="dashboard-card p-6 relative overflow-hidden group">
                <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-blue-600" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path
                            d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                    </svg>
                </div>
                <p class="text-gray-500 font-medium text-sm">สินค้าทั้งหมด</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <h3 class="text-4xl font-bold text-gray-800" id="d_items">-</h3>
                    <span class="text-sm text-gray-400">รายการ</span>
                </div>
                <div class="mt-4 h-1 w-full bg-blue-100 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500 w-3/4 rounded-full"></div>
                </div>
            </div>

            <div class="dashboard-card p-6 relative overflow-hidden group">
                <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-green-600" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path
                            d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <p class="text-gray-500 font-medium text-sm">มูลค่าในคลัง</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <h3 class="text-4xl font-bold text-gray-800" id="d_value">-</h3>
                    <span class="text-sm text-gray-400">บาท</span>
                </div>
                <div class="mt-4 h-1 w-full bg-green-100 rounded-full overflow-hidden">
                    <div class="h-full bg-green-500 w-full rounded-full"></div>
                </div>
            </div>

            <div class="dashboard-card p-6 relative overflow-hidden group">
                <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-orange-500" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <p class="text-gray-500 font-medium text-sm">เบิกจ่ายวันนี้</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <h3 class="text-4xl font-bold text-gray-800" id="d_today">-</h3>
                    <span class="text-sm text-gray-400">รายการ</span>
                </div>
                <div class="mt-4 h-1 w-full bg-orange-100 rounded-full overflow-hidden">
                    <div class="h-full bg-orange-500 w-1/2 rounded-full"></div>
                </div>
            </div>

            <div class="dashboard-card p-6 relative overflow-hidden group border-l-4 border-red-500">
                <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-red-500" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <p class="text-gray-500 font-medium text-sm">ใกล้หมด (<5)< /p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-4xl font-bold text-red-600" id="d_low">-</h3>
                            <span class="text-sm text-gray-400">รายการ</span>
                        </div>
                        <div
                            class="mt-4 flex items-center gap-2 text-xs text-red-500 font-medium bg-red-50 w-fit px-2 py-1 rounded-md">
                            ⚠️ ควรเติมสต็อกด่วน
                        </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="dashboard-card p-6 lg:col-span-2">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <span class="bg-blue-100 text-blue-600 p-2 rounded-lg text-xl">📈</span>
                        แนวโน้มการเบิกจ่าย (7 วัน)
                    </h3>
                </div>
                <div id="mainChart" style="min-height: 300px;"></div>
            </div>

            <div class="dashboard-card p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <span class="bg-purple-100 text-purple-600 p-2 rounded-lg text-xl">🍰</span>
                    สัดส่วนคลังพัสดุ
                </h3>
                <div id="pieChart" class="flex justify-center" style="min-height: 300px;"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="dashboard-card p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="bg-yellow-100 text-yellow-600 p-2 rounded-lg text-xl">🏆</span>
                    5 อันดับ เบิกสูงสุด
                </h3>
                <div id="topItemsChart" style="min-height: 250px;"></div>
            </div>

            <div class="dashboard-card p-6 lg:col-span-2">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <span class="bg-green-100 text-green-600 p-2 rounded-lg text-xl">🕒</span>
                        การเคลื่อนไหวล่าสุด
                    </h3>
                    <a href="index.php" class="text-sm text-[#0d7f4e] hover:underline font-medium">ดูทั้งหมด &rarr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead class="bg-gray-50 text-gray-500 font-normal">
                            <tr>
                                <th>เวลา</th>
                                <th>ผู้เบิก</th>
                                <th>รายการ</th>
                                <th class="text-right">จำนวน</th>
                            </tr>
                        </thead>
                        <tbody id="recentTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = loadDashboard;

        async function loadDashboard() {
            try {
                const res = await fetch('api.php?action=get_dashboard_data');
                const data = await res.json();

                if (data.success) {
                    // 1. Update Cards
                    document.getElementById('d_items').innerText = data.summary.items;
                    document.getElementById('d_value').innerText = parseFloat(data.summary.value).toLocaleString('th-TH', { maximumFractionDigits: 0 });
                    document.getElementById('d_today').innerText = data.summary.today;
                    document.getElementById('d_low').innerText = data.summary.low;

                    // 2. Render Charts
                    renderMainChart(data.chart);
                    renderPieChart(data.pie_data);
                    renderTopItemsChart(data.top_items);

                    // 3. Render Table
                    renderRecentTable(data.recent);
                }
            } catch (e) { console.error(e); }
        }

        // --- Chart Configurations ---
        function renderMainChart(data) {
            new ApexCharts(document.querySelector("#mainChart"), {
                series: [{ name: 'จำนวนเบิก', data: data.map(d => d.total_qty) }],
                chart: { type: 'area', height: 300, fontFamily: 'Sarabun', toolbar: { show: false } },
                stroke: { curve: 'smooth', width: 3 },
                colors: ['#0d7f4e'],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.6, opacityTo: 0.1 } },
                xaxis: { categories: data.map(d => d.date_label) },
                dataLabels: { enabled: false }
            }).render();
        }

        function renderPieChart(data) {
            new ApexCharts(document.querySelector("#pieChart"), {
                series: data.map(d => parseInt(d.count)),
                labels: data.map(d => d.type),
                chart: { type: 'donut', height: 320, fontFamily: 'Sarabun' },
                colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
                plotOptions: { donut: { size: '65%' } },
                dataLabels: { enabled: false },
                legend: { position: 'bottom' }
            }).render();
        }

        function renderTopItemsChart(data) {
            new ApexCharts(document.querySelector("#topItemsChart"), {
                series: [{ data: data.map(d => d.total_qty) }],
                chart: { type: 'bar', height: 250, fontFamily: 'Sarabun', toolbar: { show: false } },
                plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '60%' } },
                colors: ['#f59e0b'],
                xaxis: { categories: data.map(d => d.itemname) },
                dataLabels: { enabled: true, textAnchor: 'start', style: { colors: ['#fff'] }, offsetX: 0 }
            }).render();
        }

        function renderRecentTable(data) {
            const tbody = document.getElementById('recentTableBody');
            tbody.innerHTML = '';
            data.forEach(row => {
                // สร้าง Avatar จากตัวอักษรแรกของชื่อ
                const initial = row.member_name.charAt(0);
                const date = new Date(row.trx_date).toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });

                tbody.innerHTML += `
                    <tr class="hover:bg-gray-50 transition">
                        <td class="font-mono text-xs text-gray-500">${date}</td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar placeholder">
                                    <div class="bg-neutral text-neutral-content rounded-full w-8 h-8">
                                        <span class="text-xs">${initial}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-bold text-sm text-gray-800">${row.member_name}</div>
                                    <div class="text-xs text-gray-500">${row.department}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-sm text-gray-600">${row.itemname}</td>
                        <td class="text-right font-bold text-red-600">-${row.qty_withdraw}</td>
                    </tr>
                `;
            });
        }
    </script>
</body>

</html>