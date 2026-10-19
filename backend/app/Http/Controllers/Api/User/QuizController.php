<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\UserQuizAttempt;
use App\Models\UserAnswer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    
    //Récupérer toutes les questions d'un quiz avec leurs réponses
    
    public function getQuizQuestions(Request $request, $attemptId)
    {
        $attempt = UserQuizAttempt::where('id', $attemptId)->where('user_id', $request->user()->id)->first();

        if (!$attempt) {
            return response()->json([
                'status' => false,
                'message' => 'Tentative non trouvée'
            ], 404);
        }

        $questions = Question::with('answers')->where('quiz_id', $attempt->quiz_id)->orderBy('order')->get();

        // Cacher le champ "is_correct" pour l'utilisateur
        $questions->each(function ($question) {
            $question->answers->makeHidden('is_correct');
        });

        return response()->json([
            'status' => true,
            'quiz_id' => $attempt->quiz_id,
            'quiz_title' => $attempt->quiz->title,
            'questions' => $questions,
            'total_questions' => $questions->count()
        ]);
    }

    //Liste les Quiz 
    public function index()
    {
        $quizzes = Quiz::where('is_active', true)->withCount('questions')->orderBy('created_at', 'asc')->get();

        return response()->json([
            'status' => true,
            'quizzes' => $quizzes
        ]);
    }

    //demarer un Quiz
    public function start(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required|exists:quizzes,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier si le quiz est actif
        $quiz = Quiz::where('id', $request->quiz_id)->where('is_active', true)->first();
        if (!$quiz) {
            return response()->json([
                'status' => false,
                'message' => 'Quiz non disponible'
            ], 404);
        }

        // Créer une nouvelle tentative
        $attempt = UserQuizAttempt::create([
            'user_id' => $request->user()->id,
            'quiz_id' => $quiz->id,
            'score' => 0,
            'total_questions' => 10, // 
            'completed_at' => null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Quiz démarré avec succès',
            'attempt_id' => $attempt->id,
            'quiz' => $quiz
        ]);
    }

    //Récupérer une question par numéro
    
    public function getQuestion(Request $request, $attemptId, $number)
    {
        $attempt = UserQuizAttempt::where('id', $attemptId)->where('user_id', $request->user()->id)->first();

        if (!$attempt) {
            return response()->json([
                'status' => false,
                'message' => 'Tentative non trouvée'
            ], 404);
        }

        $question = Question::with('answers')->where('quiz_id', $attempt->quiz_id)->orderBy('order')->skip($number - 1)->first();

        if (!$question) {
            return response()->json([
                'status' => false,
                'message' => 'Question non trouvée'
            ], 404);
        }

        // Masquer le champ is_correct pour l'utilisateur
        $question->answers->makeHidden('is_correct');

        return response()->json([
            'status' => true,
            'question' => $question,
            'current_number' => $number,
            'total_questions' => $attempt->total_questions
        ]);
    }

    //Soumettre une réponse
    
    public function submitAnswer(Request $request)
    {
        //Validation de donné
        $validator = Validator::make($request->all(), [
            'attempt_id' => 'required|exists:user_quiz_attempts,id',
            'question_id' => 'required|exists:questions,id',
            'answer_id' => 'required|exists:answers,id'
        ]);

        //Gestion d'erreur de validation
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier que l'utilisateur est le propriétaire de la tentative
        $attempt = UserQuizAttempt::where('id', $request->attempt_id)->where('user_id', $request->user()->id)->first();

        if (!$attempt) {
            return response()->json([
                'status' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        // Sauvegarder ou mettre à jour la réponse
        UserAnswer::updateOrCreate(
            [
                'attempt_id' => $request->attempt_id,
                'question_id' => $request->question_id,
            ],
            [
                'answer_id' => $request->answer_id
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Réponse enregistrée'
        ]);
    }

    //Terminer le quiz et calculer le score
    
    public function finish(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attempt_id' => 'required|exists:user_quiz_attempts,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $attempt = UserQuizAttempt::where('id', $request->attempt_id)->where('user_id', $request->user()->id)->first();

        if (!$attempt) {
            return response()->json([
                'status' => false,
                'message' => 'Tentative non trouvée'
            ], 404);
        }

        // Calculer le score
        $score = 0;
        $userAnswers = UserAnswer::where('attempt_id', $attempt->id)->get();

        foreach ($userAnswers as $userAnswer) {
            $correctAnswer = $userAnswer->question->answers()->where('is_correct', true)->first();
            if ($correctAnswer && $userAnswer->answer_id === $correctAnswer->id) {
                $score += $userAnswer->question->points;
            }
        }

        $attempt->update([
            'score' => $score,
            'completed_at' => now()
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Quiz terminé',
            'score' => $score,
            'total_questions' => $attempt->total_questions,
            'attempt_id' => $attempt->id
        ]);
    }

    //Récupérer le résultat détaillé
    
    public function result(Request $request, $attemptId)
    {
        $attempt = UserQuizAttempt::with(['quiz', 'userAnswers.question.answers'])
                    ->where('id', $attemptId)
                    ->where('user_id', $request->user()->id)
                    ->first();

        if (!$attempt) {
            return response()->json([
                'status' => false,
                'message' => 'Résultat non trouvé'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'attempt' => $attempt,
            'score_percentage' => round(($attempt->score / ($attempt->total_questions * 10)) * 100, 2)
        ]);
    }

    //Récupérer le classement général (Leaderboard)
    
    public function leaderboard()
    {
        $leaderboard = UserQuizAttempt::with('user')
            ->whereNotNull('completed_at')
            ->orderBy('score', 'desc')
            ->orderBy('completed_at', 'desc')
            ->take(20)                    // Top 20
            ->get()
            ->map(function ($attempt) {
                return [
                    'user_name'    => $attempt->user->name,
                    'user_id'      => $attempt->user_id,
                    'score'        => $attempt->score,
                    'percentage'   => round(($attempt->score / ($attempt->total_questions * 10)) * 100, 2),
                    'completed_at' => $attempt->completed_at,
                ];
            });

        return response()->json($leaderboard);
    }

    //Mes Scores avec corrections detaillées
    
    public function myScores(Request $request)
    {
        $attempts = UserQuizAttempt::with([
            'quiz',
            'userAnswers.question.answers',
            'userAnswers.answer'
        ])
        ->where('user_id', $request->user()->id)
        ->whereNotNull('completed_at')
        ->join('quizzes', 'user_quiz_attempts.quiz_id', '=', 'quizzes.id')
        ->orderBy('quizzes.created_at', 'asc')
        ->orderBy('user_quiz_attempts.completed_at', 'desc')
        ->select('user_quiz_attempts.*')
        ->get();

        $scores = $attempts->map(function ($attempt) {
            $correctAnswers = 0;
            $details = [];

            foreach ($attempt->userAnswers as $userAnswer) {
                $question = $userAnswer->question;
                $correctAnswer = $question->answers->firstWhere('is_correct', true);

                $isCorrect = $userAnswer->answer_id === ($correctAnswer?->id);

                if ($isCorrect) {
                    $correctAnswers++;
                }

                $details[] = [
                    'question'       => $question->question_text,
                    'user_answer'    => $userAnswer->answer->answer_text ?? 'Non répondu',
                    'correct_answer' => $correctAnswer?->answer_text ?? 'N/A',
                    'is_correct'     => $isCorrect,
                    'points'         => $isCorrect ? ($question->points ?? 10) : 0,
                ];
            }

            $totalQuestions = $attempt->total_questions > 0
                ? $attempt->total_questions
                : $attempt->userAnswers->count();

            $percentage = $totalQuestions > 0
                ? round(($correctAnswers / $totalQuestions) * 100, 2)
                : 0;

            return [
                'attempt_id'      => $attempt->id,
                'quiz_id'         => $attempt->quiz_id,
                'quiz_title'      => $attempt->quiz->title ?? 'Quiz inconnu',
                'quiz_created_at' => $attempt->quiz->created_at ?? null,
                'score'           => $attempt->score,
                'correct_answers' => $correctAnswers,
                'total_questions' => $totalQuestions,
                'percentage'      => $percentage,
                'completed_at'    => $attempt->completed_at,
                'details'         => $details,
            ];
        });

        return response()->json([
            'status' => true,
            'scores' => $scores,
        ]);
    }
}
