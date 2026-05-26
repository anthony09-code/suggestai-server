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
            "color" => ["nullable", "string", "regex:/^#[0-9A-Fa-f]{6}$/"],
            "image" => [
                "nullable",
                "image",
                "mimes:jpg,jpeg,png,webp",
                "max:2048",
            ],
        ];
    }

    public function messages(): array
    {
        return [
            "office_name.required" => "Office name is required.",
            "office_name.unique" => "Office name already exists.",
            "office_name.max" => "Office name must not exceed 100 characters.",
            "description.max" => "Description must not exceed 250 characters.",
            "color.regex" => "Color must be a valid hex color (e.g. #ff0000).",
            "image.image" => "The file must be an image.",
            "image.mimes" => "Image must be a jpg, jpeg, png, or webp file.",
            "image.max" => "Image must not exceed 2MB.",
        ];
    }
}
