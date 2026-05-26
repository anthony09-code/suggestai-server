<?php
namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BaseExport;

class ExportService
{
    /**
     * @param array<string, mixed> $viewData
     */
    public function download(
        BaseExport $export,
        string $filename,
        string $format,
        string $view,
        array $viewData = [],
    ): mixed {
        return match ($format) {
            "excel" => Excel::download($export, "{$filename}.xlsx"),
            "pdf" => $this->pdf($view, $viewData, "{$filename}.pdf"),
            default => Excel::download(
                $export,
                "{$filename}.csv",
                \Maatwebsite\Excel\Excel::CSV,
                ["Content-Type" => "text/csv"],
            ),
        };
    }

    /**
     * @param array<string, mixed> $data
     */
    private function pdf(string $view, array $data, string $filename): mixed
    {
        return Pdf::loadView($view, $data)
            ->setPaper("a4", "landscape")
            ->download($filename);
    }
}
