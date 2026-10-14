<x-layouts::app :title="__('Stock Manager')">
    <div class="d-flex flex-column gap-4">
                @if (session()->has('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 mb-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

                <div class="card p-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 shadow-sm">
            <div>
                <div class="step-header">
                    <span class="step-bar"></span>
                    <h2 class="h4 fw-bold text-white mb-0">จัดการสต็อกสินค้าและเมนู (Stock Management)</h2>
                </div>
                <p class="text-secondary small ps-3 mt-1 mb-0">เพิ่ม ปรับปรุงจำนวนสต็อก และแก้ไขราคาอาหาร/เครื่องดื่มในร้าน</p>
            </div>

            <button
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#createProductModal"
                class="btn btn-danger btn-sm px-3 fw-bold shadow-sm"
            >
                <i class="bi bi-plus-lg me-1"></i> เพิ่มสินค้าใหม่
            </button>
        </div>

        <!-- รายการสินค้าและสต็อก -->
        <div class="card p-3 p-md-4 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark text-uppercase small text-secondary">
                        <tr>
                            <th class="py-3 px-3">หมวดหมู่</th>
                            <th class="py-3 px-3">ชื่อสินค้า</th>
                            <th class="py-3 px-3">ราคาขาย</th>
                            <th class="py-3 px-3">สต็อกคงเหลือ</th>
                            <th class="py-3 px-3 text-end">ปรับสต็อก</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $prod)
                            <tr>
                                <td class="py-3 px-3">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle small">
                                        {{ $prod->category ? $prod->category->name : '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="fw-bold text-white">{{ $prod->name }}</div>
                                    @if($prod->description)
                                        <div class="small text-secondary text-truncate" style="max-width: 280px;">{{ $prod->description }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-monospace fw-bold text-white">
                                    ฿{{ number_format($prod->price, 2) }}
                                </td>
                                <td class="py-3 px-3 font-monospace fw-bold">
                                    <span class="{{ $prod->stock_quantity > 10 ? 'text-success' : ($prod->stock_quantity > 0 ? 'text-warning' : 'text-danger') }}">
                                        {{ $prod->stock_quantity }} ชิ้น
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        @foreach ([-10, -1, 1, 10] as $d)
                                            <form method="POST" action="{{ route('admin.adjust-stock') }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $prod->id }}">
                                                <input type="hidden" name="delta" value="{{ $d }}">
                                                <button
                                                    type="submit"
                                                    class="btn btn-outline-secondary font-monospace fw-bold px-2 py-1"
                                                    title="{{ $d > 0 ? 'เพิ่ม ' . $d . ' ชิ้น' : 'ลด ' . abs($d) . ' ชิ้น' }}"
                                                >
                                                    {{ $d > 0 ? '+' . $d : $d }}
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal เพิ่มสินค้าใหม่ -->
        <div class="modal fade" id="createProductModal" tabindex="-1" aria-labelledby="createProductModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-secondary shadow-lg">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title fw-bold text-white" id="createProductModalLabel">
                            <i class="bi bi-box-seam me-2 text-danger"></i>เพิ่มสินค้าใหม่
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form method="POST" action="{{ route('admin.create-product') }}">
                        @csrf
                        <div class="modal-body d-flex flex-column gap-3 small">
                            <div>
                                <label class="form-label fw-bold text-light mb-1">หมวดหมู่</label>
                                <select name="category_id" required class="form-select form-select-sm">
                                    <option value="">-- เลือกหมวดหมู่ --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="form-label fw-bold text-light mb-1">ชื่อสินค้า</label>
                                <input type="text" name="name" required class="form-control form-control-sm" placeholder="เช่น ข้าวกะเพราหมูสับไข่ดาว..." />
                            </div>

                            <div>
                                <label class="form-label fw-bold text-light mb-1">คำอธิบาย</label>
                                <textarea name="description" rows="2" class="form-control form-control-sm" placeholder="รายละเอียดเมนู..."></textarea>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label fw-bold text-light mb-1">ราคา (บาท)</label>
                                    <input type="number" step="0.5" min="0" name="price" required class="form-control form-control-sm font-monospace fw-bold" />
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold text-light mb-1">สต็อกเริ่มต้น</label>
                                    <input type="number" min="0" name="stock_quantity" required class="form-control form-control-sm font-monospace fw-bold" />
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-secondary">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-danger btn-sm fw-bold px-4">บันทึกสินค้า</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
