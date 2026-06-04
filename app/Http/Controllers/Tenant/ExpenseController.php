<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();
        $categories = Expense::categories();

        $query = Expense::with('creator')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->when($request->category, fn($q) => $q->where('category', $request->category));

        $expenses = (clone $query)->orderByDesc('expense_date')->orderByDesc('id')->paginate(20)->withQueryString();
        $totalExpenses = (clone $query)->sum('amount');
        $byCategory = (clone $query)->selectRaw('category, SUM(amount) as total')->groupBy('category')->orderByDesc('total')->get();

        return view('tenant.expenses.index', compact('expenses','totalExpenses','byCategory','categories','startDate','endDate'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);
        $validated['created_by'] = auth()->id();
        Expense::create($validated);
        return back()->with('success', 'Pengeluaran berhasil ditambahkan.');
    }

    public function update(Request $request, Expense $expense)
    {
        abort_if($expense->tenant_id != app('currentTenant')->id, 403);
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);
        $expense->update($validated);
        return back()->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Expense $expense)
    {
        abort_if($expense->tenant_id != app('currentTenant')->id, 403);
        $expense->delete();
        return back()->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
