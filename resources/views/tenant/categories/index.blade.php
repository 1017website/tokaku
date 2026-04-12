@extends('layouts.app')
@section('title','Kategori')
@section('page-title','Kategori')
@section('page-subtitle','Kelola kategori produk')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:22px;">
            <p style="font-size:14px;font-weight:600;color:#0f172a;margin-bottom:16px;">Tambah Kategori</p>
            <form method="POST" action="{{ route('tenant.categories.store') }}">
                @csrf
                @if($errors->any())
                <div style="background:#fff1f2;border:1px solid #fecdd3;color:#be123c;font-size:13px;border-radius:10px;padding:10px 14px;margin-bottom:12px;">{{ $errors->first() }}</div>
                @endif
                <div style="margin-bottom:12px;">
                    <label class="form-label">Nama Kategori</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="contoh: Makanan">
                </div>
                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;">Tambah</button>
            </form>
        </div>
    </div>
    <div class="sm:col-span-2">
        <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">
            <div style="padding:16px 20px;border-bottom:1px solid #f8fafc;">
                <p style="font-size:14px;font-weight:600;color:#0f172a;">Daftar Kategori ({{ $categories->count() }})</p>
            </div>
            <div>
                @forelse($categories as $cat)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #f8fafc;">
                    <div>
                        <p style="font-size:14px;font-weight:500;color:#0f172a;">{{ $cat->name }}</p>
                        <p style="font-size:12px;color:#94a3b8;margin-top:2px;">{{ $cat->products_count }} produk</p>
                    </div>
                    <form method="POST" action="{{ route('tenant.categories.destroy',$cat) }}" onsubmit="return confirm('Hapus kategori ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="font-size:13px;color:#f43f5e;font-weight:500;background:none;border:none;cursor:pointer;font-family:Inter,sans-serif;">Hapus</button>
                    </form>
                </div>
                @empty
                <div style="padding:50px 20px;text-align:center;font-size:14px;color:#94a3b8;">Belum ada kategori.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
