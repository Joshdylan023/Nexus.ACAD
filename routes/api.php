<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ColaboradorController;
use App\Http\Controllers\Api\V1\GrupoEducacionalController;
use App\Http\Controllers\Api\V1\MantenedoraController;
use App\Http\Controllers\Api\V1\InstituicaoController;
use App\Http\Controllers\Api\V1\InstituicaoAtoRegulatorioController;
use App\Http\Controllers\Api\V1\CampusController;
use App\Http\Controllers\Api\V1\SetorController;
use App\Http\Controllers\Api\V1\PerfilController;
use App\Http\Controllers\Api\V1\PermissaoController;
use App\Http\Controllers\Api\V1\CampusSetorController;
use App\Http\Controllers\Api\V1\GrandeAreaConhecimentoController;
use App\Http\Controllers\Api\V1\AreaConhecimentoController;
use App\Http\Controllers\Api\V1\CursoController;
use App\Http\Controllers\Api\V1\CursosAtoRegulatorioController;
use App\Http\Controllers\Api\V1\DisciplinaController;
use App\Http\Controllers\Api\V1\EmentaController;
use App\Http\Controllers\Api\V1\CurriculoController;
use App\Http\Controllers\Api\V1\CurriculoDisciplinaController;
use App\Http\Controllers\Api\V1\ProfessorVinculoController;
use App\Http\Controllers\Api\V1\ProfessorFormacaoController;
use App\Http\Controllers\Api\V1\SetorVinculoController;
use App\Http\Controllers\Api\V1\InstituicaoSetorController;
use App\Http\Controllers\Api\V1\GrupoEducacionalSetorController;
use App\Http\Controllers\Api\V1\MantenedoraSetorController;
use App\Http\Controllers\Api\V1\SystemEventController;
use App\Http\Controllers\Api\V1\ImportController;
use App\Http\Controllers\Api\V1\ImportTemplateController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\GlobalSearchController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\Api\V1\IdentidadeVisualController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- ROTA PÚBLICA DE LOGIN ---
Route::post('/login', [AuthController::class, 'login']);

