<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\PaymentInvoice;
use App\Models\PricingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BillingController extends Controller
{
    public function index()
    {
        $tenant = app('currentTenant');

        $invoices = PaymentInvoice::where('tenant_id', $tenant->id)
            ->latest()->get();

        // Invoice yang masih perlu dibayar / dikonfirmasi
        $activeInvoice = $invoices->whereIn('status', ['unpaid', 'waiting_confirmation'])->first();

        $plans = PricingPlan::where('is_active', true)->orderBy('sort_order')->get();

        $bank = [
            'name'    => AppSetting::getValue('bank_name'),
            'account' => AppSetting::getValue('bank_account_no'),
            'holder'  => AppSetting::getValue('bank_account_name'),
        ];

        return view('tenant.billing.index', compact('tenant', 'invoices', 'activeInvoice', 'plans', 'bank'));
    }

    /**
     * User memilih paket → sistem generate invoice + kode unik.
     */
    public function createInvoice(Request $request)
    {
        $request->validate(['plan_id' => 'required|exists:pricing_plans,id']);
        $tenant = app('currentTenant');

        // Cegah dobel invoice yang belum selesai.
        $pending = PaymentInvoice::where('tenant_id', $tenant->id)
            ->whereIn('status', ['unpaid', 'waiting_confirmation'])->exists();
        if ($pending) {
            return back()->withErrors(['Masih ada tagihan yang belum diselesaikan.']);
        }

        $plan = PricingPlan::findOrFail($request->plan_id);
        PaymentInvoice::createForPlan($tenant, $plan);

        return back()->with('success', 'Tagihan dibuat. Silakan transfer sesuai nominal yang tertera.');
    }

    /**
     * User upload bukti transfer → status jadi menunggu konfirmasi.
     */
    public function uploadProof(Request $request, PaymentInvoice $invoice)
    {
        abort_if((int) $invoice->tenant_id !== (int) app('currentTenant')->id, 403);
        abort_if(!in_array($invoice->status, ['unpaid', 'waiting_confirmation']), 422, 'Tagihan ini sudah diproses.');

        $request->validate([
            'proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'proof.required' => 'Bukti transfer wajib diunggah.',
            'proof.image'    => 'File harus berupa gambar.',
            'proof.max'      => 'Ukuran gambar maksimal 4MB.',
        ]);

        // Hapus bukti lama jika ada (re-upload)
        if ($invoice->proof_path) {
            Storage::disk('public')->delete($invoice->proof_path);
        }

        $invoice->update([
            'proof_path' => $request->file('proof')->store('payment-proofs', 'public'),
            'status'     => 'waiting_confirmation',
            'paid_at'    => now(),
        ]);

        return back()->with('success', 'Bukti transfer terkirim. Menunggu verifikasi administrator.');
    }
}
