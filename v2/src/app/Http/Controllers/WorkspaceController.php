<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkspaceController extends Controller
{
    public function switch(Request $request, $id)
    {
        // Verifica se o workspace pertence ao usuário e está ativo
        $workspace = Auth::user()
            ->workspaces()
            ->where('workspaces.id', $id)
            ->where('workspaces.ativo', true)
            ->first();

        if ($workspace) {
            session(['active_workspace_id' => $workspace->id]);
        }

        return redirect()->back();
    }
}
