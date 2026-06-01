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
        return $this->success(Notification::where('company_id', $request->attributes->get('current_company')->id)
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $request->user()->id))
            ->latest()
            ->paginate((int) $request->query('per_page', 15)));
    }

    public function read(Request $request, Notification $notification)
    {
        abort_if((int) $notification->company_id !== (int) $request->attributes->get('current_company')->id, 403);
        abort_if($notification->user_id && (int) $notification->user_id !== (int) $request->user()->id, 403);
        $notification->update(['read_at' => now()]);

        return $this->success($notification->fresh(), 'Notificacao marcada como lida.');
    }

    public function readAll(Request $request)
    {
        Notification::where('company_id', $request->attributes->get('current_company')->id)
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $request->user()->id))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this->success(null, 'Notificacoes marcadas como lidas.');
    }
}
