<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesAssignmentAudit extends Model
{
    protected $table = 'sales_assignment_audits';
    protected $primaryKey = 'id_sales_assignment_audit';
    protected $guarded = ['id_sales_assignment_audit'];

    public function previousSales() { return $this->belongsTo(Petugas::class, 'previous_sales_id', 'id_petugas'); }
    public function newSales() { return $this->belongsTo(Petugas::class, 'new_sales_id', 'id_petugas'); }
    public function actor() { return $this->belongsTo(Petugas::class, 'actor_petugas_id', 'id_petugas'); }
}
