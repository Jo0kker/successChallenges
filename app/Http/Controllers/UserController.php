<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        // Recherche d'utilisateurs
        $users = User::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%");
        })
            ->where('id', '!=', Auth::id())
            ->select('id', 'name', 'email')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'type' => 'user'
                ];
            });

        // Ajouter l'option d'invité aux résultats
        $users->push([
            'id' => null,
            'name' => $query,
            'type' => 'guest'
        ]);

        return response()->json($users);
    }
}
