<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MessageTemplateController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        $query = MessageTemplate::where('company_id', $this->companyId($request));
        if ($request->filled('channel')) {
            $query->where('channel', $request->query('channel'));
        }

        return $this->success($query->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'channel' => ['required', Rule::in(['whatsapp', 'email'])],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        return $this->success(MessageTemplate::create([...$data, 'company_id' => $this->companyId($request)]), 'Template criado.', 201);
    }

    public function update(Request $request, MessageTemplate $template)
    {
        $this->authorizeTemplate($request, $template);
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:160'],
            'channel' => ['sometimes', Rule::in(['whatsapp', 'email'])],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['sometimes', 'string'],
        ]);
        $template->update($data);

        return $this->success($template);
    }

    public function destroy(Request $request, MessageTemplate $template)
    {
        $this->authorizeTemplate($request, $template);
        $template->delete();

        return $this->success(null, 'Template excluido.');
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }

    private function authorizeTemplate(Request $request, MessageTemplate $template): void
    {
        abort_if($template->company_id !== $this->companyId($request), 403);
    }
}
