<?php
namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NotGibberish;

class FeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth("student")->check();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            "raw_text" => [
                "required",
                "string",
                "min:20",
                "max:250",
                new NotGibberish(),
            ],
            "is_anonymous" => ["nullable", "boolean"],
        ];
    }

    public function messages(): array
    {
        return [
            "raw_text.required" =>
                "Please write your feedback before submitting.",
            "raw_text.min" =>
                "Your feedback is too short. Please write at least 20 characters.",
            "raw_text.max" =>
                "Your feedback is too long. Maximum 2000 characters.",
        ];
    }
}
