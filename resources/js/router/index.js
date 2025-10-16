import { createRouter, createWebHistory } from 'vue-router';

// Layout & Login
import AdminLayout from '../layouts/AdminLayout.vue';
import Login from '../views/Login.vue';
import ModuloBoasVindas from '../views/admin/shared/ModuloBoasVindas.vue';

// Gestão Institucional
import GruposEducacionais from '../views/admin/GruposEducacionais.vue';
import GrupoEducacionalSetores from '../views/admin/GrupoEducacionalSetores.vue';
import Mantenedoras from '../views/admin/Mantenedoras.vue';
import MantenedoraSetores from '../views/admin/MantenedoraSetores.vue';
import Instituicoes from '../views/admin/Instituicoes.vue';
import InstituicaoSetores from '../views/admin/InstituicaoSetores.vue';
import InstituicaoAtosRegulatorios from '../views/admin/InstituicaoAtosRegulatorios.vue';
import Campi from '../views/admin/Campi.vue';
import CampusSetores from '../views/admin/CampusSetores.vue';
import Setores from '../views/admin/Setores.vue';

// Gestão Acadêmica
import GrandeAreas from '../views/admin/GrandeAreas.vue';
import AreasConhecimento from '../views/admin/AreasConhecimento.vue';
import Cursos from '../views/admin/Cursos.vue';
import CursosAtosRegulatorios from '../views/admin/CursosAtosRegulatorios.vue';
import Disciplinas from '../views/admin/Disciplinas.vue';
import CurriculoMatriz from '../views/admin/CurriculoMatriz.vue';
import Curriculos from '../views/admin/Curriculos.vue';
import Ementas from '../views/admin/Ementas.vue';

// Gestão de Professores
import Professores from '../views/admin/Professores.vue';
import ProfessorVinculos from '../views/admin/ProfessorVinculos.vue';
import Colaboradores from '../views/admin/Colaboradores.vue';
import ProfessorFormacao from '../views/admin/ProfessorFormacao.vue';

// Gestão de Pessoas e Acessos
import ColaboradorDetalhes from '../views/admin/ColaboradorDetalhes.vue';
import ColaboradorForm from '../views/admin/ColaboradorForm.vue';
import ColaboradorAcessos from '../views/admin/ColaboradorAcessos.vue';