// --- ROTAS PROTEGIDAS ---
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // BROADCASTING AUTH
    Route::post('/broadcasting/auth', [BroadcastController::class, 'authenticate']);

    Route::prefix('v1')->group(function () {
        
        // BUSCA GLOBAL
        Route::get('search', [GlobalSearchController::class, 'search']);
        
        // ============================================
        // MÓDULO: NOTIFICAÇÕES (ATUALIZADO)
        // ============================================
        Route::prefix('notifications')->group(function () {
            // Rotas básicas
            Route::get('/', [NotificationController::class, 'index']);
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
            Route::delete('/{id}', [NotificationController::class, 'destroy']);
            
            // Novas funcionalidades
            Route::get('/preferences', [NotificationController::class, 'getPreferences']);
            Route::post('/preferences', [NotificationController::class, 'updatePreferences']);
            Route::get('/stats', [NotificationController::class, 'getStats']);
            Route::delete('/clear-read', [NotificationController::class, 'clearRead']);
        });
        
        // ROTA PARA BUSCAR USUÁRIOS (para select de eventos)
        Route::get('/users', [UserController::class, 'index']);
        
        // MÓDULO: GESTÃO DE ACESSOS
        Route::get('/colaboradores', [ColaboradorController::class, 'index']);
        Route::get('/colaboradores/{id}', [ColaboradorController::class, 'show']);
        Route::get('/users/colaboradores', [UserController::class, 'colaboradores']);
        Route::get('users/search', [UserController::class, 'search']);
        
        Route::middleware('can:gerenciar-acessos')->group(function () {
            Route::post('/colaboradores', [ColaboradorController::class, 'store']);
            Route::put('/colaboradores/{colaborador}', [ColaboradorController::class, 'update']);
            Route::delete('/colaboradores/{colaborador}', [ColaboradorController::class, 'destroy']);
            Route::apiResource('/perfis', PerfilController::class)->parameters(['perfis' => 'perfil']);
            Route::get('/permissoes', [PermissaoController::class, 'index']);
            Route::post('/perfis/{perfil}/sync-permissoes', [PerfilController::class, 'syncPermissoes']);
        });
        
        // MÓDULO: GESTÃO ACADÊMICA
        Route::middleware('can:gerenciar-academico')->group(function () {
            Route::apiResource('/grandes-areas', GrandeAreaConhecimentoController::class)->parameters(['grandes-areas' => 'grandeAreaConhecimento']);
            Route::apiResource('/areas-conhecimento', AreaConhecimentoController::class)->parameters(['areas-conhecimento' => 'areaConhecimento']);
            Route::apiResource('/cursos', CursoController::class)->parameters(['cursos' => 'curso']);
            Route::apiResource('/cursos-atos-regulatorios', CursosAtoRegulatorioController::class)->parameters(['cursos-atos-regulatorios' => 'cursosAtoRegulatorio']);
            Route::apiResource('/disciplinas', DisciplinaController::class)->parameters(['disciplinas' => 'disciplina']);
            Route::apiResource('/ementas', EmentaController::class)->parameters(['ementas' => 'ementa']);
            Route::apiResource('/curriculos', CurriculoController::class)->parameters(['curriculos' => 'curriculo']);
            
            // Rotas para a Matriz Curricular
            Route::get('/curriculos-disciplinas', [CurriculoDisciplinaController::class, 'index']);
            Route::post('/curriculos-disciplinas', [CurriculoDisciplinaController::class, 'store']);
            Route::delete('/curriculos-disciplinas/{curriculoDisciplina}', [CurriculoDisciplinaController::class, 'destroy']);
        });

        // MÓDULO: GESTÃO DE PROFESSORES / RH
        Route::middleware('can:gerenciar-professores')->group(function () {
            Route::apiResource('/professor-vinculos', ProfessorVinculoController::class)->parameters(['professor-vinculos' => 'professorVinculo']);
        });

        // MÓDULO: GESTÃO INSTITUCIONAL
Route::middleware('can:gerenciar-institucional')->group(function () {
    Route::apiResource('/grupos-educacionais', GrupoEducacionalController::class)->parameters(['grupos-educacionais' => 'grupo']);
    Route::apiResource('/mantenedoras', MantenedoraController::class)->parameters(['mantenedoras' => 'mantenedora']);
    
    // ⭐ Export ANTES do resource
    Route::get('/instituicoes/export', [InstituicaoController::class, 'export']);
    Route::apiResource('/instituicoes', InstituicaoController::class)->parameters(['instituicoes' => 'instituicao']);
    
    // ⭐ ATOS REGULATÓRIOS + ALERTAS
    Route::get('/atos-regulatorios/alertas', [InstituicaoAtoRegulatorioController::class, 'alertas']);
    Route::apiResource('/instituicao-atos-regulatorios', InstituicaoAtoRegulatorioController::class)->parameters(['instituicao-atos-regulatorios' => 'atoRegulatorio']);
    
    Route::apiResource('/campi', CampusController::class)->parameters(['campi' => 'campus']);
    Route::apiResource('/setores', SetorController::class)->parameters(['setores' => 'setor']);

    // Rotas para Setores do Grupo Educacional
    Route::get('/grupos-educacionais/{grupoEducacional}/setores', [GrupoEducacionalSetorController::class, 'index']);
    Route::post('/grupos-educacionais/{grupoEducacional}/setores', [GrupoEducacionalSetorController::class, 'store']);
    Route::put('/grupos-educacionais/{grupoEducacional}/setores/{setorId}', [GrupoEducacionalSetorController::class, 'update']);
    Route::delete('/grupos-educacionais/{grupoEducacional}/setores/{setorId}', [GrupoEducacionalSetorController::class, 'destroy']);

    // Rotas para Setores da Mantenedora
    Route::get('/mantenedoras/{mantenedora}/setores', [MantenedoraSetorController::class, 'index']);
    Route::post('/mantenedoras/{mantenedora}/setores', [MantenedoraSetorController::class, 'store']);
    Route::put('/mantenedoras/{mantenedora}/setores/{setorId}', [MantenedoraSetorController::class, 'update']);
    Route::delete('/mantenedoras/{mantenedora}/setores/{setorId}', [MantenedoraSetorController::class, 'destroy']);

    // Rotas para Setores da Instituição
    Route::get('/instituicoes/{instituicao}/setores', [InstituicaoSetorController::class, 'index']);
    Route::post('/instituicoes/{instituicao}/setores', [InstituicaoSetorController::class, 'store']);
    Route::put('/instituicoes/{instituicao}/setores/{setorId}', [InstituicaoSetorController::class, 'update']);
    Route::delete('/instituicoes/{instituicao}/setores/{setorId}', [InstituicaoSetorController::class, 'destroy']);
    // ❌ REMOVA ESTA LINHA DUPLICADA:
    // Route::get('/instituicoes/export', [InstituicaoController::class, 'export']);

    // Rotas para Setores do Campus
    Route::get('/campi/{campus}/setores', [CampusSetorController::class, 'index']);
    Route::post('/campi/{campus}/setores', [CampusSetorController::class, 'store']);
    Route::put('/campi/{campus}/setores/{setorId}', [CampusSetorController::class, 'update']);
    Route::delete('/campi/{campus}/setores/{setorId}', [CampusSetorController::class, 'destroy']);
    

            // MÓDULO: IMPORTAÇÃO EM MASSA
            Route::prefix('imports')->group(function () {
                // Templates PRIMEIRO
                Route::get('/templates/list', [ImportTemplateController::class, 'list']);
                Route::get('/templates/download/{type}', [ImportTemplateController::class, 'download']);
                
                // Rotas de importação
                Route::get('/', [ImportController::class, 'index']);
                Route::post('/preview', [ImportController::class, 'preview']);
                Route::post('/import', [ImportController::class, 'import']);
                
                // Detalhes por ID (ÚLTIMO)
                Route::get('/{id}', [ImportController::class, 'show']);
            });

            // MÓDULO: DASHBOARD
            Route::prefix('dashboard')->group(function () {
                Route::get('/institucional', [DashboardController::class, 'institucional']);
            });
        });

        // ⭐ IDENTIDADE VISUAL
Route::prefix('identidade-visual')->group(function () {
    Route::get('/show', [IdentidadeVisualController::class, 'show']);
    Route::post('/store', [IdentidadeVisualController::class, 'store']);
    Route::post('/upload-logo', [IdentidadeVisualController::class, 'uploadLogo']);
    Route::delete('/delete-logo', [IdentidadeVisualController::class, 'deleteLogo']);
    Route::get('/entidades', [IdentidadeVisualController::class, 'listarEntidades']);
});

        // MÓDULO: RELATÓRIOS (FORA DO GRUPO INSTITUCIONAL)
        Route::apiResource('reports', ReportController::class);
        Route::post('reports/{id}/execute', [ReportController::class, 'execute']);
        Route::get('reports/{id}/export', [ReportController::class, 'export']);
        Route::get('reports/columns/{type}', [ReportController::class, 'getAvailableColumns']);
    });
});
