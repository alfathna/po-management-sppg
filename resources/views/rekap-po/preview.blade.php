@extends('layouts.app', ['title' => 'Rekap PO — Cetak'])

@section('content')
    @php
        $allItems  = collect($orders)->flatMap(fn ($o) => $o['items']);
        $totalJual = (int) $allItems->sum(fn ($i) => $i['qty'] * $i['price']);
        $totalBeli = (int) $allItems->sum(fn ($i) => $i['qty'] * $i['buy_price']);
        $profit    = $totalJual - $totalBeli;
    @endphp

    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/45 p-2 backdrop-blur-sm sm:p-5">
        <section class="mx-auto max-w-[1100px] overflow-hidden rounded-2xl bg-white shadow-2xl">

            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    @include('partials.app-logo', ['class' => 'h-10 w-10'])
                    <div>
                        <h1 class="text-base font-black tracking-tight text-slate-950">Rekap PO — {{ date('d F Y', strtotime($date)) }}</h1>
                        <p class="text-xs font-bold text-slate-400">{{ count($orders) }} PO · {{ $allItems->count() }} Item Total</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-black text-white shadow-md shadow-indigo-600/20 hover:bg-indigo-700">🖨 Cetak</button>
                    <a href="{{ route('rekap-po.show', $date) }}" class="text-3xl leading-none text-slate-400 hover:text-slate-700">&times;</a>
                </div>
            </div>

            {{-- Ringkasan Finansial --}}
            <div class="grid grid-cols-3 gap-4 border-b border-slate-100 bg-slate-50 px-6 py-4">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Total Harga Jual</p>
                    <p class="mt-1 text-lg font-black text-slate-950">Rp {{ number_format($totalJual, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-[9px] font-black uppercase tracking-widest text-indigo-400">Total Harga Beli</p>
                    <p class="mt-1 text-lg font-black text-indigo-700">
                        @if ($totalBeli > 0) Rp {{ number_format($totalBeli, 0, ',', '.') }}
                        @else <span class="text-sm font-normal text-slate-400">Belum diisi</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-[9px] font-black uppercase tracking-widest {{ $profit >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">Estimasi Profit</p>
                    <p class="mt-1 text-lg font-black {{ $profit >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                        @if ($totalBeli > 0)
                            {{ $profit >= 0 ? '' : '-' }}Rp {{ number_format(abs($profit), 0, ',', '.') }}
                        @else
                            <span class="text-sm font-normal text-slate-400">—</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Daftar PO per Supplier --}}
            <div class="divide-y divide-slate-100 px-6 py-4 space-y-6">
                @foreach ($orders as $order)
                    @php
                        $poSuppliers   = collect($order['items'])->pluck('supplier')->filter(fn ($s) => $s !== '-')->unique()->values();
                        $supplierLabel = $poSuppliers->isNotEmpty() ? $poSuppliers->join(', ') : 'Belum Ada Supplier';
                        $poTotal       = (int) collect($order['items'])->sum(fn ($i) => $i['qty'] * $i['price']);
                    @endphp
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-black uppercase text-slate-800">{{ $supplierLabel }}</span>
                                <span class="rounded bg-blue-100 px-2 py-0.5 text-[9px] font-black text-blue-700">{{ $order['number'] ?? 'Belum Diterbitkan' }}</span>
                                <span class="rounded bg-emerald-100 px-2 py-0.5 text-[9px] font-black text-emerald-700" title="Tujuan SPPG">{{ $order['sppg'] ?? 'Tujuan SPPG Tidak Diketahui' }}</span>
                            </div>
                            <span class="text-sm font-black text-slate-900">Rp {{ number_format($poTotal, 0, ',', '.') }}</span>
                        </div>
                        <table class="w-full border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="border border-slate-200 px-3 py-2 text-left font-black uppercase text-slate-500">#</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-black uppercase text-slate-500">Barang</th>
                                    <th class="border border-slate-200 px-3 py-2 text-center font-black uppercase text-slate-500">Grade</th>
                                    <th class="border border-slate-200 px-3 py-2 text-right font-black uppercase text-slate-500">Qty</th>
                                    <th class="border border-slate-200 px-3 py-2 text-right font-black uppercase text-slate-500">Harga Jual</th>
                                    <th class="border border-slate-200 px-3 py-2 text-right font-black uppercase text-indigo-500">Harga Beli</th>
                                    <th class="border border-slate-200 px-3 py-2 text-right font-black uppercase text-slate-500">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order['items'] as $idx => $item)
                                    <tr class="{{ $loop->even ? 'bg-slate-50' : '' }}">
                                        <td class="border border-slate-200 px-3 py-2 text-center text-slate-400">{{ $idx + 1 }}</td>
                                        <td class="border border-slate-200 px-3 py-2 font-semibold uppercase text-slate-800">{{ $item['name'] }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-center font-bold text-emerald-700">{{ $item['grade'] }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-right font-semibold text-slate-700">{{ $item['qty'] }} {{ $item['unit'] }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-right font-semibold text-slate-600">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-right font-semibold text-indigo-600">
                                            @if ($item['buy_price'] > 0)
                                                Rp {{ number_format($item['buy_price'], 0, ',', '.') }}
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2 text-right font-black text-slate-950">Rp {{ number_format($item['qty'] * $item['price'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-slate-100">
                                    <td colspan="6" class="border border-slate-200 px-3 py-2 text-right text-[10px] font-black uppercase tracking-wide text-slate-500">Subtotal PO</td>
                                    <td class="border border-slate-200 px-3 py-2 text-right font-black text-slate-950">Rp {{ number_format($poTotal, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>

            {{-- Footer --}}
            <div class="border-t border-slate-100 px-6 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-400">
                @include('partials.copyright')
            </div>
        </section>
    </div>

    <style>
        @media print {
            @page { margin: 10mm; }
            body { visibility: hidden; background: white !important; }
            
            /* Target wrapper specifically */
            .fixed.inset-0 {
                visibility: visible;
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                height: auto !important;
                min-height: 100% !important;
                overflow: visible !important;
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .fixed.inset-0 * {
                visibility: visible;
            }

            /* Remove shadows, borders, radiuses */
            section { 
                box-shadow: none !important; 
                border-radius: 0 !important; 
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Hide interactive elements */
            button, a { display: none !important; }

            /* Pagination handling */
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            .divide-y > div { page-break-inside: avoid; margin-bottom: 20px; }
        }
    </style>
@endsection
