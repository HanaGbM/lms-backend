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
            'test_id' => 'required|exists:tests,id',
            'name' => 'required|string|unique:questions,name',
            'score_value' => 'required|numeric',
            'question_type' => 'required|string|in:choice,short,choice_short',
            'options' => 'required_if:question_type,choice,choice_short|array',
            'options.*.choice' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $questionType = $this->input('question_type');
            $options = $this->input('options', []);

            if (in_array($questionType, ['choice', 'choice_short'])) {
                $hasCorrect = collect($options)->contains(function ($option) {
                    return isset($option['is_correct']) && $option['is_correct'];
                });

                if (!$hasCorrect) {
                    $validator->errors()->add('options', 'At least one option must be marked as correct.');
                }
            }
        });
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category.in' => 'The selected category is invalid. You can only select "Test" or "Assignment".',
            'question_type.in' => 'The selected question type is invalid. You can only select "choice", "short", or "choice_short".',
        ];
    }
}
