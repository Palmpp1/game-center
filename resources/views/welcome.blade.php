<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Letsplay Gaming Cafe | ร้านเกมและคาเฟ่ 24 ชม.</title>
    @include('partials.head')
</head>
<body class="min-vh-100 d-flex flex-column justify-content-between">
        <header class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top border-bottom border-secondary-subtle px-3 py-2 shadow-sm">
        <div class="container-fluid max-w-7xl">
                        <a href="{{ route('home') }}" class="navbar-brand d-flex align-items-center gap-2 text-decoration-none">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="rounded-3 object-fit-cover shadow-sm border border-secondary-subtle" style="width: 38px; height: 38px;">
                <div class="lh-sm">
                    <div class="fw-black fs-5 tracking-wide text-white">
                        LETSPLAY <span class="text-danger">GAMING CAFE</span>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1" style="font-size: 10px;">24 HRS</span>
                    </div>
                    <small class="text-secondary d-none d-sm-block" style="font-size: 11px;">Gaming Café </small>
                </div>
            </a>



                        <div class="d-flex align-items-center gap-2">
                @auth
                                        <a href="{{ route('customer.topup') }}" class="btn btn-outline-secondary btn-sm rounded-pill d-none d-sm-flex align-items-center gap-2 px-3">
                        <span class="badge bg-warning text-dark rounded-circle" style="width: 20px; height: 20px; line-height: 12px; font-size: 11px;">฿</span>
                        <span class="fw-bold text-light font-monospace">{{ number_format(auth()->user()->balance, 2) }}</span>
                        <span class="badge bg-danger rounded-pill" style="font-size: 10px;">+ เติม</span>
                    </a>

                    <a href="{{ route('dashboard') }}" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold">
                        เข้าสู่แดชบอร์ด <i class="bi bi-arrow-right"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-link text-light text-decoration-none btn-sm">
                        เข้าสู่ระบบ
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold">
                        สมัครสมาชิก
                    </a>
                @endauth
            </div>
        </div>
    </header>

        <main class="container py-4 flex-grow-1">
                <div class="card bg-dark border-secondary-subtle rounded-4 overflow-hidden position-relative p-4 p-md-5 mb-4 shadow-lg">
            <img
                src="https://cmsassets.rgpub.io/sanity/images/dsfx7636/news_live/4c679fc9b8f253d5915261338f81fe1043fded45-3440x1020.jpg?accountingTag=VAL&fit=fill&fm=jpg&q=80&h=1020"
                alt="Valorant Gaming Hero"
                class="position-absolute top-0 end-0 bottom-0 start-0 w-100 h-100 object-fit-cover opacity-25"
                style="pointer-events: none;"
            >
            <div class="position-absolute top-0 end-0 bottom-0 start-0 bg-gradient" style="background: linear-gradient(90deg, #0c0f17 20%, rgba(12,15,23,0.85) 60%, transparent 100%); pointer-events: none;"></div>

            <div class="position-relative z-1" style="max-width: 680px;">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1 px-2.5">
                        <i class="bi bi-clock"></i> เปิดบริการ 24 ชั่วโมง
                    </span>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle py-1 px-2.5">
                        <i class="bi bi-lightning-charge"></i> เน็ตไฟเบอร์ 1000/1000 ปิงต่ำ 5ms
                    </span>
                    <span class="badge bg-info-subtle text-info border border-info-subtle py-1 px-2.5">
                        <i class="bi bi-snow"></i> แอร์เย็น 23°C ตลอดวัน
                    </span>
                </div>

                <h1 class="display-6 fw-black text-white mb-3">
                    ร้านเกมสเปกท็อป จอ 240Hz แอร์เย็นฉ่ำ <br>
                    <span class="text-danger">พร้อมอาหารถึงโต๊ะ 24 ชม.</span>
                </h1>

                <p class="text-secondary fs-6 mb-4 lh-base">
                    สัมผัสบรรยากาศร้านเกมยุคใหม่ สเปกแรงเล่นลื่นทุกเกม Valorant,Overwatch, GTA V, Apex Legends พร้อมสั่งข้าวกะเพรา มาม่าต้มยำ ขนมเลย์ และน้ำอัดลมเย็นเจี๊ยบส่งตรงถึงโต๊ะคอม ไม่ต้องลุกให้เสียจังหวะ
                </p>

                <div class="d-flex flex-wrap gap-2">
                    @auth
                        <a href="{{ route('customer.seat-map') }}" class="btn btn-danger fw-bold px-4 py-2 rounded-3 shadow">
                            <i class="bi bi-display"></i> เลือกผังโต๊ะคอม
                        </a>
                        <a href="{{ route('customer.food-order') }}" class="btn btn-outline-secondary text-light fw-bold px-4 py-2 rounded-3">
                            <i class="bi bi-cup-hot"></i> สั่งอาหาร & เครื่องดื่ม
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-danger fw-bold px-4 py-2 rounded-3 shadow">
                            <i class="bi bi-rocket-takeoff"></i> เข้าสู่ระบบเพื่อเปิดเครื่อง
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline-secondary text-light fw-bold px-4 py-2 rounded-3">
                            สมัครสมาชิกใหม่
                        </a>
                    @endauth
                </div>
            </div>
        </div>

                <div class="d-flex gap-2 overflow-x-auto pb-2 mb-4 text-nowrap">
            <button class="btn btn-danger btn-sm rounded-pill px-3 fw-bold"><i class="bi bi-fire me-1"></i> ยอดนิยมทั้งหมด</button>
            <a href="{{ route('customer.seat-map') }}" class="btn btn-outline-secondary text-light btn-sm rounded-pill px-3"><i class="bi bi-display me-1"></i> ผังโซนคอมพิวเตอร์</a>
            <a href="{{ route('customer.food-order') }}" class="btn btn-outline-secondary text-light btn-sm rounded-pill px-3"><i class="bi bi-egg-fried me-1"></i> อาหารตามสั่ง</a>
            <a href="{{ route('customer.food-order') }}" class="btn btn-outline-secondary text-light btn-sm rounded-pill px-3"><i class="bi bi-cup-hot me-1"></i> บะหมี่และมาม่า</a>
            <a href="{{ route('customer.food-order') }}" class="btn btn-outline-secondary text-light btn-sm rounded-pill px-3"><i class="bi bi-bag me-1"></i> ขนมและของทานเล่น</a>
            <a href="{{ route('customer.food-order') }}" class="btn btn-outline-secondary text-light btn-sm rounded-pill px-3"><i class="bi bi-cup-straw me-1"></i> เครื่องดื่ม</a>
            <a href="{{ route('customer.topup') }}" class="btn btn-outline-secondary text-light btn-sm rounded-pill px-3"><i class="bi bi-clock-history me-1"></i> แพ็กเกจชั่วโมง</a>
        </div>

        <!-- โซนเครื่องเล่นเกม -->
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="bg-danger rounded" style="width: 4px; height: 22px;"></span>
                    <h2 class="h5 fw-bold text-white m-0">โซนที่นั่งเกมมิ่งระดับพรีเมียม (PC Zones)</h2>
                </div>
                <a href="{{ route('customer.seat-map') }}" class="text-danger text-decoration-none fw-semibold small">
                    ดูผังที่นั่งทั้งหมด <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="row g-4">
                                <div class="col-12 col-md-4">
                    <div class="card h-100 bg-dark border-secondary-subtle rounded-4 p-4 d-flex flex-column justify-content-between shadow-sm">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">STANDARD PC</span>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle small">165Hz Fast IPS</span>
                            </div>
                            <h3 class="h5 fw-bold text-white mb-2">โซนคอมมาตรฐาน (Standard Zone)</h3>
                            <p class="text-secondary small mb-3">
                                Intel Core i5-13400F, RTX 4060 8GB, RAM 32GB, จอ 24.5" 165Hz Fast IPS, คีย์บอร์ด Mechanical, เก้าอี้เกมมิ่งระบายอากาศ เล่นลื่นทุกเกม FPS
                            </p>
                        </div>
                        <div class="pt-3 border-top border-secondary-subtle d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fs-4 fw-black text-white font-monospace">฿40</span>
                                <small class="text-secondary">/ 1 ชม.</small>
                            </div>
                            <a href="{{ route('customer.seat-map') }}" class="btn btn-danger btn-sm px-3 fw-bold rounded-3">เลือกโต๊ะนี้</a>
                        </div>
                    </div>
                </div>

                                <div class="col-12 col-md-4">
                    <div class="card h-100 bg-dark border-danger-subtle rounded-4 p-4 d-flex flex-column justify-content-between shadow-sm">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">VIP LOUNGE</span>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle small">240Hz 0.5ms</span>
                            </div>
                            <h3 class="h5 fw-bold text-white mb-2">ห้องวีไอพี เลานจ์ (VIP Lounge)</h3>
                            <p class="text-secondary small mb-3">
                                Intel Core i7-14700KF, RTX 4070 Ti 12GB, RAM 32GB DDR5, จอโค้ง 27" 240Hz 0.5ms, คีย์บอร์ด Custom Linear, หูฟัง HyperX 7.1 รอบทิศทาง
                            </p>
                        </div>
                        <div class="pt-3 border-top border-secondary-subtle d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fs-4 fw-black text-white font-monospace">฿60</span>
                                <small class="text-secondary">/ 1 ชม.</small>
                            </div>
                            <a href="{{ route('customer.seat-map') }}" class="btn btn-danger btn-sm px-3 fw-bold rounded-3">เลือกโต๊ะนี้</a>
                        </div>
                    </div>
                </div>

                                <div class="col-12 col-md-4">
                    <div class="card h-100 bg-dark border-secondary-subtle rounded-4 p-4 d-flex flex-column justify-content-between shadow-sm">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">PRO STREAMER</span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle small">Studio 4K</span>
                            </div>
                            <h3 class="h5 fw-bold text-white mb-2">ห้องสตรีมเมอร์ (Pro Studio)</h3>
                            <p class="text-secondary small mb-3">
                                Intel Core i9-14900K, RTX 4090 24GB, RAM 64GB, จอคู่ 280Hz, ไมค์สตูดิโอ Shure, กล้อง Sony 4K, ไฟ Elgato, ห้องกระจกเก็บเสียงเป็นส่วนตัว
                            </p>
                        </div>
                        <div class="pt-3 border-top border-secondary-subtle d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fs-4 fw-black text-white font-monospace">฿100</span>
                                <small class="text-secondary">/ 1 ชม.</small>
                            </div>
                            <a href="{{ route('customer.seat-map') }}" class="btn btn-danger btn-sm px-3 fw-bold rounded-3">เลือกโต๊ะนี้</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- เมนูอาหารแนะนำ -->
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="bg-danger rounded" style="width: 4px; height: 22px;"></span>
                    <h2 class="h5 fw-bold text-white m-0">เมนูยอดฮิตประจำร้าน เสิร์ฟร้อนถึงโต๊ะ (Food & Snacks)</h2>
                </div>
                <a href="{{ route('customer.food-order') }}" class="text-danger text-decoration-none fw-semibold small">
                    ดูเมนูทั้งหมด <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-6 g-3">
                                <div class="col">
                    <div class="card h-100 bg-dark border-secondary-subtle rounded-3 p-3 text-center d-flex flex-column justify-content-between">
                        <div>
                            <div class="fs-1 text-danger mb-2"><i class="bi bi-cup-hot"></i></div>
                            <span class="badge bg-danger-subtle text-danger mb-1" style="font-size: 10px;">เมนูยอดฮิต</span>
                            <div class="fw-bold text-truncate text-white small">มาม่าต้มยำหมูสับ</div>
                            <small class="text-secondary d-block" style="font-size: 11px;">ต้มยำน้ำข้นไข่เยิ้ม</small>
                        </div>
                        <div class="pt-2 border-top border-secondary-subtle mt-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-white">฿45</span>
                            <a href="{{ route('customer.food-order') }}" class="btn btn-danger btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">+</a>
                        </div>
                    </div>
                </div>

                                <div class="col">
                    <div class="card h-100 bg-dark border-secondary-subtle rounded-3 p-3 text-center d-flex flex-column justify-content-between">
                        <div>
                            <div class="fs-1 text-warning mb-2"><i class="bi bi-egg-fried"></i></div>
                            <span class="badge bg-warning-subtle text-warning mb-1" style="font-size: 10px;">ขายดีอันดับ 1</span>
                            <div class="fw-bold text-truncate text-white small">ข้าวกะเพราหมูสับ</div>
                            <small class="text-secondary d-block" style="font-size: 11px;">โปะไข่ดาวกรอบ</small>
                        </div>
                        <div class="pt-2 border-top border-secondary-subtle mt-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-white">฿59</span>
                            <a href="{{ route('customer.food-order') }}" class="btn btn-danger btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">+</a>
                        </div>
                    </div>
                </div>

                                <div class="col">
                    <div class="card h-100 bg-dark border-secondary-subtle rounded-3 p-3 text-center d-flex flex-column justify-content-between">
                        <div>
                            <div class="fs-1 text-danger mb-2"><i class="bi bi-basket2"></i></div>
                            <span class="badge bg-danger-subtle text-danger mb-1" style="font-size: 10px;">เมนูโปรด</span>
                            <div class="fw-bold text-truncate text-white small">ข้าวหมูกระเทียม</div>
                            <small class="text-secondary d-block" style="font-size: 11px;">หอมเจียวกรอบ</small>
                        </div>
                        <div class="pt-2 border-top border-secondary-subtle mt-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-white">฿59</span>
                            <a href="{{ route('customer.food-order') }}" class="btn btn-danger btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">+</a>
                        </div>
                    </div>
                </div>

                                <div class="col">
                    <div class="card h-100 bg-dark border-secondary-subtle rounded-3 p-3 text-center d-flex flex-column justify-content-between">
                        <div>
                            <div class="fs-1 text-success mb-2"><i class="bi bi-bag"></i></div>
                            <span class="badge bg-success-subtle text-success mb-1" style="font-size: 10px;">ขนมยอดฮิต</span>
                            <div class="fw-bold text-truncate text-white small">เลย์ โนริสาหร่าย</div>
                            <small class="text-secondary d-block" style="font-size: 11px;">ซองใหญ่กรอบเพลิน</small>
                        </div>
                        <div class="pt-2 border-top border-secondary-subtle mt-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-white">฿30</span>
                            <a href="{{ route('customer.food-order') }}" class="btn btn-danger btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">+</a>
                        </div>
                    </div>
                </div>

                                <div class="col">
                    <div class="card h-100 bg-dark border-secondary-subtle rounded-3 p-3 text-center d-flex flex-column justify-content-between">
                        <div>
                            <div class="fs-1 text-danger mb-2"><i class="bi bi-box2"></i></div>
                            <span class="badge bg-danger-subtle text-danger mb-1" style="font-size: 10px;">ของทอด</span>
                            <div class="fw-bold text-truncate text-white small">ไส้กรอกแดงทอด</div>
                            <small class="text-secondary d-block" style="font-size: 11px;">จิ้มน้ำจิ้มมะขาม</small>
                        </div>
                        <div class="pt-2 border-top border-secondary-subtle mt-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-white">฿35</span>
                            <a href="{{ route('customer.food-order') }}" class="btn btn-danger btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">+</a>
                        </div>
                    </div>
                </div>

                                <div class="col">
                    <div class="card h-100 bg-dark border-secondary-subtle rounded-3 p-3 text-center d-flex flex-column justify-content-between">
                        <div>
                            <div class="fs-1 text-warning mb-2"><i class="bi bi-cup-straw"></i></div>
                            <span class="badge bg-warning-subtle text-warning mb-1" style="font-size: 10px;">เย็นชื่นใจ</span>
                            <div class="fw-bold text-truncate text-white small">ชาไทยเย็นเข้มข้น</div>
                            <small class="text-secondary d-block" style="font-size: 11px;">แก้วใหญ่ 22 ออนซ์</small>
                        </div>
                        <div class="pt-2 border-top border-secondary-subtle mt-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-white">฿35</span>
                            <a href="{{ route('customer.food-order') }}" class="btn btn-danger btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">+</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-top border-secondary-subtle bg-dark py-4 text-secondary small">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="rounded-2 object-fit-cover shadow-sm" style="width: 24px; height: 24px;">
                <span class="fw-bold text-white">Letsplay <span class="text-danger">Gaming Cafe</span></span>
            </div>

            <div class="d-flex flex-wrap gap-3">
                <span><i class="bi bi-qr-code text-success"></i> พร้อมเพย์ QR</span>
                <span><i class="bi bi-wallet2 text-primary"></i> วอลเล็ท</span>
                <span><i class="bi bi-cash-coin text-warning"></i> เงินสดเคาน์เตอร์</span>
            </div>

            <div class="text-secondary" style="font-size: 11px;">
                &copy; {{ date('Y') }} Letsplay Gaming Cafe. All rights reserved.
            </div>
        </div>
    </footer>
</body>
</html>
