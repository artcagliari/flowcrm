<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportPdfController extends Controller
{
    public function __invoke(Request $request, ReportController $reports)
    {
        $data = json_decode($reports($request)->getContent(), true)['data'] ?? [];
        $from = $request->query('from', '-');
        $to = $request->query('to', '-');

        $html = view('reports.pdf', compact('data', 'from', 'to'))->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="relatorio-flowcrm.html"',
        ]);
    }
}
