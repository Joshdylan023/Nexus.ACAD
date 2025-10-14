<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GrupoEducacional;
use App\Http\Requests\StoreGrupoEducacionalRequest;
use App\Http\Requests\UpdateGrupoEducacionalRequest;

class GrupoEducacionalController extends Controller
{
    public function index()
    {
        return GrupoEducacional::with(['mantenedoras', 'creator', 'updater'])->get();
    }

    public function store(StoreGrupoEducacionalRequest $request)
    {
        $grupo = GrupoEducacional::create($request->validated());
        
        // NOTIFICAÇÃO AUTOMÁTICA
        \App\Helpers\NotificationHelper::notifyByRole(
            'admin',
            'success',
            '✅ Novo Grupo Educacional Criado',
            "O grupo '{$grupo->nome}' foi cadastrado por " . auth()->user()->name,
            'new_grupo',
            "/admin/institucional/grupos-educacionais"
        );

        return response()->json($grupo->load(['creator', 'updater']), 201);
    }

    public function show(GrupoEducacional $grupo)
    {
        return $grupo->load(['mantenedoras', 'creator', 'updater']);
    }

    public function update(UpdateGrupoEducacionalRequest $request, GrupoEducacional $grupo)
    {
        $grupo->update($request->validated());
        
        // NOTIFICAÇÃO AUTOMÁTICA
        \App\Helpers\NotificationHelper::notifyByRole(
            'admin',
            'info',
            '📝 Grupo Educacional Atualizado',
            "O grupo '{$grupo->nome}' foi atualizado por " . auth()->user()->name,
            'update_grupo',
            "/admin/institucional/grupos-educacionais"
        );

        return response()->json($grupo->load(['creator', 'updater']));
    }

    public function destroy(GrupoEducacional $grupo)
    {
        $nomeGrupo = $grupo->nome;
        $grupo->delete();
        
        // NOTIFICAÇÃO AUTOMÁTICA
        \App\Helpers\NotificationHelper::notifyByRole(
            'admin',
            'warning',
            '🗑️ Grupo Educacional Excluído',
            "O grupo '{$nomeGrupo}' foi excluído por " . auth()->user()->name,
            'delete_grupo'
        );

        return response()->json(['message' => 'Grupo excluído com sucesso']);
    }
}
