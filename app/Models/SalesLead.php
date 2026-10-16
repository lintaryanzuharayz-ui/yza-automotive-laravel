<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesLead extends Model
{
    protected $table = 'sales_leads';
    protected $primaryKey = 'id_sales_lead';
    protected $guarded = ['id_sales_lead'];

    protected function casts(): array
    {
        return ['follow_up_at' => 'datetime'];
    }

    public function sales() { return $this->belongsTo(Petugas::class, 'id_sales', 'id_petugas'); }
    public function pelanggan() { return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan'); }
    public function mobil() { return $this->belongsTo(Mobil::class, 'id_mobil', 'id_mobil'); }
    public function activities() { return $this->hasMany(SalesLeadActivity::class, 'id_sales_lead', 'id_sales_lead')->latest(); }
}
