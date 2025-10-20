<template>
  <div class="d-flex flex-column vh-100">
    <nav class="navbar navbar-expand-md navbar-dark navbar-glass" aria-label="Navegação principal dos módulos">
      <div class="container-fluid">
        <a class="navbar-brand" href="#">Nexus.ACAD</a>
        <div class="collapse navbar-collapse">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <!-- ⭐ DASHBOARD (PÁGINA INICIAL) -->
            <li class="nav-item">
              <router-link 
                to="/admin/dashboard" 
                class="nav-link"
                data-bs-toggle="tooltip"
                data-bs-placement="bottom"
                title="Dashboard"
                aria-label="Dashboard"
              >
                <i class="bi bi-house-door-fill" style="font-size: 1.2rem;"></i>
              </router-link>
            </li>

            <!-- MÓDULOS -->
            <li v-for="modulo in menuStructure" :key="modulo.name" class="nav-item">
              <router-link 
                :to="modulo.basePath" 
                class="nav-link"
                data-bs-toggle="tooltip"
                data-bs-placement="bottom"
                :title="modulo.name"
                :aria-label="modulo.name"
              >
                <i :class="modulo.icon" style="font-size: 1.2rem;"></i>
              </router-link>
            </li>
          </ul>

          <!-- ⭐ BARRA DE BUSCA GLOBAL (MENOR) -->
          <div class="mx-3" style="flex: 0 1 350px;">
            <button @click="openGlobalSearch" class="global-search-trigger">
              <i class="bi bi-search me-2"></i>
              <span>Buscar...</span>
              <kbd class="search-kbd-hint">Ctrl+K</kbd>
            </button>
          </div>

          <!-- ⭐ PERFIL DO USUÁRIO -->
          <div class="d-flex align-items-center gap-3">
            <!-- Foto + Nome + Matrícula (Clicável) -->
            <router-link to="/admin/minha-area" class="user-profile-link">
              <div class="d-flex align-items-center gap-2">
                <img 
                  v-if="userPhoto" 
                  :src="userPhoto" 
                  alt="Foto de perfil"
                  class="user-avatar"
                >
                <div v-else class="user-avatar-placeholder">
                  <i class="bi bi-person-fill"></i>
                </div>
                <div class="user-info">
                  <div class="user-name">{{ userName }}</div>
                  <div class="user-matricula">{{ userMatricula }}</div>
                </div>
              </div>
            </router-link>

            <!-- Notificações -->
            <NotificationCenter />

            <!-- Botão Sair -->
            <button @click="logout" class="btn btn-outline-light btn-sm">
              <i class="bi bi-box-arrow-right me-1"></i>
              Sair
            </button>
          </div>
        </div>
      </div>
    </nav>

    <div class="container-fluid flex-grow-1 pt-4">
      <div class="row h-100">
        <!-- ⭐ SIDEBAR CONDICIONAL (ESCONDE NO DASHBOARD) -->
        <aside v-if="!isDashboardRoute" class="col-md-3">
          <div class="card card-glass h-100">
            <div class="card-body">
              <h5 class="mb-3">{{ currentModuleName }}</h5>
              <ul class="nav nav-pills flex-column mb-auto">
                <li v-for="(menu, index) in currentMenuItems" :key="menu.label" class="nav-item mb-1">
                  <router-link v-if="!menu.submenus" :to="menu.path" class="nav-link text-white fw-bold">
                    <i :class="menu.icon" class="me-2"></i>{{ menu.label }}
                  </router-link>
                  
                  <a v-else
                     class="nav-link text-white fw-bold d-flex justify-content-between align-items-center"
                     data-bs-toggle="collapse"
                     :href="`#submenu-${index}`"
                     role="button"
                  >
                    <span><i :class="menu.icon" class="me-2"></i>{{ menu.label }}</span>
                    <i class="bi bi-chevron-down"></i>
                  </a>

                  <div v-if="menu.submenus" class="collapse" :id="`submenu-${index}`">
                    <ul class="nav flex-column ps-4 mt-1">
                      <li v-for="(submenu, subIndex) in menu.submenus" :key="submenu.label" class="nav-item">
                        <router-link v-if="!submenu.submenus" :to="submenu.path" class="nav-link text-white-50">{{ submenu.label }}</router-link>
                        
                        <a v-else
                          class="nav-link text-white-50 d-flex justify-content-between align-items-center"
                          data-bs-toggle="collapse"
                          :href="`#submenu-${index}-${subIndex}`"
                          role="button"
                        >
                          <span>{{ submenu.label }}</span>
                          <i class="bi bi-chevron-down"></i>
                        </a>

                        <div v-if="submenu.submenus" class="collapse" :id="`submenu-${index}-${subIndex}`">
                          <ul class="nav flex-column ps-4 mt-1">
                            <li v-for="subsubmenu in submenu.submenus" :key="subsubmenu.path" class="nav-item">
                              <router-link :to="subsubmenu.path" class="nav-link text-white-50">{{ subsubmenu.label }}</router-link>
                            </li>
                          </ul>
                        </div>
                      </li>
                    </ul>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </aside>
        
        <!-- ⭐ MAIN RESPONSIVO (FULL WIDTH NO DASHBOARD) -->
        <main :class="isDashboardRoute ? 'col-12' : 'col-md-9'" style="max-height: calc(100vh - 90px); overflow-y: auto;">
          <router-view></router-view>
        </main>
      </div>
    </div>

    <!-- ⭐ BUSCA GLOBAL (MODAL) -->
    <GlobalSearch ref="globalSearchRef" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, provide } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { Tooltip } from 'bootstrap';
