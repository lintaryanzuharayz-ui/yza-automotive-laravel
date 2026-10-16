<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FinancialAdjustmentEvent extends Model
{
    protected $table = 'financial_adjustment_events';
    protected $primaryKey = 'id_financial_adjustment_event';
    protected $guarded = ['id_financial_adjustment_event'];
    protected function casts(): array { return ['metadata' => 'array']; }
    public function adjustment() { return $this->belongsTo(FinancialAdjustment::class, 'id_financial_adjustment', 'id_financial_adjustment'); }
    public function actor() { return $this->belongsTo(Petugas::class, 'actor_petugas_id', 'id_petugas'); }
}
