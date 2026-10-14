<x-layouts::app :title="__('Wallet & Packages')">
    <div class="d-flex flex-column gap-4">
                @if (session()->has('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 border-success-subtle bg-success-subtle text-success py-3 px-4 rounded-3 m-0 shadow-sm">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger d-flex align-items-center gap-2 border-danger-subtle bg-danger-subtle text-danger py-3 px-4 rounded-3 m-0 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

                <div class="card bg-dark border-secondary-subtle rounded-4 p-4 shadow-sm">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="bg-danger rounded" style="width: 4px; height: 22px;"></span>
                <h2 class="h5 fw-bold text-white m-0">ความแตกต่างระหว่าง "กระเป๋าเงิน (Wallet)" กับ "แพ็กเกจชั่วโมง"</h2>
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 bg-dark-subtle border border-secondary-subtle h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle py-1.5 px-2.5">
                                <i class="bi bi-wallet2 me-1"></i> ฝั่งซ้าย: เติมเงินเข้ากระเป๋า (Wallet)
                            </span>
                        </div>
                        <p class="small text-secondary m-0">
                            คือ <strong class="text-white">เงินสดในระบบ</strong> เติมแล้วนำไปใช้ได้ทุกอย่าง ไม่ว่าจะเป็นเล่นเกมแบบคิดตามจริงรายชั่วโมง สั่งอาหาร หรือใช้ซื้อแพ็กเกจ
                        </p>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 bg-dark-subtle border border-secondary-subtle h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1.5 px-2.5">
                                <i class="bi bi-clock-history me-1"></i> ฝั่งขวา: ซื้อแพ็กเกจชั่วโมง (Packages)
                            </span>
                        </div>
                        <p class="small text-secondary m-0">
                            คือ <strong class="text-white">การเหมาเวลาล่วงหน้า</strong> โดยใช้เงินในกระเป๋าซื้อ เพื่อให้ได้ราคาเฉลี่ยต่อชั่วโมงที่ <strong class="text-success">ประหยัดและคุ้มค่ากว่า</strong> การคิดเงินตามจริง
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- ฟอร์มเติมเงิน -->
            <div class="col-12 col-xl-5">
                <div class="card bg-dark border-secondary-subtle rounded-4 p-4 shadow-sm h-100">
                    <div class="d-flex justify-content-between align-items-center border-bottom border-secondary-subtle pb-3 mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="bg-danger rounded" style="width: 4px; height: 22px;"></span>
                            <div>
                                <h2 class="h5 fw-bold text-white m-0">เติมเงินเข้าบัญชี (Top-up)</h2>
                                <small class="text-secondary">เติมเงินเพื่อใช้เล่นเกม ซื้อแพ็กเกจ หรือสั่งอาหาร</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <small class="text-secondary d-block fw-semibold">ยอดเงินปัจจุบัน</small>
                            <span class="fs-5 fw-bold text-warning font-monospace">฿{{ number_format($user->balance, 2) }}</span>
                        </div>
                    </div>

                    <form id="topupForm" method="POST" action="{{ route('customer.do-topup') }}" class="d-flex flex-column gap-3">
                        @csrf

                                                <div>
                            <label class="form-label text-secondary text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.05em;">
                                เลือกจำนวนเงิน:
                            </label>
                            <div class="row g-2">
                                @php
                                    $presets = [50, 100, 200, 300, 500, 1000];
                                @endphp
                                @foreach ($presets as $amt)
                                    <div class="col-4">
                                        <button
                                            type="button"
                                            onclick="setAmount({{ $amt }})"
                                            class="btn preset-btn w-100 py-2.5 rounded-3 border border-secondary-subtle bg-dark-subtle text-white text-center transition {{ $amt === 100 ? 'border-danger bg-danger-subtle' : '' }}"
                                            id="preset-{{ $amt }}"
                                        >
                                            <div class="fs-5 fw-bold text-white font-monospace">
                                                ฿{{ $amt }}
                                            </div>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                                                <div>
                            <label for="inputAmount" class="form-label text-secondary small fw-semibold">หรือระบุจำนวนเงินเอง (บาท):</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark-subtle border-secondary-subtle text-secondary font-monospace">฿</span>
                                <input
                                    type="number"
                                    name="amount"
                                    id="inputAmount"
                                    min="20"
                                    max="10000"
                                    step="1"
                                    value="100"
                                    required
                                    placeholder="ระบุจำนวนเงิน..."
                                    class="form-control bg-dark-subtle border-secondary-subtle text-white fw-bold font-monospace py-2"
                                />
                            </div>
                            <small class="text-secondary mt-1 d-block">ขั้นต่ำ ฿20.00 สูงสุด ฿10,000.00</small>
                        </div>

                                                <div>
                            <label class="form-label text-secondary small fw-semibold">ช่องทางการชำระเงิน:</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label id="methodLabelQr" class="form-check p-3 rounded-3 border border-danger bg-danger-subtle d-flex align-items-center gap-2 cursor-pointer transition">
                                        <input type="radio" name="topup_method" value="qr" checked onchange="handleMethodChange(this.value)" class="form-check-input mt-0">
                                        <div class="lh-sm">
                                            <span class="fw-bold text-white d-block small">
                                                <i class="bi bi-qr-code-scan me-1 text-danger"></i> พร้อมเพย์ QR
                                            </span>
                                            <small class="text-secondary">อัปเดตยอดทันที</small>
                                        </div>
                                    </label>
                                </div>

                                <div class="col-6">
                                    <label id="methodLabelCash" class="form-check p-3 rounded-3 border border-secondary-subtle bg-dark-subtle d-flex align-items-center gap-2 cursor-pointer transition">
                                        <input type="radio" name="topup_method" value="cash" onchange="handleMethodChange(this.value)" class="form-check-input mt-0">
                                        <div class="lh-sm">
                                            <span class="fw-bold text-white d-block small">
                                                <i class="bi bi-cash-stack me-1 text-secondary"></i> เงินสดเคาน์เตอร์
                                            </span>
                                            <small class="text-secondary">ติดต่อพนักงาน</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            id="submitTopupBtn"
                            onclick="handleTopupSubmit()"
                            class="btn btn-danger w-100 py-2.5 fw-bold mt-2 shadow-sm d-flex align-items-center justify-content-center gap-2"
                        >
                            <i class="bi bi-check-circle"></i> ยืนยันการทำรายการเติมเงิน
                        </button>
                    </form>
                </div>
            </div>

            <!-- ซื้อแพ็กเกจชั่วโมงตามโซน -->
            <div class="col-12 col-xl-7">
                <div class="card bg-dark border-secondary-subtle rounded-4 p-4 shadow-sm h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="border-bottom border-secondary-subtle pb-3 mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="bg-danger rounded" style="width: 4px; height: 22px;"></span>
                                <div>
                                    <h2 class="h5 fw-bold text-white m-0">ซื้อแพ็กเกจชั่วโมงตามโซน</h2>
                                    <small class="text-secondary">แต่ละโซนมีอัตราค่าบริการต่างกัน การซื้อแพ็กเกจจะประหยัดกว่าการเล่นแบบคิดตามจริง</small>
                                </div>
                            </div>
                        </div>

                                                <ul class="nav nav-pills mb-3 gap-2" id="packageZoneTabs" role="tablist">
                            @foreach ($zones as $idx => $z)
                                <li class="nav-item" role="presentation">
                                    <button
                                        class="nav-link py-1.5 px-3 rounded-3 small fw-semibold {{ $idx === 0 ? 'active' : '' }}"
                                        id="zone-tab-{{ $z->id }}"
                                        data-bs-toggle="pill"
                                        data-bs-target="#zone-pane-{{ $z->id }}"
                                        type="button"
                                        role="tab"
                                        aria-controls="zone-pane-{{ $z->id }}"
                                        aria-selected="{{ $idx === 0 ? 'true' : 'false' }}"
                                    >
                                        <i class="bi bi-display me-1"></i> {{ $z->name }}
                                        <span class="badge bg-dark border border-secondary text-light ms-1 font-monospace">฿{{ number_format($z->hourly_rate, 0) }}/ชม.</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                                                <div class="tab-content mb-4" id="packageZoneTabContent">
                            @foreach ($zones as $idx => $z)
                                <div
                                    class="tab-pane fade {{ $idx === 0 ? 'show active' : '' }}"
                                    id="zone-pane-{{ $z->id }}"
                                    role="tabpanel"
                                    aria-labelledby="zone-tab-{{ $z->id }}"
                                    tabindex="0"
                                >
                                    <div class="p-2.5 mb-3 rounded-3 bg-dark-subtle border border-secondary-subtle d-flex justify-content-between align-items-center">
                                        <div class="small text-secondary">
                                            <span class="text-light fw-bold">{{ $z->name }}</span>
                                            <div>{{ $z->description ?? 'คอมพิวเตอร์พร้อมอุปกรณ์เกมมิ่งครบชุด' }}</div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-secondary-subtle text-light border border-secondary">
                                                ปกติ ฿{{ number_format($z->hourly_rate, 2) }}/ชั่วโมง
                                            </span>
                                        </div>
                                    </div>

                                    @php
                                        $zonePackages = $z->packages;
                                    @endphp

                                    @if ($zonePackages->isEmpty())
                                        <div class="p-4 text-center text-secondary rounded-3 border border-secondary-subtle">
                                            ยังไม่มีรายการแพ็กเกจสำหรับโซนนี้
                                        </div>
                                    @else
                                        <div class="d-flex flex-column gap-2.5">
                                            @foreach ($zonePackages as $pkg)
                                                @php
                                                    $normalCost = (float) $z->hourly_rate * $pkg->duration_hours;
                                                    $savings = max(0, $normalCost - (float) $pkg->price);
                                                @endphp
                                                <div class="p-3 bg-dark-subtle border border-secondary-subtle rounded-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 transition">
                                                    <div>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="fw-bold text-white">{{ $pkg->name }}</span>
                                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                                <i class="bi bi-clock me-1"></i> {{ $pkg->duration_hours }} ชั่วโมง
                                                            </span>
                                                        </div>
                                                        <div class="small text-secondary mt-1">
                                                            <span>ได้รับเวลาเล่น {{ $pkg->duration_hours * 60 }} นาที</span>
                                                            @if ($savings > 0)
                                                                <span class="text-success fw-semibold ms-2">
                                                                    (ประหยัด ฿{{ number_format($savings, 2) }} เทียบกับจ่ายรายชั่วโมง)
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="d-flex align-items-center justify-content-between justify-content-sm-end gap-3 flex-shrink-0">
                                                        <div class="text-end">
                                                            <span class="fs-5 fw-bold text-white font-monospace">฿{{ number_format($pkg->price, 2) }}</span>
                                                            @if ($savings > 0)
                                                                <div class="small text-secondary text-decoration-line-through font-monospace" style="font-size: 11px;">
                                                                    ฿{{ number_format($normalCost, 2) }}
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <form method="POST" action="{{ route('customer.buy-package') }}" onsubmit="return confirm('ยืนยันการซื้อแพ็กเกจ {{ addslashes($pkg->name) }} ในราคา ฿{{ number_format($pkg->price, 2) }} หรือไม่?');" class="m-0">
                                                            @csrf
                                                            <input type="hidden" name="package_id" value="{{ $pkg->id }}">
                                                            <button
                                                                type="submit"
                                                                @disabled($user->balance < $pkg->price)
                                                                class="btn btn-danger btn-sm px-3 fw-bold rounded-pill"
                                                                title="{{ $user->balance < $pkg->price ? 'ยอดเงินในกระเป๋าไม่เพียงพอ' : 'ซื้อแพ็กเกจนี้' }}"
                                                            >
                                                                <i class="bi bi-cart-plus me-1"></i> ซื้อแพ็กเกจ
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                                        @if ($userPackages->isNotEmpty())
                        <div class="pt-3 border-top border-secondary-subtle">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-secondary text-uppercase fw-bold small">
                                    <i class="bi bi-collection me-1 text-danger"></i> แพ็กเกจของคุณที่พร้อมใช้งาน:
                                </span>
                                <span class="badge bg-secondary-subtle text-secondary">{{ $userPackages->count() }} รายการ</span>
                            </div>

                            <div class="d-flex flex-column gap-2">
                                @foreach ($userPackages as $mp)
                                    <div class="p-3 bg-dark-subtle border border-secondary-subtle rounded-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold text-white">{{ $mp->package?->name ?? 'แพ็กเกจชั่วโมง' }}</span>
                                                @if ($mp->package?->zone)
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                        {{ $mp->package->zone->name }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                        {{ $mp->package ? 'ทุกโซน' : 'ไม่ระบุโซน' }}
                                                    </span>
                                                @endif
                                            </div>
                                            <small class="text-secondary d-block mt-1">
                                                ซื้อเมื่อ {{ $mp->purchased_at ? $mp->purchased_at->format('d/m/Y H:i') : '-' }}
                                                @if ($mp->expired_at)
                                                    • หมดอายุ {{ $mp->expired_at->format('d/m/Y') }}
                                                @endif
                                            </small>
                                        </div>

                                        <div class="text-end">
                                            <span class="badge bg-danger text-white fs-7 font-monospace px-2.5 py-1.5">
                                                <i class="bi bi-hourglass-split me-1"></i> เหลือ {{ $mp->formattedRemainingTime() }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ประวัติการทำรายการกระเป๋าเงิน -->
        @if ($transactions->isNotEmpty())
            <div class="card bg-dark border-secondary-subtle rounded-4 p-4 shadow-sm">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="bg-danger rounded" style="width: 4px; height: 20px;"></span>
                    <h3 class="h6 fw-bold text-white m-0">
                        <i class="bi bi-clock-history me-1 text-secondary"></i> ประวัติการทำรายการล่าสุด (10 รายการ)
                    </h3>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-hover table-sm align-middle m-0">
                        <thead class="table-active text-secondary text-uppercase small">
                            <tr>
                                <th class="py-2.5 px-3">ประเภทรายการ</th>
                                <th class="py-2.5 px-3">จำนวนเงิน</th>
                                <th class="py-2.5 px-3">วันและเวลาที่ทำรายการ</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @foreach ($transactions as $tx)
                                <tr>
                                    <td class="py-2.5 px-3">
                                        @if ($tx->type === 'topup')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="bi bi-plus-circle me-1"></i>
                                                @if ($tx->ref_type === 'qr_topup')
                                                    เติมเงิน (พร้อมเพย์)
                                                @elseif ($tx->ref_type === 'cash_topup')
                                                    เติมเงิน (เงินสด)
                                                @elseif ($tx->ref_type === 'initial_seed')
                                                    ยอดเงินเริ่มต้น
                                                @else
                                                    เติมเงินเข้าบัญชี
                                                @endif
                                            </span>
                                        @elseif ($tx->type === 'deduct')
                                            <span class="badge bg-secondary-subtle text-light border border-secondary">
                                                <i class="bi bi-dash-circle me-1"></i>
                                                @if ($tx->ref_type === 'package_purchase')
                                                    ซื้อแพ็กเกจชั่วโมง
                                                @elseif ($tx->ref_type === 'session')
                                                    ค่าชั่วโมงเล่นเกม
                                                @elseif ($tx->ref_type === 'order')
                                                    สั่งอาหารและเครื่องดื่ม
                                                @else
                                                    หักค่าบริการ
                                                @endif
                                            </span>
                                        @else
                                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i> คืนเงิน
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 fw-bold font-monospace {{ $tx->type === 'topup' ? 'text-success' : 'text-light' }}">
                                        {{ $tx->type === 'topup' ? '+' : '-' }}฿{{ number_format($tx->amount, 2) }}
                                    </td>
                                    <td class="py-2.5 px-3 text-secondary font-monospace small">
                                        {{ $tx->created_at->format('d/m/Y H:i') }} น.
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Modal สแกนพร้อมเพย์ -->
        <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
                <div class="modal-content bg-dark border border-danger-subtle rounded-4 p-4 text-center shadow-lg">
                    <div class="d-flex justify-content-between align-items-center border-bottom border-secondary-subtle pb-3 mb-3">
                        <div class="d-flex align-items-center gap-2" id="qrModalLabel">
                            <span class="badge bg-danger rounded-circle p-1"></span>
                            <span class="fw-bold text-white small">ชำระเงินผ่าน PromptPay QR</span>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="mb-3">
                        <small class="text-secondary d-block">ยอดชำระที่ต้องสแกน</small>
                        <div class="fs-3 fw-black text-warning font-monospace" id="modalQrAmount">฿100.00</div>
                    </div>

                    <div class="bg-white rounded-4 mx-auto overflow-hidden shadow mb-3 p-2 text-center" style="max-width: 240px;">
                        <img src="{{ asset('images/promptpay-qr.jpg') }}" alt="PromptPay QR Code" class="img-fluid rounded-3" style="max-height: 280px; width: auto; object-fit: contain;">
                    </div>

                    <div class="bg-dark-subtle p-2.5 rounded-3 border border-secondary-subtle small mb-3">
                        <div class="text-secondary" style="font-size: 12px;">
                            ชื่อผู้รับ: <span class="text-light fw-bold">LETSPLAY GAMING CAFE</span><br>
                            หมายเลขอ้างอิง: <span class="text-secondary font-monospace">089-123-4567</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary w-50 small fw-bold" data-bs-dismiss="modal">
                            ยกเลิก
                        </button>
                        <button type="button" id="confirmPaymentBtn" onclick="submitTopupForm()" class="btn btn-danger w-50 small fw-bold">
                            <i class="bi bi-check2"></i> ชำระเงินเรียบร้อย
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setAmount(val) {
            document.getElementById('inputAmount').value = val;
            document.querySelectorAll('.preset-btn').forEach(btn => {
                btn.classList.remove('border-danger', 'bg-danger-subtle');
                btn.classList.add('border-secondary-subtle', 'bg-dark-subtle');
            });
            const selected = document.getElementById('preset-' + val);
            if (selected) {
                selected.classList.remove('border-secondary-subtle', 'bg-dark-subtle');
                selected.classList.add('border-danger', 'bg-danger-subtle');
            }
        }

        function handleMethodChange(method) {
            const qrLabel = document.getElementById('methodLabelQr');
            const cashLabel = document.getElementById('methodLabelCash');

            if (method === 'qr') {
                qrLabel.classList.add('border-danger', 'bg-danger-subtle');
                qrLabel.classList.remove('border-secondary-subtle', 'bg-dark-subtle');
                cashLabel.classList.remove('border-danger', 'bg-danger-subtle');
                cashLabel.classList.add('border-secondary-subtle', 'bg-dark-subtle');
            } else {
                cashLabel.classList.add('border-danger', 'bg-danger-subtle');
                cashLabel.classList.remove('border-secondary-subtle', 'bg-dark-subtle');
                qrLabel.classList.remove('border-danger', 'bg-danger-subtle');
                qrLabel.classList.add('border-secondary-subtle', 'bg-dark-subtle');
            }
        }

        function handleTopupSubmit() {
            const amountInput = document.getElementById('inputAmount');
            const amount = parseFloat(amountInput.value);

            if (!amount || amount < 20) {
                alert('กรุณาระบุจำนวนเงินขั้นต่ำ 20 บาทขึ้นไป');
                amountInput.focus();
                return;
            }

            if (amount > 10000) {
                alert('กรุณาระบุจำนวนเงินไม่เกิน 10,000 บาท');
                amountInput.focus();
                return;
            }

            const selectedMethod = document.querySelector('input[name="topup_method"]:checked')?.value || 'qr';

            if (selectedMethod === 'qr') {
                document.getElementById('modalQrAmount').textContent = '฿' + amount.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const modalEl = document.getElementById('qrModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            } else {
                submitTopupForm();
            }
        }

        function submitTopupForm() {
            const confirmBtn = document.getElementById('confirmPaymentBtn');
            if (confirmBtn) {
                confirmBtn.disabled = true;
                confirmBtn.innerText = 'กำลังดำเนินการ...';
            }
            document.getElementById('topupForm').submit();
        }
    </script>
</x-layouts::app>
