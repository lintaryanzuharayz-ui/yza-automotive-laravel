<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceApprovalRequestItem extends Model
{
    protected $table = 'service_approval_request_items';
    protected $primaryKey = 'id_service_approval_request_item';
    protected $guarded = ['id_service_approval_request_item'];
    protected function casts(): array { return ['quantity' => 'integer', 'unit_price_snapshot' => 'decimal:2', 'total_snapshot' => 'decimal:2']; }
    public function approval() { return $this->belongsTo(ServiceApprovalRequest::class, 'approval_request_id', 'id_service_approval_request'); }
    public function jenisServis() { return $this->belongsTo(JenisServis::class, 'id_jenis_servis', 'id_jenis_servis'); }
    public function sparepart() { return $this->belongsTo(Sparepart::class, 'id_sparepart', 'id_sparepart'); }
}
