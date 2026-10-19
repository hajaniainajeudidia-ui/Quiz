<?php

    namespace App\Http\Controllers\Api\Admin;

    use App\Http\Controllers\Controller;
    use App\Models\Quiz;
    use App\Models\Question;
    use App\Models\User;
    use App\Models\UserQuizAttempt;
    use Illuminate\Http\Request;

    class DashboardController extends Controller
    {
        //
        public function stats(Request $request)
        {
            // Statistiques globales
            $totalQuizzes = Quiz::count();
            $totalQuestions = Question::count();
            $totalUsers = User::count();
            $totalAttempts = UserQuizAttempt::whereNotNull('completed_at')->count();
            
            // Statistiques des 7 derniers jours
            $recentAttempts = UserQuizAttempt::whereNotNull('completed_at')
                ->where('completed_at', '>=', now()->subDays(7))
                ->count();

            // Meilleur score global
            $topScore = UserQuizAttempt::whereNotNull('completed_at')
                ->orderBy('score', 'desc')
                ->first();

            return response()->json([
                'status' => true,
                'stats' => [
                    'total_quizzes'     => $totalQuizzes,
                    'total_questions'   => $totalQuestions,
                    'total_users'       => $totalUsers,
                    'total_attempts'    => $totalAttempts,
                    'recent_attempts'   => $recentAttempts,
                    'top_score'         => $topScore ? [
                        'score' => $topScore->score,
                        'user_name' => $topScore->user->name ?? 'Utilisateur inconnu',
                        'percentage' => round(($topScore->score / ($topScore->total_questions * 10)) * 100, 2)
                    ] : null
                ]
            ]);
        }
    }
