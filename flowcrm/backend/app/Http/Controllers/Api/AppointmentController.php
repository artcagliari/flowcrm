<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreAppointmentRequest;
use App\Http\Requests\Api\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends CrudController
{
    protected string $model = Appointment::class;
    protected string $resource = AppointmentResource::class;

    public function store(StoreAppointmentRequest $request) { return $this->createRecord($request, $request->validated()); }
    public function show(Request $request, Appointment $appointment) { return $this->showRecord($request, $appointment); }
    public function update(UpdateAppointmentRequest $request, Appointment $appointment) { return $this->updateRecord($request, $appointment, $request->validated()); }
    public function destroy(Request $request, Appointment $appointment) { return $this->destroyRecord($request, $appointment); }
    public function cancel(Request $request, Appointment $appointment) { return $this->updateRecord($request, $appointment, ['status' => 'cancelado']); }
    public function complete(Request $request, Appointment $appointment) { return $this->updateRecord($request, $appointment, ['status' => 'concluído']); }
}
