@extends('layouts.app')
@section('title','Tim Toko')
@section('page-title','Tim Toko')
@section('page-subtitle','Kelola kasir dan admin toko Anda')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Form Tambah User --}}
    <div>
        <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:22px;">
            @php
                $maxUsers = (int) config('tokaku.max_users', 3);
                $usedSlot = $users->count();
                $slotPenuh = $usedSlot >= $maxUsers;
            @endphp
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                <p style="font-size:14px;font-weight:600;color:#0f172a;">Tambah Anggota Tim</p>
                <span style="font-size:11.5px;font-weight:500;padding:3px 10px;border-radius:99px;{{ $slotPenuh ? 'background:#fff1f2;color:#be123c;' : 'background:#f0fdf6;color:#0F6E56;' }}">
                    {{ $usedSlot }}/{{ $maxUsers }} slot
                </span>
            </div>

            @if($errors->any())
            <div style="background:#fff1f2;border:1px solid #fecdd3;color:#be123c;font-size:13px;border-radius:10px;padding:10px 14px;margin-bottom:14px;">
                {{ $errors->first() }}
            </div>
            @endif

            @if($slotPenuh)
            <div style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;font-size:13px;border-radius:10px;padding:12px 14px;line-height:1.5;">
                Slot user sudah penuh ({{ $maxUsers }} termasuk owner). Hapus atau nonaktifkan anggota lain untuk menambah user baru.
            </div>
            @else
            <form method="POST" action="{{ route('tenant.users.store') }}" style="display:flex;flex-direction:column;gap:13px;">
                @csrf
                <div>
                    <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:5px;">Nama Lengkap *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;box-sizing:border-box;"
                        placeholder="Budi Santoso"
                        onfocus="this.style.borderColor='#0F6E56';this.style.background='#fff';"
                        onblur="this.style.borderColor='#e2e8f0';this.style.background='#fafafa';">
                </div>
                <div>
                    <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:5px;">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;box-sizing:border-box;"
                        placeholder="kasir@email.com"
                        onfocus="this.style.borderColor='#0F6E56';this.style.background='#fff';"
                        onblur="this.style.borderColor='#e2e8f0';this.style.background='#fafafa';">
                </div>
                <div>
                    <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:5px;">Role *</label>
                    <select name="role" style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;cursor:pointer;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#0F6E56'" onblur="this.style.borderColor='#e2e8f0'">
                        <option value="cashier" {{ old('role')=='cashier'?'selected':'' }}>Kasir</option>
                        <option value="admin" {{ old('role')=='admin'?'selected':'' }}>Admin</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:8px;">Akses Modul</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;border:1.5px solid #e2e8f0;border-radius:10px;padding:12px;background:#fafafa;">
                        @foreach(config('permissions.modules') as $key => $label)
                        <label style="display:flex;align-items:center;gap:7px;font-size:12.5px;color:#374151;cursor:pointer;">
                            <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                {{ in_array($key, old('permissions', [])) ? 'checked' : '' }}
                                style="width:15px;height:15px;accent-color:#0F6E56;cursor:pointer;">
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                    <p style="font-size:11px;color:#94a3b8;margin-top:6px;">Centang modul yang boleh diakses anggota ini.</p>
                </div>
                <div>
                    <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:5px;">Password *</label>
                    <div style="position:relative;">
                        <input type="password" name="password" id="cpass" required minlength="8"
                            style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 42px 10px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;box-sizing:border-box;"
                            placeholder="min. 8 karakter"
                            onfocus="this.style.borderColor='#0F6E56';this.style.background='#fff';"
                            onblur="this.style.borderColor='#e2e8f0';this.style.background='#fafafa';">
                        <button type="button" onclick="togglePass('cpass', this)" tabindex="-1"
                            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:#94a3b8;line-height:0;">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:5px;">Konfirmasi Password *</label>
                    <div style="position:relative;">
                        <input type="password" name="password_confirmation" id="cpass2" required minlength="8"
                            style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 42px 10px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;box-sizing:border-box;"
                            placeholder="ulangi password"
                            onfocus="this.style.borderColor='#0F6E56';this.style.background='#fff';"
                            onblur="this.style.borderColor='#e2e8f0';this.style.background='#fafafa';">
                        <button type="button" onclick="togglePass('cpass2', this)" tabindex="-1"
                            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:#94a3b8;line-height:0;">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>
                <button type="submit"
                    style="width:100%;background:#0F6E56;color:#fff;font-family:Inter,sans-serif;font-size:13.5px;font-weight:600;padding:11px;border-radius:10px;border:none;cursor:pointer;margin-top:4px;"
                    onmouseover="this.style.background='#085041'" onmouseout="this.style.background='#0F6E56'">
                    Tambah Anggota
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Daftar User --}}
    <div class="lg:col-span-2">
        <div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">
            <div style="padding:16px 20px;border-bottom:1px solid #f8fafc;">
                <p style="font-size:14px;font-weight:600;color:#0f172a;">Anggota Tim ({{ $users->count() }})</p>
            </div>

            <div class="divide-y divide-gray-50">
                @forelse($users as $user)
                <div style="padding:16px 20px;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;{{ $user->is_active ? 'background:#dcfce9;' : 'background:#f1f5f9;' }}">
                                <span style="font-size:14px;font-weight:700;{{ $user->is_active ? 'color:#0F6E56;' : 'color:#94a3b8;' }}">
                                    {{ strtoupper(substr($user->name,0,2)) }}
                                </span>
                            </div>
                            <div>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <p style="font-size:14px;font-weight:600;color:{{ $user->is_active ? '#0f172a' : '#94a3b8' }};">{{ $user->name }}</p>
                                    @if($user->id === auth()->id())
                                    <span style="font-size:11px;background:#f0fdf6;color:#0F6E56;padding:2px 8px;border-radius:99px;font-weight:500;">Anda</span>
                                    @endif
                                </div>
                                <p style="font-size:12.5px;color:#94a3b8;margin-top:2px;">{{ $user->email }}</p>
                                <div style="display:flex;align-items:center;gap:6px;margin-top:5px;">
                                    <span style="font-size:11.5px;font-weight:500;padding:3px 10px;border-radius:99px;
                                        {{ $user->role==='owner' ? 'background:#f0fdf6;color:#15803d;' : ($user->role==='admin' ? 'background:#eff6ff;color:#1d4ed8;' : 'background:#f8fafc;color:#475569;') }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                    @if(!$user->is_active)
                                    <span style="font-size:11.5px;font-weight:500;padding:3px 10px;border-radius:99px;background:#fff1f2;color:#be123c;">Nonaktif</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($user->id !== auth()->id() && $user->role !== 'owner')
                        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                            {{-- Toggle aktif/nonaktif --}}
                            <form method="POST" action="{{ route('tenant.users.toggle', $user) }}">
                                @csrf @method('PUT')
                                <button type="submit"
                                    style="font-size:12px;font-weight:500;padding:6px 12px;border-radius:8px;border:1.5px solid;cursor:pointer;font-family:Inter,sans-serif;background:#fff;transition:all 0.15s;
                                    {{ $user->is_active ? 'border-color:#fecdd3;color:#be123c;' : 'border-color:#bbf7d2;color:#15803d;' }}">
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>

                            {{-- Atur Akses --}}
                            <button type="button" onclick='showAccessModal(@json($user->id), @json($user->name), @json($user->role), @json(array_values((array) $user->permissions)))'
                                style="font-size:12px;font-weight:500;padding:6px 12px;border-radius:8px;border:1.5px solid #bbf7d2;cursor:pointer;font-family:Inter,sans-serif;background:#fff;color:#15803d;transition:all 0.15s;"
                                onmouseover="this.style.background='#f0fdf6'" onmouseout="this.style.background='#fff'">
                                Atur Akses
                            </button>

                            {{-- Reset Password --}}
                            <button type="button" onclick="showResetModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                style="font-size:12px;font-weight:500;padding:6px 12px;border-radius:8px;border:1.5px solid #e2e8f0;cursor:pointer;font-family:Inter,sans-serif;background:#fff;color:#374151;transition:all 0.15s;"
                                onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                                Reset Password
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div style="padding:50px 20px;text-align:center;font-size:14px;color:#94a3b8;">
                    Belum ada anggota tim lain.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Modal Reset Password --}}
