<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
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
            'name' => 'required|string|unique:questions,name',
            'category' => 'required|string|in:Test,Assignment',
            'score_value' => 'required|numeric',
            'question_type' => 'required|string|in:choice,short,choice_short',
            'options' => 'required_if:question_type,choice,choice_short|array',
            'options.*.choice' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category.in' => 'The selected category is invalid you can only select in Test or Assignment.',
            'question_type.in' => 'The selected question type is invalid you can only select in choice, short, choice_short.',
        ];
    }
}
