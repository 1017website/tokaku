<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Customer extends Model {
    use BelongsToTenant;
    protected $fillable = ['tenant_id','name','phone','email','address','birthday','total_transactions','total_spent','points','notes','is_active'];
    protected $casts = ['birthday'=>'date','is_active'=>'boolean','total_spent'=>'integer','points'=>'integer'];

    public function transactions() { return $this->hasMany(Transaction::class); }
    public function debts() { return $this->hasMany(Debt::class); }
    public function totalDebt() { return $this->debts()->whereIn('status',['unpaid','partial'])->sum(\DB::raw('amount - paid_amount')); }
}