<div id="resetModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:50;padding:16px;">
    <div style="background:#fff;border-radius:20px;width:100%;max-width:380px;padding:28px;">
        <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:4px;">Reset Password</h3>
        <p style="font-size:13.5px;color:#64748b;margin-bottom:20px;" id="resetModalName"></p>

        <form method="POST" id="resetForm" style="display:flex;flex-direction:column;gap:14px;">
            @csrf @method('PUT')
            <div>
                <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:5px;">Password Baru *</label>
                <input type="password" name="password" required minlength="8"
                    style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;box-sizing:border-box;"
                    placeholder="min. 8 karakter"
                    onfocus="this.style.borderColor='#0F6E56';this.style.background='#fff';"
                    onblur="this.style.borderColor='#e2e8f0';this.style.background='#fafafa';">
            </div>
            <div>
                <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:5px;">Konfirmasi Password *</label>
                <input type="password" name="password_confirmation" required
                    style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;box-sizing:border-box;"
                    placeholder="ulangi password"
                    onfocus="this.style.borderColor='#0F6E56';this.style.background='#fff';"
                    onblur="this.style.borderColor='#e2e8f0';this.style.background='#fafafa';">
            </div>
            <div style="display:flex;gap:10px;margin-top:4px;">
                <button type="button" onclick="closeResetModal()"
                    style="flex:1;background:#fff;color:#374151;font-family:Inter,sans-serif;font-size:13.5px;font-weight:500;padding:11px;border-radius:10px;border:1.5px solid #e2e8f0;cursor:pointer;">
                    Batal
                </button>
                <button type="submit"
                    style="flex:1;background:#0F6E56;color:#fff;font-family:Inter,sans-serif;font-size:13.5px;font-weight:600;padding:11px;border-radius:10px;border:none;cursor:pointer;"
                    onmouseover="this.style.background='#085041'" onmouseout="this.style.background='#0F6E56'">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Atur Akses --}}
