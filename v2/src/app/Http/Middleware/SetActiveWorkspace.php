<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\Workspace;

class SetActiveWorkspace
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Se não há workspace ativo na sessão, define o primeiro pessoal ativo do usuário
            if (!session('active_workspace_id')) {
                $workspace = $user->workspaces()
                    ->where('workspaces.tipo', 'pessoal')
                    ->where('workspaces.ativo', true)
                    ->first();

                if ($workspace) {
                    session(['active_workspace_id' => $workspace->id]);
                }
            }

            // Compartilha dados de workspace com todas as views
            $activeWorkspaceId = session('active_workspace_id');

            $userWorkspaces = $user->workspaces()
                ->where('workspaces.ativo', true)
                ->orderBy('workspaces.nome')
                ->get();

            $activeWorkspace = $userWorkspaces->firstWhere('id', $activeWorkspaceId);

            View::share('activeWorkspace', $activeWorkspace);
            View::share('userWorkspaces', $userWorkspaces);
        }

        return $next($request);
    }
}
