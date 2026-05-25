<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StorePaymentRequest;
use App\Http\Requests\Api\UpdatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends CrudController
{
    protected string $model = Payment::class;
    protected string $resource = PaymentResource::class;

    public function store(StorePaymentRequest $request) { return $this->createRecord($request, $request->validated()); }
    public function show(Request $request, Payment $payment) { return $this->showRecord($request, $payment); }
    public function update(UpdatePaymentRequest $request, Payment $payment) { return $this->updateRecord($request, $payment, $request->validated()); }
    public function destroy(Request $request, Payment $payment) { return $this->destroyRecord($request, $payment); }
    public function paid(Request $request, Payment $payment) { return $this->updateRecord($request, $payment, ['status' => 'pago', 'paid_at' => now()->toDateString()]); }
}
