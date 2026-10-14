<x-layouts::app :title="__('Sales Report')">
    <div class="d-flex flex-column gap-4">
                @if (session()->has('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 mb-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

                <div class="card p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 shadow-sm">
            <div>
                <div class="step-header">
                    <span class="step-bar"></span>
                    <h2 class="h4 fw-bold text-white mb-0">รายงานสรุปยอดขาย (Sales & Analytics)</h2>
                </div>
                <p class="text-secondary small ps-3 mt-1 mb-0">สรุปรายได้แยกตามค่าชั่วโมงเล่นเกมและค่าอาหาร/เครื่องดื่ม (Aggregation)</p>
            </div>

            <div class="btn-group btn-group-sm" role="group" aria-label="Period Filter">
                <a
                    href="{{ route('admin.sales-report', ['period' => 'today']) }}"
                    class="btn {{ $period === 'today' ? 'btn-danger' : 'btn-outline-secondary' }}"
                >
                    วันนี้
                </a>
                <a
                    href="{{ route('admin.sales-report', ['period' => '7days']) }}"
                    class="btn {{ $period === '7days' ? 'btn-danger' : 'btn-outline-secondary' }}"
                >
                    7 วันล่าสุด
                </a>
                <a
                    href="{{ route('admin.sales-report', ['period' => 'month']) }}"
                    class="btn {{ $period === 'month' ? 'btn-danger' : 'btn-outline-secondary' }}"
                >
                    30 วันล่าสุด
                </a>
                <a
                    href="{{ route('admin.sales-report', ['period' => 'all']) }}"
                    class="btn {{ $period === 'all' ? 'btn-danger' : 'btn-outline-secondary' }}"
                >
                    ทั้งหมด
                </a>
            </div>
        </div>

        <!-- สรุปยอดรายได้ -->
        <div class="row g-3">
                        <div class="col-12 col-sm-6 col-lg-3">
                <div class="card p-3 p-md-4 text-white shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 100%);">
                    <p class="small fw-bold text-white-50 text-uppercase tracking-wider mb-1">รายได้รวมทั้งหมด (Total Revenue)</p>
                    <h3 class="h2 fw-black font-monospace mb-1">฿{{ number_format($grandTotalRevenue, 2) }}</h3>
                    <p class="small text-white-50 mb-0">ค่าชั่วโมง + แพ็กเกจ + อาหาร</p>
                </div>
            </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                <div class="card p-3 p-md-4 shadow-sm h-100">
                    <p class="small fw-bold text-secondary text-uppercase tracking-wider mb-1">รายได้ค่าชั่วโมงเล่นเกม</p>
                    <h3 class="h3 fw-black text-white font-monospace mb-2">฿{{ number_format($totalGamingRevenue, 2) }}</h3>
                    <div class="small text-secondary lh-sm">
                        <div>• รายชั่วโมง: ฿{{ number_format($hourlyRevenue, 2) }} ({{ $completedSessionsCount }} เซสชัน)</div>
                        <div class="mt-1">• ขายแพ็กเกจ: ฿{{ number_format($packageRevenue, 2) }} ({{ $packageSalesCount }} บิล)</div>
                    </div>
                </div>
            </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                <div class="card p-3 p-md-4 shadow-sm h-100">
                    <p class="small fw-bold text-secondary text-uppercase tracking-wider mb-1">รายได้อาหารและเครื่องดื่ม</p>
                    <h3 class="h3 fw-black text-white font-monospace mb-2">฿{{ number_format($foodRevenue, 2) }}</h3>
                    <p class="small text-secondary mb-0">จำนวนออเดอร์ที่ชำระแล้ว: {{ $paidOrdersCount }} บิล</p>
                </div>
            </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                <div class="card p-3 p-md-4 shadow-sm h-100">
                    <p class="small fw-bold text-secondary text-uppercase tracking-wider mb-1">ยอดเงินที่เติมเข้า Wallet</p>
                    <h3 class="h3 fw-black text-success font-monospace mb-2">฿{{ number_format($totalTopup, 2) }}</h3>
                    <p class="small text-secondary mb-0">กระแสเงินสดรับเข้าระบบ</p>
                </div>
            </div>
        </div>

                <div class="row g-4">
                        <div class="col-12 col-lg-6">
                <div class="card p-4 shadow-sm h-100">
                    <div class="step-header mb-3">
                        <span class="step-bar"></span>
                        <h3 class="h5 fw-bold text-white mb-0">5 อันดับเมนูขายดี (Top 5 Best Selling)</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark text-uppercase small text-secondary">
                                <tr>
                                    <th class="py-2 px-3">อันดับ / เมนู</th>
                                    <th class="py-2 px-3 text-center">จำนวนที่ขายได้</th>
                                    <th class="py-2 px-3 text-end">ยอดขายรวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topProducts as $idx => $tp)
                                    <tr>
                                        <td class="py-3 px-3 fw-bold text-white">
                                            <span class="text-danger fw-black me-1">#{{ $idx + 1 }}</span>
                                            {{ $tp->name }}
                                        </td>
                                        <td class="py-3 px-3 text-center font-monospace fw-bold text-light">
                                            {{ $tp->total_qty }} ชิ้น
                                        </td>
                                        <td class="py-3 px-3 text-end font-monospace fw-bold text-success">
                                            ฿{{ number_format($tp->total_sales, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-4 text-center text-secondary small">ยังไม่มีข้อมูลยอดขายอาหาร</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

                        <div class="col-12 col-lg-6">
                <div class="card p-4 shadow-sm h-100">
                    <div class="step-header mb-3">
                        <span class="step-bar"></span>
                        <h3 class="h5 fw-bold text-white mb-0">สถิติการใช้งานแยกตามโซน (Usage by Zone)</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark text-uppercase small text-secondary">
                                <tr>
                                    <th class="py-2 px-3">ชื่อโซน</th>
                                    <th class="py-2 px-3 text-center">จำนวนเครื่อง</th>
                                    <th class="py-2 px-3 text-center">รอบที่เล่นจบ</th>
                                    <th class="py-2 px-3 text-end">ยอดเงินค่าบริการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($zoneStats as $zs)
                                    <tr>
                                        <td class="py-3 px-3 fw-bold text-white">
                                            {{ $zs['zone']->name }}
                                        </td>
                                        <td class="py-3 px-3 text-center font-monospace text-light">
                                            {{ $zs['zone']->seats_count }} เครื่อง
                                        </td>
                                        <td class="py-3 px-3 text-center font-monospace text-light">
                                            {{ $zs['sessions_count'] }} ครั้ง
                                        </td>
                                        <td class="py-3 px-3 text-end font-monospace fw-bold text-danger">
                                            ฿{{ number_format($zs['revenue'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
