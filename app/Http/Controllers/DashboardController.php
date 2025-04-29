<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();

        // Récupérer les groupes dont l'utilisateur est membre
        $memberGroups = $user->groups()
            ->with(['owner', 'members', 'seasons.challenges'])
            ->get();

        // Récupérer les groupes dont l'utilisateur est propriétaire
        $ownedGroups = $user->ownedGroups()
            ->with(['owner', 'members', 'seasons.challenges'])
            ->get();

        // Fusionner les deux collections
        $allGroups = $memberGroups->concat($ownedGroups)->unique('id');

        return inertia('Dashboard', [
            'groups' => $allGroups,
            'canLogin' => !Auth::check(),
            'canRegister' => !Auth::check(),
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
}
