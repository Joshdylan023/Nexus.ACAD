<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SystemEvent;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SystemEventController extends Controller
{
    /**
     * Lista todos os eventos do sistema.
     */
    public function index()
    {
        $events = SystemEvent::with('creator')
            ->orderBy('start_at', 'desc')
            ->get();

        return response()->json($events);
    }

    /**
     * Cria um novo evento do sistema.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:maintenance,import,backup,migration',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after:start_at',
            'restricted_access' => 'nullable|array',
            'restricted_access.*' => 'exists:users,id',
            'block_student_portal' => 'boolean',
            'block_teacher_portal' => 'boolean',
            'block_admin_portal' => 'boolean'
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'scheduled';

        $event = SystemEvent::create($validated);

        return response()->json($event, 201);
    }

    /**
     * Ativa um evento (coloca o sistema em modo restrito).
     */
    public function activate($id)
    {
        $event = SystemEvent::findOrFail($id);
        
        // Cancela outros eventos ativos
        SystemEvent::where('status', 'active')
            ->where('id', '!=', $id)
            ->update(['status' => 'cancelled']);

        $event->update([
            'status' => 'active',
            'start_at' => Carbon::now()
        ]);

        return response()->json([
            'message' => 'Evento ativado com sucesso',
            'event' => $event->fresh()
        ]);
    }

    /**
     * Desativa um evento (retorna o sistema ao normal).
     */
    public function deactivate($id)
    {
        $event = SystemEvent::findOrFail($id);
        
        $event->update([
            'status' => 'completed',
            'end_at' => Carbon::now()
        ]);

        return response()->json([
            'message' => 'Evento finalizado com sucesso',
            'event' => $event->fresh()
        ]);
    }

    /**
     * Remove um evento do sistema.
     */
    public function destroy($id)
    {
        $event = SystemEvent::findOrFail($id);
        
        if ($event->status === 'active') {
            return response()->json([
                'message' => 'Não é possível excluir um evento ativo'
            ], 400);
        }

        $event->delete();

        return response()->json([
            'message' => 'Evento excluído com sucesso'
        ]);
    }

    /**
     * Retorna o evento atualmente ativo (se houver).
     */
    public function current()
{
    $event = SystemEvent::getActiveEvent();
    
    // Retorna null explicitamente se não houver evento
    if (!$event) {
        return response()->json(null);
    }
    
    return response()->json($event);
}
}
