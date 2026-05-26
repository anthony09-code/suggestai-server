<?php
namespace App\Http\Controllers\Concerns;

use App\Services\ExportService;
use App\Exports\BaseExport;
use Illuminate\Http\Request;

trait Exportable
{
    protected function handleExport(
        Request $request,
        BaseExport $export,
        string $filename,
        string $pdfView,
        array $pdfData = [],
    ): mixed {
        $format = $request->input("format", "csv");

        return app(ExportService::class)->download(
            export: $export,
            filename: $filename,
            format: $format,
            view: $pdfView,
            viewData: $pdfData,
        );
    }
}
