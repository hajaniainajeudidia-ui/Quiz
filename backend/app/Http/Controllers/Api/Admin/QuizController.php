<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    //Liste des Quiz avec ses reponses
    public function index()
    {
        $quizzes = Quiz::with('questions')->get();
        return response()->json($quizzes);
    }

    //Ajouter un Quiz
    public function store(Request $request)
    {
        $quiz = Quiz::create($request->all());
        return response()->json($quiz, 201);
    }

    //Afficher un detail d'un Quiz
    public function show(Quiz $quiz)
    {
        return response()->json($quiz->load('questions.answers'));
    }

    //Mettre à jour un Quiz
    public function update(Request $request, Quiz $quiz)
    {
        $quiz->update($request->all());
        return response()->json($quiz);
    }

    //Supprimer un Quiz
    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return response()->json(['message' => 'Quiz supprimé']);
    }
}