<div id="accessModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:50;padding:16px;">
    <div style="background:#fff;border-radius:20px;width:100%;max-width:440px;padding:28px;max-height:90vh;overflow-y:auto;">
        <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:4px;">Atur Akses</h3>
        <p style="font-size:13.5px;color:#64748b;margin-bottom:20px;" id="accessModalName"></p>

        <form method="POST" id="accessForm" style="display:flex;flex-direction:column;gap:16px;">
            @csrf @method('PUT')
            <div>
                <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:5px;">Role *</label>
                <select name="role" id="accessRole" style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;cursor:pointer;box-sizing:border-box;">
                    <option value="cashier">Kasir</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:12.5px;font-weight:500;color:#374151;margin-bottom:8px;">Akses Modul</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;border:1.5px solid #e2e8f0;border-radius:10px;padding:12px;background:#fafafa;">
                    @foreach(config('permissions.modules') as $key => $label)
                    <label style="display:flex;align-items:center;gap:7px;font-size:12.5px;color:#374151;cursor:pointer;">
                        <input type="checkbox" name="permissions[]" value="{{ $key }}" class="access-perm"
                            style="width:15px;height:15px;accent-color:#0F6E56;cursor:pointer;">
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:4px;">
                <button type="button" onclick="closeAccessModal()"
                    style="flex:1;background:#fff;color:#374151;font-family:Inter,sans-serif;font-size:13.5px;font-weight:500;padding:11px;border-radius:10px;border:1.5px solid #e2e8f0;cursor:pointer;">
                    Batal
                </button>
                <button type="submit"
                    style="flex:1;background:#0F6E56;color:#fff;font-family:Inter,sans-serif;font-size:13.5px;font-weight:600;padding:11px;border-radius:10px;border:none;cursor:pointer;"
                    onmouseover="this.style.background='#085041'" onmouseout="this.style.background='#0F6E56'">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showResetModal(userId, name) {
    document.getElementById('resetModalName').textContent = 'Ganti password untuk: ' + name;
    document.getElementById('resetForm').action = '{{ route('tenant.users.reset-password', ['user' => '__ID__']) }}'.replace('__ID__', userId);
    document.getElementById('resetModal').style.display = 'flex';
}
function closeResetModal() {
    document.getElementById('resetModal').style.display = 'none';
    document.getElementById('resetForm').reset();
}

function showAccessModal(userId, name, role, perms) {
    document.getElementById('accessModalName').textContent = 'Atur akses untuk: ' + name;
    document.getElementById('accessForm').action = '{{ route('tenant.users.permissions', ['user' => '__ID__']) }}'.replace('__ID__', userId);
    document.getElementById('accessRole').value = role === 'admin' ? 'admin' : 'cashier';
    document.querySelectorAll('.access-perm').forEach(function (cb) {
        cb.checked = Array.isArray(perms) && perms.includes(cb.value);
    });
    document.getElementById('accessModal').style.display = 'flex';
}
function closeAccessModal() {
    document.getElementById('accessModal').style.display = 'none';
}

function togglePass(id, btn) {
    var inp = document.getElementById(id);
    if (!inp) return;
    var show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    btn.style.color = show ? '#0F6E56' : '#94a3b8';
}
</script>
@endpush
