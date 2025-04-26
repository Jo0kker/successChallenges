<?php

namespace App\Providers;

use App\Models\Group;
use App\Models\Season;
use App\Models\Challenge;
use App\Policies\GroupPolicy;
use App\Policies\SeasonPolicy;
use App\Policies\ChallengePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Group::class => GroupPolicy::class,
        Season::class => SeasonPolicy::class,
        Challenge::class => ChallengePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
