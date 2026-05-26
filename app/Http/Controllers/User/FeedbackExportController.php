<?php
namespace App\Http\Controllers\User;

use App\Exports\FeedbackExport;
use App\Filters\FeedbackFilter;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\Exportable;
use App\Models\Feedback;
use App\Models\Office;
use Illuminate\Http\Request;

class FeedbackExportController extends Controller
{
    use Exportable;

    public function export(Request $request, Office $office): mixed
    {
        $filters = $request->only(["date", "status", "anonymous"]);
        $filename =
            "feedbacks-{$office->access_link}-" . now()->format("Y-m-d");

        $feedbacks = FeedbackFilter::apply(
            Feedback::query()
                ->with(["student", "office"])
                ->where("office_id", $office->id),
            $filters,
        )->get();

        return $this->handleExport(
            request: $request,
            export: new FeedbackExport($office->id, $filters),
            filename: $filename,
            pdfView: "exports.feedbacks",
            pdfData: ["feedbacks" => $feedbacks, "office" => $office],
        );
    }
}
