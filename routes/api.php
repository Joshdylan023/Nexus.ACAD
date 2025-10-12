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

    Route::prefix('v1')->group(function () {
        
        // MÓDULO: GESTÃO INSTITUCIONAL
        Route::middleware('can:gerenciar-institucional')->group(function () {
            Route::apiResource('/grupos-educacionais', GrupoEducacionalController::class)->parameters(['grupos-educacionais' => 'grupo']);
            Route::apiResource('/mantenedoras', MantenedoraController::class)->parameters(['mantenedoras' => 'mantenedora']);
            Route::apiResource('/instituicoes', InstituicaoController::class)->parameters(['instituicoes' => 'instituicao']);
            Route::apiResource('/instituicao-atos-regulatorios', InstituicaoAtoRegulatorioController::class)->parameters(['instituicao-atos-regulatorios' => 'atoRegulatorio']);
            Route::apiResource('/campi', CampusController::class)->parameters(['campi' => 'campus']);
            Route::apiResource('/setores', SetorController::class)->parameters(['setores' => 'setor']);

            // Rotas para Setores do Grupo Educacional
            Route::get('/grupos-educacionais/{grupo}/setores', [GrupoEducacionalSetorController::class, 'index']);
            Route::post('/grupos-educacionais/{grupo}/setores', [GrupoEducacionalSetorController::class, 'store']);
            Route::put('/grupos-educacionais/{grupo}/setores/{setor}', [GrupoEducacionalSetorController::class, 'update']);
            Route::delete('/grupos-educacionais/{grupo}/setores/{setor}', [GrupoEducacionalSetorController::class, 'destroy']);

            // Rotas para Setores da Mantenedora
            Route::get('/mantenedoras/{mantenedora}/setores', [MantenedoraSetorController::class, 'index']);
            Route::post('/mantenedoras/{mantenedora}/setores', [MantenedoraSetorController::class, 'store']);
            Route::put('/mantenedoras/{mantenedora}/setores/{setor}', [MantenedoraSetorController::class, 'update']);
            Route::delete('/mantenedoras/{mantenedora}/setores/{setor}', [MantenedoraSetorController::class, 'destroy']);
            
            // Rota para listar todos os vínculos para selects
            Route::get('/setor-vinculos/all', [SetorVinculoController::class, 'all']);
            Route::apiResource('/setor-vinculos', SetorVinculoController::class)->parameters(['setor-vinculos' => 'setorVinculo']);

            Route::get('/campi/{campus}/setores', [CampusSetorController::class, 'index']);
            Route::post('/campi/{campus}/setores', [CampusSetorController::class, 'store']);
            Route::put('/campi/{campus}/setores/{setorId}', [CampusSetorController::class, 'update']);
            Route::delete('/campi/{campus}/setores/{setorId}', [CampusSetorController::class, 'destroy']);
            
            // Rotas para Setores da Instituição
            Route::get('/instituicoes/{instituicao}/setores', [InstituicaoSetorController::class, 'index']);
            Route::post('/instituicoes/{instituicao}/setores', [InstituicaoSetorController::class, 'store']);
            Route::put('/instituicao-setores/{setorVinculo}', [InstituicaoSetorController::class, 'update']);
            Route::delete('/instituicao-setores/{setorVinculo}', [InstituicaoSetorController::class, 'destroy']);
       
        });

        // MÓDULO: GESTÃO DE ACESSOS
        Route::get('/colaboradores', [ColaboradorController::class, 'index']);
        Route::get('/colaboradores/{id}', [ColaboradorController::class, 'show']);
        Route::middleware('can:gerenciar-acessos')->group(function () {
            Route::post('/colaboradores', [ColaboradorController::class, 'store']);
            Route::put('/colaboradores/{colaborador}', [ColaboradorController::class, 'update']);
            Route::delete('/colaboradores/{colaborador}', [ColaboradorController::class, 'destroy']);
            Route::apiResource('/perfis', PerfilController::class)->parameters(['perfis' => 'perfil']);
            Route::get('/permissoes', [PermissaoController::class, 'index']);
            Route::post('/perfis/{perfil}/sync-permissoes', [PerfilController::class, 'syncPermissoes']);
        });
        
        // Subgrupo para a Estrutura Acadêmica
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

        // Subgrupo para Gestão de Professores / RH
        Route::middleware('can:gerenciar-professores')->group(function () {
            Route::apiResource('/professor-vinculos', ProfessorVinculoController::class)->parameters(['professor-vinculos' => 'professorVinculo']);
            // A ROTA FOI REMOVIDA DAQUI
        });
    });
});