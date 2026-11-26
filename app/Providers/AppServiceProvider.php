<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\AmigoSecreto\Group;
use App\Models\User;
use App\Policies\AmigoSecreto\GroupPolicy;
use Illuminate\Support\Facades\Gate;
use View;

class AppServiceProvider extends ServiceProvider
{
    private $totalVisitors = 0;
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('App\Domain\Veiculo\Repositories\VeiculoRepositoryInterface', 'App\Infrastructure\Persistence\Eloquent\VeiculoRepository');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('access-organizer', fn (User $user): bool => (bool) $user->is_organizer);
        Gate::define('access-super-admin', fn (User $user): bool => $user->is_organizer && $user->is_super_admin);
        Gate::policy(Group::class, GroupPolicy::class);

        // total de visitantes únicos registrados
        // só executa se a tabela existir
        if (\Schema::hasTable('visitors')) {
            $this->totalVisitors = \DB::table('visitors')->count();
            view()->share('totalVisitors', $this->totalVisitors);
        }

         View::composer('layouts.amigo-secreto', function ($view) {
            $data = $view->getData();
            $group = $data['group'] ?? request()->route('group');
            $year = $data['selectedYear'] ?? ($group instanceof Group
                ? $group->eventYear()
                : Group::selectedYear(request()->query('year')));
            $user = request()->user();
            $groups = $user?->is_organizer
                ? Group::createdBy($user)->select('id', 'name', 'event_date', 'created_at')->orderBy('name')->get()
                : collect();
            $years = $groups->toBase()->map(fn (Group $group) => $group->eventYear())
                ->push(now()->year, $year)->unique()->sortDesc();

            $view->with([
                'selectedYear' => $year,
                'availableYears' => $years,
                'allGroups' => $groups->filter(fn (Group $group) => $group->eventYear() === $year),
            ]);
        });

        // compartilhar com todas as views
        view()->share('totalVisitors', $this->totalVisitors);
    }
}
