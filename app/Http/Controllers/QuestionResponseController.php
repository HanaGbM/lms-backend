<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionResponseRequest;
use App\Http\Requests\UpdateQuestionResponseRequest;
use App\Models\Module;
use App\Models\ModuleTeacher;
use App\Models\Question;
use App\Models\QuestionResponse;
use App\Models\StudentTest;
use App\Models\Test;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class QuestionResponseController extends StudentModuleController
{

    public function startTest(Request $request, Test $test)
    {
        DB::beginTransaction();

        try {
            $timezone = 'Africa/Addis_Ababa';

            $testStartDate = Carbon::parse($test->start_date)->timezone($timezone);
            $testEndDate = Carbon::parse($test->due_date)->timezone($timezone);

            $now = Carbon::now($timezone);
            if (!$now->between($testStartDate, $testEndDate)) {
                abort(403, 'Test is not available at this time. It starts on ' . $testStartDate->toDateTimeString() . ' and ends on ' . $testEndDate->toDateTimeString());
            }
            $studentTest = StudentTest::where('student_id', Auth::id())
                ->where('test_id', $test->id)
                ->first();
            if ($studentTest) {
                abort(400, 'You have already started this test.');
            } else {
                $studentTest = StudentTest::updateOrCreate([
                    'student_id' => Auth::id(),
                    'test_id' => $test->id,
                ], [
                    'started_at' => now(),
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Test started successfully',
                    'test' => $test,
                    'student_test' => $studentTest,
                ], 200);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function completeTest(Request $request, Test $test)
    {
        DB::beginTransaction();

        try {
            $timezone = 'Africa/Addis_Ababa';

            $testStartDate = Carbon::parse($test->start_date)->timezone($timezone);
            $testEndDate = Carbon::parse($test->due_date)->timezone($timezone);

            $now = Carbon::now($timezone);
            if (!$now->between($testStartDate, $testEndDate)) {
                abort(403, 'Test is not available at this time. It starts on ' . $testStartDate->toDateTimeString() . ' and ends on ' . $testEndDate->toDateTimeString());
            }
            $studentTest = StudentTest::where('student_id', Auth::id())
                ->where('test_id', $test->id)
                ->first();
            if ($studentTest) {
                abort(400, 'You have already started this test.');
            } else {
                $studentTest = StudentTest::updateOrCreate([
                    'student_id' => Auth::id(),
                    'test_id' => $test->id,
                ], [
                    'started_at' => now(),
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Test started successfully',
                    'test' => $test,
                    'student_test' => $studentTest,
                ], 200);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function questionResponse(StoreQuestionResponseRequest $request)
    {
        Gate::authorize('create', QuestionResponse::class);

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

        // DB::commit();

        return response()->json(
            ['message' => count($request->responses) . ' responses saved successfully'],
            201
        );
    }

    public function studentResponses(Request $request, Test $test, User $student)
    {
        Gate::authorize('read_question_response');
        return QuestionResponse::whereHas('question', function ($query) use ($test) {
            $query->where('test_id', $test->id);
        })->with('question')->where('user_id', $student->id)->latest()->paginate($request->per_page ?? 10)->through(function ($response) {
            $score = 0;
            $answer = '';
            $isCorrect = null;
            $isEvaluated = true;

            if ($response->question->question_type == 'short') {
                $score = $response->score;
                $answer = $response->other_answer ?: 'No answer provided';
                $isEvaluated = $response->score  ? true : false;
                $correctAnswer = "";
                $isCorrect = "";
            } elseif (isset($response->option)) {
                $score = $response->option->is_correct ? $response->question->score_value : 0;
                $answer = $response->option ? $response->option->choice : 'No option selected';
                $correctAnswer = $response->question->options->where('is_correct', true)->first()->choice ?? null;
                $isCorrect = $response->option->is_correct;
            }
            return [
                'id' => $response->id,
                'score' => $score,
                'score_value' => $response->question->score_value,
                'question_type' => $response->question->question_type,
                'question' => $response->question->name,
                'answer' => $answer,
                'correct_answer' => $correctAnswer,
                'is_correct' => $isCorrect,
                'is_evaluated' => $isEvaluated
            ];
        })->groupBy('question_type');
    }

    public function shortAnswerAssignmentResponses(Request $request, Module $module)
    {
        Gate::authorize('read_question_response');
        return QuestionResponse::whereHas('question', function ($query) use ($module) {
            $query->where('questionable_id', $module->id)->where('question_type', 'short');
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
        Gate::authorize('evaluate_question_response');
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
        $test = Test::findOrFail($request->test_id);
        $studentTest = StudentTest::where('student_id', Auth::id())
            ->where('test_id', $test->id)
            ->first();

        $validationRules = [];

        if (!$studentTest) {
            abort(403, "Test is not started yet. Please start the test before submitting responses.");
        }

        $testDuration = (int)$test->duration;
        $testDurationUnit = $test->duration_unit;

        // Check if test duration has expired
        $startedAt = Carbon::parse($studentTest->started_at);
        $now = Carbon::now();

        if ($testDurationUnit === 'minutes') {
            $expiresAt = $startedAt->copy()->addMinutes($testDuration);
        } elseif ($testDurationUnit === 'hours') {
            $expiresAt = $startedAt->copy()->addHours($testDuration);
        } else {
            $expiresAt = $startedAt;
        }

        if ($now->greaterThan($expiresAt)) {
            abort(403, "Test duration has expired. You cannot submit responses., ended at: " . $expiresAt->toDateTimeString());
        }
        // dd($testDuration, $testDurationUnit);

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
