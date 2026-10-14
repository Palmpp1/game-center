<x-layouts::app :title="__('Order Food & Beverages')">
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

                <!-- เครื่องที่กำลังใช้งาน -->
        @if ($activeSeat)
            <div class="alert alert-dark border-secondary-subtle bg-dark d-flex justify-content-between align-items-center p-3 rounded-4 shadow-sm m-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="spinner-grow spinner-grow-sm text-success" style="width: 8px; height: 8px;"></span>
                    <small class="text-light">
                        กำลังใช้งานเครื่อง <strong class="text-white text-decoration-underline">{{ $activeSeat->seat_number }}</strong> ({{ $activeSeat->zone->name }}) &bull; พนักงานจะนำอาหารและเครื่องดื่มไปเสิร์ฟที่โต๊ะของคุณ
                    </small>
                </div>
                <span class="badge bg-danger-subtle text-danger d-none d-sm-inline-block">
                    <i class="bi bi-geo-alt me-1"></i> เสิร์ฟถึงโต๊ะ
                </span>
            </div>
        @else
            <div class="alert alert-warning border-warning-subtle bg-warning-subtle text-warning d-flex flex-column flex-md-row justify-content-between align-items-md-center p-3.5 rounded-4 shadow-sm m-0 gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-warning text-dark d-flex align-items-center justify-content-center fs-4 flex-shrink-0" style="width: 44px; height: 44px;">
                        <i class="bi bi-display"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-white fs-6">
                            คุณยังไม่ได้เปิดใช้งานเครื่องคอมพิวเตอร์ในร้าน
                        </div>
                        <small class="text-warning-emphasis d-block mt-0.5">
                            กรุณาเปิดเครื่องก่อนทำการสั่งอาหาร เพื่อให้พนักงานจัดส่งอาหารถึงโต๊ะของคุณได้อย่างถูกต้อง
                        </small>
                    </div>
                </div>
                <a href="{{ route('customer.seat-map') }}" class="btn btn-warning btn-sm fw-bold px-4 py-2 rounded-pill flex-shrink-0 text-nowrap">
                    <i class="bi bi-geo-alt me-1"></i> ไปที่หน้าผังที่นั่งเพื่อเปิดเครื่อง
                </a>
            </div>
        @endif

        <div class="row g-4 {{ ! $activeSeat ? 'opacity-75' : '' }}">
            <!-- รายการเมนูอาหาร -->
            <div class="col-12 col-lg-8 d-flex flex-column gap-4">
                <div class="card bg-dark border-secondary-subtle rounded-4 p-4 shadow-sm">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="bg-danger rounded" style="width: 4px; height: 22px;"></span>
                        <h2 class="h5 fw-bold text-white m-0">
                            สั่งอาหารและเครื่องดื่ม
                        </h2>
                    </div>
                    <small class="text-secondary ps-3 d-block mb-3">
                        เลือกเมนูอาหารตามสั่ง ของทานเล่น และเครื่องดื่ม ส่งตรงถึงโต๊ะคอมพิวเตอร์ของคุณ
                    </small>

                    <div class="d-flex flex-wrap gap-2 pt-3 border-top border-secondary-subtle">
                        <a
                            href="{{ route('customer.food-order') }}"
                            class="btn btn-sm rounded-pill px-3 fw-semibold {{ is_null($selectedCategoryId) ? 'btn-danger' : 'btn-outline-secondary text-light' }}"
                        >
                            <i class="bi bi-grid-fill me-1"></i> เมนูทั้งหมด
                        </a>
                        @foreach ($categories as $cat)
                            <a
                                href="{{ route('customer.food-order', ['category_id' => $cat->id]) }}"
                                class="btn btn-sm rounded-pill px-3 fw-semibold {{ $selectedCategoryId == $cat->id ? 'btn-danger' : 'btn-outline-secondary text-light' }}"
                            >
                                {{ $cat->name }} ({{ $cat->products_count }})
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="row row-cols-1 row-cols-sm-2 g-3">
                    @forelse ($products as $prod)
                        <div class="col">
                            <div class="card h-100 bg-dark border-secondary-subtle rounded-4 p-3 shadow-sm d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-danger-subtle text-danger" style="font-size: 11px;">{{ $prod->category->name }}</span>
                                        <small class="fw-bold font-monospace {{ $prod->stock_quantity > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $prod->stock_quantity > 0 ? 'คงเหลือ ' . $prod->stock_quantity . ' ที่' : 'สินค้าหมด' }}
                                        </small>
                                    </div>

                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 bg-dark-subtle border border-secondary-subtle d-flex align-items-center justify-content-center fs-3 flex-shrink-0" style="width: 52px; height: 52px;">
                                            @if(str_contains(strtolower($prod->name), 'มาม่า') || str_contains(strtolower($prod->name), 'บะหมี่') || str_contains(strtolower($prod->name), 'noodle'))
                                                <i class="bi bi-cup-hot text-danger"></i>
                                            @elseif(str_contains(strtolower($prod->name), 'ข้าว') || str_contains(strtolower($prod->name), 'กะเพรา') || str_contains(strtolower($prod->name), 'rice'))
                                                <i class="bi bi-egg-fried text-warning"></i>
                                            @elseif(str_contains(strtolower($prod->name), 'เครื่องดื่ม') || str_contains(strtolower($prod->name), 'น้ำ') || str_contains(strtolower($prod->name), 'ชา') || str_contains(strtolower($prod->name), 'โค้ก'))
                                                <i class="bi bi-cup-straw text-info"></i>
                                            @elseif(str_contains(strtolower($prod->name), 'ขนม') || str_contains(strtolower($prod->name), 'snack'))
                                                <i class="bi bi-bag text-success"></i>
                                            @else
                                                <i class="bi bi-basket2 text-secondary"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="h6 fw-bold text-white mb-1">{{ $prod->name }}</h4>
                                            @if ($prod->description)
                                                <small class="text-secondary d-block line-clamp-2" style="font-size: 12px;">{{ $prod->description }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-3 border-top border-secondary-subtle mt-3 d-flex justify-content-between align-items-center">
                                    <span class="fs-5 fw-bold text-white font-monospace">
                                        ฿{{ number_format($prod->price, 2) }}
                                    </span>

                                    @if ($prod->stock_quantity > 0)
                                        <button
                                            type="button"
                                            @disabled(! $activeSeat)
                                            onclick="addToCart({{ $prod->id }}, '{{ addslashes($prod->name) }}', {{ $prod->price }}, {{ $prod->stock_quantity }})"
                                            class="btn btn-danger btn-sm px-3 fw-bold rounded-pill"
                                        >
                                            <i class="bi bi-cart-plus me-1"></i> ใส่ตะกร้า
                                        </button>
                                    @else
                                        <button disabled class="btn btn-outline-secondary btn-sm px-3 disabled">
                                            สินค้าหมด
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="card bg-dark border-secondary-subtle rounded-4 p-5 text-center text-secondary small">
                                ไม่พบรายการอาหารในหมวดหมู่นี้
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- ตะกร้าสินค้า -->
            <div class="col-12 col-lg-4">
                <div class="card bg-dark border-secondary-subtle rounded-4 p-4 shadow-sm sticky-top" style="top: 80px;">
                    <div class="d-flex justify-content-between align-items-center border-bottom border-secondary-subtle pb-3 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="bg-danger rounded" style="width: 4px; height: 20px;"></span>
                            <h3 class="h6 fw-bold text-white m-0">
                                <i class="bi bi-cart3 me-1 text-danger"></i> ตะกร้าสินค้า
                            </h3>
                        </div>
                        <span id="cartCountBadge" class="badge bg-danger-subtle text-danger font-monospace">
                            0 รายการ
                        </span>
                    </div>

                    @if ($activeSeat)
                        <div class="p-3 bg-dark-subtle border border-danger-subtle rounded-3 d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-display fs-4 text-danger"></i>
                                <div>
                                    <small class="text-secondary d-block">จัดส่งตรงถึงโต๊ะคอมพิวเตอร์</small>
                                    <strong class="text-white small">เครื่อง {{ $activeSeat->seat_number }}</strong>
                                    <small class="text-danger fw-semibold d-block">{{ $activeSeat->zone->name }}</small>
                                </div>
                            </div>
                            <span class="badge bg-danger-subtle text-danger">ผูกเครื่องแล้ว</span>
                        </div>
                    @endif

                    <div id="cartItemsList" class="overflow-y-auto mb-3" style="max-height: 240px;">
                        <div class="py-4 text-center text-secondary small" id="emptyCartNotice">
                            <i class="bi bi-cart-x fs-3 d-block mb-1 text-secondary"></i>
                            ยังไม่มีรายการอาหารในตะกร้า
                        </div>
                    </div>

                    <form method="POST" action="{{ route('customer.place-order') }}" id="checkoutForm" class="d-flex flex-column gap-3">
                        @csrf
                        <input type="hidden" name="seat_id" value="{{ $activeSeat ? $activeSeat->id : '' }}">
                        <div id="formHiddenInputs"></div>

                        <div class="pt-3 border-top border-secondary-subtle">
                            <label class="form-label text-secondary small fw-bold text-uppercase">ช่องทางการชำระเงิน:</label>

                            <div class="d-flex flex-column gap-2">
                                <label class="form-check p-2.5 rounded-3 border border-secondary-subtle bg-dark-subtle d-flex align-items-start gap-2 cursor-pointer transition">
                                    <input type="radio" name="payment_method" value="wallet" checked class="form-check-input mt-1">
                                    <div>
                                        <strong class="text-white small d-block">
                                            <i class="bi bi-wallet2 me-1 text-danger"></i> หักจากกระเป๋าเงิน (Wallet)
                                        </strong>
                                        <small class="text-secondary">
                                            ยอดเงินที่ใช้ได้: <span class="text-warning fw-bold font-monospace">฿{{ number_format($availableBalance, 2) }}</span>
                                        </small>
                                    </div>
                                </label>

                                <label class="form-check p-2.5 rounded-3 border border-secondary-subtle bg-dark-subtle d-flex align-items-center gap-2 cursor-pointer transition">
                                    <input type="radio" name="payment_method" value="promptpay" class="form-check-input mt-0">
                                    <span class="text-white small fw-bold">
                                        <i class="bi bi-qr-code-scan me-1 text-info"></i> สแกนพร้อมเพย์ QR Code
                                    </span>
                                </label>

                                <label class="form-check p-2.5 rounded-3 border border-secondary-subtle bg-dark-subtle d-flex align-items-center gap-2 cursor-pointer transition">
                                    <input type="radio" name="payment_method" value="cash" class="form-check-input mt-0">
                                    <span class="text-white small fw-bold">
                                        <i class="bi bi-cash-stack me-1 text-success"></i> ชำระเงินสดที่เคาน์เตอร์
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="pt-3 border-top border-secondary-subtle">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-secondary small">ยอดรวมทั้งสิ้น:</span>
                                <span class="fs-4 fw-bold text-white font-monospace" id="cartTotalText">฿0.00</span>
                            </div>

                            <button
                                type="submit"
                                id="btnSubmitOrder"
                                @disabled(! $activeSeat)
                                class="btn btn-danger w-100 py-2.5 fw-bold rounded-pill shadow-sm"
                            >
                                @if (! $activeSeat)
                                    กรุณาเปิดเครื่องก่อนสั่งอาหาร
                                @else
                                    <i class="bi bi-check2-circle me-1"></i> ยืนยันการสั่งอาหาร (ส่งที่เครื่อง {{ $activeSeat->seat_number }})
                                @endif
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let cart = {};

        function addToCart(productId, name, price, maxStock) {
            if (!cart[productId]) {
                cart[productId] = { id: productId, name: name, price: price, quantity: 1, maxStock: maxStock };
            } else {
                if (cart[productId].quantity < maxStock) {
                    cart[productId].quantity += 1;
                } else {
                    alert('สินค้ามีในสต็อกเพียง ' + maxStock + ' ชิ้น');
                }
            }
            renderCart();
        }

        function changeQty(productId, delta) {
            if (cart[productId]) {
                const newQty = cart[productId].quantity + delta;
                if (newQty <= 0) {
                    delete cart[productId];
                } else if (newQty > cart[productId].maxStock) {
                    alert('สินค้ามีในสต็อกเพียง ' + cart[productId].maxStock + ' ชิ้น');
                } else {
                    cart[productId].quantity = newQty;
                }
                renderCart();
            }
        }

        function removeCartItem(productId) {
            delete cart[productId];
            renderCart();
        }

        function renderCart() {
            const listEl = document.getElementById('cartItemsList');
            const hiddenInputsEl = document.getElementById('formHiddenInputs');
            const countBadge = document.getElementById('cartCountBadge');
            const totalText = document.getElementById('cartTotalText');

            listEl.innerHTML = '';
            hiddenInputsEl.innerHTML = '';

            const keys = Object.keys(cart);
            let total = 0;
            let totalCount = 0;

            if (keys.length === 0) {
                listEl.innerHTML = '<div class="py-4 text-center text-secondary small"><i class="bi bi-cart-x fs-3 d-block mb-1 text-secondary"></i>ยังไม่มีรายการอาหารในตะกร้า</div>';
                countBadge.innerText = '0 รายการ';
                totalText.innerText = '฿0.00';
                return;
            }

            keys.forEach((pId, idx) => {
                const item = cart[pId];
                const subtotal = item.price * item.quantity;
                total += subtotal;
                totalCount += item.quantity;

                hiddenInputsEl.innerHTML += `<input type="hidden" name="items[${idx}][product_id]" value="${item.id}">`;
                hiddenInputsEl.innerHTML += `<input type="hidden" name="items[${idx}][quantity]" value="${item.quantity}">`;

                listEl.innerHTML += `
                    <div class="py-2 border-bottom border-secondary-subtle d-flex justify-content-between align-items-center gap-2 small">
                        <div class="overflow-hidden flex-grow-1">
                            <strong class="text-white d-block text-truncate" style="font-size: 13px;">${item.name}</strong>
                            <small class="text-secondary font-monospace">฿${item.price.toFixed(2)} x ${item.quantity}</small>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" onclick="changeQty(${item.id}, -1)" class="btn btn-outline-secondary btn-sm py-0 px-2 fw-bold">&minus;</button>
                            <span class="text-white fw-bold px-1" style="font-size: 13px;">${item.quantity}</span>
                            <button type="button" onclick="changeQty(${item.id}, 1)" class="btn btn-outline-secondary btn-sm py-0 px-2 fw-bold">+</button>
                            <button type="button" onclick="removeCartItem(${item.id})" class="btn btn-link text-danger btn-sm p-0 ms-1 text-decoration-none">&times;</button>
                        </div>
                        <span class="fw-bold text-white font-monospace text-end" style="min-width: 55px; font-size: 13px;">
                            ฿${subtotal.toFixed(2)}
                        </span>
                    </div>
                `;
            });

            countBadge.innerText = totalCount + ' ชิ้น';
            totalText.innerText = '฿' + total.toFixed(2);
        }

        let isConfirmedQr = false;

        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            if (Object.keys(cart).length === 0) {
                e.preventDefault();
                alert('กรุณาเลือกอาหารใส่ตะกร้าก่อนสั่งซื้อ');
                return;
            }

            const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'wallet';

            if (paymentMethod === 'promptpay' && !isConfirmedQr) {
                e.preventDefault();
                let total = 0;
                Object.values(cart).forEach(item => {
                    total += item.price * item.quantity;
                });
                document.getElementById('modalFoodQrAmount').innerText = '฿' + total.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const modalEl = document.getElementById('foodQrModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        });

        function submitFoodOrderDirect() {
            isConfirmedQr = true;
            const confirmBtn = document.getElementById('foodConfirmPaymentBtn');
            if (confirmBtn) {
                confirmBtn.disabled = true;
                confirmBtn.innerText = 'กำลังส่งออเดอร์...';
            }
            document.getElementById('checkoutForm').submit();
        }
    </script>

    <!-- Modal สแกนพร้อมเพย์ -->
    <div class="modal fade" id="foodQrModal" tabindex="-1" aria-labelledby="foodQrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark border border-danger-subtle rounded-4 p-4 text-center shadow-lg">
                <div class="d-flex justify-content-between align-items-center border-bottom border-secondary-subtle pb-3 mb-3">
                    <div class="d-flex align-items-center gap-2" id="foodQrModalLabel">
                        <span class="badge bg-danger rounded-circle p-1"></span>
                        <span class="fw-bold text-white small">ชำระเงินผ่าน PromptPay QR</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="mb-3">
                    <small class="text-secondary d-block">ยอดชำระที่ต้องสแกน</small>
                    <div class="fs-3 fw-black text-warning font-monospace" id="modalFoodQrAmount">฿0.00</div>
                    @if ($activeSeat)
                        <small class="text-light d-block mt-1">จัดส่งที่เครื่อง {{ $activeSeat->seat_number }} ({{ $activeSeat->zone->name }})</small>
                    @endif
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
                    <button type="button" id="foodConfirmPaymentBtn" onclick="submitFoodOrderDirect()" class="btn btn-danger w-50 small fw-bold">
                        <i class="bi bi-check2"></i> ชำระเงินเรียบร้อย
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
