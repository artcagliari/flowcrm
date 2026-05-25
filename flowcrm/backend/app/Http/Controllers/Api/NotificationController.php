<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        return $this->success(Notification::where('company_id', $request->attributes->get('current_company')->id)->latest()->paginate(15));
    }

    public function read(Request $request, Notification $notification)
    {
        abort_if($notification->company_id !== $request->attributes->get('current_company')->id, 403);
        $notification->update(['read_at' => now()]);
        return $this->success($notification->fresh(), 'Notificação marcada como lida.');
    }
}
