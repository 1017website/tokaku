<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Shift extends Model {
    use BelongsToTenant;
    protected $fillable = ['tenant_id','user_id','opening_cash','closing_cash','expected_cash','cash_difference','total_transactions','total_revenue','opened_at','closed_at','notes'];
    protected $casts = ['opening_cash'=>'integer','closing_cash'=>'integer','expected_cash'=>'integer','cash_difference'=>'integer','total_revenue'=>'integer','opened_at'=>'datetime','closed_at'=>'datetime'];

    public function user()         { return $this->belongsTo(User::class); }
    public function transactions() { return $this->hasMany(Transaction::class); }
    public function isOpen(): bool { return is_null($this->closed_at); }
}
