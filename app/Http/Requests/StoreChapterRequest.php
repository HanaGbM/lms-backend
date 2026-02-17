<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChapterRequest extends FormRequest
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
            'module_id' => 'required|exists:modules,id',
            'parent_id' => 'nullable|exists:chapters,id',
            'name' => 'required|string|unique:chapters,name,NULL,id,module_id,' . $this->module_id,
            'description' => 'required|string',
            'is_custom' => 'required|boolean',
            'student_ids' => 'required_if:is_custom,true|array',
            'student_ids.*' => 'required|exists:users,id',
        ];
    }
}
