<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreAppointmentRequest;
use App\Http\Requests\Api\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;

class AppointmentController extends CrudController
{
    protected string $model = Appointment::class;
    protected string $resource = AppointmentResource::class;
    protected array $with = ['user', 'owner', 'client', 'lead'];

    public function __construct(private GoogleCalendarService $googleCalendar) {}

    public function store(StoreAppointmentRequest $request)
    {
        $data = $request->validated();
        $this->abortUnlessCanManageModule($request, 'appointments');
        $data['company_id'] = $this->companyId($request);
        $data['user_id'] = $data['user_id'] ?? $request->user()->id;
        $data['owner_id'] = $data['owner_id'] ?? $data['user_id'];

        $appointment = Appointment::create($data);
        $this->activity($request, 'criado', 'Registro criado.', $appointment);
        $this->googleCalendar->pushAppointment($appointment);

        return $this->success(new AppointmentResource($appointment->load($this->with)), 'Registro criado com sucesso.', 201);
    }

    public function show(Request $request, Appointment $appointment) { return $this->showRecord($request, $appointment); }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $response = $this->updateRecord($request, $appointment, $request->validated());
        $this->googleCalendar->pushAppointment($appointment->fresh());

        return $response;
    }

    public function destroy(Request $request, Appointment $appointment) { return $this->destroyRecord($request, $appointment); }
    public function cancel(Request $request, Appointment $appointment) { return $this->updateRecord($request, $appointment, ['status' => 'cancelado']); }
    public function complete(Request $request, Appointment $appointment) { return $this->updateRecord($request, $appointment, ['status' => 'concluido']); }
}
