<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id','expense_date','category','description','amount','created_by'];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public static function categories(): array
    {
        return ['Listrik','Air','Wifi','Sewa','Gaji','Biaya Langganan','Transport','Lainnya'];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
