<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FinancialAdjustment extends Model
{
    protected $table = 'financial_adjustments';
    protected $primaryKey = 'id_financial_adjustment';
    protected $guarded = ['id_financial_adjustment'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'approved_at' => 'datetime', 'confirmed_at' => 'datetime', 'metadata' => 'array']; }
    public function payment() { return $this->belongsTo(Pembayaran::class, 'id_pembayaran', 'id_pembayaran'); }
    public function transaction() { return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi'); }
    public function requester() { return $this->belongsTo(Petugas::class, 'requested_by', 'id_petugas'); }
    public function approver() { return $this->belongsTo(Petugas::class, 'approved_by', 'id_petugas'); }
    public function confirmer() { return $this->belongsTo(Petugas::class, 'confirmed_by', 'id_petugas'); }
    public function events() { return $this->hasMany(FinancialAdjustmentEvent::class, 'id_financial_adjustment', 'id_financial_adjustment')->latest(); }
}
