<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\QuestionResponse;
use App\Models\StudentModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeReportController extends Controller
{
    public function myGrade(Request $request, StudentModule $studentModule)
    {
        $responses = QuestionResponse::whereHas('question', function ($query) use ($studentModule) {
            $query->where('module_id', $studentModule->module_id);
        })->with(['question', 'option'])->where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(function ($response) {
                $score = 0;
                $answer = '';
                $isCorrect = null;

                if ($response->question->question_type == 'short') {
                    $score = $response->score;
                    $answer = $response->other_answer ?: 'No answer provided';
                    $answer = 'Not Evaluated';
                } elseif (isset($response->option)) {
                    $score = $response->option->is_correct ? $response->question->score_value : 0;
                    $answer = $response->option->choice ?: 'No option selected';
                    $isCorrect = $response->option->is_correct;
                }

                return [
                    'score' => $score,
                    'score_value' => $response->question->score_value,
                    'category' => $response->question->category,
                    'question_type' => $response->question->question_type,
                    'question' => $response->question->title,
                    'answer' => $answer,
                    'is_correct' => $isCorrect
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