import axios from 'axios';
import GlobalSearch from '@/components/GlobalSearch.vue';
import NotificationCenter from '@/components/NotificationCenter.vue';
import emitter from '@/eventBus';

const router = useRouter();
const route = useRoute();

const globalSearchRef = ref(null);

// ⭐ DADOS DO USUÁRIO
const userName = ref('Carregando...');
const userMatricula = ref('---');
const userPhoto = ref(null);

const openGlobalSearch = () => {
  globalSearchRef.value?.open();
};

provide('openGlobalSearch', openGlobalSearch);

// ⭐ BUSCAR DADOS DO USUÁRIO
const fetchUserData = async () => {
  try {
    const response = await axios.get('/api/me');
    const user = response.data.user || response.data;
    
    userName.value = user.name || 'Usuário';
    userMatricula.value = user.colaborador?.matricula_funcional || 'Sem matrícula';
    
    if (user.colaborador?.foto_registro_rh) {
      userPhoto.value = `/storage/${user.colaborador.foto_registro_rh}`;
    } else {
      userPhoto.value = null;
    }
  } catch (error) {
    console.error('Erro ao buscar dados do usuário:', error);
    userName.value = 'Usuário';
    userMatricula.value = '---';
  }
};

// ⭐ VERIFICA SE É ROTA DO DASHBOARD
const isDashboardRoute = computed(() => {
  return route.path === '/admin/dashboard';
});

