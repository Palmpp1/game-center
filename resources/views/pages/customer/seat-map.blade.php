<x-layouts::app :title="__('Seat Map')">
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

                <!-- แถบสถานะที่นั่ง -->
        <div class="card bg-dark border-secondary-subtle rounded-4 p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 shadow-sm">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="bg-danger rounded" style="width: 4px; height: 24px;"></span>
                    <h2 class="h4 fw-bold text-white m-0">ผังที่นั่งคอมพิวเตอร์ (Seat Map)</h2>
                </div>
                <small class="text-secondary ps-3">เลือกเครื่องคอมพิวเตอร์ที่ว่างเพื่อเริ่มใช้งาน</small>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="badge bg-dark-subtle border border-secondary-subtle text-success d-flex align-items-center gap-2 py-2 px-3">
                    <span class="badge bg-success rounded-circle p-1"></span>
                    <span>ว่างพร้อมใช้งาน</span>
                </div>
                <div class="badge bg-dark-subtle border border-secondary-subtle text-danger d-flex align-items-center gap-2 py-2 px-3">
                    <span class="badge bg-danger rounded-circle p-1"></span>
                    <span>มีผู้ใช้งานอยู่</span>
                </div>
                <div class="badge bg-dark-subtle border border-warning-subtle text-warning d-flex align-items-center gap-2 py-2 px-3">
                    <span class="badge bg-warning rounded-circle p-1"></span>
                    <span>ปิดปรับปรุง</span>
                </div>
            </div>
        </div>

        <!-- เครื่องที่กำลังใช้งาน -->
        @if ($activeSession)
            <div class="card bg-dark border-warning rounded-4 p-3.5 shadow-sm">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-warning text-dark d-flex align-items-center justify-content-center fs-3 flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="bi bi-display-fill"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="spinner-grow spinner-grow-sm text-warning" style="width: 10px; height: 10px;"></span>
                                <h3 class="h6 fw-bold text-white m-0">
                                    คุณกำลังใช้งานเครื่อง {{ $activeSession->seat->seat_number }} ({{ $activeSession->seat->zone->name }}) อยู่ในขณะนี้
                                </h3>
                                <span class="badge bg-warning text-dark fw-bold">ONLINE</span>
                            </div>
                            <small class="text-secondary d-block mt-1">
                                คุณมีเครื่องที่เปิดใช้งานอยู่แล้ว หากต้องการเปลี่ยนเครื่องหรือย้ายที่นั่ง กรุณาเช็คเอาท์ออกจากเครื่องเดิมก่อน
                            </small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <a href="{{ route('customer.food-order', ['seat_id' => $activeSession->seat_id]) }}" class="btn btn-outline-secondary text-light btn-sm fw-semibold px-3 py-2 rounded-pill">
                            <i class="bi bi-cup-hot me-1 text-danger"></i> สั่งอาหาร
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-warning btn-sm fw-bold px-3.5 py-2 rounded-pill">
                            <i class="bi bi-speedometer2 me-1"></i> กลับไปที่แดชบอร์ด
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- ผังที่นั่งแต่ละโซน -->
        @foreach ($zones as $zone)
            <div class="card bg-dark border-secondary-subtle rounded-4 p-4 shadow-sm">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center border-bottom border-secondary-subtle pb-3 mb-4 gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="bg-danger rounded" style="width: 4px; height: 22px;"></span>
                        <div>
                            <h3 class="h5 fw-bold text-white m-0">{{ $zone->name }}</h3>
                            <small class="text-secondary">{{ $zone->description }}</small>
                        </div>
                    </div>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-7 py-1.5 px-3">
                        <i class="bi bi-tag me-1"></i> ฿{{ number_format($zone->hourly_rate, 2) }} / ชั่วโมง
                    </span>
                </div>

                                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3">
                    @foreach ($zone->seats as $seat)
                        @php
                            $isCurrent = $activeSession && $activeSession->seat_id === $seat->id;
                        @endphp
                        <div class="col">
                            <div
                                @if ($seat->isAvailable() && ! $activeSession)
                                    onclick="openCheckInModal({{ $seat->id }}, '{{ $seat->seat_number }}', {{ $zone->id }}, '{{ $zone->name }}', {{ $zone->hourly_rate }})"
                                    role="button"
                                @endif
                                class="card text-center p-3 rounded-4 transition h-100 d-flex flex-column align-items-center justify-content-between
                                    {{ $isCurrent ? 'bg-warning-subtle border-warning shadow' : '' }}
                                    @if (! $isCurrent)
                                        @if($seat->status === 'available')
                                            {{ $activeSession ? 'bg-dark-subtle border-secondary-subtle opacity-75' : 'bg-dark-subtle border-secondary-subtle hover-border-danger cursor-pointer shadow-sm' }}
                                        @elseif($seat->status === 'occupied')
                                            bg-dark border-secondary-subtle opacity-50 cursor-not-allowed
                                        @else
                                            bg-dark-subtle border-warning-subtle opacity-75 cursor-not-allowed
                                        @endif
                                    @endif
                                "
                            >
                                <div class="rounded-3 d-flex align-items-center justify-center mb-2 fs-3
                                    @if($isCurrent) text-warning
                                    @elseif($seat->status === 'available') text-success
                                    @elseif($seat->status === 'occupied') text-danger
                                    @else text-warning
                                    @endif
                                ">
                                    <i class="bi bi-tools"></i>
                                </div>

                                <div class="fs-4 fw-black font-monospace text-white">
                                    {{ $seat->seat_number }}
                                </div>

                                <small class="mt-1 fw-bold
                                    @if($isCurrent) text-warning
                                    @elseif($seat->status === 'available') text-success
                                    @elseif($seat->status === 'occupied') text-danger
                                    @else text-warning
                                    @endif
                                ">
                                    @if($isCurrent) เครื่องของคุณ
                                    @elseif($seat->status === 'available') {{ $activeSession ? 'ว่าง' : 'คลิกเพื่อเปิดเครื่อง' }}
                                    @elseif($seat->status === 'occupied') ไม่ว่าง
                                    @else ปิดปรับปรุง
                                    @endif
                                </small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <!-- Modal ยืนยันเปิดเครื่อง -->
        <div class="modal fade" id="checkInModal" tabindex="-1" aria-labelledby="checkInModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark border border-danger-subtle rounded-4 p-4 shadow-lg">
                    <div class="d-flex justify-content-between align-items-center border-bottom border-secondary-subtle pb-3 mb-3">
                        <div class="d-flex align-items-center gap-2" id="checkInModalLabel">
                            <span class="bg-danger rounded" style="width: 4px; height: 20px;"></span>
                            <h3 class="h5 fw-bold text-white m-0">ยืนยันการเปิดเครื่อง</h3>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form method="POST" action="{{ route('customer.check-in') }}" class="d-flex flex-column gap-3">
                        @csrf
                        <input type="hidden" name="seat_id" id="modalSeatId" value="">

                        <div class="p-3 bg-dark-subtle border border-secondary-subtle rounded-3 small">
                            <div class="d-flex justify-content-between mb-1.5">
                                <span class="text-secondary">หมายเลขเครื่อง:</span>
                                <strong class="text-white fs-6 font-monospace" id="modalSeatNumber">-</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1.5">
                                <span class="text-secondary">โซนบริการ:</span>
                                <strong class="text-danger" id="modalZoneName">-</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1.5">
                                <span class="text-secondary">อัตราค่าบริการปกติ:</span>
                                <strong class="text-success font-monospace" id="modalHourlyRate">-</strong>
                            </div>
                            <div class="d-flex justify-content-between pt-2 border-top border-secondary-subtle mt-2">
                                <span class="text-secondary">ยอดเงินในกระเป๋าของคุณ:</span>
                                <strong class="text-warning font-monospace fs-6">฿{{ number_format(auth()->user()->balance ?? 0, 2) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between pt-1">
                                <span class="text-secondary">เวลาที่เล่นได้โดยประมาณ:</span>
                                <strong class="text-info font-monospace fs-6" id="modalPlayableTime">-</strong>
                            </div>
                            @if ((float)(auth()->user()->balance ?? 0) <= 0.00)
                                <div id="insufficientBalanceAlert" class="alert alert-danger p-2.5 small mt-2 mb-0">
                                    <div class="d-flex align-items-center gap-1.5 fw-bold text-danger">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        <span>ยอดเงินในกระเป๋าหมดแล้ว</span>
                                    </div>
                                    <div class="mt-1 text-light">
                                        การเล่นแบบคิดตามจริงต้องมียอดเงินในกระเป๋า (คุณมี ฿{{ number_format(auth()->user()->balance ?? 0, 2) }})
                                    </div>
                                    <div class="mt-2">
                                        <a href="{{ route('customer.topup') }}" class="btn btn-warning btn-sm py-1 px-3 fw-bold">
                                            <i class="bi bi-wallet2 me-1"></i> เติมเงินทันที
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>

                                                <div>
                            <label class="form-label text-secondary small fw-bold text-uppercase">เลือกรูปแบบการชำระเงิน:</label>

                            <div class="d-flex flex-column gap-2">
                                <label class="form-check p-3 rounded-3 border border-secondary-subtle bg-dark-subtle d-flex align-items-start gap-2 cursor-pointer transition">
                                    <input type="radio" name="billing_mode" value="pay_as_you_go" checked onchange="togglePackageSelect(false)" class="form-check-input mt-1">
                                    <div>
                                        <strong class="text-white small d-block">
                                            <i class="bi bi-wallet2 me-1 text-danger"></i> จ่ายตามจริง (Pay as you go)
                                        </strong>
                                        <small class="text-secondary">หักเงินจากกระเป๋าเงินตามระยะเวลาที่เล่นจริงเมื่อสิ้นสุดการใช้งาน</small>
                                    </div>
                                </label>

                                <label class="form-check p-3 rounded-3 border border-secondary-subtle bg-dark-subtle d-flex align-items-start gap-2 cursor-pointer transition">
                                    <input type="radio" name="billing_mode" value="package" onchange="togglePackageSelect(true)" class="form-check-input mt-1">
                                    <div class="w-100">
                                        <strong class="text-white small d-block">
                                            <i class="bi bi-clock-history me-1 text-danger"></i> ใช้แพ็กเกจชั่วโมงประจำโซน
                                        </strong>
                                        <small class="text-secondary d-block mb-2">หักเวลาจากแพ็กเกจชั่วโมงที่คุณซื้อไว้สำหรับโซนนี้</small>

                                        <div id="packageSelectContainer" class="d-none">
                                            @if ($availablePackages->isEmpty())
                                                <div class="alert alert-danger p-2.5 small m-0">
                                                    คุณยังไม่มีแพ็กเกจชั่วโมง สามารถเลือกเล่นแบบคิดตามจริง หรือไปซื้อแพ็กเกจได้ที่หน้าเติมเงิน
                                                </div>
                                            @else
                                                <div id="noZonePackageWarning" class="alert alert-warning p-2.5 small m-0 d-none">
                                                    ไม่มีแพ็กเกจชั่วโมงสำหรับโซนนี้ (สามารถเล่นแบบคิดตามจริง หรือซื้อแพ็กเกจเพิ่มได้)
                                                </div>
                                                <select name="user_package_id" id="userPackageSelect" class="form-select form-select-sm bg-dark border-secondary-subtle text-light">
                                                    <option value="">-- เลือกแพ็กเกจที่ต้องการใช้งาน --</option>
                                                    @foreach ($availablePackages as $upkg)
                                                        <option
                                                            value="{{ $upkg->id }}"
                                                            data-zone-id="{{ $upkg->package?->zone_id ?? '' }}"
                                                            data-zone-name="{{ $upkg->package?->zone?->name ?? 'ทุกโซน' }}"
                                                        >
                                                            {{ $upkg->package?->name ?? 'แพ็กเกจชั่วโมง' }} (เหลือ {{ $upkg->formattedRemainingTime() }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">
                                ยกเลิก
                            </button>
                            <button type="submit" id="submitCheckInBtn" class="btn btn-danger btn-sm px-4 fw-bold rounded-pill">
                                <i class="bi bi-power me-1"></i> เริ่มต้นใช้งานเครื่อง
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const userBalance = {{ (float) (auth()->user()->balance ?? 0) }};
        let currentSeatZoneId = null;

        function openCheckInModal(seatId, seatNumber, zoneId, zoneName, hourlyRate) {
            currentSeatZoneId = zoneId;
            document.getElementById('modalSeatId').value = seatId;
            document.getElementById('modalSeatNumber').innerText = seatNumber;
            document.getElementById('modalZoneName').innerText = zoneName;
            document.getElementById('modalHourlyRate').innerText = '฿' + Number(hourlyRate).toFixed(2) + ' / ชั่วโมง';

            const rate = Number(hourlyRate);
            const totalMinutes = Math.floor((userBalance / rate) * 60);
            if (userBalance > 0 && rate > 0) {
                const hours = Math.floor(totalMinutes / 60);
                const mins = totalMinutes % 60;
                document.getElementById('modalPlayableTime').innerText = hours > 0 ? `${hours} ชม ${mins} นาที` : `${mins} นาที`;
            } else {
                document.getElementById('modalPlayableTime').innerText = 'ยอดเงินไม่พอ (ยอดเงินคงเหลือ ฿0.00)';
            }

            filterPackagesForZone(zoneId);
            checkPayAsYouGoAbility();

            const modalEl = document.getElementById('checkInModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }

        function checkPayAsYouGoAbility() {
            const payRadio = document.querySelector('input[name="billing_mode"][value="pay_as_you_go"]');
            const submitBtn = document.getElementById('submitCheckInBtn');
            const isPayAsYouGo = payRadio ? payRadio.checked : true;

            if (isPayAsYouGo && userBalance <= 0) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50');
            } else {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50');
            }
        }

        function filterPackagesForZone(zoneId) {
            const select = document.getElementById('userPackageSelect');
            const warning = document.getElementById('noZonePackageWarning');
            if (!select) return;

            let matchingCount = 0;
            const options = select.querySelectorAll('option');

            options.forEach(opt => {
                if (opt.value === '') {
                    opt.style.display = '';
                    return;
                }
                const optZoneId = opt.getAttribute('data-zone-id');
                if (!optZoneId || Number(optZoneId) === Number(zoneId)) {
                    opt.style.display = '';
                    matchingCount++;
                } else {
                    opt.style.display = 'none';
                    if (opt.selected) {
                        select.value = '';
                    }
                }
            });

            if (warning) {
                if (matchingCount === 0) {
                    warning.classList.remove('d-none');
                    select.classList.add('d-none');
                } else {
                    warning.classList.add('d-none');
                    select.classList.remove('d-none');
                }
            }
        }

        function togglePackageSelect(isPackage) {
            const container = document.getElementById('packageSelectContainer');
            if (container) {
                if (isPackage) {
                    container.classList.remove('d-none');
                    if (currentSeatZoneId) {
                        filterPackagesForZone(currentSeatZoneId);
                    }
                } else {
                    container.classList.add('d-none');
                }
            }

            checkPayAsYouGoAbility();
        }
    </script>
</x-layouts::app>
