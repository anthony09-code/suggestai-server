<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\FeedbackRequest;
use App\Models\Feedback;
use App\Models\Office;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function show(Office $office): View|RedirectResponse
    {
        abort_if(
            !$office->is_active,
            404,
            "This office is not currently accepting feedback.",
        );

        if (!auth("student")->check()) {
            session()->put("intended_office", $office->access_link);
            return redirect()->route("student.auth.google");
        }

        return view("student.feedback.form", compact("office"));
    }

    public function store(
        FeedbackRequest $request,
        Office $office,
    ): RedirectResponse {
        abort_if(
            !$office->is_active,
            404,
            "This office is not currently accepting feedback.",
        );

        Feedback::create([
            "student_id" => auth("student")->id(),
            "office_id" => $office->id,
            "raw_text" => $request->raw_text,
            "status" => "pending",
        ]);

        return back()->with(
            "success",
            "Your feedback has been submitted successfully!",
        );
    }
}
