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
use App\Http\Controllers\Api\V1\GestaoAcessosController;
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
use App\Http\Controllers\Api\V1\ColaboradorDashboardController;
use App\Http\Controllers\Api\V1\GlobalSearchController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\Api\V1\IdentidadeVisualController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\AniversariantesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ⭐ ROTAS PÚBLICAS DE AUTENTICAÇÃO
Route::post('/login', [AuthController::class, 'login']);

// --- ROTAS PROTEGIDAS ---
Route::middleware('auth:sanctum')->group(function () {

    // ⭐ ROTAS DE AUTENTICAÇÃO
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // BROADCASTING AUTH
    Route::post('/broadcasting/auth', [BroadcastController::class, 'authenticate']);

    Route::prefix('v1')->group(function () {

        // Minha Equipe
    Route::get('/minha-equipe', [ColaboradorController::class, 'minhaEquipe']);
    Route::get('/minha-equipe/stats', [ColaboradorController::class, 'minhaEquipeStats']);
    // Exportação de Minha Equipe
    Route::get('/minha-equipe/export/excel', [ColaboradorController::class, 'exportarEquipeExcel']);
    Route::get('/minha-equipe/export/pdf', [ColaboradorController::class, 'exportarEquipePDF']);
    // Organograma
    Route::get('/organograma', [ColaboradorController::class, 'organograma']);


        // ⭐ ANIVERSARIANTES
        Route::prefix('aniversariantes')->group(function () {
            Route::get('/', [AniversariantesController::class, 'index']);
            Route::get('/stats', [AniversariantesController::class, 'stats']);
            Route::get('/proximos', [AniversariantesController::class, 'proximos']);
        });

        
        // BUSCA GLOBAL
        Route::get('search', [GlobalSearchController::class, 'search']);
        
        // ============================================
        // MÓDULO: NOTIFICAÇÕES
        // ============================================
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
            Route::delete('/{id}', [NotificationController::class, 'destroy']);
            Route::get('/preferences', [NotificationController::class, 'getPreferences']);
            Route::post('/preferences', [NotificationController::class, 'updatePreferences']);
            Route::get('/stats', [NotificationController::class, 'getStats']);
            Route::delete('/clear-read', [NotificationController::class, 'clearRead']);
        });
        
        // ============================================
        // MÓDULO: GESTÃO DE PESSOAS E ACESSOS
        // ============================================
        
        // ROTA PARA BUSCAR USUÁRIOS (para select de eventos)
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/colaboradores', [UserController::class, 'colaboradores']);
        Route::get('/users/search', [UserController::class, 'search']);
        
        // Colaboradores (visualização)
        Route::get('/colaboradores', [ColaboradorController::class, 'index']);
        Route::get('/colaboradores/{id}', [ColaboradorController::class, 'show']);
        Route::put('/profile/update', [UserController::class, 'updateProfile']);
        Route::post('/profile/change-password', [UserController::class, 'changePassword']);
        Route::post('/profile/upload-photo', [UserController::class, 'uploadProfilePhoto']); // ⭐ NOVO
        Route::delete('/profile/delete-photo', [UserController::class, 'deleteProfilePhoto']); // ⭐ NOVO
        // Gestão de acessos (CRUD + permissões)
        Route::middleware('can:gerenciar-acessos')->group(function () {
            // CRUD de colaboradores
            Route::post('/colaboradores', [ColaboradorController::class, 'store']);
            Route::put('/colaboradores/{colaborador}', [ColaboradorController::class, 'update']);
            Route::delete('/colaboradores/{colaborador}', [ColaboradorController::class, 'destroy']);
            
            // ⭐ CRUD de perfis (roles)
            Route::get('/perfis/permissoes-disponiveis', [PerfilController::class, 'getAvailablePermissions']); // ✅ PRIMEIRO!
            Route::post('/perfis/{perfil}/duplicate', [PerfilController::class, 'duplicate']); // ✅ ROTAS ESPECÍFICAS ANTES
            Route::post('/perfis/{perfil}/sync-permissoes', [PerfilController::class, 'syncPermissoes']);
            Route::apiResource('/perfis', PerfilController::class)->parameters(['perfis' => 'perfil']); // ✅ POR ÚLTIMO!

            // Listar permissões
            Route::get('/permissoes', [PermissaoController::class, 'index']);
            
            // ⭐ GESTÃO DE ACESSOS (PERMISSÕES AVULSAS + MULTI-TENANT)
            Route::prefix('gestao-acessos')->group(function () {
                // Permissões do usuário
                Route::get('/users/{user}/permissions', [GestaoAcessosController::class, 'getUserPermissions']);
                
                // Atribuir/Remover Roles
                Route::post('/users/{user}/roles', [GestaoAcessosController::class, 'assignRoles']);
                
                // Permissões avulsas
                Route::post('/users/{user}/grant-permission', [GestaoAcessosController::class, 'grantPermission']);
                Route::post('/users/{user}/revoke-permission', [GestaoAcessosController::class, 'revokePermission']);
                Route::post('/users/{user}/sync-direct-permissions', [GestaoAcessosController::class, 'syncDirectPermissions']);
                Route::delete('/users/{user}/clear-direct-permissions', [GestaoAcessosController::class, 'clearDirectPermissions']);
                
                // ⭐ NOVO: Multi-Tenant - Instituições com Acesso
                Route::get('/colaboradores/{colaborador}/instituicoes', [GestaoAcessosController::class, 'getColaboradorInstituicoes']);
                Route::post('/colaboradores/{colaborador}/instituicoes', [GestaoAcessosController::class, 'assignInstituicoes']);
                Route::get('/instituicoes', [GestaoAcessosController::class, 'listInstituicoes']);
                // ⭐ NOVO: Acessos por Instituição
                Route::get('/colaboradores/{colaborador}/instituicoes/{instituicao}/acessos', [GestaoAcessosController::class, 'getInstituicaoAcessos']);
                Route::post('/colaboradores/{colaborador}/instituicoes/{instituicao}/roles', [GestaoAcessosController::class, 'assignRolesToInstituicao']);
                Route::post('/colaboradores/{colaborador}/instituicoes/{instituicao}/permissions', [GestaoAcessosController::class, 'assignPermissionsToInstituicao']);
            });
        });
        
        // ============================================
        // MÓDULO: GESTÃO ACADÊMICA
        // ============================================
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

        // ============================================
        // MÓDULO: GESTÃO DE PROFESSORES / RH
        // ============================================
        Route::middleware('can:gerenciar-professores')->group(function () {
            Route::apiResource('/professor-vinculos', ProfessorVinculoController::class)->parameters(['professor-vinculos' => 'professorVinculo']);
        });

        // ============================================
        // MÓDULO: GESTÃO INSTITUCIONAL
        // ============================================
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

            // Rotas para Setores do Campus
            Route::get('/campi/{campus}/setores', [CampusSetorController::class, 'index']);
            Route::post('/campi/{campus}/setores', [CampusSetorController::class, 'store']);
            Route::put('/campi/{campus}/setores/{setorId}', [CampusSetorController::class, 'update']);
            Route::delete('/campi/{campus}/setores/{setorId}', [CampusSetorController::class, 'destroy']);

            // ============================================
            // MÓDULO: IMPORTAÇÃO EM MASSA
            // ============================================
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

            // ============================================
            // MÓDULO: DASHBOARD
            // ============================================
            Route::prefix('dashboard')->group(function () {
                Route::get('/institucional', [DashboardController::class, 'institucional']);
            });
        });


         // ============================================
        // ⭐ DASHBOARD DO COLABORADOR (FORA DO MIDDLEWARE!)
        // ============================================
        Route::get('/dashboard/colaborador', [ColaboradorDashboardController::class, 'index']);

        // ============================================
        // ⭐ IDENTIDADE VISUAL
        // ============================================
        Route::prefix('identidade-visual')->group(function () {
            Route::get('/show', [IdentidadeVisualController::class, 'show']);
            Route::post('/store', [IdentidadeVisualController::class, 'store']);
            Route::post('/upload-logo', [IdentidadeVisualController::class, 'uploadLogo']);
            Route::delete('/delete-logo', [IdentidadeVisualController::class, 'deleteLogo']);
            Route::get('/entidades', [IdentidadeVisualController::class, 'listarEntidades']);
        });

       // Logs de Auditoria
Route::prefix('audit-logs')->group(function () {
    // Listagem e estatísticas
    Route::get('/', [AuditLogController::class, 'index']);
    Route::get('/statistics', [AuditLogController::class, 'statistics']);
    
    // ⭐ EXPORTAÇÃO (ANTES das rotas com parâmetros)
    Route::get('/export/excel', [AuditLogController::class, 'exportExcel']);
    Route::get('/export/pdf', [AuditLogController::class, 'exportPdf']);
    
    // Detalhes (POR ÚLTIMO)
    Route::get('/{auditLog}', [AuditLogController::class, 'show']);
});


        // ============================================
        // MÓDULO: RELATÓRIOS
        // ============================================
        Route::apiResource('reports', ReportController::class);
        Route::post('reports/{id}/execute', [ReportController::class, 'execute']);
        Route::get('reports/{id}/export', [ReportController::class, 'export']);
        Route::get('reports/columns/{type}', [ReportController::class, 'getAvailableColumns']);
    });
});
