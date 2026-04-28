@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('content')
{{-- ── STAT CARDS ── --}}
    {{-- Pelanggan --}}
Total Pelanggan Aktif
{{ $stats['customers'] }}
    {{-- Paket Aktif --}}
Pilihan Paket Layanan
{{ $stats['packages'] }}
{{-- ── FULL COLUMN ── --}}
    {{-- Pelanggan Terbaru --}}
Pelanggan baru bergabung
                Lihat semua pelanggan →
            @forelse($recentCustomers as $cust)
                    {{ strtoupper(substr($cust->full_name,0,1)) }}
{{ $cust->full_name }}
{{ $cust->customer_code }} ·
{{ $cust->package->name ?? '-' }}
                        Active
{{ $cust->created_at->translatedFormat('d M Y') }}
            @empty
Belum ada data pelanggan terbaru.
            @endforelse
@endsection