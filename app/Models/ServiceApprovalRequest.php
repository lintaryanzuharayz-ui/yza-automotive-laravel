<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceApprovalRequest extends Model
{
    protected $table = 'service_approval_requests';
    protected $primaryKey = 'id_service_approval_request';
    protected $guarded = ['id_service_approval_request'];
    protected function casts(): array { return ['requested_at' => 'datetime', 'responded_at' => 'datetime']; }
    public function servis() { return $this->belongsTo(Servis::class, 'id_servis', 'id_servis'); }
    public function items() { return $this->hasMany(ServiceApprovalRequestItem::class, 'approval_request_id', 'id_service_approval_request'); }
    public function finding() { return $this->belongsTo(ServiceFinding::class, 'id_service_finding', 'id_service_finding'); }
}
