<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentModuleRequest;
use App\Models\Module;
use App\Models\ModuleTeacher;
use App\Models\StudentModule;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class StudentModuleController extends Controller
{

    public function moduleChapters(Request $request, StudentModule $studentModule)
    {
        Gate::authorize('read_module_chapters');

        return $studentModule->moduleTeacher->module->chapters()
            ->when(function ($query) {
                $query->where('is_custom', true)
                    ->whereHas('studentContent', function ($query) {
                        $query->where('student_id', auth()->id());
                    })->orWhere('is_custom', false);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%");
            })->orderBy('order')->paginate($request->per_page ?? 10);
    }

    public function moduleTests(Request $request, StudentModule $studentModule)
    {

        Gate::authorize('read_module_tests');

        return $studentModule->moduleTeacher->module->tests()->when(function ($query) {
            $query->where('is_custom', true)
                ->whereHas('studentContent', function ($query) {
                    $query->where('student_id', auth()->id());
                })->orWhere('is_custom', false);
        })->when($request->has('search'), function ($query) use ($request) {
            $query->where('title', 'like', "%{$request->search}%");
        })->latest()->paginate($request->per_page ?? 10)->through(function ($question) {
            return $this->score($question);
        });
    }

    public function testQuestions(Request $request, Test $test)
    {
        Gate::authorize('read_test_questions');


        return $test->questions()->when($request->has('search'), function ($query) use ($request) {
            $query->where('name', 'like', "%{$request->search}%");
        })->get()->groupBy('question_type');
    }


    protected function score($question)
    {
        if ($question->question_type === 'choice') {
            $myResponse = $question->responses()->where('user_id', auth()->id())->latest()->first();
            $question->score = $myResponse ? ($myResponse->option->is_correct ? $question->score_value : 0) : null;
            $question->answer = [
                'option_id' => $myResponse ? $myResponse->question_option_id : null,
                'option' => $myResponse ? $myResponse->option->choice : null,
            ];
        }

        if ($question->question_type === 'short') {
            $myResponse = $question->responses()->where('user_id', auth()->id())->latest()->first();
            $question->score = $myResponse ? ($myResponse->score ? $myResponse->score . "/" . $question->score_value : null)  : null;
            $question->answer = $myResponse ? $myResponse->other_answer : null;
        }

        if ($question->question_type === 'choice_short') {
            $myResponse = $question->responses()->where('user_id', auth()->id())->latest()->first();
            $question->score = $myResponse->option->is_correct ? $question->score_value : 0;
            $question->answer = [
                'option_id' => $myResponse ? $myResponse->question_option_id : null,
                'option' => $myResponse ? $myResponse->option->choice : null,
            ];
            $question->reason = $myResponse ? $myResponse->other_answer : null;
        }

        return $question;
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentModuleRequest $request, ModuleTeacher $moduleTeacher)
    {
        try {
            DB::beginTransaction();

            Gate::authorize('create', $moduleTeacher);


            if ($moduleTeacher->students()->where('student_id', auth()->id())->exists()) {
                return response()->json(['message' => 'Module already enrolled.'], 400);
            }

            $moduleTeacher->students()->create([
                'student_id' => auth()->id(),

            ]);

            DB::commit();

            return response()->json(['message' => 'Module added successfully'], 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(StudentModule $studentModule)
    {
        return [
            'id' => $studentModule->id,
            'title' => $studentModule->module->title,
            'description' => $studentModule->module->description,
            'cover' => $studentModule->module->cover,
            'status' => $studentModule->status,
            'enrolled_at' => $studentModule->created_at,
            'started_at' => $studentModule->started_at,
            'completed_at' => $studentModule->completed_at,
        ];
    }
}
