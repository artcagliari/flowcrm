<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        return $this->success(Tag::where('company_id', $this->companyId($request))->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'color' => ['nullable', 'string', 'max:16'],
        ]);

        return $this->success(Tag::create([...$data, 'company_id' => $this->companyId($request)]), 'Tag criada.', 201);
    }

    public function update(Request $request, Tag $tag)
    {
        $this->authorizeTag($request, $tag);
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:80'],
            'color' => ['nullable', 'string', 'max:16'],
        ]);
        $tag->update($data);

        return $this->success($tag);
    }

    public function destroy(Request $request, Tag $tag)
    {
        $this->authorizeTag($request, $tag);
        $tag->delete();

        return $this->success(null, 'Tag excluida.');
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }

    private function authorizeTag(Request $request, Tag $tag): void
    {
        abort_if($tag->company_id !== $this->companyId($request), 403);
    }
}
