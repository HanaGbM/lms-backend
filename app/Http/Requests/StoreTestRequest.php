<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestRequest extends FormRequest
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
            'model_type' => 'required|in:module,chapter',
            'module_id' => 'required_if:category,module|exists:modules,id',
            'chapter_id' => 'required_if:category,chapter|exists:chapters,id',
            'name' => 'required|string',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after:start_date',
            'duration' => 'nullable|numeric|min:1',
            'duration_unit' => 'nullable|in:minutes,hours',
        ];
    }
}
