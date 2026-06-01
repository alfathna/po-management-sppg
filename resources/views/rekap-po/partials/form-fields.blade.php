@php
    $allItems  = collect($orders)->flatMap(fn ($o) => $o['items']);
    $totalJual = (int) $allItems->sum(fn ($i) => ($i['qty'] ?? 0) * ($i['price'] ?? 0));
    $totalBeli = (int) $allItems->sum(fn ($i) => ($i['qty'] ?? 0) * ($i['buy_price'] ?? 0));
    $profit    = $totalJual - $totalBeli;
@endphp

<div class="space-y-4 px-4 py-4 sm:px-6 sm:py-5">

    {{-- Panel Ringkasan Finansial --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="flex flex-col justify-center rounded-xl bg-slate-900 p-4 text-white shadow-lg">
            <p class="text-[9px] font-black uppercase tracking-[0.12em] text-slate-400">Total Harga Jual</p>
            <p class="mt-1 break-words text-lg font-black tracking-tight" id="total-jual-display">Rp {{ number_format($totalJual, 0, ',', '.') }}</p>
        </div>
        <div class="flex flex-col justify-center rounded-xl bg-indigo-900 p-4 text-white shadow-lg">
            <p class="text-[9px] font-black uppercase tracking-[0.12em] text-indigo-300">Total Harga Beli</p>
            <p class="mt-1 break-words text-lg font-black tracking-tight" id="total-beli-display">Rp {{ number_format($totalBeli, 0, ',', '.') }}</p>
        </div>
        <div class="flex flex-col justify-center rounded-xl p-4 text-white shadow-lg {{ $profit >= 0 ? 'bg-emerald-700' : 'bg-rose-700' }}" id="profit-panel">
            <p class="text-[9px] font-black uppercase tracking-[0.12em] text-white/60">Estimasi Profit</p>
            <p class="mt-1 break-words text-lg font-black tracking-tight" id="profit-display">
                {{ $profit >= 0 ? '' : '-' }}Rp {{ number_format(abs($profit), 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Info Tanggal Drop --}}
    <div class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-2">
        <label class="block">
            <span class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-slate-500">Tanggal Drop</span>
            <input name="droping_date" type="date" value="{{ old('droping_date', $date) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
        </label>
        <label class="block">
            <span class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-slate-500">Jam Drop</span>
            @php
                $firstOrder = $orders[0] ?? [];
            @endphp
            <input name="droping_time" type="time" value="{{ old('droping_time', $firstOrder['droping_time'] ?? '') }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
        </label>
    </div>

    {{-- Daftar Barang per PO/Supplier --}}
    @foreach ($orders as $orderIdx => $order)
        @php
            $poSuppliers = collect($order['items'])->pluck('supplier')->filter(fn ($s) => $s !== '-')->unique()->values();
            $supplierLabel = $poSuppliers->isNotEmpty() ? $poSuppliers->join(', ') : 'Belum Ada Supplier';
        @endphp
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            {{-- Header PO --}}
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-2.5">
                <div class="flex items-center gap-3">
                    <span class="rounded bg-blue-100 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-blue-700">
                        {{ $order['number'] ?? 'Belum Diterbitkan' }}
                    </span>
                    <span class="rounded bg-emerald-100 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-emerald-700" title="Tujuan SPPG">
                        {{ $order['sppg'] ?? 'Tujuan SPPG Tidak Diketahui' }}
                    </span>
                    <span class="text-[10px] font-black uppercase text-slate-500">{{ $supplierLabel }}</span>
                </div>
                <span class="text-[10px] font-bold text-slate-400">{{ count($order['items']) }} item</span>
            </div>

            {{-- Tabel Item --}}
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-sm">
                    <thead class="bg-slate-50/60">
                        <tr>
                            <th class="w-10 px-3 py-2 text-left text-[10px] font-bold uppercase tracking-wide text-slate-400">#</th>
                            <th class="px-3 py-2 text-left text-[10px] font-bold uppercase tracking-wide text-slate-400">Barang</th>
                            <th class="w-36 px-2 py-2 text-left text-[10px] font-bold uppercase tracking-wide text-slate-400">Catatan</th>
                            <th class="w-16 px-2 py-2 text-left text-[10px] font-bold uppercase tracking-wide text-slate-400">Grade</th>
                            <th class="w-24 px-2 py-2 text-left text-[10px] font-bold uppercase tracking-wide text-slate-400">Qty</th>
                            <th class="w-16 px-2 py-2 text-left text-[10px] font-bold uppercase tracking-wide text-slate-400">Satuan</th>
                            <th class="w-32 px-2 py-2 text-left text-[10px] font-bold uppercase tracking-wide text-slate-400">Harga Jual</th>
                            <th class="w-32 px-2 py-2 text-left text-[10px] font-bold uppercase tracking-wide text-indigo-500">Harga Beli</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" data-rekap-tbody>
                        @foreach ($order['items'] as $item)
                            @php($itemId = $item['id'])
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-3 py-2 text-xs font-bold text-slate-400">{{ $loop->iteration }}</td>
                                <td class="px-3 py-2">
                                    <p class="max-w-[200px] truncate text-xs font-black uppercase text-slate-900">{{ $item['name'] }}</p>
                                    @if (! empty($item['supplier']) && $item['supplier'] !== '-')
                                        <p class="mt-0.5 text-[9px] font-bold uppercase text-blue-600">{{ $item['supplier'] }}</p>
                                    @endif
                                </td>
                                <td class="px-2 py-2">
                                    <input name="items[{{ $itemId }}][request]" value="{{ old('items.'.$itemId.'.request', $item['request'] ?? '') }}" placeholder="-" class="w-full min-w-[100px] rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs font-normal text-slate-600 outline-none">
                                </td>
                                <td class="px-2 py-2">
                                    <select name="items[{{ $itemId }}][grade]" class="w-full rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500">
                                        <option value="A" @selected(old('items.'.$itemId.'.grade', $item['grade'] ?? 'A') === 'A')>A</option>
                                        <option value="B" @selected(old('items.'.$itemId.'.grade', $item['grade'] ?? '') === 'B')>B</option>
                                        <option value="C" @selected(old('items.'.$itemId.'.grade', $item['grade'] ?? '') === 'C')>C</option>
                                        <option value="REJECT" @selected(old('items.'.$itemId.'.grade', $item['grade'] ?? '') === 'REJECT')>REJ</option>
                                    </select>
                                </td>
                                <td class="px-2 py-2">
                                    <input name="items[{{ $itemId }}][qty]" type="number" min="0.01" step="0.01" value="{{ old('items.'.$itemId.'.qty', $item['qty'] ?? 0) }}" class="w-full min-w-[80px] rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs font-semibold text-slate-800 outline-none rekap-qty">
                                </td>
                                <td class="px-2 py-2">
                                    <span class="block px-2 py-1.5 text-xs font-semibold uppercase text-slate-500">{{ $item['unit'] }}</span>
                                    <input type="hidden" name="items[{{ $itemId }}][unit]" value="{{ $item['unit'] }}">
                                </td>
                                {{-- Harga Jual --}}
                                <td class="px-2 py-2">
                                    <div class="flex items-center rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5">
                                        <span class="mr-1 text-[9px] font-bold text-slate-400">Rp</span>
                                        <input name="items[{{ $itemId }}][price]" type="text" inputmode="numeric" data-currency-input value="{{ old('items.'.$itemId.'.price', $item['price'] ?? 0) }}" class="rekap-price min-w-0 flex-1 bg-transparent text-xs font-semibold text-slate-800 outline-none">
                                    </div>
                                </td>
                                {{-- Harga Beli --}}
                                <td class="px-2 py-2">
                                    <div class="flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-2 py-1.5">
                                        <span class="mr-1 text-[9px] font-bold text-indigo-400">Rp</span>
                                        <input name="items[{{ $itemId }}][buy_price]" type="text" inputmode="numeric" data-currency-input value="{{ old('items.'.$itemId.'.buy_price', $item['buy_price'] ?? 0) }}" class="rekap-buy-price min-w-0 flex-1 bg-transparent text-xs font-semibold text-indigo-800 outline-none">
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endforeach
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function parseRupiah(val) {
            return parseInt(String(val || '0').replace(/[^\d]/g, ''), 10) || 0;
        }

        function formatRupiah(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        function recalcTotals() {
            let sumJual = 0;
            let sumBeli = 0;

            document.querySelectorAll('[data-rekap-tbody] tr').forEach(function (row) {
                const qtyInput      = row.querySelector('input[name$="[qty]"]');
                const priceInput    = row.querySelector('input[name$="[price]"]');
                const buyPriceInput = row.querySelector('input[name$="[buy_price]"]');

                const qty      = parseFloat(qtyInput?.value || 0) || 0;
                const price    = parseRupiah(priceInput?.value);
                const buyPrice = parseRupiah(buyPriceInput?.value);

                sumJual += qty * price;
                sumBeli += qty * buyPrice;
            });

            const profit = sumJual - sumBeli;

            document.getElementById('total-jual-display').textContent = 'Rp ' + formatRupiah(sumJual);
            document.getElementById('total-beli-display').textContent = 'Rp ' + formatRupiah(sumBeli);
            document.getElementById('profit-display').textContent = (profit < 0 ? '-' : '') + 'Rp ' + formatRupiah(Math.abs(profit));

            const panel = document.getElementById('profit-panel');
            if (panel) {
                panel.classList.remove('bg-emerald-700', 'bg-rose-700', 'bg-slate-700');
                if (sumBeli === 0) {
                    panel.classList.add('bg-slate-700');
                } else if (profit >= 0) {
                    panel.classList.add('bg-emerald-700');
                } else {
                    panel.classList.add('bg-rose-700');
                }
            }
        }

        // Format & attach currency input events
        document.querySelectorAll('[data-currency-input]').forEach(function (input) {
            const raw = parseRupiah(input.value);
            input.value = raw > 0 ? formatRupiah(raw) : '0';

            input.addEventListener('focus', function () {
                this.value = parseRupiah(this.value).toString();
            });
            input.addEventListener('blur', function () {
                const num = parseRupiah(this.value);
                this.value = formatRupiah(num);
                recalcTotals();
            });
            input.addEventListener('input', recalcTotals);
        });

        // Also handle qty changes
        document.querySelectorAll('input[name$="[qty]"]').forEach(function (input) {
            input.addEventListener('input', recalcTotals);
        });

        recalcTotals();
    });
</script>
