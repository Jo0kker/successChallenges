<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

class FeedbackController extends Controller
{
    public function create()
    {
        return Inertia::render('Feedback/Create', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:bug,feature,feedback,other',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'email' => 'nullable|email|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        $feedback = new Feedback($validated);

        if (auth()->check()) {
            $feedback->user_id = auth()->id();
        }

        $feedback->save();

        return Inertia::render('Feedback/Create', [
            'flash' => [
                'success' => 'Merci pour votre feedback !'
            ]
        ]);
    }
}
