<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Services\{MemberIdentityService, ServiceReminderService};
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceReminderController extends Controller
{
    public function index(Request $request, MemberIdentityService $identity, ServiceReminderService $reminders): View
    {
        $customer = $request->user()->pelanggan; abort_unless($customer, 403); $identity->assertActive($customer, $request->user());
        return view('pelanggan.reminder.index', ['customer' => $customer, 'reminders' => $reminders->forCustomer($customer->id_pelanggan)]);
    }
}
