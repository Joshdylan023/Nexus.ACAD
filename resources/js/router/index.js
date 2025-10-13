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

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', name: 'login', component: Login },
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      { path: '', redirect: '/admin/institucional' },
      // Módulo: Gestão Institucional
      {
        path: 'institucional',
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
        ],
      },

      // Hierarquia Institucional

{
  path: '/admin/institucional/hierarquia',
  name: 'HierarchyView',
  component: () => import('@/pages/Institucional/HierarchyView.vue'),
  meta: { requiresAuth: true }
},

// Dashboard Institucional

{
  path: '/admin/institucional/dashboard',
  name: 'DashboardInstitucional',
  component: () => import('@/pages/Institucional/DashboardInstitucional.vue'),
  meta: { requiresAuth: true }
},

{
  path: '/admin/institucional/system-events',
  name: 'SystemEvents',
  component: () => import('@/pages/Institucional/SystemEvents.vue'),
  meta: { requiresAuth: true }
},
{
  path: '/admin/institucional/bulk-import',
  name: 'BulkImport',
  component: () => import('@/pages/Institucional/BulkImport.vue'),
  meta: { requiresAuth: true }
},

      // Módulo: Gestão Acadêmica
      {
        path: 'academico',
        children: [
            // ... (omitted for brevity)
        ]
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
      // Módulo: Gestão de Pessoas e Acessos
      {
        path: 'pessoas-acessos',
        children: [
          { path: '', name: 'admin.pessoasAcessos', component: ModuloBoasVindas, props: { modulo: 'Gestão de Pessoas e Acessos' } },
          { path: 'colaboradores', name: 'admin.pessoasAcessos.colaboradores', component: Colaboradores },
          { path: 'colaboradores/novo', name: 'admin.pessoasAcessos.colaboradores.novo', component: ColaboradorForm },
          { path: 'colaboradores/:id', name: 'admin.pessoasAcessos.colaboradores.detalhes', component: ColaboradorDetalhes, props: true },
          { path: 'colaboradores/:id/editar', name: 'admin.pessoasAcessos.colaboradores.editar', component: ColaboradorForm, props: true },
          { path: 'colaboradores/:id/acessos', name: 'admin.pessoasAcessos.colaboradores.acessos', component: ColaboradorAcessos, props: true },
        ],
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;