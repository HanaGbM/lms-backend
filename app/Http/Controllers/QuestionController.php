<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Chapter;
use App\Models\Module;
use App\Models\Question;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Question::class);

        $request->validate([
            'test_id' => 'required|exists:tests,id',
        ]);

        $test = Test::find($request->test_id);

        $questions = $test->questions()->when($request->has('search'), function ($query) use ($request) {
            $query->where('name', 'like', "%{$request->search}%");
        })->get()->groupBy('question_type');


        return $questions;
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuestionRequest $request)
    {
        Gate::authorize('create', Question::class);

        try {

            DB::beginTransaction();

            $question = Question::create([
                'test_id' => $request->test_id,
                'name' => $request->name,
                'description' => $request->description,
                'question_type' => $request->question_type ?? 'choice',
                'score_value' => $request->score_value,
            ]);

            if ($request->question_type === 'choice' || $request->question_type === 'choice_short') {
                foreach ($request->options as $key => $value) {
                    $question->options()->create([
                        'choice' => $value['choice'],
                        'is_correct' => $value['is_correct'],
                        'order' => $key + 1,
                    ]);
                }
            }


            DB::commit();

            return response()->json([
                'message' => 'Question created successfully',
                'question' => $question,
            ], 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        Gate::authorize('view', $question);
        return $question;
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuestionRequest $request, Question $question)
    {
        Gate::authorize('update', $question);
        try {
            DB::beginTransaction();

            $question->update([
                'name' => $request->name ?? $question->name,
                'description' => $request->description ?? $question->description,
                'category' => $request->category ?? $question->category,
                'score_value' => $request->score_value ?? $question->score_value,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Question updated successfully',
                'question' => $question,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question)
    {
        Gate::authorize('delete', $question);
        try {
            $question->delete();
            return response()->json([
                'message' => 'Question deleted successfully',
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
