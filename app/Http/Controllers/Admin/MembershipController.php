<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\View\View;
class MembershipController extends Controller { public function index():View{return view('admin.memberships.index',['items'=>Membership::with('customer.user')->latest('id_membership')->paginate(20),'stats'=>['total'=>Membership::count(),'active'=>Membership::where('status','active')->count(),'pending'=>Membership::where('status','pending_payment')->count(),'cancelled'=>Membership::where('status','cancelled')->count(),'revenue'=>Membership::where('status','active')->sum('activation_fee'),'month'=>Membership::where('status','active')->whereBetween('activated_at',[now()->startOfMonth(),now()->endOfMonth()])->count()]]);} }
