<!DOCTYPE html>
<html lang="th" data-theme="emerald">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>สำนักงานที่ดินจังหวัดอ่างทอง</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="uploads/dol.ico">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@latest/dist/full.min.css" rel="stylesheet" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        html {
            /* เปลี่ยนเว็บเป็นขาวดำ 100% */
            -webkit-filter: grayscale(100%);
            -moz-filter: grayscale(100%);
            filter: grayscale(100%);
        }

        body {
            font-family: "Sarabun", system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background: linear-gradient(to bottom, hsl(var(--b2)), hsl(var(--b3)));
        }

        /* 🌟 ปรับธีม Emerald ให้ "ขาว-เขียว" เจ๋งจัด: พื้นหลังขาวบริสุทธิ์, เงาเขียวเข้ม */
        [data-theme="emerald"] {
            --b1: 0 0% 100%;
            --b2: 0 0% 98%;
            --b3: 0 0% 96%;
            --p: 160 60% 40%;
            /* เขียวเข้ม premium */
            --pf: 160 60% 35%;
            /* เขียวเข้ม focus */
        }

        /* การแบ่งสัดส่วนชัดเจนด้วยเงาและขอบ */
        .navbar {
            border-bottom: 2px solid hsl(var(--p) / 0.2);
            background: hsl(var(--b1));
            box-shadow: 0 2px 8px hsl(var(--bc) / 0.1);
        }

        .card.bg-base-100 {
            border: 1px solid hsl(var(--p) / 0.15);
            box-shadow: 0 6px 16px hsl(var(--bc) / 0.12);
            transition: all 0.3s ease-in-out;
            border-radius: 12px;
        }

        .card.bg-base-100:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 24px hsl(var(--bc) / 0.2);
            border-color: hsl(var(--p) / 0.3);
        }

        /* ปุ่มและเมนู: เน้นความ premium */
        .btn,
        .menu li a {
            transition: all 0.3s ease-in-out;
            border-radius: 8px;
        }

        .btn-primary {
            background: linear-gradient(45deg, hsl(var(--p)), hsl(var(--pf)));
            border: none;
            color: hsl(var(--b1));
            font-weight: 600;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, hsl(var(--pf)), hsl(var(--p)));
            transform: scale(1.05);
        }

        .menu li a:hover {
            background: hsl(var(--p) / 0.15);
            color: hsl(var(--p));
            transform: translateX(4px);
        }

        /* หัวข้อและ badge: ชัดเจนและ formal */
        .card-title {
            font-weight: 800;
            letter-spacing: -0.02em;
            color: hsl(var(--bc) / 0.9);
        }

        .badge-outline {
            border: 2px solid hsl(var(--p));
            color: hsl(var(--p));
            font-weight: 600;
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
        }

        .badge-success {
            background: hsl(var(--p));
            color: hsl(var(--b1));
            font-weight: 600;
        }

        /* Aside sticky: เน้นการแบ่งสัดส่วน */
        aside .card {
            background: hsl(var(--b1));
            border: 1px solid hsl(var(--p) / 0.1);
            box-shadow: 0 4px 12px hsl(var(--bc) / 0.08);
        }

        /* Main content: เงาและขอบชัดเจน */
        main {
            border: 1px solid hsl(var(--p) / 0.1);
            box-shadow: 0 8px 20px hsl(var(--bc) / 0.1);
            background: hsl(var(--b1));
        }

        /* Footer: สีเขียวเข้ม formal */
        footer {
            color: hsl(var(--bc) / 0.7);
            border-top: 2px solid hsl(var(--p) / 0.2);
            background: hsl(var(--b2));
        }
    </style>
</head>

