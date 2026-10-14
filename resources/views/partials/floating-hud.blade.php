@auth
    @php
        $hudSession = \App\Models\SeatSession::with(['seat.zone', 'userPackage'])
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();
    @endphp

    @if ($hudSession)
        @php
            $hudElapsedSeconds = \Carbon\Carbon::parse($hudSession->start_time)->diffInSeconds(\Carbon\Carbon::now());
            $hudElapsedMinutes = max(1, (int) ceil($hudElapsedSeconds / 60));
            $hudHourlyRate = (float) $hudSession->rate_snapshot;

            if ($hudSession->user_package_id !== null && $hudSession->userPackage !== null) {
                $hudRemainingMinutes = max(0, (int) $hudSession->userPackage->remaining_minutes - $hudElapsedMinutes);
                $hudEstimatedCost = 0.00;
            } else {
                $hudEstimatedCost = round(($hudElapsedMinutes / 60) * $hudHourlyRate, 2);
                $hudRemainingMinutes = $hudHourlyRate > 0 ? max(0, (int) floor(((float) auth()->user()->balance / $hudHourlyRate) * 60) - $hudElapsedMinutes) : null;
            }
        @endphp

        <!-- แถบแสดงสถานะเครื่องมุมจอ -->
        <div
            x-data="{
                minimized: true,
                elapsedSeconds: {{ $hudElapsedMinutes * 60 }},
                remainingSeconds: {{ $hudRemainingMinutes !== null ? $hudRemainingMinutes * 60 : 'null' }},
                formatDHMS(totalSecs) {
                    if (totalSecs === null || totalSecs < 0) return '0 นาที';
                    const totalMins = Math.floor(totalSecs / 60);
                    const days = Math.floor(totalMins / 1440);
                    const hours = Math.floor((totalMins % 1440) / 60);
                    const mins = totalMins % 60;

                    let parts = [];
                    if (days > 0) parts.push(days + ' วัน');
                    if (hours > 0) parts.push(hours + ' ชม.');
                    parts.push(mins + ' นาที');
                    return parts.join(' ');
                },
                init() {
                    setInterval(() => {
                        this.elapsedSeconds++;
                        if (this.remainingSeconds !== null && this.remainingSeconds > 0) {
                            this.remainingSeconds--;
                        }
                    }, 1000);
                }
            }"
            class="position-fixed bottom-0 end-0 m-4"
            style="z-index: 1045; user-select: none;"
        >
                        <div x-show="minimized" x-cloak>
                <div
                    @click="minimized = false"
                    class="badge bg-dark border border-danger-subtle rounded-pill py-2.5 px-3.5 shadow-lg d-flex align-items-center gap-2 cursor-pointer hover-border-danger hud-pill"
                    style="cursor: pointer;"
                >
                    <span class="spinner-grow spinner-grow-sm text-success" style="width: 8px; height: 8px;"></span>
                    <span class="font-monospace fw-bold hud-seat-label">เครื่อง {{ $hudSession->seat->seat_number }}</span>
                    <span class="text-secondary opacity-75">|</span>
                    @if ($hudRemainingMinutes !== null)
                        <span class="font-monospace fw-bold hud-time-warning" x-text="'เหลือ ' + formatDHMS(remainingSeconds)"></span>
                    @else
                        <span class="font-monospace fw-bold hud-time-elapsed" x-text="'เล่นไป ' + formatDHMS(elapsedSeconds)"></span>
                    @endif
                    <span class="text-secondary opacity-75">|</span>
                    <span class="text-success font-monospace fw-bold">฿{{ number_format($hudEstimatedCost, 2) }}</span>
                    <span class="badge bg-secondary-subtle text-secondary rounded-circle ms-1">+</span>
                </div>
            </div>

                        <div x-show="!minimized" x-cloak>
                <div
                    class="card bg-dark border-secondary-subtle rounded-4 p-3 shadow-lg hud-card"
                    style="width: 360px; max-width: calc(100vw - 2rem);"
                >
                    <div class="d-flex justify-content-between align-items-center border-bottom border-secondary-subtle pb-2 mb-2 gap-2">
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <button type="button" @click="minimized = true" class="btn btn-sm btn-link text-secondary p-0 text-decoration-none flex-shrink-0" title="ย่อขนาด"></button>
                                
                            <span class="badge bg-success rounded-circle p-1"></span>
                            <span class="text-danger fw-bold small text-nowrap">LETSPLAY CLIENT HUD</span>
                        </div>
                        <div class="d-flex align-items-center gap-1.5 overflow-hidden ms-auto" style="min-width: 0;">
                            <span
                                class="badge bg-danger-subtle text-danger fs-7 text-truncate"
                                style="max-width: 140px;"
                                title="{{ $hudSession->seat->seat_number }} ({{ $hudSession->seat->zone->name }})"
                            >
                                {{ $hudSession->seat->seat_number }} ({{ $hudSession->seat->zone->name }})
                            </span>
                            <button
                                type="button"
                                @click="minimized = true"
                                class="btn btn-sm btn-link text-secondary p-0 text-decoration-none flex-shrink-0"
                                title="ย่อขนาด"
                            >
                                <i class="bi bi-dash-lg fs-5"></i>
                            </button>
                        </div>
                    </div>

                    <div class="row g-2 mb-2 text-center">
                        <div class="col-6">
                            <div class="p-2 bg-dark-subtle border border-secondary-subtle rounded-3">
                                <small class="text-secondary d-block">
                                    {{ $hudRemainingMinutes !== null ? 'เวลาคงเหลือ' : 'เวลาที่เล่นไปแล้ว' }}
                                </small>
                                <span class="fw-bold font-monospace fs-7 {{ $hudRemainingMinutes !== null && $hudRemainingMinutes <= 15 ? 'text-danger' : 'hud-time-warning' }}">
                                    @if ($hudRemainingMinutes !== null)
                                        <span x-text="formatDHMS(remainingSeconds)"></span>
                                    @else
                                        <span x-text="formatDHMS(elapsedSeconds)"></span>
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-dark-subtle border border-secondary-subtle rounded-3">
                                <small class="text-secondary d-block">ค่าบริการขณะนี้</small>
                                <span class="fw-bold font-monospace text-success fs-6">
                                    ฿{{ number_format($hudEstimatedCost, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-1 pt-1">
                        <div class="col-4">
                            <a href="{{ route('customer.food-order') }}?seat_id={{ $hudSession->seat_id }}" class="btn btn-outline-secondary btn-sm w-100 py-1">
                                <i class="bi bi-cup-hot me-1"></i> สั่งอาหาร
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="{{ route('customer.topup') }}" class="btn btn-outline-secondary btn-sm w-100 py-1">
                                <i class="bi bi-wallet2 me-1"></i> เติมเงิน
                            </a>
                        </div>
                        <div class="col-4">
                            <form method="POST" action="{{ route('customer.check-out') }}" onsubmit="return confirm('ยืนยันการปิดเครื่องและสิ้นสุดการใช้งานหรือไม่?');" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm w-100 py-1 fw-bold">
                                    <i class="bi bi-power me-1"></i> เช็คเอาท์
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endauth
