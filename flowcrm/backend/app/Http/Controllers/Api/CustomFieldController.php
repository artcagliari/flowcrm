<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomFieldController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        $query = CustomField::where('company_id', $this->companyId($request))->orderBy('position');
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->query('entity_type'));
        }

        return $this->success($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'entity_type' => ['required', Rule::in(['client', 'lead', 'deal'])],
            'name' => ['required', 'string', 'max:120'],
            'field_type' => ['required', Rule::in(['text', 'number', 'date', 'select', 'boolean'])],
            'options' => ['nullable', 'array'],
            'is_required' => ['boolean'],
            'position' => ['nullable', 'integer'],
        ]);

        return $this->success(CustomField::create([...$data, 'company_id' => $this->companyId($request)]), 'Campo criado.', 201);
    }

    public function update(Request $request, CustomField $field)
    {
        $this->authorizeField($request, $field);
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'field_type' => ['sometimes', Rule::in(['text', 'number', 'date', 'select', 'boolean'])],
            'options' => ['nullable', 'array'],
            'is_required' => ['boolean'],
            'position' => ['nullable', 'integer'],
        ]);
        $field->update($data);

        return $this->success($field);
    }

    public function destroy(Request $request, CustomField $field)
    {
        $this->authorizeField($request, $field);
        $field->delete();

        return $this->success(null, 'Campo excluido.');
    }

    public function saveValues(Request $request)
    {
        $companyId = $this->companyId($request);
        $data = $request->validate([
            'entity_type' => ['required', Rule::in(['client', 'lead', 'deal'])],
            'entity_id' => ['required', 'integer'],
            'values' => ['required', 'array'],
            'values.*.custom_field_id' => ['required', 'integer'],
            'values.*.value' => ['nullable'],
        ]);

        foreach ($data['values'] as $item) {
            $field = CustomField::where('company_id', $companyId)->findOrFail($item['custom_field_id']);
            CustomFieldValue::updateOrCreate(
                ['custom_field_id' => $field->id, 'entity_type' => $data['entity_type'], 'entity_id' => $data['entity_id']],
                ['value' => is_array($item['value']) ? json_encode($item['value']) : (string) ($item['value'] ?? '')]
            );
        }

        return $this->success(null, 'Valores salvos.');
    }

    public function getValues(Request $request)
    {
        $data = $request->validate([
            'entity_type' => ['required', Rule::in(['client', 'lead', 'deal'])],
            'entity_id' => ['required', 'integer'],
        ]);

        $values = CustomFieldValue::where('entity_type', $data['entity_type'])
            ->where('entity_id', $data['entity_id'])
            ->whereHas('field', fn ($q) => $q->where('company_id', $this->companyId($request)))
            ->with('field:id,name,field_type,options')
            ->get();

        return $this->success($values);
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }

    private function authorizeField(Request $request, CustomField $field): void
    {
        abort_if($field->company_id !== $this->companyId($request), 403);
    }
}