<body>

    <div class="drawer">
        <input id="my-drawer-3" type="checkbox" class="drawer-toggle" />
        <div class="drawer-content flex flex-col">
            <!-- Navbar -->
            <div class="w-full navbar bg-base-100 sticky top-0 z-50">
                <div class="flex-none lg:hidden">
                    <label for="my-drawer-3" class="btn btn-square btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-6 h-6 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </label>
                </div>
                <div class="flex-1">
                    <a href="index.html"
                        class="btn btn-ghost normal-case text-xl md:text-2xl font-bold">สำนักงานที่ดินจังหวัดอ่างทอง</a>
                </div>
                <div class="flex-none hidden lg:block">
                    <ul class="menu menu-horizontal gap-2">
                        <li><a href="index.html" class="active">หน้าแรก</a></li>
                        <li><a href="pdf.html">ประกาศทั้งหมด</a></li>
                        <li><a href="contact.html">ติดต่อเรา</a></li>
                    </ul>
                </div>
            </div>

            <div class="min-h-screen">
                <div class="mx-auto max-w-7xl px-4 lg:px-8 py-8 lg:py-12">
                    <div class="grid grid-cols-1 lg:grid-cols-[360px,1fr] gap-8">

                        <aside class="space-y-8">
                            <div class="sticky top-28 space-y-8">
                                <div class="card bg-base-100 shadow-lg">
                                    <figure class="p-5">
                                        <img src="uploads/officer.jpg" alt="เจ้าพนักงานที่ดินจังหวัดอ่างทอง"
                                            class="rounded-xl object-cover aspect-[3/4] w-full border border-base-300" />
                                    </figure>
                                    <div class="card-body py-4 items-center text-center">
                                        <h2 class="card-title text-xl">นายประภัสร์ รื่นภาคเพ็ชร</h2>
                                        <h1 class="text-base-content/80 text-base font-medium">
                                            เจ้าพนักงานที่ดินจังหวัดอ่างทอง</h1>
                                    </div>
                                </div>

                                <section class="card bg-base-100 shadow-lg">
                                    <header class="px-5 py-4 border-b-2 border-base-300">
                                        <h3 class="font-bold text-xl text-base-content/90">ประกาศล่าสุด</h3>
                                    </header>
                                    <nav class="p-3">
                                        <ul class="menu menu-lg gap-2">
                                            <li><a class="justify-between font-medium" href="https://classic.dol.go.th/"
                                                    aria-label="เกี่ยวกับกรมที่ดิน">เกี่ยวกับกรมที่ดิน <span
                                                        class="badge badge-success">ใหม่</span></a></li>
                                            <li><a href="upfile/Lands.pdf" class="font-medium">ผู้บริหารกรมที่ดิน</a>
                                            </li>
                                            <li><a href="upfile/Lands ang.pdf" class="font-medium">โครงสร้างหน่วยงาน</a>
                                            </li>
                                            <li><a href="Budget.html" class="font-medium">จัดซื้อจัดจ้าง</a></li>
                                            <li><a href="Trial Balance.html" class="font-medium">งบทดลอง</a></li>
                                            <li><a href="https://elands.dol.go.th/QueueOnlineServer/queue/253?queueNo=&counterNo="
                                                    class="font-medium">คิวบริการ</a></li>
                                        </ul>
                                        <div class="px-4 pt-4 text-center">
                                            <a href="pdf.html" class="btn btn-primary btn-block">
                                                ดูประกาศทั้งหมด
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        </div>
                                    </nav>
                                </section>
                            </div>
                        </aside>

                        <main class="bg-base-100 rounded-3xl p-6 lg:p-10 shadow-lg">
                            <div class="max-w-4xl mx-auto">
                                <div class="flex items-center justify-center mb-8">
                                    <div class="badge badge-lg badge-outline px-8 py-5 text-lg font-bold">
                                        ข่าวประชาสัมพันธ์</div>
                                </div>

                                <div class="grid gap-10 md:grid-cols-1">
                                    <article class="card bg-base-100 shadow-lg">
                                        <figure class="p-4">
                                            <img src="uploads/278503.jpg" alt="ข่าวประชาสัมพันธ์ 1"
                                                class="rounded-xl w-full object-cover" />
                                        </figure>
                                        <div class="card-body py-4">
                                            <h3 class="card-title text-lg">Smartlands รวม 19 บริการกรมที่ดิน</h3>
                                            <p class="text-base text-base-content/70">Smartland Application
                                                ที่รวมบริการทางออนไลน์ของกรมที่ดิน เช่น จองคิวยื่นคำขอจดทะเบียนที่ดิน
                                                คำนวณภาษีอากร ค่าใช้จ่ายการรังวัด</p>

                                        </div>
                                    </article>

                                    <article class="card bg-base-100 shadow-lg">
                                        <figure class="p-4">
                                            <img src="uploads/e-QLands.jpg" alt="ข่าวประชาสัมพันธ์ 2"
                                                class="rounded-xl w-full object-cover" />
                                        </figure>
                                        <div class="card-body py-4">
                                            <h3 class="card-title text-lg">e-Qlands จองคิวล่วงหน้า</h3>
                                            <p class="text-base text-base-content/70">Application e-Qlands
                                                สำหรับจองคิวล่วงหน้า ลดเวลารอคอย สะดวก รวดเร็ว ลดค่าใช้จ่าย</p>

                                        </div>
                                    </article>

                                    <article class="card bg-base-100 shadow-lg">
                                        <figure class="p-4">
                                            <img src="uploads/Eservice3.jpg" alt="ข่าวประชาสัมพันธ์ 3"
                                                class="rounded-xl w-full object-cover" />
                                        </figure>
                                        <div class="card-body py-4">
                                            <h3 class="card-title text-lg">บริการ E-services กรมที่ดิน</h3>
                                            <p class="text-base text-base-content/70">E-service กรมที่ดิน
                                                ขอหนังสือรับรองราคาประเมินทุนทรัพย์ ตรวจสอบหลักทรัพย์
                                                สำเนาภาพลักษณ์เอกสารสิทธิ ยื่นขอได้ตลอด 24 ชั่วโมง </p>

                                        </div>
                                    </article>
                                </div>
                            </div>
                        </main>
                    </div>
                </div>

                <footer class="mt-12 text-center text-base font-medium py-10 border-t-2">
                    © <span id="year"></span> สำนักงานที่ดินจังหวัดอ่างทอง 1/5 หมู่ 8 ถนน 309 อยุธยา-อ่างทอง<br>
                    ตำบลโพสะ อำเภอเมืองอ่างทอง<br>
                    จังหวัดอ่างทอง 14000
                </footer>
            </div>

            <script>
                document.getElementById('year').textContent = new Date().getFullYear();
            </script>
</body>


</html>