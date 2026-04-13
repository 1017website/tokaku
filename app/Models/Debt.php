<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Debt extends Model {
    use BelongsToTenant;
    protected $fillable = ['tenant_id','transaction_id','customer_id','customer_name','customer_phone','amount','paid_amount','status','due_date','notes'];
    protected $casts = ['amount'=>'integer','paid_amount'=>'integer','due_date'=>'date'];

    public function transaction() { return $this->belongsTo(Transaction::class); }
    public function customer()    { return $this->belongsTo(Customer::class); }
    public function payments()    { return $this->hasMany(DebtPayment::class); }
    public function getRemainingAttribute(): int { return $this->amount - $this->paid_amount; }
    public function isOverdue(): bool { return $this->due_date && now()->gt($this->due_date) && $this->status !== 'paid'; }
}