const menuStructure = ref({
  institucional: {
    name: 'Gestão Institucional',
    icon: 'bi bi-bank2',
    basePath: '/admin/institucional',
    menus: [
      { label: 'Dashboard', path: '/admin/institucional/dashboard', icon: 'bi bi-speedometer2' },
      {
        label: 'Estrutura Organizacional',
        path: '#',
        icon: 'bi bi-diagram-3-fill',
        submenus: [
          { label: 'Grupos Educacionais', path: '/admin/institucional/grupos-educacionais' },
          { label: 'Mantenedoras', path: '/admin/institucional/mantenedoras' },
          { label: 'Instituições (IES)', path: '/admin/institucional/instituicoes' },
          { label: 'Campi', path: '/admin/institucional/campi' },
          { label: 'Hierarquia Visual', path: '/admin/institucional/hierarquia' },
        ]
      },
      {
        label: 'Gestão de Setores',
        path: '#',
        icon: 'bi bi-building-gear',
        submenus: [
          { label: 'Catálogo de Setores', path: '/admin/institucional/catalogo-setores' },
        ]
      },
      { label: 'Contrato Educacional', path: '/admin/institucional/contrato-educacional', icon: 'bi bi-file-text' },
      {
        label: 'Configurações',
        path: '#',
        icon: 'bi bi-sliders',
        submenus: [
          { label: 'Eventos de Sistema', path: '/admin/institucional/system-events' },
          { label: 'Importação em Massa', path: '/admin/institucional/bulk-import' },
          { label: 'Identidade Visual', path: '/admin/institucional/miscelaneas/identidade-visual' },
          { label: 'Travas de Matrícula', path: '/admin/institucional/miscelaneas/travas-matricula' },
          { label: 'Travas de Renovação', path: '/admin/institucional/miscelaneas/travas-renovacao' },
          { label: 'Travas Financeiras', path: '/admin/institucional/miscelaneas/travas-financeiras' },
        ]
      },
      { label: 'NexusVision', path: '/admin/institucional/nexusvision', icon: 'bi bi-speedometer2' },
    ],
  },
  // ✅ MÓDULO ACADÊMICO COMPLETO - 95 FUNCIONALIDADES
  academico: {
    name: 'Gestão Acadêmica',
    icon: 'bi bi-mortarboard-fill',
    basePath: '/admin/academico',
    menus: [
      // ========================================
      // 1. ADMINISTRA CURSOS
      // ========================================
      {
        label: 'Administra Cursos',
        path: '#',
        icon: 'bi bi-book',
        submenus: [
          { label: 'Grandes Áreas', path: '/admin/academico/grande-areas' },
          { label: 'Áreas de Conhecimento', path: '/admin/academico/areas-conhecimento' },
          { 
            label: 'Curso',
            path: '#',
            submenus: [
              { label: 'Catálogo de Cursos', path: '/admin/academico/catalogo-cursos' },
              { label: 'Cursos', path: '/admin/academico/cursos' },
            ]
          },
          { label: 'Coordenador(es) de Curso', path: '/admin/academico/coordenadores-curso' },
          { label: 'Turmas', path: '/admin/academico/turmas' },
          {
            label: 'Matrizes',
            path: '#',
            submenus: [
              { label: 'Disciplinas', path: '/admin/academico/disciplinas' },
              { label: 'Matriz Curricular', path: '/admin/academico/curriculos' },
              { label: 'Currículo', path: '/admin/academico/curriculos' },
              { label: 'Equivalência entre Currículos', path: '/admin/academico/equivalencia-curriculos' },
              { label: 'Ementas', path: '/admin/academico/ementas' },
            ]
          },
        ]
      },

      // ========================================
      // 2. PLANEJAMENTO ACADÊMICO
      // ========================================
      {
        label: 'Planejamento Acadêmico',
        path: '#',
        icon: 'bi bi-calendar3',
        submenus: [
          { label: 'Dashboard', path: '/admin/academico/planejamento/dashboard' },
          { label: 'Oferta de Cursos', path: '/admin/academico/planejamento/oferta-cursos' },
          { label: 'Oferta de Disciplinas/Turmas', path: '/admin/academico/planejamento/oferta-disciplinas' },
          { label: 'Disponibilidade Docente', path: '/admin/academico/planejamento/disponibilidade-docente' },
          { label: 'Alocação de Espaço Físico', path: '/admin/academico/planejamento/alocacao-espaco' },
        ]
      },

      // ========================================
      // 3. MATRÍCULA
      // ========================================
      {
        label: 'Matrícula',
        path: '#',
        icon: 'bi bi-clipboard-check',
        submenus: [
          { label: 'Enturmação', path: '/admin/academico/matricula/enturmacao' },
          { label: 'Equivalência de Disciplinas', path: '/admin/academico/matricula/equivalencia' },
        ]
      },

      // ========================================
      // 4. ALUNOS (SEÇÃO MAIS COMPLETA)
      // ========================================
      {
        label: 'Alunos',
        path: '#',
        icon: 'bi bi-people',
        submenus: [
          { label: 'Cadastro de Alunos', path: '/admin/academico/alunos' },
          { label: 'Consulta Contratos Educacionais Aluno', path: '/admin/academico/alunos/contratos' },
          { label: 'Consulta Notas (e Frequência) por Aluno', path: '/admin/academico/alunos/notas-aluno' },
          { label: 'Consulta Notas (e Frequência) por Turma', path: '/admin/academico/alunos/notas-turma' },
          { label: 'Isenção/Aproveitamento de Disciplinas', path: '/admin/academico/alunos/aproveitamento' },
          { label: 'Lançamento Situação ENADE', path: '/admin/academico/alunos/lancamento-enade' },
          { label: 'Gera/Remove Impedimento Renovação', path: '/admin/academico/alunos/impedimento-renovacao' },
          { label: 'Ajuste de Histórico', path: '/admin/academico/alunos/ajuste-historico' },
          { label: 'Carteirinha de Estudante', path: '/admin/academico/alunos/carteirinha' },
          { label: 'Log', path: '/admin/academico/alunos/log' },
          {
            label: 'Movimentações',
            path: '#',
            submenus: [
              { label: 'Transferência de Turno', path: '/admin/academico/alunos/movimentacoes/transferencia-turno' },
              { label: 'Transferência de Campus', path: '/admin/academico/alunos/movimentacoes/transferencia-campus' },
              { label: 'Transferência de Curso', path: '/admin/academico/alunos/movimentacoes/transferencia-curso' },
              { label: 'Transferência Externa (Saída)', path: '/admin/academico/alunos/movimentacoes/transferencia-externa' },
              { label: 'Trancamento', path: '/admin/academico/alunos/movimentacoes/trancamento' },
              { label: 'Cancelamento', path: '/admin/academico/alunos/movimentacoes/cancelamento' },
              { label: 'Desfaz Abandono', path: '/admin/academico/alunos/movimentacoes/desfaz-abandono' },
            ]
          },
          {
            label: 'Declarações',
            path: '#',
            submenus: [
              { label: 'Declarações Cadastradas', path: '/admin/academico/alunos/declaracoes' },
            ]
          },
          {
            label: 'Relatórios',
            path: '#',
            submenus: [
              { label: 'Plano de Ensino', path: '/admin/academico/alunos/relatorios/plano-ensino' },
              { label: 'Histórico Escolar Graduação - Simples Conferência', path: '/admin/academico/alunos/relatorios/historico-graduacao' },
              { label: 'Histórico Escolar Mestrado', path: '/admin/academico/alunos/relatorios/historico-mestrado' },
              { label: 'Histórico Escolar - Pós-Graduação', path: '/admin/academico/alunos/relatorios/historico-pos' },
              { label: 'Histórico Horas AAC', path: '/admin/academico/alunos/relatorios/historico-aac' },
              { label: 'Relação de Alunos Simples', path: '/admin/academico/alunos/relatorios/relacao-alunos' },
              { label: 'Situação do Aluno no Curso', path: '/admin/academico/alunos/relatorios/situacao-aluno' },
              { label: 'Prováveis Formandos', path: '/admin/academico/alunos/relatorios/provaveis-formandos' },
              { label: 'Relação de Alunos por Situação', path: '/admin/academico/alunos/relatorios/alunos-situacao' },
              { label: 'Lista de Alunos para ENADE', path: '/admin/academico/alunos/relatorios/alunos-enade' },
              { label: 'Lista de Alunos para Secretaria de Transportes', path: '/admin/academico/alunos/relatorios/alunos-transporte' },
              { label: 'Alunos Aptos a Renovar', path: '/admin/academico/alunos/relatorios/aptos-renovar' },
              { label: 'Relação de Estágios', path: '/admin/academico/alunos/relatorios/estagios' },
            ]
          },
        ]
      },

      // ========================================
      // 5. CONTRATO EDUCACIONAL
      // ========================================
      {
        label: 'Contrato Educacional',
        path: '#',
        icon: 'bi bi-file-text',
        submenus: [
          { label: 'Editar Contrato Aluno', path: '/admin/academico/contrato/editar' },
        ]
      },

      // ========================================
      // 6. DOCUMENTOS DIGITAIS
      // ========================================
      {
        label: 'Documentos Digitais',
        path: '#',
        icon: 'bi bi-file-earmark-medical',
        submenus: [
          { label: 'Diploma Digital', path: '/admin/academico/documentos/diploma' },
          { label: 'Histórico Escolar Digital - Final', path: '/admin/academico/documentos/historico-final' },
          { label: 'Histórico Escolar Digital - Parcial', path: '/admin/academico/documentos/historico-parcial' },
        ]
      },

      // ========================================
      // 7. COLAÇÃO DE GRAU
      // ========================================
      {
        label: 'Colação de Grau',
        path: '#',
        icon: 'bi bi-award',
        submenus: [
          { label: 'Solenidades', path: '/admin/academico/colacao/solenidades' },
          { label: 'Livro de Colação', path: '/admin/academico/colacao/livro' },
          { label: 'Lista de Formandos', path: '/admin/academico/colacao/formandos' },
          { label: 'Gera/Remove Impedimento Colação', path: '/admin/academico/colacao/impedimento' },
        ]
      },

      // ========================================
      // 8. EXPEDIÇÃO E REGISTRO DE DIPLOMAS
      // ========================================
      {
        label: 'Expedição e Registro de Diplomas',
        path: '#',
        icon: 'bi bi-patch-check',
        submenus: [
          { label: 'Preparação para Expedição', path: '/admin/academico/diplomas/preparacao' },
          { label: 'Registro de Diplomas', path: '/admin/academico/diplomas/registro' },
          { label: 'Consulta Diplomas Registrados', path: '/admin/academico/diplomas/consulta' },
        ]
      },

      // ========================================
      // 9. REQUERIMENTOS
      // ========================================
      {
        label: 'Requerimentos',
        path: '#',
        icon: 'bi bi-inbox',
        submenus: [
          { label: 'Novo Requerimento', path: '/admin/academico/requerimentos/novo' },
          { label: 'Consulta Requerimento', path: '/admin/academico/requerimentos/consulta' },
          { label: 'Fila de Requerimentos (Tramitador)', path: '/admin/academico/requerimentos/fila' },
          { label: 'Relatório de Requerimentos', path: '/admin/academico/requerimentos/relatorio' },
        ]
      },

      // ========================================
      // 10. CONTROLE ACADÊMICO
      // ========================================
      {
        label: 'Controle Acadêmico',
        path: '#',
        icon: 'bi bi-journal-check',
        submenus: [
          { label: 'Pauta', path: '/admin/academico/controle/pauta' },
          { label: 'Lançamento de Frequência Turma', path: '/admin/academico/controle/frequencia' },
          { label: 'Lançamento de Nota Turma', path: '/admin/academico/controle/notas' },
          { label: 'Relatórios', path: '/admin/academico/controle/relatorios' },
        ]
      },

      // ========================================
      // 11. ATIVIDADE ACADÊMICA COMPLEMENTAR (AAC)
      // ========================================
      {
        label: 'Atividade Acadêmica Complementar (AAC)',
        path: '#',
        icon: 'bi bi-trophy',
        submenus: [
          { label: 'Eixo Temático', path: '/admin/academico/aac/eixos' },
          { label: 'Criar Atividade', path: '/admin/academico/aac/criar' },
          { label: 'Inscrição', path: '/admin/academico/aac/inscricao' },
          { label: 'Lançamento de Frequência a Lote', path: '/admin/academico/aac/frequencia-lote' },
          { label: 'Consulta AAC Aluno', path: '/admin/academico/aac/consulta-aluno' },
          { label: 'Consulta Atividades Cadastradas', path: '/admin/academico/aac/consulta-atividades' },
          { label: 'Relatórios AAC', path: '/admin/academico/aac/relatorios' },
        ]
      },

      // ========================================
      // 12. ATENDIMENTO
      // ========================================
      {
        label: 'Atendimento',
        path: '#',
        icon: 'bi bi-headset',
        submenus: [
          { label: 'Registrar Atendimento (Avulso e Agendado)', path: '/admin/academico/atendimento/registrar' },
          { label: 'Agendar Atendimento', path: '/admin/academico/atendimento/agendar' },
          { label: 'Consulta Atendimento', path: '/admin/academico/atendimento/consulta' },
          { label: 'Setores de Atendimento', path: '/admin/academico/atendimento/setores' },
          { label: 'Disponibiliza Agenda de Atendimento', path: '/admin/academico/atendimento/agenda' },
          { label: 'Relatórios', path: '/admin/academico/atendimento/relatorios' },
        ]
      },

      // ========================================
      // 13. INFORMAÇÕES REFERENCIAIS
      // ========================================
      {
        label: 'Informações Referenciais',
        path: '#',
        icon: 'bi bi-gear-fill',
        submenus: [
          { 
            label: 'Catálogo de Documentos, Relatórios e Históricos', 
            path: '/admin/academico/informacoes-referenciais/catalogo-documentos' 
          },
          { 
            label: 'Catálogo de Requerimentos', 
            path: '/admin/academico/informacoes-referenciais/catalogo-requerimentos' 
          },
        ]
      },

      // ========================================
      // 14. MENSAGEM PORTAL/APP ALUNO
      // ========================================
      {
        label: 'Mensagem Portal/App Aluno',
        path: '/admin/academico/mensagem-portal-aluno',
        icon: 'bi bi-chat-dots',
      },

      // ========================================
      // 15. PESQUISA INSTITUCIONAL
      // ========================================
      {
        label: 'Pesquisa Institucional',
        path: '#',
        icon: 'bi bi-clipboard-data',
        submenus: [
          { label: 'Cria Pesquisa', path: '/admin/academico/pesquisa/criar' },
          { label: 'Libera Pesquisa', path: '/admin/academico/pesquisa/liberar' },
          { label: 'Consulta Pesquisa', path: '/admin/academico/pesquisa/consulta' },
          { label: 'Resultado Pesquisa', path: '/admin/academico/pesquisa/resultado' },
        ]
      },
    ]
  },
  // ⭐ MÓDULO DE ESPAÇO FÍSICO
  espacoFisico: {
    name: 'Gestão de Espaço Físico',
    icon: 'bi bi-building',
    basePath: '/admin/espaco-fisico/dashboard',
    menus: [
      { label: 'Dashboard 360°', path: '/admin/espaco-fisico/dashboard', icon: 'bi bi-pie-chart' },
      { label: 'Prédios', path: '/admin/espaco-fisico/predios', icon: 'bi bi-building' },
      { label: 'Blocos', path: '/admin/espaco-fisico/blocos', icon: 'bi bi-layers' },
      { label: 'Andares', path: '/admin/espaco-fisico/andares', icon: 'bi bi-stack' },
      { label: 'Espaços Físicos', path: '/admin/espaco-fisico/espacos', icon: 'bi bi-door-open' },
      { label: 'Reservas', path: '/admin/espaco-fisico/reservas', icon: 'bi bi-calendar-check' },
    ]
  },

  professores: {
    name: 'Gestão de Professores',
    icon: 'bi bi-person-video3',
    basePath: '/admin/professores',
    menus: []
  },

  estagios: {
    name: 'Gestão de Estágios',
    icon: 'bi bi-briefcase-fill',
    basePath: '/admin/estagios',
    menus: []
  },

  financeiro: {
    name: 'Gestão Financeira',
    icon: 'bi bi-piggy-bank-fill',
    basePath: '/admin/financeiro',
    menus: []
  },

  vestibulares: {
    name: 'Gestão de Candidatos/Vestibulares',
    icon: 'bi bi-person-vcard',
    basePath: '/admin/vestibulares',
    menus: []
  },

  regulatorio: {
    name: 'Gestão Regulatória',
    icon: 'bi bi-gavel',
    basePath: '/admin/regulatorio',
    menus: []
  },

  sgd: {
    name: 'SGD/GED',
    icon: 'bi bi-archive-fill',
    basePath: '/admin/sgd',
    menus: []
  },

  // ⭐ MÓDULO DE RELATÓRIOS
  relatorios: {
    name: 'Relatórios',
    icon: 'bi bi-graph-up',
    basePath: '/admin/relatorios',
    menus: [
      { 
        label: 'Dashboard de RH', 
        path: '/admin/relatorios/dashboard-rh', 
        icon: 'bi bi-graph-up-arrow' 
      },
      { 
        label: 'Relatórios Personalizados', 
        path: '/admin/relatorios/personalizados',
        icon: 'bi bi-file-earmark-bar-graph' 
      },
    ]
  },

  'pessoas-acessos': {
    name: 'Gestão de Pessoas e Acessos',
    icon: 'bi bi-person-lock',
    basePath: '/admin/pessoas-acessos',
    menus: [
      { 
        label: 'Minha Área', 
        path: '/admin/minha-area', 
        icon: 'bi bi-person-circle' 
      },
      { 
        label: 'Minha Equipe', 
        path: '/admin/pessoas-acessos/minha-equipe', 
        icon: 'bi bi-people-fill' 
      },
      { 
        label: 'Organograma', 
        path: '/admin/pessoas-acessos/organograma', 
        icon: 'bi bi-diagram-3' 
      },
      { 
        label: 'Aniversariantes', 
        path: '/admin/rh/aniversariantes', 
        icon: 'bi bi-cake2' 
      },
      { 
        label: 'Colaboradores', 
        path: '/admin/pessoas-acessos/colaboradores', 
        icon: 'bi bi-people' 
      },
      { 
        label: 'Perfis de Acesso', 
        path: '/admin/pessoas-acessos/perfis',
        icon: 'bi bi-shield-lock' 
      },
      { 
        label: 'Dashboard de RH', 
        path: '/admin/relatorios/dashboard-rh', 
        icon: 'bi bi-graph-up-arrow' 
      },
    ]
  },

  // ⭐ MÓDULO DE INTEGRAÇÕES
  integracoes: {
    name: 'Integrações com Sistemas de RH',
    icon: 'bi bi-link-45deg',
    basePath: '/admin/integracoes',
    menus: [
      { 
        label: 'Integrações', 
        path: '/admin/integracoes', 
        icon: 'bi bi-diagram-3' 
      },
    ]
  },

  // ⭐ MÓDULO DE LOGS
  logs: {
    name: 'Logs de Auditoria',
    icon: 'bi bi-clock-history',
    basePath: '/admin/logs',
    menus: [
      { 
        label: 'Histórico de Alterações', 
        path: '/admin/logs', 
        icon: 'bi bi-journal-text' 
      },
    ]
  },
});

