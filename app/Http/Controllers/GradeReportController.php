<?php

namespace App\Http\Controllers;

use App\Models\QuestionResponse;
use App\Models\StudentModule;
use App\Models\Test;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class GradeReportController extends Controller
{
    public function myGrade(Test $test)
    {
        Gate::authorize('readGradeReport', QuestionResponse::class);
        $responses = QuestionResponse::whereHas('question', function ($query) use ($test) {
            $query->where('test_id', $test->id);
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
                    $answer = $response->option ? $response->option->choice : 'No option selected';
                    $correctAnswer = $response->question->options->where('is_correct', true)->first()->choice ?? null;
                    $isCorrect = $response->option->is_correct;
                }

                return [
                    'score' => $score,
                    'score_value' => $response->question->score_value,
                    'question_type' => $response->question->question_type,
                    'question' => $response->question->name,
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

    public function moduleGrades(StudentModule $studentModule)
    {
        // Gate::authorize('readGradeReport', QuestionResponse::class);
        return $studentModule->moduleTeacher->tests->map(function ($test) {

            $questionCount = $test->questions()->count();
            $responseCount = $test->questions()
                ->whereHas('responses', function ($query) {
                    $query->where('user_id', Auth::id());
                })->count();
            $isCompleted = $questionCount === $responseCount;


            $questions = $test->questions()
                ->with([
                    'options',
                    'responses' => function ($query) {
                        $query->where('user_id', Auth::id());
                    }
                ])
                ->get();

            $results = [];

            foreach ($questions as $question) {
                $userResponse = $question->responses->first();
                $earnedScore = 0;

                if ($question->question_type === 'choice') {
                    $correctOptionId = $question->options->where('is_correct', true)->first()->id;

                    if ($userResponse && $userResponse->question_option_id === $correctOptionId) {
                        $earnedScore = $question->score_value;
                    }
                } elseif ($question->question_type === 'short') {
                    if ($userResponse) {
                        $earnedScore = $userResponse->score ?? 0;
                    }
                }

                $results[] = [
                    'question_id' => $question->id,
                    'question_type' => $question->question_type,
                    'score_awarded' => $earnedScore,
                    'max_score' => $question->score_value,
                    'user_response' => $userResponse,
                ];
            }

            $scoreValue = collect($results)->sum('score_awarded');

            $questionValue = $test->questions()->sum('score_value');
            $percentage = 0;

            if ($questionValue > 0) {
                $percentage = ($scoreValue / $questionValue) * 100;
            }
            $gradeData = $this->getGradeAndRemarkFromPercentage($percentage);

            return [
                'id' => $test->id,
                'name' => $test->name,
                'question_count' => $questionCount,
                'response_count' => $responseCount,
                'value' => $questionValue,
                'score' => $scoreValue,
                'points' => $scoreValue . '/' . $questionValue,
                'point_by_percent' => $percentage  . '/' . 100,
                'grade' => $gradeData['grade'],
                'remark' => $gradeData['remark'],
                // 'responses' => $responses,
                'is_completed' => $questionCount == 0 ? false : $isCompleted,
                'created_at' => $test->created_at,
            ];
        });
    }

    private function getGradeAndRemarkFromPercentage($percentage)
    {
        if ($percentage >= 95) {
            return ['grade' => 'A+', 'remark' => 'Excellent'];
        } elseif ($percentage >= 90) {
            return ['grade' => 'A', 'remark' => 'Very Good'];
        } elseif ($percentage >= 85) {
            return ['grade' => 'A-', 'remark' => 'Very Good'];
        } elseif ($percentage >= 80) {
            return ['grade' => 'B+', 'remark' => 'Good'];
        } elseif ($percentage >= 75) {
            return ['grade' => 'B', 'remark' => 'Good'];
        } elseif ($percentage >= 70) {
            return ['grade' => 'B-', 'remark' => 'Good'];
        } elseif ($percentage >= 65) {
            return ['grade' => 'C+', 'remark' => 'Fair'];
        } elseif ($percentage >= 60) {
            return ['grade' => 'C', 'remark' => 'Fair'];
        } elseif ($percentage >= 55) {
            return ['grade' => 'C-', 'remark' => 'Fair'];
        } elseif ($percentage >= 53) {
            return ['grade' => 'D+', 'remark' => 'Poor'];
        } elseif ($percentage >= 50) {
            return ['grade' => 'D-', 'remark' => 'Poor'];
        } else {
            return ['grade' => 'F', 'remark' => 'Fail'];
        }
    }
}
