@extends('layouts.app', ['title' => 'Rekap PO'])

@section('content')
    @php
        $allItems  = collect($orders)->flatMap(fn ($o) => $o['items']);
        $totalJual = (int) $allItems->sum(fn ($i) => $i['qty'] * $i['price']);
        $totalBeli = (int) $allItems->sum(fn ($i) => $i['qty'] * $i['buy_price']);
        $profit    = $totalJual - $totalBeli;
        $hasBeli   = $totalBeli > 0;
        $droppingTime = $orders[0]['droping_time'] ?? null;
    @endphp

    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/45 p-2 backdrop-blur-sm sm:p-5">
        <section class="mx-auto max-w-[1200px] overflow-hidden rounded-2xl bg-slate-100 shadow-2xl sm:rounded-3xl">

            {{-- Header --}}
            <header class="flex flex-col gap-4 border-b border-slate-200 bg-white px-4 py-4 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-start gap-3 sm:gap-4">
                    @include('partials.app-logo', ['class' => 'mt-1 h-12 w-12'])
                    <div class="min-w-0">
                        <h1 class="text-lg font-black tracking-tight text-slate-950 sm:text-2xl">
                            Detail Rekap PO — {{ date('d F Y', strtotime($date)) }}
                        </h1>
                        <p class="mt-1 text-sm font-bold text-slate-400">
                            {{ count($orders) }} PO · {{ $allItems->count() }} Item
                            @if ($droppingTime) · Jam {{ $droppingTime }} @endif
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-3">
                    <a href="{{ route('rekap-po.preview', $date) }}" class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-black text-slate-600 hover:bg-slate-100">Cetak PDF</a>
                    @if ($currentUser['role'] === 'ADMIN')
                        <a href="{{ route('rekap-po.edit', $date) }}" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-blue-600/20 hover:bg-blue-700">Edit</a>
                    @endif
                    <a href="{{ route('rekap-po.index') }}" class="text-3xl leading-none text-slate-400 hover:text-slate-700">&times;</a>
                </div>
            </header>

            <div class="space-y-4 px-3 py-4 sm:space-y-5 sm:px-6 sm:py-5">

                {{-- Ringkasan Finansial --}}
                <div class="grid grid-cols-3 gap-3">
                    <div class="flex flex-col justify-center rounded-xl bg-slate-900 p-4 text-white shadow-lg">
                        <p class="text-[9px] font-black uppercase tracking-[0.12em] text-slate-400">Total Harga Jual</p>
                        <p class="mt-1 text-lg font-black tracking-tight break-words">Rp {{ number_format($totalJual, 0, ',', '.') }}</p>
                    </div>
                    <div class="flex flex-col justify-center rounded-xl bg-indigo-900 p-4 text-white shadow-lg">
                        <p class="text-[9px] font-black uppercase tracking-[0.12em] text-indigo-300">Total Harga Beli</p>
                        <p class="mt-1 text-lg font-black tracking-tight break-words">
                            @if ($hasBeli) Rp {{ number_format($totalBeli, 0, ',', '.') }}
                            @else <span class="text-indigo-400 font-normal text-sm">Belum diisi</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-col justify-center rounded-xl p-4 text-white shadow-lg {{ $profit >= 0 ? 'bg-emerald-700' : 'bg-rose-700' }}">
                        <p class="text-[9px] font-black uppercase tracking-[0.12em] text-white/60">Estimasi Profit</p>
                        <p class="mt-1 text-lg font-black tracking-tight break-words">
                            @if ($hasBeli)
                                {{ $profit >= 0 ? '' : '-' }}Rp {{ number_format(abs($profit), 0, ',', '.') }}
                            @else
                                <span class="font-normal text-sm text-white/50">—</span>
                            @endif
                        </p>
                    </div>
                </div>

                {{-- PO per Supplier --}}
                @foreach ($orders as $order)
                    @php
                        $poSuppliers  = collect($order['items'])->pluck('supplier')->filter(fn ($s) => $s !== '-')->unique()->values();
                        $supplierLabel = $poSuppliers->isNotEmpty() ? $poSuppliers->join(', ') : 'Belum Ada Supplier';
                        $poTotalJual  = (int) collect($order['items'])->sum(fn ($i) => $i['qty'] * $i['price']);
                        $poTotalBeli  = (int) collect($order['items'])->sum(fn ($i) => $i['qty'] * $i['buy_price']);
                        $poProfit     = $poTotalJual - $poTotalBeli;
                    @endphp
                    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        {{-- Header PO --}}
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50 px-5 py-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded bg-blue-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-blue-700">
                                    {{ $order['number'] ?? 'Belum Diterbitkan' }}
                                </span>
                                <span class="rounded bg-emerald-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-emerald-700" title="Tujuan SPPG">
                                    {{ $order['sppg'] ?? 'Tujuan SPPG Tidak Diketahui' }}
                                </span>
                                <span class="text-sm font-black uppercase text-slate-700">{{ $supplierLabel }}</span>
                                <span class="rounded bg-slate-200 px-2 py-0.5 text-[9px] font-black uppercase text-slate-600">
                                    {{ $order['created_by'] }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-right">
                                <div>
                                    <span class="block text-[8px] font-black uppercase text-slate-400">Harga Jual</span>
                                    <span class="text-sm font-black text-slate-900">Rp {{ number_format($poTotalJual, 0, ',', '.') }}</span>
                                </div>
                                @if ($poTotalBeli > 0)
                                    <div>
                                        <span class="block text-[8px] font-black uppercase text-indigo-400">Harga Beli</span>
                                        <span class="text-sm font-black text-indigo-700">Rp {{ number_format($poTotalBeli, 0, ',', '.') }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-[8px] font-black uppercase {{ $poProfit >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">Profit</span>
                                        <span class="text-sm font-black {{ $poProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                            {{ $poProfit >= 0 ? '' : '-' }}Rp {{ number_format(abs($poProfit), 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endif
                                @include('partials.status-badge', ['status' => $order['status']])
                            </div>
                        </div>

                        {{-- Tabel Item --}}
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[800px] divide-y divide-slate-100">
                                <thead class="bg-slate-50/50">
                                    <tr>
                                        <th class="w-10 px-5 py-2.5 text-left text-[9px] font-black uppercase tracking-widest text-slate-400">#</th>
                                        <th class="px-5 py-2.5 text-left text-[9px] font-black uppercase tracking-widest text-slate-400">Barang & Grade</th>
                                        <th class="px-5 py-2.5 text-right text-[9px] font-black uppercase tracking-widest text-slate-400">Qty</th>
                                        <th class="px-5 py-2.5 text-right text-[9px] font-black uppercase tracking-widest text-slate-400">Harga Jual</th>
                                        <th class="px-5 py-2.5 text-right text-[9px] font-black uppercase tracking-widest text-indigo-400">Harga Beli</th>
                                        <th class="px-5 py-2.5 text-right text-[9px] font-black uppercase tracking-widest text-emerald-600">Subtotal Jual</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($order['items'] as $idx => $item)
                                        <tr class="hover:bg-slate-50/60">
                                            <td class="px-5 py-3 text-xs font-black text-slate-400">{{ $idx + 1 }}</td>
                                            <td class="px-5 py-3">
                                                <p class="text-sm font-black uppercase text-slate-900">{{ $item['name'] }}</p>
                                                @if (! empty($item['request']))
                                                    <p class="mt-0.5 text-xs font-bold italic text-orange-500">{{ $item['request'] }}</p>
                                                @endif
                                                <span class="mt-1 inline-flex rounded bg-emerald-50 px-1.5 py-0.5 text-[9px] font-black text-emerald-600">{{ $item['grade'] }}</span>
                                            </td>
                                            <td class="px-5 py-3 text-right text-sm font-black text-slate-700">
                                                {{ $item['qty'] }} <span class="text-xs uppercase text-slate-400">{{ $item['unit'] }}</span>
                                            </td>
                                            <td class="px-5 py-3 text-right text-sm font-medium text-slate-600">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                            <td class="px-5 py-3 text-right text-sm font-medium text-indigo-600">
                                                @if ($item['buy_price'] > 0)
                                                    Rp {{ number_format($item['buy_price'], 0, ',', '.') }}
                                                @else
                                                    <span class="text-slate-300">—</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 text-right text-sm font-black text-slate-950">Rp {{ number_format($item['qty'] * $item['price'], 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endforeach

                <div class="text-center text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">
                    @include('partials.copyright')
                </div>
            </div>
        </section>
    </div>
@endsection
