<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ImportExportController extends Controller
{
    use RespondsWithJson;

    public function exportClients(Request $request)
    {
        $companyId = $this->companyId($request);
        $rows = Client::where('company_id', $companyId)->get(['name', 'email', 'phone', 'whatsapp', 'city', 'origin', 'status']);

        return $this->csvResponse('clientes.csv', ['nome', 'email', 'telefone', 'whatsapp', 'cidade', 'origem', 'status'], $rows->map(fn ($r) => [
            $r->name, $r->email, $r->phone, $r->whatsapp, $r->city, $r->origin, $r->status,
        ]));
    }

    public function exportLeads(Request $request)
    {
        $companyId = $this->companyId($request);
        $rows = Lead::where('company_id', $companyId)->with('stage:id,name')->get();

        return $this->csvResponse('leads.csv', ['nome', 'email', 'telefone', 'whatsapp', 'origem', 'status', 'etapa', 'valor', 'score'], $rows->map(fn ($r) => [
            $r->name, $r->email, $r->phone, $r->whatsapp, $r->origin, $r->status, $r->stage?->name, $r->estimated_value, $r->score,
        ]));
    }

    public function importClients(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt']]);
        $companyId = $this->companyId($request);
        $created = 0;
        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 1 || blank($row[0])) {
                continue;
            }

            Client::create([
                'company_id' => $companyId,
                'name' => $row[0],
                'email' => $row[1] ?? null,
                'phone' => $row[2] ?? null,
                'whatsapp' => $row[3] ?? null,
                'city' => $row[4] ?? null,
                'origin' => $row[5] ?? 'importacao',
                'status' => $row[6] ?? 'ativo',
            ]);
            $created++;
        }

        fclose($handle);

        return $this->success(['imported' => $created], "{$created} clientes importados.");
    }

    public function importLeads(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt']]);
        $companyId = $this->companyId($request);
        $created = 0;
        $handle = fopen($request->file('file')->getRealPath(), 'r');
        fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 1 || blank($row[0])) {
                continue;
            }

            Lead::create([
                'company_id' => $companyId,
                'name' => $row[0],
                'email' => $row[1] ?? null,
                'phone' => $row[2] ?? null,
                'whatsapp' => $row[3] ?? null,
                'origin' => $row[4] ?? 'importacao',
                'status' => 'novo',
                'estimated_value' => (float) ($row[5] ?? 0),
            ]);
            $created++;
        }

        fclose($handle);

        return $this->success(['imported' => $created], "{$created} leads importados.");
    }

    private function csvResponse(string $filename, array $headers, $rows)
    {
        $callback = function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }
}
