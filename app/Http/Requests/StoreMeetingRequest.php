<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeetingRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'url' => ['nullable', 'url'],
            'all_day' => ['required', 'boolean'],
            'start_date' => ['required', 'date', 'before_or_equal:end_date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date', 'after_or_equal:today'],
            'start_time' => ['required_if:all_day,false', 'date_format:H:i', 'before_or_equal:end_time', 'after_or_equal:00:00'],
            'end_time' => ['required_if:all_day,false', 'date_format:H:i', 'after_or_equal:start_time', 'before:23:59'],
            'participants' => ['required_without:teacher_module_id', 'array'],
            'participants.*' => ['required', 'exists:users,id'],

        ];
    }
}
