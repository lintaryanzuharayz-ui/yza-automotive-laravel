<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceFinding extends Model
{
    protected $table = 'service_findings';
    protected $primaryKey = 'id_service_finding';
    protected $guarded = ['id_service_finding'];
    public function servis() { return $this->belongsTo(Servis::class, 'id_servis', 'id_servis'); }
    public function approvalRequests() { return $this->hasMany(ServiceApprovalRequest::class, 'id_service_finding', 'id_service_finding'); }
}
