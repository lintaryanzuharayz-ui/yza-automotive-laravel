<?php
namespace App\Services;

use App\Models\{BookingServis, JenisServis, Petugas, ServiceStaffProfile, TechnicianTimeOff, TechnicianWorkingHour};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ServicePersonnelAvailabilityService
{
    public function technicians(string $date, string $time, int $serviceTypeId, ?int $excludeServisId = null): array
    {
        $dateTime = Carbon::parse($date.' '.$time, config('app.timezone')); $type = JenisServis::findOrFail($serviceTypeId); $end = $dateTime->copy()->addMinutes((int)($type->duration_minutes ?: 60)); $workshop = app(ServiceSchedulingService::class)->availability($date, $serviceTypeId); $workshopSlot = collect($workshop['slots'])->firstWhere('time', $dateTime->format('H:i')); $workshopAvailable = (bool)($workshopSlot['available'] ?? false);
        return ServiceStaffProfile::with(['petugas','specializations'])->where('can_be_technician',true)->where('is_bookable',true)->whereHas('petugas',fn($q)=>$q->where('status','aktif'))->get()->map(function($profile)use($dateTime,$end,$excludeServisId,$workshopAvailable){$p=$profile->petugas;$hour=TechnicianWorkingHour::where('id_petugas',$p->id_petugas)->where('day_of_week',$dateTime->isoWeekday())->first();$off=TechnicianTimeOff::where('id_petugas',$p->id_petugas)->whereDate('date',$dateTime->toDateString())->exists();$working=(bool)($hour?->is_working&&$hour->open_time&&$hour->close_time&&$dateTime->gt(now(config('app.timezone')))&&$dateTime->format('H:i:s') >= $hour->open_time&&$end->format('H:i:s') <= $hour->close_time&&$workshopAvailable);$conflict=$this->hasConflict($p->id_petugas,$dateTime,$end,$excludeServisId);$status=$off?'time_off':(!$working?'unavailable':($conflict?'assigned':'available'));return ['id'=>$p->id_petugas,'display_name'=>$p->nama_lengkap,'photo'=>$p->foto,'title'=>$p->jabatan,'specialization'=>$profile->specializations->pluck('name')->values()->all(),'experience_years'=>(int)$profile->experience_years,'availability_status'=>$status,'available'=>$status==='available'];})->values()->all();
    }

    public function eligible(int $petugasId, string $date, string $time, int $serviceTypeId, ?int $excludeServisId = null): Petugas
    {
        $row=collect($this->technicians($date,$time,$serviceTypeId,$excludeServisId))->firstWhere('id',$petugasId); if(!$row||!$row['available']) throw new RuntimeException('Teknisi tidak aktif, tidak tersedia, sedang time off, atau memiliki pekerjaan yang bertabrakan.'); return Petugas::findOrFail($petugasId);
    }

    public function advisors(): array { return ServiceStaffProfile::with('petugas')->where('can_be_service_advisor',true)->whereHas('petugas',fn($q)=>$q->where('status','aktif'))->get()->map(fn($p)=>['id'=>$p->petugas->id_petugas,'display_name'=>$p->petugas->nama_lengkap,'title'=>$p->petugas->jabatan,'photo'=>$p->petugas->foto])->values()->all(); }

    private function hasConflict(int $petugasId, Carbon $start, Carbon $end, ?int $exclude): bool
    {
        return BookingServis::with('jenisServis')->whereHas('servis',function($q)use($petugasId,$exclude){$q->where('id_petugas',$petugasId)->whereIn('status',['menunggu','diterima','pemeriksaan','dikerjakan']);if($exclude)$q->where('id_servis','!=',$exclude);})->whereDate('tanggal',$start->toDateString())->whereIn('status',['menunggu','diterima','diproses'])->get()->contains(fn($b)=>$this->overlap($start,$end,Carbon::parse($b->tanggal->toDateString().' '.substr((string)$b->jam,0,8),config('app.timezone')),Carbon::parse($b->tanggal->toDateString().' '.substr((string)$b->jam,0,8),config('app.timezone'))->addMinutes((int)($b->jenisServis?->duration_minutes?:60))));
    }
    private function overlap(Carbon $a,Carbon $b,Carbon $c,Carbon $d):bool{return $a->lt($d)&&$b->gt($c);}
}
