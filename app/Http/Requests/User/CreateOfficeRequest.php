<?php
namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateOfficeRequest extends FormRequest
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
            "office_name" => [
                "required",
                "string",
                "max:100",
                "unique:offices,office_name",
            ],
            "description" => ["nullable", "string", "max:250"],
            "is_active" => ["boolean"],
        ];
    }

    public function messages(): array
    {
        return [
            "office_name.required" => "Office name is required.",
            "office_name.unique" => "Office name already exists.",
            "office_name.max" => "Office name must not exceed 100 characters.",
            "description.max" => "Description must not exceed 250 characters.",
        ];
    }
}
