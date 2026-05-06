<?php
namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class FeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth("student")->check();
    }

    public function rules(): array
    {
        return [
            "raw_text" => ["required", "string", "min:10", "max:1000"],
            "language" => ["required", "in:english,tagalog,taglish"],
            "submission_method" => ["required", "in:qr_code,manual_pick"],
        ];
    }

    public function messages(): array
    {
        return [
            "raw_text.required" => "Please enter your feedback.",
            "raw_text.min" => "Feedback must be at least 10 characters.",
            "raw_text.max" => "Feedback must not exceed 1000 characters.",
            "language.required" =>
                "Please select the language of your feedback.",
        ];
    }
}
