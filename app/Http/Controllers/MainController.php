<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MainController extends Controller
{
    public function home(): View
    {
        return view('home');
    }

    public function generateExercises(Request $request)
    {
        $request->validate([
            'check_sum' => 'required_without_all:check_subtraction,check_multiplication,check_division',
            'check_subtraction' => 'required_without_all:check_sum,check_multiplication,check_division',
            'check_multiplication' => 'required_without_all:check_sum,check_subtraction,check_division',
            'check_division' => 'required_without_all:check_sum,check_subtraction,check_multiplication',

            'number_one' => 'required|integer|min:0|max:999|lt:number_two',
            'number_two' => 'required|integer|min:0|max:999',

            'number_exercises' => 'required|integer|min:5|max:50',
            ]);

            $operations = [];
            if($request->check_sum) {
                $operations[] = 'sum';
            }

            if($request->check_subtraction) {
                $operations[] = 'subtraction';
            }

            if($request->check_multiplication) {
                $operations[] = 'multiplication';
            }

            if($request->check_division) {
                $operations[] = 'division';
            }

            $min = $request->number_one;
            $max = $request->number_two;

            $numberExercises = $request->number_exercises;

            $exercises = [];
            for($index = 0; $index <= $numberExercises; $index++) {
                $exercises[] = $this->generateExercise($index, $operations, $min, $max);
            }

            session(['exercises' => $exercises]);

            return view('operations', [
                'exercises' => $exercises,
            ]);

    }

    public function printExercises()
    {
        if (!session()->has('exercises')) {
            return redirect()->route('home');
        }

        $exercises = session('exercises');

        echo '<pre>';
        echo '<h1> Exercícios de Matemática (' . env('APP_NAME') . ')</h1>';
        echo '<hr>';

        foreach ($exercises as $exercise) {
            echo '<h2><small>' . $exercise['exercise_number'] . ' >> </small>' . $exercise['exercise'] . '</h2>';

        }

        echo '<hr>';
        echo '<small>Soluções</small><br>';

        foreach ($exercises as $exercise) {
            echo '<small>'. $exercise['exercise_number'] . ' >> ' . $exercise['solution'] . '</small><br>';

        }
    }

    public function exportExercises()
    {
        if (!session()->has('exercises')) {
            return redirect()->route('home');
        }

        $exercises = session('exercises');

        $filename = 'exercises_' . env('APP_NAME') . '_' . date('YmdHis') . '.txt';

        $content = 'Exercícios de Matemática (' . env('APP_NAME') . ')' . "\n\n";

        foreach ($exercises as $exercise) {
            $content .= $exercise['exercise_number'] . ' >> ' . $exercise['exercise'] . "\n";
        }


        $content .= "\n";
        $content .= "Soluções\n" . str_repeat('-', 20) . "\n";

        foreach ($exercises as $exercise) {
            $content .= $exercise['exercise_number'] . ' >> ' . $exercise['solution'] . "\n";
        }

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

    }

    private function generateExercise($index, $operations, $min, $max)
    {
        $operation = $operations[array_rand($operations)];
        $number1 = rand($min, $max);
        $number2 = rand($min, $max);

        $exercise = '';
        $solution = '';

        switch ($operation) {
            case 'sum':
                $exercise = "$number1 + $number2 = ";
                $solution = $number1 + $number2;
                break;
            case 'subtraction':
                $exercise = "$number1 - $number2 = ";
                $solution = $number1 - $number2;
                break;
            case 'multiplication':
                $exercise = "$number1 X $number2 = ";
                $solution = $number1 * $number2;
                break;
            case 'division':
                if ($number2 == 0) {
                    $number2 = 1;
                }

                $exercise = "$number1 : $number2 = ";
                $solution = $number1 / $number2;
                break;
        }

        if (is_float($solution)) {
            $solution = number_format($solution, 2);
        }

        return [
            'operation' => $operation,
            'exercise_number' => str_pad($index, 2, "0", STR_PAD_LEFT),
            'exercise' => $exercise,
            'solution' => "$exercise $solution",
        ];
    }
}
