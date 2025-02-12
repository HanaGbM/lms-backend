<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionResponseRequest;
use App\Http\Requests\UpdateQuestionResponseRequest;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestionResponseController extends StudentModuleController
{
    public function questionResponse(StoreQuestionResponseRequest $request)
    {
        $this->validation($request);

        DB::beginTransaction();

        foreach ($request->responses  as $value) {
            $response = QuestionResponse::updateOrCreate([
                'user_id' => Auth::id(),
                'question_id' => $value['question_id'],
            ], [
                'question_option_id' => $value['option_id'] ?? null,
                'other_answer' => $value['short_answer'] ?? null,
            ]);

            $response->save();
        }

        DB::commit();

        return response()->json(
            ['message' => count($request->responses) . ' responses saved successfully'],
            201
        );
    }

    public function shortAnswerTestResponses(Request $request, Module $module)
    {
        return QuestionResponse::whereHas('question', function ($query) use ($module) {
            $query->where('module_id', $module->id)->where('question_type', 'short');
        })->with('question')->latest()->paginate($request->per_page ?? 10)->through(function ($response) {
            return [
                'id' => $response->id,
                'student' => $response->user->name,
                'category' => $response->question->category,
                'question' => $response->question->title,
                'answer' => $response->other_answer,
                'score' => $response->score,
            ];
        })->groupBy('category');
    }


    public function evaluate(Request $request, QuestionResponse $questionResponse)
    {
        $request->validate([
            'score' => 'required|numeric',
        ]);

        if ($request->score > $questionResponse->question->score_value) {
            abort(400, 'Request Score (' . $request->score . ') cannot be greater than the question score value (' . $questionResponse->question->score_value . ')');
        }

        $questionResponse->update([
            'score' => $request->score,
        ]);

        return response()->json(['message' => 'Response evaluated successfully'], 200);
    }


    private function validation($request)
    {
        $validationRules = [];

        foreach ($request->input('responses') as $key => $response) {
            $question = Question::find($response['question_id']);


            switch ($question->question_type) {
                case 'choice':
                    $validationRules["responses.$key.option_id"] = 'required|exists:question_options,id,question_id,' . $question->id;
                    break;
                case 'short':
                    $validationRules["responses.$key.short_answer"] = 'required|string';
                    break;
                case 'choice_short':
                    $validationRules["responses.$key.option_id"] = 'required|exists:question_options,id,question_id,' . $question->id;
                    $validationRules["responses.$key.reason"] = 'nullable|string';
                    break;
            }
        }

        return $request->validate($validationRules);
    }
}
