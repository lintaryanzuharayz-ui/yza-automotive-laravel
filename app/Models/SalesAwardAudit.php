<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SalesAwardAudit extends Model
{
    protected $table = 'sales_award_audits';
    protected $primaryKey = 'id_sales_award_audit';
    protected $guarded = ['id_sales_award_audit'];
    protected function casts(): array { return ['payload' => 'array']; }
    public function award() { return $this->belongsTo(SalesAward::class, 'id_sales_award', 'id_sales_award'); }
    public function actor() { return $this->belongsTo(Petugas::class, 'actor_petugas_id', 'id_petugas'); }
}
