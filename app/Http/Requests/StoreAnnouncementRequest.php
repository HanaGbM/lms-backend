<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
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
            'teacher_module_id' => ['nullable', 'exists:module_teachers,id'],
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_custom' => 'required|boolean',
            'student_ids' => 'required_if:is_custom,true|array',
            'student_ids.*' => 'required|exists:users,id',
        ];
    }
}