// Componentes de RH
import MinhaArea from '../components/Profile/MinhaArea.vue';
import MinhaEquipe from '../components/Profile/MinhaEquipe.vue';
import Organograma from '../components/Equipe/Organograma.vue';
import Aniversariantes from '../components/RH/Aniversariantes.vue';

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', name: 'login', component: Login },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true },
    children: [
      { path: '', redirect: 'dashboard' },

      // Dashboard
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('../components/Dashboard/ColaboradorDashboard.vue')
      },

      // ⭐ ANIVERSARIANTES (DENTRO DO ADMIN)
      {
        path: 'rh/aniversariantes',
        name: 'Aniversariantes',
        component: Aniversariantes,
        meta: { requiresAuth: true, title: 'Aniversariantes', module: 'pessoas-acessos' }
      },

      
      // Módulo: Gestão Institucional
      {
        path: 'institucional',
        meta: { module: 'institucional' },
        children: [
          { path: '', name: 'admin.institucional', component: ModuloBoasVindas, props: { modulo: 'Gestão Institucional' } },
          { path: 'grupos-educacionais', name: 'admin.institucional.grupos', component: GruposEducacionais },
          { path: 'grupos-educacionais/:id/setores', name: 'admin.institucional.grupo.setores', component: GrupoEducacionalSetores, props: true },
          { path: 'mantenedoras', name: 'admin.institucional.mantenedoras', component: Mantenedoras },
          { path: 'mantenedoras/:id/setores', name: 'admin.institucional.mantenedora.setores', component: MantenedoraSetores, props: true },
          { path: 'instituicoes', name: 'admin.institucional.instituicoes', component: Instituicoes },
          { path: 'instituicoes/:id/setores', name: 'admin.institucional.instituicao.setores', component: InstituicaoSetores, props: true },
          { path: 'instituicoes/:id/atos-regulatorios', name: 'admin.institucional.atos', component: InstituicaoAtosRegulatorios, props: true },
          { path: 'campi', name: 'admin.institucional.campi', component: Campi },
          { path: 'campi/:id/setores', name: 'admin.institucional.campus.setores', component: CampusSetores, props: true },
          { path: 'catalogo-setores', name: 'admin.institucional.setores', component: Setores },
          { path: 'hierarquia', name: 'HierarchyView', component: () => import('@/pages/Institucional/HierarchyView.vue'), meta: { requiresAuth: true } },
          { path: 'dashboard', name: 'DashboardInstitucional', component: () => import('@/pages/Institucional/DashboardInstitucional.vue'), meta: { requiresAuth: true } },
          { path: 'system-events', name: 'SystemEvents', component: () => import('@/pages/Institucional/SystemEvents.vue'), meta: { requiresAuth: true } },
          { path: 'bulk-import', name: 'BulkImport', component: () => import('@/pages/Institucional/BulkImport.vue'), meta: { requiresAuth: true } },
          { path: 'alertas-regulatorios', name: 'alertas-regulatorios', component: () => import('@/views/admin/AlertasRegulatorios.vue'), meta: { requiresAuth: true } },
          { path: 'miscelaneas/identidade-visual', name: 'IdentidadeVisual', component: () => import('@/views/admin/institucional/IdentidadeVisual.vue'), meta: { requiresAuth: true } },
        ],
      },

      // Módulo: Relatórios e Análises
      { path: 'reports', name: 'Reports', component: () => import('@/pages/Reports/Reports.vue'), meta: { requiresAuth: true } },

      // ⭐ MÓDULO: NOTIFICAÇÕES
      { path: 'notificacoes', name: 'Notificacoes', component: () => import('@/pages/Notificacoes/NotificationDashboard.vue'), meta: { requiresAuth: true, title: 'Central de Notificações' } },

      // Módulo: Gestão Acadêmica
      {
        path: 'academico',
        children: [
          { path: '', name: 'admin.academico', component: ModuloBoasVindas, props: { modulo: 'Gestão Acadêmica' } },
          { path: 'grande-areas', name: 'admin.academico.grandeAreas', component: GrandeAreas },
          { path: 'areas-conhecimento', name: 'admin.academico.areasConhecimento', component: AreasConhecimento },
          { path: 'cursos', name: 'admin.academico.cursos', component: Cursos },
          { path: 'cursos-atos-regulatorios', name: 'admin.academico.cursosAtos', component: CursosAtosRegulatorios },
          { path: 'disciplinas', name: 'admin.academico.disciplinas', component: Disciplinas },
          { path: 'curriculos', name: 'admin.academico.curriculos', component: Curriculos },
          { path: 'curriculos/:id/matriz', name: 'admin.academico.curriculo.matriz', component: CurriculoMatriz, props: true },
          { path: 'ementas', name: 'admin.academico.ementas', component: Ementas },
        ],
      },

      // Módulo: Gestão de Professores
      {
        path: 'professores',
        children: [
          { path: '', name: 'admin.professores', component: ModuloBoasVindas, props: { modulo: 'Gestão de Professores' } },
          { path: 'cadastro', name: 'admin.professores.cadastro', component: Professores },
          { path: 'vinculos', name: 'admin.professores.vinculos', component: ProfessorVinculos },
          { path: 'formacao/:id', name: 'admin.professores.formacao', component: ProfessorFormacao, props: true },
        ],
      },

      // Minha Área (Perfil do Usuário Logado)
      {
        path: 'minha-area',
        name: 'MinhaArea',
        component: MinhaArea,
        meta: { requiresAuth: true, title: 'Minha Área', module: 'pessoas-acessos' }
      },

      // ⭐ MÓDULO: GESTÃO DE PESSOAS E ACESSOS (COMPLETO)
      {
        path: 'pessoas-acessos',
        meta: { module: 'pessoas-acessos' },
        children: [
          { path: '', name: 'admin.pessoasAcessos', component: ModuloBoasVindas, props: { modulo: 'Gestão de Pessoas e Acessos' } },

          // Minha Equipe
          {
            path: 'minha-equipe',
            name: 'MinhaEquipe',
            component: MinhaEquipe,
            meta: { requiresAuth: true, title: 'Minha Equipe', module: 'pessoas-acessos' }
          },
          
          // ⭐ ORGANOGRAMA
          {
            path: 'organograma',
            name: 'Organograma',
            component: Organograma,
            meta: { requiresAuth: true, title: 'Organograma', module: 'pessoas-acessos' }
          },

          // Colaboradores
          { path: 'colaboradores', name: 'admin.pessoasAcessos.colaboradores', component: Colaboradores },
          { path: 'colaboradores/novo', name: 'admin.pessoasAcessos.colaboradores.novo', component: ColaboradorForm },
          { path: 'colaboradores/:id', name: 'admin.pessoasAcessos.colaboradores.detalhes', component: ColaboradorDetalhes, props: true },
          { path: 'colaboradores/:id/editar', name: 'admin.pessoasAcessos.colaboradores.editar', component: ColaboradorForm, props: true },
          { path: 'colaboradores/:id/acessos', name: 'admin.pessoasAcessos.colaboradores.acessos', component: ColaboradorAcessos, props: true },
          
          // ⭐ PERFIS DE ACESSO
          { 
            path: 'perfis', 
            name: 'admin.pessoasAcessos.perfis', 
            component: () => import('@/views/admin/Perfis.vue'),
            meta: { requiresAuth: true, permission: 'gerenciar-perfis', title: 'Perfis de Acesso' }
          },
          
          // ⭐ GESTÃO DE ACESSOS (USUÁRIOS)
          { 
            path: 'gestao-acessos', 
            name: 'admin.pessoasAcessos.gestaoAcessos', 
            component: () => import('@/views/admin/GestaoAcessos.vue'),
            meta: { requiresAuth: true, permission: 'gerenciar-acessos', title: 'Gestão de Acessos' }
          },
        ],
      },

      // ⭐ MÓDULO: LOGS DE AUDITORIA
      {
        path: 'logs',
        children: [
          { 
            path: '', 
            name: 'admin.logs.auditoria', 
            component: () => import('@/components/AuditLogs/AuditLogs.vue'),
            meta: { 
              requiresAuth: true, 
              permissionAny: ['visualizar-logs', 'gerenciar-sistema'],
              title: 'Logs de Auditoria' 
            }
          },
        ],
      },
    ],
  },

  // ⭐ PÁGINA DE ACESSO NEGADO
  {
    path: '/forbidden',
    name: 'forbidden',
    component: () => import('@/views/admin/Forbidden.vue'),
    meta: { title: 'Acesso Negado' }
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// ⭐ GUARD GLOBAL (Verificação de Autenticação e Permissões)
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');
  const user = JSON.parse(localStorage.getItem('user') || '{}');

  // 1️⃣ Verificar se a rota requer autenticação
  if (to.meta.requiresAuth && !token) {
    return next('/login');
  }

  // 2️⃣ Verificar permissão específica da rota
  if (to.meta.permission) {
    const permissions = user.permissions || [];
    if (!permissions.includes(to.meta.permission)) {
      return next({ 
        name: 'forbidden',
        query: { 
          message: 'Você não tem permissão para acessar esta página.',
          permission: to.meta.permission
        }
      });
    }
  }

  // 3️⃣ Verificar se o usuário tem alguma das permissões (OR)
  if (to.meta.permissionAny) {
    const permissions = user.permissions || [];
    const hasPermission = to.meta.permissionAny.some(p => permissions.includes(p));
    if (!hasPermission) {
      return next({ 
        name: 'forbidden',
        query: { message: 'Você não tem permissão para acessar esta página.' }
      });
    }
  }

  // 4️⃣ Verificar se o usuário tem todas as permissões (AND)
  if (to.meta.permissionAll) {
    const permissions = user.permissions || [];
    const hasAllPermissions = to.meta.permissionAll.every(p => permissions.includes(p));
    if (!hasAllPermissions) {
      return next({ 
        name: 'forbidden',
        query: { message: 'Você não tem todas as permissões necessárias.' }
      });
    }
  }

  next();
});

export default router;
