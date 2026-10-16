<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SalesAwardResult extends Model
{
    protected $table = 'sales_award_results';
    protected $primaryKey = 'id_sales_award_result';
    protected $guarded = ['id_sales_award_result'];
    protected function casts(): array { return ['achievement_percentage' => 'decimal:2', 'final_amount' => 'decimal:2']; }
    public function award() { return $this->belongsTo(SalesAward::class, 'id_sales_award', 'id_sales_award'); }
    public function sales() { return $this->belongsTo(Petugas::class, 'id_sales', 'id_petugas'); }
}
