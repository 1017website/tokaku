@extends('layouts.app')
@section('title','Pengaturan Toko')
@section('page-title','Pengaturan Toko')
@section('page-subtitle','Kelola informasi toko Anda')

@section('content')
<div style="max-width:560px;">
<div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:24px;">
    <form method="POST" action="{{ route('tenant.profil.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div style="display:flex;flex-direction:column;gap:16px;">
            <div>
                <label class="form-label">Nama Toko <span style="color:#f43f5e;">*</span></label>
                <input type="text" name="name" value="{{ old('name',$tenant->name) }}" required class="form-input">
            </div>
            <div>
                <label class="form-label">Nomor Telepon</label>
                <input type="text" name="phone" value="{{ old('phone',$tenant->phone) }}" class="form-input" placeholder="08xxxxxxxxxx">
            </div>
            <div>
                <label class="form-label">Alamat</label>
                <textarea name="address" rows="3" class="form-input" style="resize:vertical;">{{ old('address',$tenant->address) }}</textarea>
            </div>
            <div>
                <label class="form-label">Logo Toko</label>
                @if($tenant->logo_path)
                <div style="margin-bottom:8px;">
                    <img src="{{ Storage::url($tenant->logo_path) }}" style="width:60px;height:60px;border-radius:12px;object-fit:cover;border:1px solid #f1f5f9;">
                </div>
                @endif
                <input type="file" name="logo" accept="image/*" class="form-input" style="padding:8px 14px;cursor:pointer;">
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end;padding-top:20px;border-top:1px solid #f8fafc;margin-top:20px;">
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
</div>
@endsection
