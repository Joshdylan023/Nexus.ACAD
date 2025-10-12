<template>
  <div class="d-flex flex-column vh-100">
    <nav class="navbar navbar-expand-md navbar-dark navbar-glass" aria-label="Navegação principal dos módulos">
      <div class="container-fluid">
        <a class="navbar-brand" href="#">Nexus.ACAD</a>
        <div class="collapse navbar-collapse">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
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
          <button @click="logout" class="btn btn-outline-light">Sair</button>
        </div>
      </div>
    </nav>

    <div class="container-fluid flex-grow-1 pt-4">
      <div class="row h-100">
        <aside class="col-md-3">
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
        
        <main class="col-md-9" style="max-height: calc(100vh - 90px); overflow-y: auto;">
          <router-view></router-view>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { Tooltip } from 'bootstrap';

const router = useRouter();
const route = useRoute();

const menuStructure = ref({
  // GESTÃO INSTITUCIONAL
  institucional: {
    name: 'Gestão Institucional',
    icon: 'bi bi-bank2',
    basePath: '/admin/institucional',
    menus: [
      {
        label: 'Grupos Educacionais',
        path: '#',
        icon: 'bi bi-collection',
        submenus: [
          { label: 'Grupos Educacionais', path: '/admin/institucional/grupos-educacionais' },
          { label: 'Setores', path: '/admin/institucional/grupos-educacionais/setores' },
        ]
      },
      {
        label: 'Mantenedora',
        path: '#',
        icon: 'bi bi-building',
        submenus: [
          { label: 'Mantenedora', path: '/admin/institucional/mantenedoras' },
          { label: 'Setores', path: '/admin/institucional/mantenedoras/setores' },
        ]
      },
      {
        label: 'Instituição',
        path: '#',
        icon: 'bi bi-bank2',
        submenus: [
          { label: 'Instituição', path: '/admin/institucional/instituicoes' },
          { label: 'Setores', path: '/admin/institucional/instituicoes/setores' },
          { label: 'Atos Regulatórios da IES', path: '/admin/institucional/instituicoes/atos-regulatorios' },
        ]
      },
      { label: 'Contrato Educacional', path: '/admin/institucional/contrato-educacional', icon: 'bi bi-file-text' },
      {
        label: 'Campus',
        path: '#',
        icon: 'bi bi-geo-alt',
        submenus: [
          { label: 'Campus', path: '/admin/institucional/campi' },
          { label: 'Setor', path: '/admin/institucional/campi/setores' },
        ]
      },
      { label: 'Setor (Catálogo)', path: '/admin/institucional/catalogo-setores', icon: 'bi bi-journal-bookmark' },
      {
        label: 'Miscelâneas',
        path: '#',
        icon: 'bi bi-layers',
        submenus: [
          { label: 'Identidade Visual', path: '/admin/institucional/miscelaneas/identidade-visual' },
          { label: 'Travas Matrícula', path: '/admin/institucional/miscelaneas/travas-matricula' },
          { label: 'Travas Renovação', path: '/admin/institucional/miscelaneas/travas-renovacao' },
          { label: 'Travas Financeiras', path: '/admin/institucional/miscelaneas/travas-financeiras' },
        ]
      },
      { label: 'NexusVision', path: '/admin/institucional/nexusvision', icon: 'bi bi-display' },
    ],
  },
  // GESTÃO ACADÊMICA
  academico: {
    name: 'Gestão Acadêmica',
    icon: 'bi bi-mortarboard-fill',
    basePath: '/admin/academico',
    menus: [
      {
        label: 'Administra Cursos',
        path: '#',
        icon: 'bi bi-kanban',
        submenus: [
          { label: 'Grandes Áreas', path: '/admin/academico/grandes-areas' },
          { label: 'Áreas de Conhecimento', path: '/admin/academico/areas-conhecimento' },
          {
            label: 'Curso',
            path: '#',
            submenus: [
              { label: 'Curso', path: '/admin/academico/cursos' },
              { label: 'Atos Regulatórios do Curso', path: '/admin/academico/cursos-atos-regulatorios' },
            ]
          },
          { label: 'Coordenador(es) de Curso', path: '/admin/academico/coordenadores-curso' },
          { label: 'Turmas', path: '/admin/academico/turmas' },
          {
            label: 'Matrizes',
            path: '#',
            submenus: [
              { label: 'Disciplinas', path: '/admin/academico/disciplinas' },
              { label: 'Matriz Curricular', path: '/admin/academico/matrizes-curriculares' },
              { label: 'Currículo', path: '/admin/academico/curriculos' },
              { label: 'Equivalência entre Currículos', path: '/admin/academico/equivalencia-curriculos' },
              { label: 'Ementas', path: '/admin/academico/ementas' },
            ]
          },
        ]
      },
      {
        label: 'Planejamento Acadêmico',
        path: '#',
        icon: 'bi bi-calendar-check',
        submenus: [
          { label: 'Dashboard', path: '/admin/academico/planejamento/dashboard' },
          { label: 'Oferta de Cursos', path: '/admin/academico/planejamento/oferta-cursos' },
          { label: 'Oferta de Disciplinas/Turmas', path: '/admin/academico/planejamento/oferta-disciplinas' },
          { label: 'Disponibilidade Docente', path: '/admin/academico/planejamento/disponibilidade-docente' },
          { label: 'Alocação de Espaço Físico', path: '/admin/academico/planejamento/alocacao-espaco' },
        ]
      },
      {
        label: 'Matrícula',
        path: '#',
        icon: 'bi bi-card-list',
        submenus: [
          { label: 'Enturmação', path: '/admin/academico/matricula/enturmacao' },
          { label: 'Equivalência de Disciplinas', path: '/admin/academico/matricula/equivalencia-disciplinas' },
        ]
      },
      {
        label: 'Alunos',
        path: '#',
        icon: 'bi bi-person-video2',
        submenus: [
          { label: 'Cadastro de Alunos', path: '/admin/academico/alunos/cadastro' },
          { label: 'Consulta Contratos Educacionais', path: '/admin/academico/alunos/contratos' },
          { label: 'Consulta Notas e Frequência por Aluno', path: '/admin/academico/alunos/notas-aluno' },
          { label: 'Consulta Notas e Frequência por Turma', path: '/admin/academico/alunos/notas-turma' },
          { label: 'Isenção/Aproveitamento de Disciplinas', path: '/admin/academico/alunos/aproveitamento' },
          { label: 'Lançamento Situação ENADE', path: '/admin/academico/alunos/enade' },
          { label: 'Gera/Remove Impedimento Renovação', path: '/admin/academico/alunos/impedimento-renovacao' },
          { label: 'Ajuste de Histórico', path: '/admin/academico/alunos/ajuste-historico' },
          { label: 'Carteirinha de Estudante', path: '/admin/academico/alunos/carteirinha' },
          { label: 'Log', path: '/admin/academico/alunos/log' },
          {
            label: 'Movimentações',
            path: '#',
            submenus: [
              { label: 'Transferência de Turno', path: '/admin/academico/alunos/movimentacoes/turno' },
              { label: 'Transferência de Campus', path: '/admin/academico/alunos/movimentacoes/campus' },
              { label: 'Transferência de Curso', path: '/admin/academico/alunos/movimentacoes/curso' },
              { label: 'Transferência Externa (Saída)', path: '/admin/academico/alunos/movimentacoes/externa' },
              { label: 'Trancamento', path: '/admin/academico/alunos/movimentacoes/trancamento' },
              { label: 'Cancelamento', path: '/admin/academico/alunos/movimentacoes/cancelamento' },
              { label: 'Desfaz Abandono', path: '/admin/academico/alunos/movimentacoes/desfaz-abandono' },
            ]
          },
          { label: 'Declarações', path: '/admin/academico/alunos/declaracoes' },
          {
            label: 'Relatórios',
            path: '#',
            submenus: [
                { label: 'Plano de Ensino', path: '/admin/academico/relatorios/plano-ensino' },
                { label: 'Histórico Escolar Graduação', path: '/admin/academico/relatorios/historico-graduacao' },
                { label: 'Histórico Escolar Mestrado', path: '/admin/academico/relatorios/historico-mestrado' },
                { label: 'Histórico Escolar Pós-Graduação', path: '/admin/academico/relatorios/historico-pos' },
                { label: 'Histórico Horas AAC', path: '/admin/academico/relatorios/historico-aac' },
                { label: 'Relação de Alunos Simples', path: '/admin/academico/relatorios/alunos-simples' },
                { label: 'Situação do Aluno no Curso', path: '/admin/academico/relatorios/situacao-aluno' },
                { label: 'Prováveis Formandos', path: '/admin/academico/relatorios/provaveis-formandos' },
                { label: 'Relação de Alunos por Situação', path: '/admin/academico/relatorios/alunos-situacao' },
                { label: 'Lista de Alunos para ENADE', path: '/admin/academico/relatorios/alunos-enade' },
                { label: 'Lista para Secretaria de Transportes', path: '/admin/academico/relatorios/alunos-transporte' },
                { label: 'Alunos Aptos a Renovar', path: '/admin/academico/relatorios/alunos-aptos-renovar' },
                { label: 'Relação de Estágios', path: '/admin/academico/relatorios/estagios' },
            ]
          },
        ]
      },
      { label: 'Editar Contrato Aluno', path: '/admin/academico/contrato-aluno', icon: 'bi bi-pencil-square' },
      {
        label: 'Documentos Digitais',
        path: '#',
        icon: 'bi bi-file-earmark-binary',
        submenus: [
            { label: 'Diploma Digital', path: '/admin/academico/documentos/diploma-digital' },
            { label: 'Histórico Escolar Digital - Final', path: '/admin/academico/documentos/historico-digital-final' },
            { label: 'Histórico Escolar Digital - Parcial', path: '/admin/academico/documentos/historico-digital-parcial' },
        ]
      },
      {
        label: 'Colação de Grau',
        path: '#',
        icon: 'bi bi-mortarboard',
        submenus: [
            { label: 'Solenidades', path: '/admin/academico/colacao/solenidades' },
            { label: 'Livro de Colação', path: '/admin/academico/colacao/livro' },
            { label: 'Lista de Formandos', path: '/admin/academico/colacao/lista-formandos' },
            { label: 'Gera/Remove Impedimento Colação', path: '/admin/academico/colacao/impedimento' },
        ]
      },
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
      {
        label: 'Requerimentos',
        path: '#',
        icon: 'bi bi-file-earmark-text',
        submenus: [
            { label: 'Novo Requerimento', path: '/admin/academico/requerimentos/novo' },
            { label: 'Consulta Requerimento', path: '/admin/academico/requerimentos/consulta' },
            { label: 'Fila de Requerimentos (Tramitador)', path: '/admin/academico/requerimentos/fila' },
            { label: 'Relatório de Requerimentos', path: '/admin/academico/requerimentos/relatorio' },
        ]
      },
      {
        label: 'Controle Acadêmico',
        path: '#',
        icon: 'bi bi-sliders',
        submenus: [
            { label: 'Pauta', path: '/admin/academico/controle/pauta' },
            { label: 'Lançamento de Frequência Turma', path: '/admin/academico/controle/frequencia' },
            { label: 'Lançamento de Nota Turma', path: '/admin/academico/controle/nota' },
            { label: 'Relatórios', path: '/admin/academico/controle/relatorios' },
        ]
      },
      {
        label: 'Atividade Acadêmica Complementar (AAC)',
        path: '#',
        icon: 'bi bi-puzzle',
        submenus: [
            { label: 'Eixo Temático', path: '/admin/academico/aac/eixo-tematico' },
            { label: 'Criar Atividade', path: '/admin/academico/aac/criar' },
            { label: 'Inscrição', path: '/admin/academico/aac/inscricao' },
            { label: 'Lançamento de Frequência a Lote', path: '/admin/academico/aac/frequencia-lote' },
            { label: 'Consulta AAC Aluno', path: '/admin/academico/aac/consulta-aluno' },
            { label: 'Consulta Atividades Cadastradas', path: '/admin/academico/aac/consulta-atividades' },
            { label: 'Relatórios AAC', path: '/admin/academico/aac/relatorios' },
        ]
      },
      {
        label: 'Atendimento',
        path: '#',
        icon: 'bi bi-headset',
        submenus: [
            { label: 'Registrar Atendimento', path: '/admin/academico/atendimento/registrar' },
            { label: 'Agendar Atendimento', path: '/admin/academico/atendimento/agendar' },
            { label: 'Consulta Atendimento', path: '/admin/academico/atendimento/consulta' },
            { label: 'Setores de Atendimento', path: '/admin/academico/atendimento/setores' },
            { label: 'Disponibiliza Agenda', path: '/admin/academico/atendimento/agenda' },
            { label: 'Relatórios', path: '/admin/academico/atendimento/relatorios' },
        ]
      },
      {
        label: 'Informações Referenciais',
        path: '#',
        icon: 'bi bi-info-circle',
        submenus: [
            { label: 'Catálogo de Documentos', path: '/admin/academico/referenciais/catalogo-documentos' },
            { label: 'Catálogo de Requerimentos', path: '/admin/academico/referenciais/catalogo-requerimentos' },
        ]
      },
      { label: 'Mensagem Portal/App Aluno', path: '/admin/academico/mensagens', icon: 'bi bi-chat-left-text' },
      {
        label: 'Pesquisa Institucional',
        path: '#',
        icon: 'bi bi-search',
        submenus: [
            { label: 'Criar Pesquisa', path: '/admin/academico/pesquisa/criar' },
            { label: 'Liberar Pesquisa', path: '/admin/academico/pesquisa/liberar' },
            { label: 'Consultar Pesquisa', path: '/admin/academico/pesquisa/consultar' },
            { label: 'Resultado Pesquisa', path: '/admin/academico/pesquisa/resultado' },
        ]
      },
    ]
  },
  // GESTÃO ESPAÇO FISICO
  espacoFisico: {
    name: 'Gestão Espaço Físico',
    icon: 'bi bi-compass',
    basePath: '/admin/espaco-fisico',
    menus: [
        { label: 'Prédio', path: '/admin/espaco-fisico/predios', icon: 'bi bi-building' },
        { label: 'Blocos e Andares', path: '/admin/espaco-fisico/blocos-andares', icon: 'bi bi-stack' },
        { label: 'Salas de Aula', path: '/admin/espaco-fisico/salas-aula', icon: 'bi bi-door-open' },
        { label: 'Laboratórios', path: '/admin/espaco-fisico/laboratorios', icon: 'bi bi-eyedropper' },
        { label: 'Auditórios', path: '/admin/espaco-fisico/auditorios', icon: 'bi bi-mic' },
        { label: 'Áreas de Convivência', path: '/admin/espaco-fisico/areas-convivencia', icon: 'bi bi-people' },
        { label: 'Espaços Administrativos', path: '/admin/espaco-fisico/espacos-administrativos', icon: 'bi bi-briefcase' },
        { label: 'Mapas', path: '/admin/espaco-fisico/mapas', icon: 'bi bi-map' },
        { label: 'Espaço Físico 360º', path: '/admin/espaco-fisico/360', icon: 'bi bi-compass' },
    ]
  },
  // GESTÃO DE PROFESSORES
  professores: {
    name: 'Gestão de Professores',
    icon: 'bi bi-person-video3',
    basePath: '/admin/professores',
    menus: [
        { label: 'Cadastro de Professor', path: '/admin/professores/cadastro', icon: 'bi bi-person-plus' },
        { label: 'Titulação Docente', path: '/admin/professores/titulacao', icon: 'bi bi-award' },
        { label: 'Frequência Docente', path: '/admin/professores/frequencia', icon: 'bi bi-calendar-check' },
        { label: 'Turmas por Professor', path: '/admin/professores/turmas', icon: 'bi bi-person-video' },
        { label: 'Eficiência Docente 360º', path: '/admin/professores/eficiencia-360', icon: 'bi bi-speedometer2' },
    ]
  },
  // GESTÃO DE ESTÁGIOS
  estagios: {
    name: 'Gestão de Estágios',
    icon: 'bi bi-briefcase-fill',
    basePath: '/admin/estagios',
    menus: [
      // Itens a serem adicionados posteriormente
    ]
  },
  // GESTÃO FINANCEIRA
  financeiro: {
    name: 'Gestão Financeira',
    icon: 'bi bi-piggy-bank-fill',
    basePath: '/admin/financeiro',
    menus: [
      // Itens a serem adicionados posteriormente
    ]
  },
  // GESTÃO DE CANDIDATOS/VESTIBULARES
  vestibulares: {
    name: 'Gestão de Candidatos/Vestibulares',
    icon: 'bi bi-person-vcard',
    basePath: '/admin/vestibulares',
    menus: [
      // Itens a serem adicionados posteriormente
    ]
  },
  // GESTÃO REGULATÓRIA
  regulatorio: {
    name: 'Gestão Regulatória',
    icon: 'bi bi-gavel',
    basePath: '/admin/regulatorio',
    menus: [
      // Itens a serem adicionados posteriormente
    ]
  },
  // SGD/GED
  sgd: {
    name: 'SGD/GED',
    icon: 'bi bi-archive-fill',
    basePath: '/admin/sgd',
    menus: [
      // Itens a serem adicionados posteriormente
    ]
  },
  // GESTÃO DE PESSOAS E ACESSOS
  'pessoas-acessos': {
    name: 'Gestão de Pessoas e Acessos',
    icon: 'bi bi-person-lock',
    basePath: '/admin/pessoas-acessos',
    menus: [
      { label: 'Colaboradores', path: '/admin/pessoas-acessos/colaboradores', icon: 'bi bi-people' },
      { label: 'Perfis', path: '/admin/pessoas-acessos/perfis', icon: 'bi bi-person-badge' },
      { label: 'Permissões', path: '/admin/pessoas-acessos/permissoes', icon: 'bi bi-key' },
    ]
  },
});

const currentModuleName = computed(() => {
  const moduleName = route.path.split('/')[2];
  return menuStructure.value[moduleName]?.name || 'Menu';
});

const currentMenuItems = computed(() => {
  const moduleName = route.path.split('/')[2];
  return menuStructure.value[moduleName]?.menus || [];
});

const logout = () => {
  localStorage.removeItem('authToken');
  router.push('/login');
};

onMounted(() => {
  // Aguarda o DOM ser atualizado para inicializar os tooltips
  nextTick(() => {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new Tooltip(tooltipTriggerEl);
    });
  });
});
</script>

<style scoped>
/* Estilos para o link ativo */
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
</style>
