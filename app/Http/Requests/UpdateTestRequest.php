<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'duration' => 'nullable|numeric',
            'passing_score' => 'nullable|numeric',
            'is_published' => 'nullable|boolean',
            'questions' => 'nullable|array',
            'questions.*.id' => 'nullable|exists:questions,id',
            'questions.*.score_value' => 'nullable|numeric',
        ];
    }
}
