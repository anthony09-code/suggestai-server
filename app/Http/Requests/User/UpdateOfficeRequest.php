<?php
namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfficeRequest extends FormRequest
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
        $officeId = $this->route("office")->id;

        return [
            "office_name" => [
                "sometimes",
                "string",
                "max:255",
                "unique:offices,office_name," . $officeId,
            ],
            "description" => ["nullable", "string", "max:500"],
            "is_active" => ["boolean"],
        ];
    }

    public function messages(): array
    {
        return [
            "office_name.unique" => "Office name already exists.",
            "office_name.max" => "Office name must not exceed 255 characters.",
            "description.max" => "Description must not exceed 500 characters.",
        ];
    }
}
