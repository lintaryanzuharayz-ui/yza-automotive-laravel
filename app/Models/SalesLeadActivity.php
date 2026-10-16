<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesLeadActivity extends Model
{
    protected $table = 'sales_lead_activities';
    protected $primaryKey = 'id_sales_lead_activity';
    protected $guarded = ['id_sales_lead_activity'];

    protected function casts(): array { return ['follow_up_at' => 'datetime']; }
    public function lead() { return $this->belongsTo(SalesLead::class, 'id_sales_lead', 'id_sales_lead'); }
    public function petugas() { return $this->belongsTo(Petugas::class, 'id_petugas', 'id_petugas'); }
}
