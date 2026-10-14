<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-vh-100 d-flex flex-column">
                <header class="navbar navbar-dark bg-dark d-lg-none border-bottom border-secondary-subtle sticky-top px-3 py-2">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <a href="{{ route('home') }}" class="navbar-brand d-flex align-items-center gap-2 m-0 fs-6 fw-bold">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="rounded-2 object-fit-cover shadow-sm" style="width: 28px; height: 28px;">
                    <span>LETSPLAY <span class="text-danger">GAMING</span></span>
                </a>
            </div>

                        <a href="{{ route('customer.topup') }}" class="badge text-bg-dark border border-secondary text-decoration-none py-2 px-2.5 d-flex align-items-center gap-1 fs-7">
                <span class="text-warning fw-bold">฿</span>
                <span class="fw-bold">{{ number_format(auth()->user()->balance ?? 0, 2) }}</span>
            </a>
        </header>

        <!-- เมนูด้านข้างสำหรับเดสก์ท็อป -->
        <aside class="bs-sidebar d-none d-lg-flex flex-column position-fixed top-0 bottom-0 start-0 z-3 p-3 overflow-y-auto overflow-x-hidden">
                        <a href="{{ route('home') }}" class="d-flex align-items-center gap-2.5 text-decoration-none text-white mb-3 px-1">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="rounded-3 object-fit-cover shadow-sm border border-secondary-subtle" style="width: 38px; height: 38px;">
                <div class="lh-sm">
                    <div class="fw-black fs-6 tracking-wide">LETSPLAY <span class="text-danger">GAMING</span></div>
                    <small class="text-secondary" style="font-size: 12px;">Gaming Cafe & Kitchen</small>
                </div>
            </a>

                        <div class="bs-card p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-secondary fw-semibold">ยอดเงินในกระเป๋า</small>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 11px;">WALLET</span>
                </div>
                <div class="d-flex justify-content-between align-items-baseline">
                    <span class="fs-5 fw-bold text-white font-monospace">
                        ฿{{ number_format(auth()->user()->balance ?? 0, 2) }}
                    </span>
                    <a href="{{ route('customer.topup') }}" class="text-danger text-decoration-none fw-semibold small">
                        + เติมเงิน
                    </a>
                </div>
            </div>

                        <nav class="nav flex-column gap-1 flex-grow-1">
                <div class="text-secondary text-uppercase fw-bold px-2 pt-2 pb-1" style="font-size: 11px; letter-spacing: 0.05em;">
                    บริการลูกค้า
                </div>

                <a href="{{ route('dashboard') }}" class="bs-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 fs-6"></i>
                    <span>แดชบอร์ด</span>
                </a>

                <a href="{{ route('customer.seat-map') }}" class="bs-nav-link {{ request()->routeIs('customer.seat-map') ? 'active' : '' }}">
                    <i class="bi bi-display fs-6"></i>
                    <span>เลือกที่นั่ง (Seat Map)</span>
                </a>

                <a href="{{ route('customer.food-order') }}" class="bs-nav-link {{ request()->routeIs('customer.food-order') ? 'active' : '' }}">
                    <i class="bi bi-cup-hot fs-6"></i>
                    <span>สั่งอาหารและเครื่องดื่ม</span>
                </a>

                <a href="{{ route('customer.topup') }}" class="bs-nav-link {{ request()->routeIs('customer.topup') ? 'active' : '' }}">
                    <i class="bi bi-wallet2 fs-6"></i>
                    <span>เติมเงิน / แพ็กเกจเวลา</span>
                </a>

                @if (auth()->user()->isStaff())
                    <div class="text-secondary text-uppercase fw-bold px-2 pt-3 pb-1" style="font-size: 11px; letter-spacing: 0.05em;">
                        จัดการร้าน (เจ้าหน้าที่)
                    </div>

                    <a href="{{ route('staff.seat-monitor') }}" class="bs-nav-link {{ request()->routeIs('staff.seat-monitor') ? 'active' : '' }}">
                        <i class="bi bi-grid-3x3-gap fs-6"></i>
                        <span>มอนิเตอร์ที่นั่งหน้าร้าน</span>
                    </a>

                    <a href="{{ route('staff.kitchen-queue') }}" class="bs-nav-link {{ request()->routeIs('staff.kitchen-queue') ? 'active' : '' }}">
                        <i class="bi bi-fire fs-6"></i>
                        <span>คิวออเดอร์ห้องครัว</span>
                    </a>

                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.stock-manager') }}" class="bs-nav-link {{ request()->routeIs('admin.stock-manager') ? 'active' : '' }}">
                            <i class="bi bi-box-seam fs-6"></i>
                            <span>จัดการสต็อกสินค้า</span>
                        </a>

                        <a href="{{ route('admin.sales-report') }}" class="bs-nav-link {{ request()->routeIs('admin.sales-report') ? 'active' : '' }}">
                            <i class="bi bi-graph-up-arrow fs-6"></i>
                            <span>รายงานสรุปยอดขาย</span>
                        </a>
                    @endif
                @endif
            </nav>

            <hr class="border-secondary-subtle my-3">

                        <div class="dropdown dropup mt-auto">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle p-2 rounded-3 hover-bg-secondary w-100" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 0;">
                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold me-2 flex-shrink-0" style="width: 36px; height: 36px; font-size: 13px;">
                        {{ auth()->user()->initials() ?? 'U' }}
                    </div>
                    <div class="overflow-hidden lh-sm me-auto pe-1" style="min-width: 0;">
                        <div class="text-truncate fw-bold text-white" style="font-size: 13px;">{{ auth()->user()->name }}</div>
                        <small class="text-secondary text-truncate d-block" style="font-size: 11px;">{{ auth()->user()->email }}</small>
                    </div>
                </a>
                <ul class="dropdown-menu shadow-lg border-secondary-subtle w-100 mb-2 py-1" style="min-width: 100%; max-width: 100%;">
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-truncate" href="{{ route('profile.edit') }}">
                            <i class="bi bi-gear text-secondary"></i>
                            <span>การตั้งค่าโปรไฟล์</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider border-secondary-subtle my-1"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2 py-2 text-truncate">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>ออกจากระบบ</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- เมนูด้านข้างสำหรับมือถือ -->
        <div class="offcanvas offcanvas-start bg-dark text-white border-end border-secondary-subtle d-lg-none" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
            <div class="offcanvas-header border-bottom border-secondary-subtle">
                <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 text-decoration-none text-white" id="sidebarOffcanvasLabel">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="rounded-2 object-fit-cover shadow-sm" style="width: 28px; height: 28px;">
                    <span class="fw-bold">LETSPLAY <span class="text-danger">GAMING</span></span>
                </a>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column p-3">
                                <div class="bs-card p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-secondary">ยอดเงินในกระเป๋า</small>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 11px;">WALLET</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-baseline">
                        <span class="fs-5 fw-bold text-white font-monospace">฿{{ number_format(auth()->user()->balance ?? 0, 2) }}</span>
                        <a href="{{ route('customer.topup') }}" class="text-danger text-decoration-none fw-semibold small">+ เติมเงิน</a>
                    </div>
                </div>

                <nav class="nav flex-column gap-1 flex-grow-1">
                    <div class="text-secondary text-uppercase fw-bold px-2 pt-1 pb-1" style="font-size: 11px;">บริการลูกค้า</div>
                    <a href="{{ route('dashboard') }}" class="bs-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> แดชบอร์ด</a>
                    <a href="{{ route('customer.seat-map') }}" class="bs-nav-link {{ request()->routeIs('customer.seat-map') ? 'active' : '' }}"><i class="bi bi-display"></i> เลือกที่นั่ง</a>
                    <a href="{{ route('customer.food-order') }}" class="bs-nav-link {{ request()->routeIs('customer.food-order') ? 'active' : '' }}"><i class="bi bi-cup-hot"></i> สั่งอาหารและเครื่องดื่ม</a>
                    <a href="{{ route('customer.topup') }}" class="bs-nav-link {{ request()->routeIs('customer.topup') ? 'active' : '' }}"><i class="bi bi-wallet2"></i> เติมเงิน / แพ็กเกจ</a>

                    @if (auth()->user()->isStaff())
                        <div class="text-secondary text-uppercase fw-bold px-2 pt-3 pb-1" style="font-size: 11px;">จัดการร้าน</div>
                        <a href="{{ route('staff.seat-monitor') }}" class="bs-nav-link {{ request()->routeIs('staff.seat-monitor') ? 'active' : '' }}"><i class="bi bi-grid-3x3-gap"></i> มอนิเตอร์ที่นั่ง</a>
                        <a href="{{ route('staff.kitchen-queue') }}" class="bs-nav-link {{ request()->routeIs('staff.kitchen-queue') ? 'active' : '' }}"><i class="bi bi-fire"></i> คิวห้องครัว</a>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.stock-manager') }}" class="bs-nav-link {{ request()->routeIs('admin.stock-manager') ? 'active' : '' }}"><i class="bi bi-box-seam"></i> จัดการสต็อก</a>
                            <a href="{{ route('admin.sales-report') }}" class="bs-nav-link {{ request()->routeIs('admin.sales-report') ? 'active' : '' }}"><i class="bi bi-graph-up-arrow"></i> สรุปยอดขาย</a>
                        @endif
                    @endif
                </nav>

                <hr class="border-secondary-subtle my-3">

                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-bold text-white">{{ auth()->user()->name }}</div>
                        <small class="text-secondary">{{ auth()->user()->email }}</small>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-box-arrow-right"></i> ออก
                        </button>
                    </form>
                </div>
            </div>
        </div>

                <div class="bs-main-layout flex-grow-1 d-flex flex-column min-vh-100">
            {{ $slot }}
        </div>

        @include('partials.floating-hud')

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
