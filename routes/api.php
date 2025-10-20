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
use App\Http\Controllers\Api\V1\RelatorioController;
use App\Http\Controllers\Api\V1\IntegrationController;
use App\Http\Controllers\Api\V1\PredioController;
use App\Http\Controllers\Api\V1\BlocoController;
use App\Http\Controllers\Api\V1\AndarController;
use App\Http\Controllers\Api\V1\EspacoFisicoController;
use App\Http\Controllers\Api\V1\ReservaEspacoController;
use App\Http\Controllers\Api\V1\DashboardEspacosController;
use App\Http\Controllers\Api\V1\RelatoriosReservasController;
use App\Http\Controllers\Api\V1\CoordenadorCursoController;
use App\Http\Controllers\Api\V1\CatalogoCursoController;

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

        // ============================================
        // ⭐ ROTAS PÚBLICAS (SEM PERMISSÕES) - PARA FILTROS E DASHBOARDS
        // ============================================
        
        // BUSCA GLOBAL
        Route::get('search', [GlobalSearchController::class, 'search']);
        
        // ROTAS PARA FILTROS DE DASHBOARD (HIERARQUIA INSTITUCIONAL)
        Route::get('/grupos-educacionais', [GrupoEducacionalController::class, 'index']);
        Route::get('/grupos-educacionais/{id}/mantenedoras', [GrupoEducacionalController::class, 'mantenedoras']);
        Route::get('/mantenedoras/{id}/instituicoes', [MantenedoraController::class, 'instituicoes']);
        Route::get('/instituicoes/{id}/campi', [InstituicaoController::class, 'campi']);
        Route::get('/campi/{id}/setores', [CampusController::class, 'setores']);
        Route::get('/setores', [SetorController::class, 'index']);
        
        // COLABORADORES - LISTAGEM E CARGOS (PARA FILTROS)
        Route::get('/colaboradores/cargos-unicos', [ColaboradorController::class, 'cargosUnicos']);
        Route::get('/colaboradores', [ColaboradorController::class, 'index']);
        Route::get('/colaboradores/{id}', [ColaboradorController::class, 'show']);
        
        // USERS - PARA BUSCA E SELEÇÃO
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/colaboradores', [UserController::class, 'colaboradores']);
        Route::get('/users/search', [UserController::class, 'search']);
        
        // DASHBOARD DO COLABORADOR (SEM PERMISSÃO)
        Route::get('/dashboard/colaborador', [ColaboradorDashboardController::class, 'index']);
        
        // PERFIL DO USUÁRIO
        Route::put('/profile/update', [UserController::class, 'updateProfile']);
        Route::post('/profile/change-password', [UserController::class, 'changePassword']);
        Route::post('/profile/upload-photo', [UserController::class, 'uploadProfilePhoto']);
        Route::delete('/profile/delete-photo', [UserController::class, 'deleteProfilePhoto']);
        
        // MINHA EQUIPE
        Route::get('/minha-equipe', [ColaboradorController::class, 'minhaEquipe']);
        Route::get('/minha-equipe/stats', [ColaboradorController::class, 'minhaEquipeStats']);
        Route::get('/minha-equipe/export/excel', [ColaboradorController::class, 'exportarEquipeExcel']);
        Route::get('/minha-equipe/export/pdf', [ColaboradorController::class, 'exportarEquipePDF']);
        
        // ORGANOGRAMA
        Route::get('/organograma', [ColaboradorController::class, 'organograma']);
        
        // ⭐ ANIVERSARIANTES
        Route::prefix('aniversariantes')->group(function () {
            Route::get('/', [AniversariantesController::class, 'index']);
            Route::get('/stats', [AniversariantesController::class, 'stats']);
            Route::get('/proximos', [AniversariantesController::class, 'proximos']);
        });
        
        // ⭐ IDENTIDADE VISUAL
        Route::prefix('identidade-visual')->group(function () {
            Route::get('/show', [IdentidadeVisualController::class, 'show']);
            Route::post('/store', [IdentidadeVisualController::class, 'store']);
            Route::post('/upload-logo', [IdentidadeVisualController::class, 'uploadLogo']);
            Route::delete('/delete-logo', [IdentidadeVisualController::class, 'deleteLogo']);
            Route::get('/entidades', [IdentidadeVisualController::class, 'listarEntidades']);
        });
        
        // ============================================
        // ⭐ ROTAS DE CONSULTA ACADÊMICA (SEM PERMISSÃO DE EDIÇÃO)
        // ============================================
        
        // Grandes Áreas e Áreas de Conhecimento (CONSULTA)
        Route::get('/grandes-areas', [GrandeAreaConhecimentoController::class, 'index']);
        Route::get('/grandes-areas/{grandeAreaConhecimento}', [GrandeAreaConhecimentoController::class, 'show']);
        Route::get('/areas-conhecimento', [AreaConhecimentoController::class, 'index']);
        Route::get('/areas-conhecimento/{areaConhecimento}', [AreaConhecimentoController::class, 'show']);
        
        // Cursos (CONSULTA)
        Route::get('/cursos', [CursoController::class, 'index']);
        Route::get('/cursos/{curso}', [CursoController::class, 'show']);
        
        // Catálogo de Cursos (CONSULTA)
        Route::get('/catalogo-cursos', [CatalogoCursoController::class, 'index']);
        Route::get('/catalogo-cursos/{catalogoCurso}', [CatalogoCursoController::class, 'show']);
        Route::get('/catalogo-cursos/{catalogoCurso}/instituicoes-vinculadas', [CatalogoCursoController::class, 'instituicoesVinculadas']);
        Route::get('/catalogo-cursos/estatisticas/geral', [CatalogoCursoController::class, 'estatisticas']);
        
        // ✅ EXPORTAÇÕES (DISPONÍVEL PARA TODOS OS USUÁRIOS AUTENTICADOS)
        Route::get('/cursos/export/excel', [CursoController::class, 'exportExcel']);
        Route::get('/cursos/export/pdf', [CursoController::class, 'exportPDF']);
        Route::get('/catalogo-cursos/export/excel', [CatalogoCursoController::class, 'exportExcel']);
        Route::get('/catalogo-cursos/export/pdf', [CatalogoCursoController::class, 'exportPDF']);
        
        // Exportar Atos Regulatórios
        Route::get('cursos/{curso}/atos-regulatorios/export/excel', [CursosAtoRegulatorioController::class, 'exportExcel']);
        Route::get('cursos/{curso}/atos-regulatorios/export/pdf', [CursosAtoRegulatorioController::class, 'exportPDF']);
            
        // ✅ COORDENADORES DE CURSO - ROTAS ESPECÍFICAS ANTES DOS PARÂMETROS {id}
        Route::get('/coordenadores-curso/dashboard', [CoordenadorCursoController::class, 'dashboard']);
        Route::post('/coordenadores-curso/sincronizar-titulares', [CoordenadorCursoController::class, 'sincronizarTitulares']);
        Route::get('/coordenadores-curso/curso/{cursoId}/historico', [CoordenadorCursoController::class, 'historicoCurso']);
        Route::get('/coordenadores-curso/curso/{cursoId}', [CoordenadorCursoController::class, 'porCurso']);

        // Coordenadores de Curso (CONSULTA)
        Route::get('/coordenadores-curso', [CoordenadorCursoController::class, 'index']);
        Route::get('/coordenadores-curso/{id}', [CoordenadorCursoController::class, 'show']);
        Route::get('/coordenadores-curso/curso/{cursoId}', [CoordenadorCursoController::class, 'porCurso']);
        
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
        // MÓDULO: LOGS DE AUDITORIA
        // ============================================
        Route::prefix('audit-logs')->group(function () {
            Route::get('/', [AuditLogController::class, 'index']);
            Route::get('/statistics', [AuditLogController::class, 'statistics']);
            Route::get('/export/excel', [AuditLogController::class, 'exportExcel']);
            Route::get('/export/pdf', [AuditLogController::class, 'exportPdf']);
            Route::get('/{auditLog}', [AuditLogController::class, 'show']);
        });
        
        // ============================================
        // MÓDULO: RELATÓRIOS DE RH
        // ============================================
        Route::get('/relatorios/dashboard-rh', [RelatorioController::class, 'dashboardRH']);
        Route::get('/relatorios/dashboard-rh/export', [RelatorioController::class, 'exportarDashboard']);
        Route::get('/relatorios/colaboradores-pendentes', [RelatorioController::class, 'colaboradoresPendentes']);
        
        // ============================================
        // MÓDULO: INTEGRAÇÕES COM SISTEMAS DE RH
        // ============================================
        Route::prefix('hr/integrations')->group(function () {
            Route::get('/', [IntegrationController::class, 'index']);
            Route::post('/', [IntegrationController::class, 'store']);
            Route::get('/providers', [IntegrationController::class, 'providers']);
            Route::get('/providers/{provider}/config', [IntegrationController::class, 'configFields']);
            
            Route::get('/{integration}', [IntegrationController::class, 'show']);
            Route::put('/{integration}', [IntegrationController::class, 'update']);
            Route::delete('/{integration}', [IntegrationController::class, 'destroy']);
            
            Route::post('/{integration}/test', [IntegrationController::class, 'testConnection']);
            Route::post('/{integration}/sync', [IntegrationController::class, 'sync']);
            Route::post('/{integration}/toggle', [IntegrationController::class, 'toggle']);
            Route::put('/{integration}/schedule', [IntegrationController::class, 'updateSchedule']);
            Route::get('/{integration}/sample-data', [IntegrationController::class, 'sampleData']);
            
            Route::get('/{integration}/logs', [IntegrationController::class, 'logs']);
            Route::get('/{integration}/logs/{syncLog}', [IntegrationController::class, 'logDetails']);
            Route::get('/{integration}/stats', [IntegrationController::class, 'stats']);
            
            Route::get('/{integration}/errors', [IntegrationController::class, 'errors']);
            Route::post('/errors/{error}/resolve', [IntegrationController::class, 'resolveError']);
        });
        
        // ============================================
        // ✅ MÓDULO: GESTÃO DE ESPAÇO FÍSICO
        // ============================================

        // 🔓 ROTAS PÚBLICAS DE ESPAÇOS FÍSICOS (PARA CONSULTA E FILTROS)
        Route::prefix('espacos-fisicos')->group(function () {
            Route::get('/', [EspacoFisicoController::class, 'index']);
            Route::get('/estatisticas', [EspacoFisicoController::class, 'estatisticas']);
            Route::get('/exportar-pdf', [EspacoFisicoController::class, 'exportarPdf']);
            Route::get('/exportar-excel', [EspacoFisicoController::class, 'exportarExcel']);
            Route::get('/{id}', [EspacoFisicoController::class, 'show']);
            Route::post('/{id}/verificar-disponibilidade', [EspacoFisicoController::class, 'verificarDisponibilidade']);
        });

        // 🔓 ROTAS DE HIERARQUIA (PÚBLICAS PARA FILTROS)
        Route::prefix('predios')->group(function () {
            Route::get('/', [PredioController::class, 'index']);
            Route::get('/estatisticas', [PredioController::class, 'estatisticas']);
            Route::get('/exportar-pdf', [PredioController::class, 'exportarPdf']);
            Route::get('/{id}', [PredioController::class, 'show']);
        });

        Route::prefix('blocos')->group(function () {
            Route::get('/', [BlocoController::class, 'index']);
            Route::get('/exportar-pdf', [BlocoController::class, 'exportarPdf']);
            Route::get('/{id}', [BlocoController::class, 'show']);
        });

        Route::prefix('andares')->group(function () {
            Route::get('/', [AndarController::class, 'index']);
            Route::get('/exportar-pdf', [AndarController::class, 'exportarPdf']);
            Route::get('/{id}', [AndarController::class, 'show']);
        });

        Route::get('/campi/{campus}/predios', [PredioController::class, 'porCampus']);
        Route::get('/predios/{predio}/blocos', [BlocoController::class, 'porPredio']);
        Route::get('/blocos/{bloco}/andares', [AndarController::class, 'porBloco']);

        // 🔓 ROTAS DE RESERVAS (ACESSO BÁSICO PARA TODOS)
        Route::prefix('reservas-espacos')->group(function () {
            Route::get('/', [ReservaEspacoController::class, 'index']);
            Route::get('/estatisticas', [ReservaEspacoController::class, 'estatisticas']);
            Route::get('/calendario', [ReservaEspacoController::class, 'calendario']);
            Route::get('/exportar-pdf', [ReservaEspacoController::class, 'exportarPdf']);
            Route::get('/{id}', [ReservaEspacoController::class, 'show']);
            
            Route::post('/', [ReservaEspacoController::class, 'store']);
            Route::put('/{id}', [ReservaEspacoController::class, 'update']);
            Route::delete('/{id}', [ReservaEspacoController::class, 'destroy']);
            Route::post('/{id}/cancelar', [ReservaEspacoController::class, 'cancelar']);
            
            Route::post('/{id}/aprovar', [ReservaEspacoController::class, 'aprovar'])->middleware('can:aprovar-reservas');
            Route::post('/{id}/rejeitar', [ReservaEspacoController::class, 'rejeitar'])->middleware('can:aprovar-reservas');
        });

        // 🔒 ROTAS PROTEGIDAS DE GESTÃO DE ESPAÇOS FÍSICOS
        Route::middleware('can:gerenciar-espacos-fisicos')->group(function () {
            Route::prefix('predios')->group(function () {
                Route::post('/', [PredioController::class, 'store']);
                Route::put('/{id}', [PredioController::class, 'update']);
                Route::delete('/{id}', [PredioController::class, 'destroy']);
            });

            Route::prefix('blocos')->group(function () {
                Route::post('/', [BlocoController::class, 'store']);
                Route::put('/{id}', [BlocoController::class, 'update']);
                Route::delete('/{id}', [BlocoController::class, 'destroy']);
            });

            Route::prefix('andares')->group(function () {
                Route::post('/', [AndarController::class, 'store']);
                Route::put('/{id}', [AndarController::class, 'update']);
                Route::delete('/{id}', [AndarController::class, 'destroy']);
            });

            Route::prefix('espacos-fisicos')->group(function () {
                Route::post('/', [EspacoFisicoController::class, 'store']);
                Route::put('/{id}', [EspacoFisicoController::class, 'update']);
                Route::delete('/{id}', [EspacoFisicoController::class, 'destroy']);
            });
        });

        // ⭐ DASHBOARD 360° E RELATÓRIOS DE ESPAÇOS FÍSICOS
        Route::get('dashboard-espacos', [DashboardEspacosController::class, 'index']);

        Route::prefix('relatorios/reservas')->group(function () {
            Route::get('pdf', [RelatoriosReservasController::class, 'exportarPDF']);
            Route::get('excel', [RelatoriosReservasController::class, 'exportarExcel']);
            Route::get('ocupacao', [RelatoriosReservasController::class, 'relatorioOcupacao']);
            Route::get('solicitante', [RelatoriosReservasController::class, 'relatorioPorSolicitante']);
        });
        
        // ============================================
        // MÓDULO: GESTÃO DE PESSOAS E ACESSOS (PROTEGIDO)
        // ============================================
        Route::middleware('can:gerenciar-acessos')->group(function () {
            Route::post('/colaboradores', [ColaboradorController::class, 'store']);
            Route::put('/colaboradores/{colaborador}', [ColaboradorController::class, 'update']);
            Route::delete('/colaboradores/{colaborador}', [ColaboradorController::class, 'destroy']);
            
            Route::get('/perfis/permissoes-disponiveis', [PerfilController::class, 'getAvailablePermissions']);
            Route::post('/perfis/{perfil}/duplicate', [PerfilController::class, 'duplicate']);
            Route::post('/perfis/{perfil}/sync-permissoes', [PerfilController::class, 'syncPermissoes']);
            Route::apiResource('/perfis', PerfilController::class)->parameters(['perfis' => 'perfil']);

            Route::get('/permissoes', [PermissaoController::class, 'index']);
            
            Route::prefix('gestao-acessos')->group(function () {
                Route::get('/users/{user}/permissions', [GestaoAcessosController::class, 'getUserPermissions']);
                Route::post('/users/{user}/roles', [GestaoAcessosController::class, 'assignRoles']);
                Route::post('/users/{user}/grant-permission', [GestaoAcessosController::class, 'grantPermission']);
                Route::post('/users/{user}/revoke-permission', [GestaoAcessosController::class, 'revokePermission']);
                Route::post('/users/{user}/sync-direct-permissions', [GestaoAcessosController::class, 'syncDirectPermissions']);
                Route::delete('/users/{user}/clear-direct-permissions', [GestaoAcessosController::class, 'clearDirectPermissions']);
                Route::get('/colaboradores/{colaborador}/instituicoes', [GestaoAcessosController::class, 'getColaboradorInstituicoes']);
                Route::post('/colaboradores/{colaborador}/instituicoes', [GestaoAcessosController::class, 'assignInstituicoes']);
                Route::get('/instituicoes', [GestaoAcessosController::class, 'listInstituicoes']);
                Route::get('/colaboradores/{colaborador}/instituicoes/{instituicao}/acessos', [GestaoAcessosController::class, 'getInstituicaoAcessos']);
                Route::post('/colaboradores/{colaborador}/instituicoes/{instituicao}/roles', [GestaoAcessosController::class, 'assignRolesToInstituicao']);
                Route::post('/colaboradores/{colaborador}/instituicoes/{instituicao}/permissions', [GestaoAcessosController::class, 'assignPermissionsToInstituicao']);
            });
        });
        
        // ============================================
        // MÓDULO: GESTÃO ACADÊMICA (PROTEGIDO)
        // ============================================
        Route::middleware('can:gerenciar-academico')->group(function () {
            
            // ✅ CRUD GRANDES ÁREAS E ÁREAS DE CONHECIMENTO
            Route::post('/grandes-areas', [GrandeAreaConhecimentoController::class, 'store']);
            Route::put('/grandes-areas/{grandeAreaConhecimento}', [GrandeAreaConhecimentoController::class, 'update']);
            Route::delete('/grandes-areas/{grandeAreaConhecimento}', [GrandeAreaConhecimentoController::class, 'destroy']);
            
            Route::post('/areas-conhecimento', [AreaConhecimentoController::class, 'store']);
            Route::put('/areas-conhecimento/{areaConhecimento}', [AreaConhecimentoController::class, 'update']);
            Route::delete('/areas-conhecimento/{areaConhecimento}', [AreaConhecimentoController::class, 'destroy']);
            
            // ✅ CATÁLOGO DE CURSOS (CRUD)
            Route::post('/catalogo-cursos', [CatalogoCursoController::class, 'store']);
            Route::put('/catalogo-cursos/{catalogoCurso}', [CatalogoCursoController::class, 'update']);
            Route::delete('/catalogo-cursos/{catalogoCurso}', [CatalogoCursoController::class, 'destroy']);
            Route::post('/catalogo-cursos/{catalogoCurso}/duplicar', [CatalogoCursoController::class, 'duplicar']);
            Route::post('/catalogo-cursos/{catalogoCurso}/sincronizar', [CatalogoCursoController::class, 'sincronizarComCursos']);
            
            // ✅ CURSOS (CRUD)
            Route::post('/cursos', [CursoController::class, 'store']);
            Route::put('/cursos/{curso}', [CursoController::class, 'update']);
            Route::delete('/cursos/{curso}', [CursoController::class, 'destroy']);
            Route::post('/cursos/{curso}/sincronizar-com-catalogo', [CursoController::class, 'sincronizarComCatalogo']);
            Route::get('/cursos/catalogo-disponiveis/list', [CursoController::class, 'catalogoDisponiveis']);
            
            // ✅ ATOS REGULATÓRIOS
            Route::apiResource('/cursos-atos-regulatorios', CursosAtoRegulatorioController::class)
                ->parameters(['cursos-atos-regulatorios' => 'cursosAtoRegulatorio']);

            // ✅ DISCIPLINAS, EMENTAS E CURRÍCULOS
            Route::apiResource('/disciplinas', DisciplinaController::class)
                ->parameters(['disciplinas' => 'disciplina']);
            
            Route::apiResource('/ementas', EmentaController::class)
                ->parameters(['ementas' => 'ementa']);
            
            Route::apiResource('/curriculos', CurriculoController::class)
                ->parameters(['curriculos' => 'curriculo']);
            
            Route::get('/curriculos-disciplinas', [CurriculoDisciplinaController::class, 'index']);
            Route::post('/curriculos-disciplinas', [CurriculoDisciplinaController::class, 'store']);
            Route::delete('/curriculos-disciplinas/{curriculoDisciplina}', [CurriculoDisciplinaController::class, 'destroy']);
            
            // ✅ COORDENADORES DE CURSO (CRUD)
            Route::post('/coordenadores-curso', [CoordenadorCursoController::class, 'store']);
            Route::put('/coordenadores-curso/{id}', [CoordenadorCursoController::class, 'update']);
            Route::delete('/coordenadores-curso/{id}', [CoordenadorCursoController::class, 'destroy']);
        });

        // ============================================
        // MÓDULO: GESTÃO DE PROFESSORES / RH
        // ============================================
        Route::middleware('can:gerenciar-professores')->group(function () {
            Route::apiResource('/professor-vinculos', ProfessorVinculoController::class)
                ->parameters(['professor-vinculos' => 'professorVinculo']);
        });

        // ============================================
        // MÓDULO: GESTÃO INSTITUCIONAL
        // ============================================
        Route::middleware('can:gerenciar-institucional')->group(function () {
            
            Route::post('/grupos-educacionais', [GrupoEducacionalController::class, 'store']);
            Route::get('/grupos-educacionais/{grupo}', [GrupoEducacionalController::class, 'show']);
            Route::put('/grupos-educacionais/{grupo}', [GrupoEducacionalController::class, 'update']);
            Route::delete('/grupos-educacionais/{grupo}', [GrupoEducacionalController::class, 'destroy']);
            
            Route::get('/mantenedoras', [MantenedoraController::class, 'index']);
            Route::post('/mantenedoras', [MantenedoraController::class, 'store']);
            Route::get('/mantenedoras/{mantenedora}', [MantenedoraController::class, 'show']);
            Route::put('/mantenedoras/{mantenedora}', [MantenedoraController::class, 'update']);
            Route::delete('/mantenedoras/{mantenedora}', [MantenedoraController::class, 'destroy']);
            
            Route::get('/instituicoes/export', [InstituicaoController::class, 'export']);
            Route::get('/instituicoes', [InstituicaoController::class, 'index']);
            Route::post('/instituicoes', [InstituicaoController::class, 'store']);
            Route::get('/instituicoes/{instituicao}', [InstituicaoController::class, 'show']);
            Route::put('/instituicoes/{instituicao}', [InstituicaoController::class, 'update']);
            Route::delete('/instituicoes/{instituicao}', [InstituicaoController::class, 'destroy']);
            
            Route::get('/atos-regulatorios/alertas', [InstituicaoAtoRegulatorioController::class, 'alertas']);
            Route::apiResource('/instituicao-atos-regulatorios', InstituicaoAtoRegulatorioController::class)
                ->parameters(['instituicao-atos-regulatorios' => 'atoRegulatorio']);
            
            Route::get('/campi', [CampusController::class, 'index']);
            Route::post('/campi', [CampusController::class, 'store']);
            Route::get('/campi/{campus}', [CampusController::class, 'show']);
            Route::put('/campi/{campus}', [CampusController::class, 'update']);
            Route::delete('/campi/{campus}', [CampusController::class, 'destroy']);
            
            Route::post('/setores', [SetorController::class, 'store']);
            Route::get('/setores/{setor}', [SetorController::class, 'show']);
            Route::put('/setores/{setor}', [SetorController::class, 'update']);
            Route::delete('/setores/{setor}', [SetorController::class, 'destroy']);
            
            Route::get('/system-events/current', [SystemEventController::class, 'current']);
            Route::post('/system-events/{id}/activate', [SystemEventController::class, 'activate']);
            Route::post('/system-events/{id}/deactivate', [SystemEventController::class, 'deactivate']);
            Route::apiResource('/system-events', SystemEventController::class);

            Route::get('/grupos-educacionais/{grupoEducacional}/setores', [GrupoEducacionalSetorController::class, 'index']);
            Route::post('/grupos-educacionais/{grupoEducacional}/setores', [GrupoEducacionalSetorController::class, 'store']);
            Route::put('/grupos-educacionais/{grupoEducacional}/setores/{setorId}', [GrupoEducacionalSetorController::class, 'update']);
            Route::delete('/grupos-educacionais/{grupoEducacional}/setores/{setorId}', [GrupoEducacionalSetorController::class, 'destroy']);

            Route::get('/mantenedoras/{mantenedora}/setores', [MantenedoraSetorController::class, 'index']);
            Route::post('/mantenedoras/{mantenedora}/setores', [MantenedoraSetorController::class, 'store']);
            Route::put('/mantenedoras/{mantenedora}/setores/{setorId}', [MantenedoraSetorController::class, 'update']);
            Route::delete('/mantenedoras/{mantenedora}/setores/{setorId}', [MantenedoraSetorController::class, 'destroy']);

            Route::get('/instituicoes/{instituicao}/setores', [InstituicaoSetorController::class, 'index']);
            Route::post('/instituicoes/{instituicao}/setores', [InstituicaoSetorController::class, 'store']);
            Route::put('/instituicoes/{instituicao}/setores/{setorId}', [InstituicaoSetorController::class, 'update']);
            Route::delete('/instituicoes/{instituicao}/setores/{setorId}', [InstituicaoSetorController::class, 'destroy']);

            Route::get('/campi/{campus}/setores', [CampusSetorController::class, 'index']);
            Route::post('/campi/{campus}/setores', [CampusSetorController::class, 'store']);
            Route::put('/campi/{campus}/setores/{setorId}', [CampusSetorController::class, 'update']);
            Route::delete('/campi/{campus}/setores/{setorId}', [CampusSetorController::class, 'destroy']);

            Route::prefix('imports')->group(function () {
                Route::get('/templates/list', [ImportTemplateController::class, 'list']);
                Route::get('/templates/download/{type}', [ImportTemplateController::class, 'download']);
                
                Route::get('/', [ImportController::class, 'index']);
                Route::post('/preview', [ImportController::class, 'preview']);
                Route::post('/import', [ImportController::class, 'import']);
                
                Route::get('/{id}', [ImportController::class, 'show']);
            });

            Route::prefix('dashboard')->group(function () {
                Route::get('/institucional', [DashboardController::class, 'institucional']);
            });
        });

        // ============================================
        // MÓDULO: RELATÓRIOS PERSONALIZADOS
        // ============================================
        Route::prefix('reports')->group(function () {
            Route::get('/', [ReportController::class, 'index']);
            Route::get('/columns/{type}', [ReportController::class, 'getAvailableColumns']);
            
            Route::post('/', [ReportController::class, 'store']);
            Route::get('/{id}', [ReportController::class, 'show']);
            Route::put('/{id}', [ReportController::class, 'update']);
            Route::delete('/{id}', [ReportController::class, 'destroy']);
            
            Route::post('/{id}/execute', [ReportController::class, 'execute']);
            Route::get('/{id}/export', [ReportController::class, 'export']);
        });

    }); // FIM DO GRUPO V1
}); // FIM DO MIDDLEWARE AUTH:SANCTUM
