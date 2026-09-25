<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    //
    public function index(Request $request)
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

        $questions = Question::with('answers')
                    ->where('quiz_id', $request->quiz_id)
                    ->orderBy('order')
                    ->get();

        return response()->json([
            'status' => true,
            'questions' => $questions
        ]);
    }

    //Créer une nouvelle question + ses réponses
    
    public function store(Request $request)
    {
        //validation des donneés
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required|exists:quizzes,id',
            'question_text' => 'required|string',
            'points' => 'integer|min:1',
            'order' => 'integer|min:1',
            'answers' => 'required|array|min:4|max:4',           
            'answers.*.answer_text' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier qu'il y a exactement une bonne réponse
        $correctAnswers = collect($request->answers)->where('is_correct', true)->count();
        if ($correctAnswers !== 1) {
            return response()->json([
                'status' => false,
                'message' => 'Il doit y avoir exactement une seule bonne réponse'
            ], 422);
        }

        // Créer la question
        $question = Question::create([
            'quiz_id' => $request->quiz_id,
            'question_text' => $request->question_text,
            'points' => $request->points ?? 10,
            'order' => $request->order ?? 1,
        ]);

        // Créer les 4 réponses
        foreach ($request->answers as $answer) {
            Answer::create([
                'question_id' => $question->id,
                'answer_text' => $answer['answer_text'],
                'is_correct' => $answer['is_correct'],
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Question créée avec succès',
            'question' => $question->load('answers')
        ], 201);
    }

    
    //Afficher une question avec ses réponses
    
    public function show(Question $question)
    {
        $question->load('answers');
        return response()->json([
            'status' => true,
            'question' => $question
        ]);
    }

    //Mettre à jour une question + ses réponses
    
    public function update(Request $request, Question $question)
    {
        $validator = Validator::make($request->all(), [
            'question_text' => 'required|string',
            'points' => 'integer|min:1',
            'order' => 'integer|min:1',
            'answers' => 'required|array|min:4|max:4',
            'answers.*.id' => 'sometimes|exists:answers,id',
            'answers.*.answer_text' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Mise à jour de la question
        $question->update([
            'question_text' => $request->question_text,
            'points' => $request->points ?? $question->points,
            'order' => $request->order ?? $question->order,
        ]);

        // Mise à jour des reponses
        foreach ($request->answers as $answerData) {
            if (isset($answerData['id'])) {
                $answer = Answer::find($answerData['id']);
                if ($answer && $answer->question_id === $question->id) {
                    $answer->update([
                        'answer_text' => $answerData['answer_text'],
                        'is_correct' => $answerData['is_correct'],
                    ]);
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Question mise à jour avec succès',
            'question' => $question->load('answers')
        ]);
    }

    //Supprimer une question
    
    public function destroy(Question $question)
    {
        $question->answers()->delete();   
        $question->delete();

        return response()->json([
            'status' => true,
            'message' => 'Question supprimée avec succès'
        ]);
    }
}