const currentModuleName = computed(() => {
  const moduleName = route.meta.module;
  return menuStructure.value[moduleName]?.name || 'Menu';
});

const currentMenuItems = computed(() => {
  const moduleName = route.meta.module;
  return menuStructure.value[moduleName]?.menus || [];
});

const logout = () => {
  localStorage.removeItem('authToken');
  router.push('/login');
};

onMounted(() => {
  fetchUserData();
  emitter.on('profile-updated', fetchUserData);
  
  nextTick(() => {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new Tooltip(tooltipTriggerEl);
    });
  });
});

onUnmounted(() => {
  emitter.off('profile-updated', fetchUserData);
});
</script>

<style scoped>
.navbar-glass {
  position: relative;
  z-index: 1030;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  background: rgba(0, 0, 0, 0.4) !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.card-glass {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.nav-link.router-link-exact-active {
  background-color: rgba(255, 255, 255, 0.2) !important;
  border-radius: .25rem;
}

.nav-link.text-white-50.router-link-exact-active {
  color: #fff !important;
  background-color: rgba(255, 255, 255, 0.15) !important;
}

a[data-bs-toggle="collapse"] {
  cursor: pointer;
}

/* ================================
   BUSCA GLOBAL (MENOR)
   ================================ */
.global-search-trigger {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 10px;
  color: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  transition: all 0.3s ease;
  width: 100%;
  text-align: left;
}

.global-search-trigger:hover {
  background: rgba(255, 255, 255, 0.12);
  border-color: rgba(102, 126, 234, 0.5);
  color: rgba(255, 255, 255, 0.9);
  transform: translateY(-1px);
}

.global-search-trigger span {
  flex: 1;
  font-size: 0.875rem;
}

.search-kbd-hint {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 4px;
  padding: 0.2rem 0.4rem;
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.7);
  font-family: monospace;
}

/* ================================
   PERFIL DO USUÁRIO
   ================================ */
.user-profile-link {
  text-decoration: none;
  transition: all 0.3s ease;
  padding: 0.5rem 0.75rem;
  border-radius: 10px;
}

.user-profile-link:hover {
  background: rgba(255, 255, 255, 0.1);
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.user-avatar-placeholder {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.7);
  font-size: 1.2rem;
}

.user-info {
  display: flex;
  flex-direction: column;
  line-height: 1.2;
}

.user-name {
  color: rgba(255, 255, 255, 0.95);
  font-size: 0.875rem;
  font-weight: 600;
}

.user-matricula {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.75rem;
}
</style>

<style>
.form-select option {
  background: #1a1a2e;
  color: white;
}

.vs__search::placeholder {
  color: rgba(255, 255, 255, 0.6);
}
</style>
