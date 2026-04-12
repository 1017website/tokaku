@extends('layouts.app')
@section('title','Tambah Produk')
@section('page-title','Tambah Produk')
@section('page-subtitle','Isi informasi produk baru')

@section('header-actions')
<a href="{{ route('tenant.products.index') }}" class="btn-secondary">&larr; Kembali</a>
@endsection

@section('content')
<div style="max-width:600px;">
<div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:24px;">
    <form method="POST" action="{{ route('tenant.products.store') }}" enctype="multipart/form-data">
        @csrf
        @include('tenant.products._form', ['product' => null])
        <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:20px;border-top:1px solid #f8fafc;margin-top:20px;">
            <a href="{{ route('tenant.products.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Simpan Produk</button>
        </div>
    </form>
</div></div>
@endsection
