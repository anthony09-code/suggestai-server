<?php
namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeOfficeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            "date" => [
                "sometimes",
                "string",
                "in:all,today,7days,30days,90days,wtd,mtd,qtd,ytd,custom",
            ],
            "status" => ["sometimes", "string", "in:all,pending,processed"],
            "anonymous" => [
                "sometimes",
                "string",
                "in:all,anonymous,identified",
            ],
            "date_from" => [
                "required_if:date,custom",
                "sometimes",
                "nullable",
                "date",
            ],
            "date_to" => [
                "required_if:date,custom",
                "sometimes",
                "nullable",
                "date",
                "after_or_equal:date_from",
            ],

        ];
    }
}
