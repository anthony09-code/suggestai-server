<?php
namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeOfficeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
