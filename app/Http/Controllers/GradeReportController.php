<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\QuestionResponse;
use App\Models\StudentModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class GradeReportController extends Controller
{
    public function myGrade(StudentModule $studentModule)
    {
        Gate::authorize('readGradeReport', QuestionResponse::class);
        $responses = QuestionResponse::whereHas('question', function ($query) use ($studentModule) {
            $query->where('questionable_id', $studentModule->moduleTeacher->module_id);
        })->with(['question', 'option'])->where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(function ($response) {
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
                    $answer = $response->option->choice ?: 'No option selected';
                    $correctAnswer = $response->question->options->where('is_correct', true)->first()->choice;
                    $isCorrect = $response->option->is_correct;
                }

                return [
                    'score' => $score,
                    'score_value' => $response->question->score_value,
                    'category' => $response->question->category,
                    'question_type' => $response->question->question_type,
                    'question' => $response->question->title,
                    'answer' => $answer,
                    'correct_answer' => $correctAnswer,
                    'is_correct' => $isCorrect,
                    'is_evaluated' => $isEvaluated
                ];
            })
            ->groupBy('question_type')
            ->mapWithKeys(function ($items, $questionType) {
                $totalScore = $items->reduce(function ($carry, $item) {
                    return $carry + $item['score'];
                }, 0);

                $totalScoreValue = $items->reduce(function ($carry, $item) {
                    return $carry + $item['score_value'];
                }, 0);
                $numberOfQuestions = $items->count();

                return [$questionType => [
                    'total_questions' => $numberOfQuestions,
                    'total_score' => $totalScore,
                    'total_score_value' => $totalScoreValue,
                    'by_question' => $items
                ]];
            });

        $overallTotalScore = $responses->sum('total_score');
        $overallTotalScoreValue = $responses->sum('total_score_value');
        $totalQuestions = $responses->sum('total_questions');

        return [
            'total_questions' => $totalQuestions,
            'overall_total_score' => $overallTotalScore,
            'overall_total_score_value' => $overallTotalScoreValue,
            'by_question_type' => $responses
        ];
    }
}
