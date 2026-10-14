<x-layouts::app :title="__('Kitchen Queue')">
    <div class="d-flex flex-column gap-4">
                @if (session()->has('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 border-success-subtle bg-success-subtle text-success py-3 px-4 rounded-3 m-0">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger d-flex align-items-center gap-2 border-danger-subtle bg-danger-subtle text-danger py-3 px-4 rounded-3 m-0">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

                <div class="card bg-dark border-secondary-subtle rounded-4 p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 shadow-sm">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="spinner-grow spinner-grow-sm text-warning" style="width: 10px; height: 10px;"></span>
                    <h2 class="h4 fw-bold text-white m-0">คิวออเดอร์ห้องครัว (Kitchen Queue)</h2>
                </div>
                <small class="text-secondary ps-3">มอนิเตอร์และจัดการสถานะอาหารที่ลูกค้าสั่งจากเครื่องคอมพิวเตอร์</small>
            </div>

                        <div class="d-flex flex-wrap gap-2">
                <a
                    href="{{ route('staff.kitchen-queue', ['status' => 'active']) }}"
                    class="btn btn-sm rounded-pill px-3 fw-semibold {{ $filterStatus === 'active' ? 'btn-warning text-dark' : 'btn-outline-secondary text-light' }}"
                >
                    กำลังรอทำ/กำลังทำ ({{ $pendingCount + $preparingCount }})
                </a>
                <a
                    href="{{ route('staff.kitchen-queue', ['status' => 'served']) }}"
                    class="btn btn-sm rounded-pill px-3 fw-semibold {{ $filterStatus === 'served' ? 'btn-success' : 'btn-outline-secondary text-light' }}"
                >
                    เสิร์ฟแล้ว
                </a>
                <a
                    href="{{ route('staff.kitchen-queue', ['status' => 'all']) }}"
                    class="btn btn-sm rounded-pill px-3 fw-semibold {{ $filterStatus === 'all' ? 'btn-danger' : 'btn-outline-secondary text-light' }}"
                >
                    ทั้งหมด
                </a>
            </div>
        </div>

        <!-- คิวออเดอร์อาหาร -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @forelse ($orders as $order)
                <div class="col">
                    <div class="card h-100 bg-dark border-secondary-subtle rounded-4 p-4 shadow-sm d-flex flex-column justify-content-between">
                        <div>
                                                        <div class="d-flex justify-content-between align-items-start border-bottom border-secondary-subtle pb-3 mb-3">
                                <div>
                                    <small class="text-secondary fw-bold" style="font-size: 11px;">ออเดอร์ #{{ $order->id }}</small>
                                    <h3 class="h4 fw-black text-white m-0">
                                        เครื่อง {{ $order->seat ? $order->seat->seat_number : '-' }}
                                    </h3>
                                    <small class="text-secondary" style="font-size: 11px;">
                                        {{ $order->user ? $order->user->name : '-' }} &bull; {{ $order->created_at->format('H:i') }} น.
                                    </small>
                                </div>

                                <div class="text-end">
                                    @if ($order->order_status === 'pending')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle py-1 px-2" style="font-size: 11px;">
                                            <i class="bi bi-hourglass-split me-1"></i> รอดำเนินการ
                                        </span>
                                    @elseif ($order->order_status === 'preparing')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle py-1 px-2 d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                            <span class="spinner-grow spinner-grow-sm text-info" style="width: 7px; height: 7px;"></span> กำลังปรุงอาหาร
                                        </span>
                                    @elseif ($order->order_status === 'served')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2" style="font-size: 11px;">
                                            <i class="bi bi-check-circle-fill me-1"></i> เสิร์ฟแล้ว
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1 px-2" style="font-size: 11px;">
                                            <i class="bi bi-x-circle me-1"></i> ยกเลิก
                                        </span>
                                    @endif

                                    <div class="mt-1">
                                        <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }}" style="font-size: 10px;">
                                            @if ($order->payment_status === 'paid')
                                                <i class="bi bi-check2"></i> ชำระแล้ว ({{ $order->payment_method === 'wallet' ? 'กระเป๋าเงิน' : ($order->payment_method === 'cash' ? 'เงินสด' : 'พร้อมเพย์') }})
                                            @else
                                                <i class="bi bi-clock"></i> รอเก็บเงิน ({{ $order->payment_method === 'cash' ? 'เงินสด' : ($order->payment_method === 'promptpay' ? 'พร้อมเพย์' : 'กระเป๋าเงิน') }})
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                                                        <div class="d-flex flex-column gap-2 mb-3">
                                @foreach ($order->items as $item)
                                    <div class="d-flex justify-content-between align-items-center small">
                                        <span class="text-light">
                                            {{ $item->product ? $item->product->name : 'สินค้า' }} <strong class="text-danger">x{{ $item->quantity }}</strong>
                                        </span>
                                        <span class="text-secondary font-monospace" style="font-size: 11px;">
                                            ฿{{ number_format($item->subtotal, 2) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                                                <div class="pt-3 border-top border-secondary-subtle">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-secondary small">ยอดรวม:</span>
                                <span class="fs-5 fw-bold text-white font-monospace">฿{{ number_format($order->total_amount, 2) }}</span>
                            </div>

                            @if ($order->payment_method === 'cash' && $order->payment_status === 'pending_payment')
                                <div class="alert alert-warning py-1.5 px-3 rounded-3 d-flex align-items-center gap-2 mb-2 small text-warning-emphasis bg-warning-subtle border border-warning-subtle" style="font-size: 11.5px;">
                                    <i class="bi bi-exclamation-circle-fill text-warning"></i>
                                    <span>ต้องกดยืนยันรับเงินสดก่อน จึงจะเริ่มทำอาหารได้</span>
                                </div>
                                <form method="POST" action="{{ route('staff.confirm-cash') }}" class="mb-2">
                                    @csrf
                                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                                    <button type="submit" class="btn btn-warning btn-sm w-100 fw-bold rounded-pill text-dark shadow-sm">
                                        <i class="bi bi-cash-stack me-1"></i> ยืนยันรับเงินสดแล้ว (฿{{ number_format($order->total_amount, 2) }})
                                    </button>
                                </form>
                            @endif

                            <div class="row g-2">
                                @if ($order->order_status === 'pending')
                                    @if ($order->payment_status !== 'paid')
                                        <div class="col-7">
                                            <button type="button" class="btn btn-secondary btn-sm w-100 fw-semibold rounded-pill py-2 opacity-50" disabled title="ต้องกดยืนยันรับเงินสดก่อนเริ่มทำอาหาร">
                                                <i class="bi bi-lock-fill me-1"></i> รอยืนยันรับเงิน
                                            </button>
                                        </div>
                                    @else
                                        <div class="col-7">
                                            <form method="POST" action="{{ route('staff.update-order-status') }}">
                                                @csrf
                                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                                <input type="hidden" name="status" value="preparing">
                                                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold rounded-pill py-2">
                                                    <i class="bi bi-fire me-1"></i> เริ่มทำอาหาร &rarr;
                                                </button>
                                            </form>
                                        </div>
                                    @endif

                                    <div class="col-5">
                                        <form method="POST" action="{{ route('staff.update-order-status') }}" onsubmit="return confirm('คุณต้องการยกเลิกออเดอร์นี้หรือไม่?');">
                                            @csrf
                                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100 rounded-pill py-2">
                                                ยกเลิก
                                            </button>
                                        </form>
                                    </div>
                                @elseif ($order->order_status === 'preparing')
                                    @if ($order->payment_status !== 'paid')
                                        <div class="col-12">
                                            <button type="button" class="btn btn-secondary btn-sm w-100 fw-semibold rounded-pill py-2 opacity-50" disabled title="ต้องกดยืนยันรับเงินสดก่อนเสิร์ฟ">
                                                <i class="bi bi-lock-fill me-1"></i> รอยืนยันรับเงินสดก่อนเสิร์ฟ
                                            </button>
                                        </div>
                                    @else
                                        <div class="col-12">
                                            <form method="POST" action="{{ route('staff.update-order-status') }}">
                                                @csrf
                                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                                <input type="hidden" name="status" value="served">
                                                <button type="submit" class="btn btn-outline-success btn-sm w-100 fw-bold rounded-pill py-2">
                                                    <i class="bi bi-send-check me-1"></i> ทำเสร็จแล้ว &bull; กดเพื่อเสิร์ฟ
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                @elseif ($order->order_status === 'served')
                                    <div class="col-12">
                                        <div class="badge bg-success-subtle text-success border border-success-subtle w-100 py-2 rounded-pill small fw-semibold">
                                            <i class="bi bi-check-circle-fill me-1"></i> เสิร์ฟที่โต๊ะเรียบร้อยแล้ว
                                        </div>
                                    </div>
                                @elseif ($order->order_status === 'cancelled')
                                    <div class="col-12">
                                        <div class="badge bg-danger-subtle text-danger border border-danger-subtle w-100 py-2 rounded-pill small fw-semibold">
                                            <i class="bi bi-x-circle me-1"></i> ยกเลิกออเดอร์แล้ว
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card bg-dark border-secondary-subtle rounded-4 p-5 text-center text-secondary small">
                        ไม่มีออเดอร์อาหารในสถานะนี้
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts::app>
