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
        $groups = $user->groups()
            ->with(['owner', 'members', 'seasons.challenges'])
            ->get();

        return inertia('Dashboard', [
            'groups' => $groups,
            'canLogin' => !Auth::check(),
            'canRegister' => !Auth::check(),
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
}
