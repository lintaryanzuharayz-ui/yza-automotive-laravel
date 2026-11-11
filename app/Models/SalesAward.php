<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SalesAward extends Model
{
    protected $table = 'sales_awards';
    protected $primaryKey = 'id_sales_award';
    protected $guarded = ['id_sales_award'];
    protected function casts(): array { return ['method_snapshot' => 'array', 'finalized_at' => 'datetime']; }
    public function results() { return $this->hasMany(SalesAwardResult::class, 'id_sales_award', 'id_sales_award')->orderBy('ranking'); }
    public function audits() { return $this->hasMany(SalesAwardAudit::class, 'id_sales_award', 'id_sales_award')->latest(); }
    public function finalizer() { return $this->belongsTo(Petugas::class, 'finalized_by', 'id_petugas'); }
}
