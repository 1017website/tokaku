@extends('superadmin.layout')
@section('title','Semua User')
@section('page-title','Semua User')
@section('page-subtitle','Owner sebagai akun utama — klik untuk lihat sub-akun (admin/kasir)')

@section('content')
<div style="background:#fff;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <p style="font-size:14px;font-weight:600;color:#0f172a;">Total: {{ $totalUser }} user &middot; {{ $groups->count() }} toko</p>
        <form method="GET">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama / email..."
                style="border:1.5px solid #e2e8f0;border-radius:10px;padding:8px 14px;font-size:13.5px;font-family:Inter,sans-serif;outline:none;background:#fafafa;width:220px;"
                onfocus="this.style.borderColor='#0F6E56'" onblur="this.style.borderColor='#e2e8f0'">
        </form>
    </div>

    <div class="table-responsive overflow-x-auto">
        <table style="width:100%;border-collapse:collapse;min-width:560px;">
            <thead><tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;">Owner / Toko</th>
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;">Tenant</th>
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;">Role</th>
                <th style="text-align:center;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;">Status</th>
                <th style="text-align:left;font-size:11px;font-weight:600;color:#64748b;padding:10px 18px;text-transform:uppercase;">Bergabung</th>
            </tr></thead>
            <tbody>

            {{-- Superadmin (tanpa tenant) di paling atas --}}
            @foreach($superadmins as $sa)
            <tr style="border-bottom:1px solid #f8fafc;">
                <td style="padding:13px 18px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="width:16px;display:inline-block;"></span>
                        <div>
                            <p style="font-size:13.5px;font-weight:600;color:#0f172a;">{{ $sa->name }}</p>
                            <p style="font-size:12px;color:#94a3b8;">{{ $sa->email }}</p>
                        </div>
                    </div>
                </td>
                <td style="padding:13px 18px;"><span style="font-size:13px;color:#94a3b8;">—</span></td>
                <td style="padding:13px 18px;"><span style="font-size:12px;font-weight:500;padding:3px 10px;border-radius:99px;background:#f5f3ff;color:#6d28d9;">Superadmin</span></td>
                <td style="padding:13px 18px;text-align:center;"><span style="font-size:12px;font-weight:500;background:#f0fdf4;color:#15803d;padding:3px 10px;border-radius:99px;">Aktif</span></td>
                <td style="padding:13px 18px;font-size:13px;color:#64748b;">{{ $sa->created_at->format('d M Y') }}</td>
            </tr>
            @endforeach

            {{-- Grup per tenant: owner = baris utama, sub-akun expandable --}}
            @forelse($groups as $idx => $g)
            @php $hasSubs = $g['subs']->count() > 0; $expand = !empty($g['matched_sub']); @endphp

            <tr style="border-bottom:1px solid #f8fafc;transition:background 0.1s;{{ $hasSubs ? 'cursor:pointer;' : '' }}"
                @if($hasSubs) onclick="toggleSubs({{ $idx }})" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='#fff'" @endif>
                <td style="padding:13px 18px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        @if($hasSubs)
                        <svg id="caret-{{ $idx }}" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.5" style="flex-shrink:0;transition:transform .15s;{{ $expand ? 'transform:rotate(90deg);' : '' }}"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        @else
                        <span style="width:16px;display:inline-block;flex-shrink:0;"></span>
                        @endif
                        <div>
                            <p style="font-size:13.5px;font-weight:600;color:#0f172a;">{{ $g['owner']->name }}</p>
                            <p style="font-size:12px;color:#94a3b8;">{{ $g['owner']->email }}</p>
                        </div>
                        @if($hasSubs)
                        <span style="font-size:11px;font-weight:600;background:#eef2ff;color:#4338ca;padding:2px 9px;border-radius:99px;margin-left:4px;">+{{ $g['subs']->count() }} akun</span>
                        @endif
                    </div>
                </td>
                <td style="padding:13px 18px;">
                    @if($g['tenant'])
                    <a href="{{ route('superadmin.tenants.detail', $g['tenant']) }}" onclick="event.stopPropagation();" style="font-size:13px;color:#0F6E56;font-weight:500;text-decoration:none;">{{ $g['tenant']->name }}</a>
                    @else
                    <span style="font-size:13px;color:#94a3b8;">—</span>
                    @endif
                </td>
                <td style="padding:13px 18px;">
                    <span style="font-size:12px;font-weight:500;padding:3px 10px;border-radius:99px;{{ $g['owner']->role==='owner'?'background:#f0fdf4;color:#15803d;':'background:#f8fafc;color:#475569;' }}">{{ ucfirst($g['owner']->role) }}</span>
                </td>
                <td style="padding:13px 18px;text-align:center;">
                    @if($g['tenant'])
                        @php $sb = $g['tenant']->statusBadge(); @endphp
                        <span style="font-size:12px;font-weight:500;padding:3px 10px;border-radius:99px;background:{{ $sb[1] }};color:{{ $sb[2] }};">{{ $sb[0] }}</span>
                        @unless($g['owner']->is_active)
                        <span style="display:inline-block;margin-top:4px;font-size:11px;font-weight:500;background:#fff1f2;color:#be123c;padding:2px 9px;border-radius:99px;">Akun nonaktif</span>
                        @endunless
                    @else
                        @if($g['owner']->is_active)
                        <span style="font-size:12px;font-weight:500;background:#f0fdf4;color:#15803d;padding:3px 10px;border-radius:99px;">Aktif</span>
                        @else
                        <span style="font-size:12px;font-weight:500;background:#fff1f2;color:#be123c;padding:3px 10px;border-radius:99px;">Nonaktif</span>
                        @endif
                    @endif
                </td>
                <td style="padding:13px 18px;font-size:13px;color:#64748b;">{{ $g['owner']->created_at->format('d M Y') }}</td>
            </tr>

            {{-- Sub-akun --}}
            @if($hasSubs)
                @foreach($g['subs'] as $sub)
                <tr class="sub-{{ $idx }}" style="border-bottom:1px solid #f8fafc;background:#fcfcfd;{{ $expand ? '' : 'display:none;' }}">
                    <td style="padding:11px 18px;">
                        <div style="display:flex;align-items:center;gap:10px;padding-left:30px;">
                            <span style="width:8px;height:8px;border-left:1.5px solid #cbd5e1;border-bottom:1.5px solid #cbd5e1;display:inline-block;flex-shrink:0;margin-top:-6px;"></span>
                            <div>
                                <p style="font-size:13px;font-weight:500;color:#334155;">{{ $sub->name }}</p>
                                <p style="font-size:12px;color:#94a3b8;">{{ $sub->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td style="padding:11px 18px;"><span style="font-size:12.5px;color:#94a3b8;">{{ $g['tenant']->name ?? '—' }}</span></td>
                    <td style="padding:11px 18px;">
                        <span style="font-size:12px;font-weight:500;padding:3px 10px;border-radius:99px;{{ $sub->role==='admin'?'background:#eff6ff;color:#1d4ed8;':'background:#f8fafc;color:#475569;' }}">{{ ucfirst($sub->role) }}</span>
                    </td>
                    <td style="padding:11px 18px;text-align:center;">
                        @if($sub->is_active)
                        <span style="font-size:12px;font-weight:500;background:#f0fdf4;color:#15803d;padding:3px 10px;border-radius:99px;">Aktif</span>
                        @else
                        <span style="font-size:12px;font-weight:500;background:#fff1f2;color:#be123c;padding:3px 10px;border-radius:99px;">Nonaktif</span>
                        @endif
                    </td>
                    <td style="padding:11px 18px;font-size:13px;color:#64748b;">{{ $sub->created_at->format('d M Y') }}</td>
                </tr>
                @endforeach
            @endif

            @empty
            <tr><td colspan="5" style="padding:50px;text-align:center;color:#94a3b8;font-size:13.5px;">Tidak ada user.</td></tr>
            @endforelse

            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSubs(idx){
    var rows = document.querySelectorAll('.sub-' + idx);
    if(!rows.length) return;
    var caret = document.getElementById('caret-' + idx);
    var willShow = getComputedStyle(rows[0]).display === 'none';
    rows.forEach(function(r){ r.style.display = willShow ? 'table-row' : 'none'; });
    if(caret) caret.style.transform = willShow ? 'rotate(90deg)' : 'rotate(0deg)';
}
</script>
@endpush
