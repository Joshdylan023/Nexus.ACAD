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

          <!-- Barra de Busca Global -->
          <div class="mx-3" style="flex: 1; max-width: 500px;">
            <button @click="openGlobalSearch" class="global-search-trigger">
              <i class="bi bi-search me-2"></i>
              <span>Buscar em todo sistema...</span>
              <kbd class="search-kbd-hint">Ctrl+K</kbd>
            </button>
          </div>

          <!-- Notificações -->
          <NotificationCenter class="me-3" />

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

    <!-- ⭐ BUSCA GLOBAL (MODAL) -->
    <GlobalSearch ref="globalSearchRef" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, provide } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { Tooltip } from 'bootstrap';
import GlobalSearch from '@/components/GlobalSearch.vue';
import NotificationCenter from '@/components/NotificationCenter.vue';

const router = useRouter();
const route = useRoute();

const globalSearchRef = ref(null);

const openGlobalSearch = () => {
  globalSearchRef.value?.open();
};

provide('openGlobalSearch', openGlobalSearch);

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

  academico: {
    name: 'Gestão Acadêmica',
    icon: 'bi bi-mortarboard-fill',
    basePath: '/admin/academico',
    menus: []
  },

  espacoFisico: {
    name: 'Gestão Espaço Físico',
    icon: 'bi bi-compass',
    basePath: '/admin/espaco-fisico',
    menus: []
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

  reports: {
    name: 'Relatórios',
    icon: 'bi bi-file-earmark-bar-graph',
    basePath: '/admin/reports',
    menus: []
  },

  // ⭐ GESTÃO DE PESSOAS E ACESSOS - CORRIGIDO
'pessoas-acessos': {
  name: 'Gestão de Pessoas e Acessos',
  icon: 'bi bi-person-lock',
  basePath: '/admin/pessoas-acessos',
  menus: [
    { 
      label: 'Colaboradores', 
      path: '/admin/pessoas-acessos/colaboradores', 
      icon: 'bi bi-people' 
    },
    { 
      label: 'Perfis de Acesso', 
      path: '/admin/pessoas-acessos/perfis', // ✅ CORRIGIDO
      icon: 'bi bi-shield-lock' 
    },
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
  nextTick(() => {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new Tooltip(tooltipTriggerEl);
    });
  });
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

.global-search-trigger {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.625rem 1.25rem;
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
  font-size: 0.9rem;
}

.search-kbd-hint {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 4px;
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.7);
  font-family: monospace;
}
</style>
