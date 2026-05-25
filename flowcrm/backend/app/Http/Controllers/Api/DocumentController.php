<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDocumentRequest;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        return $this->success(Document::where('company_id', $request->attributes->get('current_company')->id)->latest()->paginate(15));
    }

    public function store(StoreDocumentRequest $request)
    {
        $file = $request->file('file');
        $path = $file->store('documents');
        $document = Document::create([
            ...$request->safe()->except('file'),
            'company_id' => $request->attributes->get('current_company')->id,
            'uploaded_by' => $request->user()->id,
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
        ]);

        return $this->success($document, 'Documento enviado com sucesso.', 201);
    }

    public function show(Request $request, Document $document)
    {
        abort_if($document->company_id !== $request->attributes->get('current_company')->id, 403);
        return $this->success($document);
    }

    public function destroy(Request $request, Document $document)
    {
        abort_if($document->company_id !== $request->attributes->get('current_company')->id, 403);
        $document->delete();
        return $this->success(null, 'Documento excluído com sucesso.');
    }

    public function download(Request $request, Document $document)
    {
        abort_if($document->company_id !== $request->attributes->get('current_company')->id, 403);
        return Storage::download($document->path, $document->name);
    }
}
