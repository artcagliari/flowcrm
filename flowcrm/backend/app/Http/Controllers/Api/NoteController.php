<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Api\Concerns\AuthorizesCompanyAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreNoteRequest;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    use RespondsWithJson, AuthorizesCompanyAccess;

    public function index(Request $request)
    {
        return $this->success(Note::where('company_id', $request->attributes->get('current_company')->id)->latest()->paginate(15));
    }

    public function store(StoreNoteRequest $request)
    {
        $this->abortUnlessCanManageModule($request, 'notes');
        $note = Note::create([...$request->validated(), 'company_id' => $request->attributes->get('current_company')->id, 'user_id' => $request->user()->id]);
        return $this->success($note, 'Observação criada com sucesso.', 201);
    }

    public function update(StoreNoteRequest $request, Note $note)
    {
        abort_if($note->company_id !== $request->attributes->get('current_company')->id, 403);
        $this->abortUnlessCanManageModule($request, 'notes');
        $note->update($request->validated());
        return $this->success($note->fresh());
    }

    public function destroy(Request $request, Note $note)
    {
        abort_if($note->company_id !== $request->attributes->get('current_company')->id, 403);
        $this->abortUnlessCanManageModule($request, 'notes');
        $note->delete();
        return $this->success(null, 'Observação excluída com sucesso.');
    }
}
