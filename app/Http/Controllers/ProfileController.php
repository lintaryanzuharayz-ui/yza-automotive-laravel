<?php
namespace App\Http\Controllers;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdatePelangganRequest;
use App\Http\Requests\UpdatePetugasProfileRequest;
use App\Services\MasterAuditService;
use App\Services\PasswordCompatibilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
class ProfileController extends Controller
{
 public function pelanggan(): View { return view('pelanggan.profile', ['item'=>auth()->user()->pelanggan]); }
 public function updatePelanggan(UpdatePelangganRequest $request, MasterAuditService $audit): RedirectResponse { $p=auth()->user()->pelanggan; abort_unless($p,404); $data=$request->validated(); $email=$data['email']??null; unset($data['email']); $p->update($data); if($email!==null) auth()->user()->update(['email'=>$email]); $audit->record('update','pelanggan',$p->id_pelanggan,'Pelanggan memperbarui profil sendiri'); return back()->with('success','Profil berhasil diperbarui.'); }
 public function petugas(): View { return view('petugas.profile', ['item'=>auth()->user()->petugas]); }
 public function updatePetugas(UpdatePetugasProfileRequest $request, MasterAuditService $audit): RedirectResponse { $p=auth()->user()->petugas; abort_unless($p,404); $p->update($request->validated()); $audit->record('update','petugas',$p->id_petugas,'Petugas memperbarui profil sendiri'); return back()->with('success','Profil berhasil diperbarui.'); }
 public function password(): View { return view('auth.password'); }
 public function updatePassword(ChangePasswordRequest $request, PasswordCompatibilityService $passwords): RedirectResponse { $user=auth()->user(); if(!$passwords->verify($user,$request->validated()['password_lama'])) return back()->withErrors(['password_lama'=>'Password saat ini tidak sesuai.']); $user->forceFill(['password'=>Hash::make($request->validated()['password_baru'])])->save(); return back()->with('success','Password berhasil diperbarui.'); }
}
