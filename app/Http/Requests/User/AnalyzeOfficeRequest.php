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
                "in:all,today,7days,30days,90days,wtd,mtd,qtd,ytd",
            ],
            "status" => ["sometimes", "string", "in:all,pending,processed"],
            "anonymous" => [
                "sometimes",
                "string",
                "in:all,anonymous,identified",
            ],
            "date_from" => ["sometimes", "nullable", "date"],
            "date_to" => [
                "sometimes",
                "nullable",
                "date",
                "after_or_equal:date_from",
            ],
        ];
    }
}
