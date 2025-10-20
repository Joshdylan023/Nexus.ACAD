<template>
  <div class="card card-glass mb-4">
    <div class="card-header"><h4 class="text-white">{{ isEditing ? 'Editar Colaborador' : 'Adicionar Novo Colaborador' }}</h4></div>
    <div class="card-body">
      <form @submit.prevent="submitForm">
        
        <h5 class="mt-2 text-white">Dados Pessoais (Pessoa)</h5>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label text-white">Nome Completo <span class="text-danger">*</span></label>
            <input 
              type="text" 
              class="form-control" 
              :class="{ 'is-invalid': errors.name }"
              v-model="form.name" 
              placeholder="Nome Completo *"
              @input="delete errors.name"
              required
            >
            <div v-if="errors.name" class="invalid-feedback d-block">{{ errors.name }}</div>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label text-white">Nome Social</label>
            <input type="text" class="form-control" v-model="form.nome_social" placeholder="Nome Social">
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label text-white">CPF <span class="text-danger">*</span></label>
            <input 
              type="text" 
              class="form-control" 
              :class="{ 'is-invalid': errors.cpf }"
              :value="form.cpf" 
              @input="handleCpfInput"
              placeholder="000.000.000-00 *"
              maxlength="14"
              required
            >
            <div v-if="errors.cpf" class="invalid-feedback d-block">{{ errors.cpf }}</div>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label text-white">Data de Nascimento</label>
            <input type="date" class="form-control" v-model="form.data_nascimento" placeholder="Data de Nascimento">
          </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label text-white">RG</label>
                <input type="text" class="form-control" v-model="form.rg" placeholder="RG">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label text-white">Órgão Expedidor</label>
                <input type="text" class="form-control" v-model="form.rg_orgao_expedidor" placeholder="Órgão Expedidor">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label text-white">Data de Expedição</label>
                <input type="date" class="form-control" v-model="form.rg_data_expedicao" placeholder="Data de Expedição">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label text-white">Nacionalidade</label>
                <input type="text" class="form-control" v-model="form.nacionalidade" placeholder="Nacionalidade">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label text-white">Cidade Natal</label>
                <input type="text" class="form-control" v-model="form.naturalidade_cidade" placeholder="Cidade Natal">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label text-white">UF Natal</label>
                <input type="text" class="form-control" v-model="form.naturalidade_uf" placeholder="UF Natal">
            </div>
        </div>
        <div class="mb-3">
          <label class="form-label text-white">E-mail Pessoal <span class="text-danger">*</span></label>
          <input 
            type="email" 
            class="form-control" 
            :class="{ 'is-invalid': errors.email }"
            v-model="form.email" 
            placeholder="E-mail Pessoal *"
            @input="delete errors.email"
            required
          >
          <div v-if="errors.email" class="invalid-feedback d-block">{{ errors.email }}</div>
        </div>
        <div class="mb-3">
            <label class="form-label text-white">Endereço Completo</label>
            <input type="text" class="form-control" v-model="form.endereco_completo" placeholder="Endereço Completo">
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label text-white">Telefone Principal</label>
                <input type="text" class="form-control" v-model="form.telefone_principal" placeholder="Telefone Principal">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label text-white">Telefone Secundário</label>
                <input type="text" class="form-control" v-model="form.telefone_secundario" placeholder="Telefone Secundário">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label text-white">Nome do Pai</label>
                <input type="text" class="form-control" v-model="form.nome_pai" placeholder="Nome do Pai">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label text-white">Nome da Mãe</label>
                <input type="text" class="form-control" v-model="form.nome_mae" placeholder="Nome da Mãe">
            </div>
        </div>
        <h6 class="mt-2 text-white">Formação</h6>
        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label text-white">Instituição do Ensino Médio</label>
                <input type="text" class="form-control" v-model="form.ensino_medio_instituicao" placeholder="Instituição do Ensino Médio">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label text-white">Ano de Conclusão</label>
                <input type="number" class="form-control" v-model="form.ensino_medio_ano_conclusao" placeholder="Ano de Conclusão">
            </div>
        </div>


        <hr class="my-4" style="border-color: rgba(255,255,255,0.3);">
        
        <h5 class="mt-2 text-white">Dados do Vínculo (Colaborador)</h5>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label text-white">Matrícula Funcional <span class="text-danger">*</span></label>
            <input 
              type="text" 
              class="form-control" 
              :class="{ 'is-invalid': errors.matricula_funcional }"
              v-model="form.matricula_funcional" 
              placeholder="Matrícula Funcional *"
              @input="delete errors.matricula_funcional"
              required
            >
            <div v-if="errors.matricula_funcional" class="invalid-feedback d-block">{{ errors.matricula_funcional }}</div>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label text-white">E-mail Funcional <span class="text-danger">*</span></label>
            <input 
              type="email" 
              class="form-control" 
              :class="{ 'is-invalid': errors.email_funcional }"
              v-model="form.email_funcional" 
              placeholder="E-mail Funcional *"
              @input="delete errors.email_funcional"
              required
            >
            <div v-if="errors.email_funcional" class="invalid-feedback d-block">{{ errors.email_funcional }}</div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label text-white">Senha de Acesso <span class="text-danger" v-if="!isEditing">*</span></label>
            <input 
              type="password" 
              class="form-control" 
              :class="{ 'is-invalid': errors.password }"
              v-model="form.password" 
              placeholder="Senha de Acesso *"
              @input="delete errors.password"
              :required="!isEditing"
            >
            <div v-if="errors.password" class="invalid-feedback d-block">{{ errors.password }}</div>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label text-white">Cargo <span class="text-danger">*</span></label>
            <input 
              type="text" 
              class="form-control" 
              :class="{ 'is-invalid': errors.cargo }"
              v-model="form.cargo" 
              placeholder="Cargo *"
              @input="delete errors.cargo"
              required
            >
            <div v-if="errors.cargo" class="invalid-feedback d-block">{{ errors.cargo }}</div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
              <label class="form-label text-white">Data de Admissão <span class="text-danger">*</span></label>
              <input 
                type="date" 
                class="form-control" 
                :class="{ 'is-invalid': errors.data_admissao }"
                v-model="form.data_admissao"
                @change="delete errors.data_admissao"
                required
              >
              <div v-if="errors.data_admissao" class="invalid-feedback d-block">{{ errors.data_admissao }}</div>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label text-white">Unidade Organizacional (Empresa) <span class="text-danger">*</span></label>
            <div class="input-group">
                <select class="form-select" v-model="form.unidade_organizacional_type">
                    <option v-for="(label, type) in unidadeOrganizacionalTypes" :key="type" :value="type">{{ label }}</option>
                </select>
                <select 
                  class="form-select" 
                  :class="{ 'is-invalid': errors.unidade_organizacional_id }"
                  v-model="form.unidade_organizacional_id"
                  @change="delete errors.unidade_organizacional_id"
                >
                    <option :value="null">-- Selecione --</option>
                    <option v-for="option in unidadeOrganizacionalOptions" :key="option.id" :value="option.id">{{ option.nome }}</option>
                </select>
            </div>
            <div v-if="errors.unidade_organizacional_id" class="invalid-feedback d-block">{{ errors.unidade_organizacional_id }}</div>
          </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label text-white">Unidade de Lotação (Local de Trabalho) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <select class="form-select" v-model="form.unidade_lotacao_type">
                        <option v-for="(label, type) in unidadeLotacaoTypes" :key="type" :value="type">{{ label }}</option>
                    </select>
                    <select 
                      class="form-select" 
                      :class="{ 'is-invalid': errors.unidade_lotacao_id }"
                      v-model="form.unidade_lotacao_id"
                      @change="delete errors.unidade_lotacao_id"
                    >
                        <option :value="null">-- Selecione --</option>
                        <option v-for="option in unidadeLotacaoOptions" :key="option.id" :value="option.id">{{ option.nome }}</option>
                    </select>
                </div>
                <div v-if="errors.unidade_lotacao_id" class="invalid-feedback d-block">{{ errors.unidade_lotacao_id }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label text-white">Setor de Vínculo <span class="text-danger">*</span></label>
                <select 
                  class="form-select" 
                  :class="{ 'is-invalid': errors.setor_vinculo_id }"
                  v-model="form.setor_vinculo_id"
                  @change="delete errors.setor_vinculo_id"
                  required
                >
                    <option :value="null">-- Selecione uma unidade de lotação primeiro --</option>
                    <option v-for="setorVinculo in setorVinculoOptions" :key="setorVinculo.id" :value="setorVinculo.id">{{ setorVinculo.setor.nome }}</option>
                </select>
                <div v-if="errors.setor_vinculo_id" class="invalid-feedback d-block">{{ errors.setor_vinculo_id }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label text-white">Status <span class="text-danger">*</span></label>
                <select 
                  class="form-select" 
                  :class="{ 'is-invalid': errors.status }"
                  v-model="form.status"
                  @change="delete errors.status"
                  required
                >
                    <option value="Ativo">Ativo</option>
                    <option value="Afastado">Afastado</option>
                    <option value="Desligado">Desligado</option>
                </select>
                <div v-if="errors.status" class="invalid-feedback d-block">{{ errors.status }}</div>
            </div>
            <div class="col-md-6 mb-3" v-if="form.status === 'Desligado'">
                <label class="form-label text-white">Data de Desligamento</label>
                <input type="date" class="form-control" v-model="form.data_desligamento">
            </div>
        </div>
        <div class="mb-3">
          <label class="form-label text-white">Gestor Imediato (Opcional)</label>
          <v-select 
            label="name" 
            :options="gestorOptions" 
            :reduce="gestor => gestor.id" 
            v-model="form.gestor_imediato_id" 
            @search="fetchGestores"
            placeholder="Digite o nome ou matrícula para buscar..."
            :filterable="false"
            :searchable="true"
          >
            <template #no-options>
              Digite para buscar um gestor...
            </template>
          </v-select>
        </div>
        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="is_gestor" v-model="form.is_gestor">
          <label class="form-label text-white" for="is_gestor">É Gestor de Equipe</label>
        </div>


        <hr class="my-4" style="border-color: rgba(255,255,255,0.3);">


        <h5 class="mt-2 text-white">Fotos</h5>


        <!-- ✅ PREVIEW DA FOTO -->
        <div class="row mb-3" v-if="fotoPreviewUrl">
          <div class="col-md-12 text-center">
            <img :src="fotoPreviewUrl" alt="Preview da Foto RH" class="img-thumbnail" style="max-width: 200px; max-height: 200px; object-fit: cover; border: 2px solid rgba(255,255,255,0.3);">
          </div>
        </div>


        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="foto_registro_rh" class="form-label text-white">Foto para Registro RH</label>
                <input type="file" class="form-control" @change="handleRhPhotoUpload" accept="image/*">
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary" :disabled="saving">
          <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
          Salvar Colaborador
        </button>
        <router-link :to="cancelLink" class="btn btn-secondary ms-2">Cancelar</router-link>
      </form>
    </div>
  </div>
</template>


<script setup>
import { ref, onMounted, defineProps, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import vSelect from 'vue-select';


const props = defineProps({ id: String });
const isEditing = ref(!!props.id);


const cancelLink = computed(() => {
  if (isEditing.value) {
    return `/admin/pessoas-acessos/colaboradores/${props.id}`;
  }
  return '/admin/pessoas-acessos/colaboradores';
});


const router = useRouter();
const form = ref({});
const gestorOptions = ref([]);
const errors = ref({});
const saving = ref(false);
const isInitialLoad = ref(true);


// Opções para os selects dinâmicos
const unidadeOrganizacionalTypes = ref({
    'grupo_educacional': 'Grupo Educacional',
    'mantenedora': 'Mantenedora',
    'instituicao': 'Instituição',
    'campus': 'Campus',
    'setor': 'Setor',
});


const unidadeLotacaoTypes = ref({
    'grupo_educacional': 'Grupo Educacional',
    'mantenedora': 'Mantenedora',
    'instituicao': 'Instituição',
});


const unidadeOrganizacionalOptions = ref([]);
const unidadeLotacaoOptions = ref([]);
const setorVinculoOptions = ref([]);


// Photo refs
const foto_registro_rh_file = ref(null);
const fotoPreviewUrl = ref(null);


const resetForm = () => {
  form.value = {
    name: '', nome_social: '', cpf: '', data_nascimento: '', rg: '', rg_orgao_expedidor: '', rg_data_expedicao: '',
    nacionalidade: '', naturalidade_cidade: '', naturalidade_uf: '', email: '', endereco_completo: '',
    telefone_principal: '', telefone_secundario: '', nome_pai: '', nome_mae: '',
    ensino_medio_instituicao: '', ensino_medio_ano_conclusao: '',
    matricula_funcional: '', email_funcional: '', password: '',
    cargo: '', data_admissao: null, 
    status: 'Ativo', data_desligamento: null,
    gestor_imediato_id: null, is_gestor: false,
    unidade_organizacional_type: 'mantenedora',
    unidade_organizacional_id: null,
    unidade_lotacao_type: 'instituicao',
    unidade_lotacao_id: null,
    setor_vinculo_id: null,
  };
  foto_registro_rh_file.value = null;
  fotoPreviewUrl.value = null;
};


const fetchColaborador = async () => {
  if (!isEditing.value) return;
  try {
    const response = await axios.get(`/api/v1/colaboradores/${props.id}`);
    const colaborador = response.data;
    
    // ✅ CARREGAR DADOS DO USUÁRIO E COLABORADOR DE FORMA EXPLÍCITA
    const { usuario } = colaborador;

    const formatDate = (dateString) => {
      if (!dateString) return null;
      try {
        return new Date(dateString).toISOString().split('T')[0];
      } catch (e) {
        console.error('Invalid date string:', dateString);
        return null;
      }
    };

    form.value = {
      // Dados Pessoais (de usuario)
      name: usuario.name,
      nome_social: usuario.nome_social,
      cpf: usuario.cpf,
      data_nascimento: formatDate(usuario.data_nascimento),
      rg: usuario.rg,
      rg_orgao_expedidor: usuario.rg_orgao_expedidor,
      rg_data_expedicao: formatDate(usuario.rg_data_expedicao),
      nacionalidade: usuario.nacionalidade,
      naturalidade_cidade: usuario.naturalidade_cidade,
      naturalidade_uf: usuario.naturalidade_uf,
      email: usuario.email,
      endereco_completo: usuario.endereco_completo,
      telefone_principal: usuario.telefone_principal,
      telefone_secundario: usuario.telefone_secundario,
      nome_pai: usuario.nome_pai,
      nome_mae: usuario.nome_mae,
      ensino_medio_instituicao: usuario.ensino_medio_instituicao,
      ensino_medio_ano_conclusao: usuario.ensino_medio_ano_conclusao,


      // Dados do Vínculo (de colaborador)
      matricula_funcional: colaborador.matricula_funcional,
      email_funcional: colaborador.email_funcional,
      password: '', // Senha não é carregada, apenas definida na edição se necessário
      cargo: colaborador.cargo,
      data_admissao: formatDate(colaborador.data_admissao),
      status: colaborador.status,
      data_desligamento: formatDate(colaborador.data_desligamento),
      gestor_imediato_id: colaborador.gestor_imediato_id,
      is_gestor: colaborador.is_gestor,


      // Campos polimórficos
      unidade_organizacional_type: colaborador.unidade_organizacional_type || 'mantenedora',
      unidade_organizacional_id: colaborador.unidade_organizacional_id,
      unidade_lotacao_type: colaborador.unidade_lotacao_type || 'instituicao',
      unidade_lotacao_id: colaborador.unidade_lotacao_id,
      setor_vinculo_id: colaborador.setor_vinculo_id,
    };
    
    // ✅ As opções de select são carregadas pelos watchers ao definir os valores do form
    
    // ✅ CARREGAR GESTOR SE EXISTIR
    if (colaborador.gestor_imediato_id && colaborador.gestor_imediato) {
      gestorOptions.value = [{
        id: colaborador.gestor_imediato.id,
        name: `${colaborador.gestor_imediato.usuario.name} (${colaborador.gestor_imediato.matricula_funcional})`
      }];
    }
    
    // ✅ CARREGAR FOTO EXISTENTE
    if (colaborador.foto_registro_rh) {
      fotoPreviewUrl.value = `/storage/${colaborador.foto_registro_rh}`;
    }
    
  } catch (error) {
    console.error("Erro ao buscar dados do colaborador para edição:", error);
  }
};


const fetchGestores = (search, loading) => {
  if (search && search.length >= 2) {
    loading(true);
    axios.get(`/api/v1/colaboradores`, {
      params: { search: search }
    })
      .then(response => {
        const data = Array.isArray(response.data) ? response.data : (response.data.data || []);
        gestorOptions.value = data.map(colaborador => ({
            id: colaborador.id,
            name: `${colaborador.usuario.name} (${colaborador.matricula_funcional})`
        }));
      })
      .catch(error => console.error("Erro ao buscar gestores:", error))
      .finally(() => loading(false));
  }
};


const fetchUnidadeOrganizacionalOptions = async () => {
    if (!form.value.unidade_organizacional_type) return;
    const type = form.value.unidade_organizacional_type;
    let endpoint = '';
    if (type === 'grupo_educacional') endpoint = '/api/v1/grupos-educacionais';
    if (type === 'mantenedora') endpoint = '/api/v1/mantenedoras';
    if (type === 'instituicao') endpoint = '/api/v1/instituicoes';
    if (type === 'campus') endpoint = '/api/v1/campi';
    if (type === 'setor') endpoint = '/api/v1/setores';
    
    if(endpoint) {
        try {
            const response = await axios.get(endpoint);
            const data = Array.isArray(response.data) ? response.data : (response.data.data || []);
            
            unidadeOrganizacionalOptions.value = data.map(item => ({
                ...item,
                nome: item.nome || item.nome_fantasia || item.razao_social
            }));
        } catch (error) {
            console.error('Erro ao buscar opções de unidade organizacional:', error);
            unidadeOrganizacionalOptions.value = [];
        }
    }
};


const fetchUnidadeLotacaoOptions = async () => {
    if (!form.value.unidade_lotacao_type) return;
    const type = form.value.unidade_lotacao_type;
    let endpoint = '';
    if (type === 'grupo_educacional') endpoint = '/api/v1/grupos-educacionais';
    if (type === 'mantenedora') endpoint = '/api/v1/mantenedoras';
    if (type === 'instituicao') endpoint = '/api/v1/instituicoes';
    
    if(endpoint) {
        try {
            const response = await axios.get(endpoint);
            const data = Array.isArray(response.data) ? response.data : (response.data.data || []);
            
            unidadeLotacaoOptions.value = data.map(item => ({
                ...item,
                nome: item.nome || item.nome_fantasia || item.razao_social
            }));
        } catch (error) {
            console.error('Erro ao buscar opções de unidade de lotação:', error);
            unidadeLotacaoOptions.value = [];
        }
    }
};


const fetchSetorVinculoOptions = async () => {
    const type = form.value.unidade_lotacao_type;
    const id = form.value.unidade_lotacao_id;


    if (!type || !id) {
        setorVinculoOptions.value = [];
        return;
    }


    try {
        let endpoint = '';
        
        if (type === 'grupo_educacional') {
            endpoint = `/api/v1/grupos-educacionais/${id}/setores`;
        } else if (type === 'mantenedora') {
            endpoint = `/api/v1/mantenedoras/${id}/setores`;
        } else if (type === 'instituicao') {
            endpoint = `/api/v1/instituicoes/${id}/setores`;
        }


        if (endpoint) {
            const response = await axios.get(endpoint);
            const data = Array.isArray(response.data) ? response.data : (response.data.data || []);
            
            setorVinculoOptions.value = data;
        }
    } catch (error) {
        console.error('Erro ao buscar setores:', error);
        setorVinculoOptions.value = [];
    }
};


watch(() => form.value.unidade_organizacional_type, () => {
    if (!isInitialLoad.value) {
        form.value.unidade_organizacional_id = null;
    }
    fetchUnidadeOrganizacionalOptions();
});


watch(() => form.value.unidade_lotacao_type, () => {
    if (!isInitialLoad.value) {
        form.value.unidade_lotacao_id = null;
        form.value.setor_vinculo_id = null;
        setorVinculoOptions.value = [];
    }
    fetchUnidadeLotacaoOptions();
});


watch(() => form.value.unidade_lotacao_id, () => {
    if (!isInitialLoad.value) {
        form.value.setor_vinculo_id = null;
    }
    fetchSetorVinculoOptions();
});


// ===================================
// 🆕 FUNÇÃO DE MÁSCARA DE CPF
// ===================================
const handleCpfInput = (event) => {
  // Remove erros ao digitar
  delete errors.value.cpf;
  
  // Remove tudo que não é número
  let value = event.target.value.replace(/\D/g, '');
  
  // Limita a 11 dígitos
  if (value.length > 11) {
    value = value.slice(0, 11);
  }
  
  // Aplica a máscara 000.000.000-00
  if (value.length <= 3) {
    form.value.cpf = value;
  } else if (value.length <= 6) {
    form.value.cpf = `${value.slice(0, 3)}.${value.slice(3)}`;
  } else if (value.length <= 9) {
    form.value.cpf = `${value.slice(0, 3)}.${value.slice(3, 6)}.${value.slice(6)}`;
  } else {
    form.value.cpf = `${value.slice(0, 3)}.${value.slice(3, 6)}.${value.slice(6, 9)}-${value.slice(9, 11)}`;
  }
};


const handleRhPhotoUpload = (event) => {
    foto_registro_rh_file.value = event.target.files[0];
    
    // ✅ CRIAR PREVIEW
    if (event.target.files[0]) {
      const reader = new FileReader();
      reader.onload = (e) => {
        fotoPreviewUrl.value = e.target.result;
      };
      reader.readAsDataURL(event.target.files[0]);
    }
};


// ===================================
// 🔴 VALIDAÇÃO
// ===================================
const validateForm = () => {
  errors.value = {};


  // Dados pessoais
  if (!form.value.name) errors.value.name = 'Nome completo é obrigatório';
  if (!form.value.cpf) errors.value.cpf = 'CPF é obrigatório';
  if (!form.value.email) errors.value.email = 'E-mail pessoal é obrigatório';


  // Dados do vínculo
  if (!form.value.matricula_funcional) errors.value.matricula_funcional = 'Matrícula funcional é obrigatória';
  if (!form.value.email_funcional) errors.value.email_funcional = 'E-mail funcional é obrigatório';
  if (!form.value.cargo) errors.value.cargo = 'Cargo é obrigatório';
  if (!form.value.data_admissao) errors.value.data_admissao = 'Data de admissão é obrigatória';
  if (!form.value.status) errors.value.status = 'Status é obrigatório';


  // Campos polimórficos
  if (!form.value.unidade_organizacional_id) errors.value.unidade_organizacional_id = 'Unidade organizacional é obrigatória';
  if (!form.value.unidade_lotacao_id) errors.value.unidade_lotacao_id = 'Unidade de lotação é obrigatória';
  if (!form.value.setor_vinculo_id) errors.value.setor_vinculo_id = 'Setor de vínculo é obrigatório';


  // Senha obrigatória na criação
  if (!isEditing.value && !form.value.password) {
    errors.value.password = 'Senha é obrigatória';
  }


  return Object.keys(errors.value).length === 0;
};


const submitForm = async () => {
  // ✅ VALIDAR ANTES DE ENVIAR
  if (!validateForm()) {
    alert('Por favor, preencha todos os campos obrigatórios marcados com *');
    return;
  }


  saving.value = true;


  const formData = new FormData();
  
  // Separar dados do usuário para enviar como um objeto aninhado
  const usuarioKeys = [
    'name', 'nome_social', 'cpf', 'data_nascimento', 'rg', 'rg_orgao_expedidor', 'rg_data_expedicao',
    'nacionalidade', 'naturalidade_cidade', 'naturalidade_uf', 'email', 'endereco_completo',
    'telefone_principal', 'telefone_secundario', 'nome_pai', 'nome_mae',
    'ensino_medio_instituicao', 'ensino_medio_ano_conclusao'
  ];


  for (const key in form.value) {
    if (form.value[key] !== null && form.value[key] !== undefined) {
      // Não envia senha vazia na edição para evitar falha de validação
      if (isEditing.value && key === 'password' && !form.value[key]) {
        continue;
      }
      formData.append(key, form.value[key]);
    }
  }


  formData.set('is_gestor', form.value.is_gestor ? 1 : 0);


  if (foto_registro_rh_file.value) {
    formData.append('foto_registro_rh', foto_registro_rh_file.value);
  }


  try {
    const config = {
        headers: {
            'Content-Type': 'multipart/form-data',
        }
    };


    let response;
    if (isEditing.value) {
      formData.append('_method', 'PUT');
      response = await axios.post(`/api/v1/colaboradores/${props.id}`, formData, config);
    } else {
      response = await axios.post('/api/v1/colaboradores', formData, config);
    }
    const colaboradorId = response.data.data.id;
    router.push(`/admin/pessoas-acessos/colaboradores/${colaboradorId}`);
  } catch (error) {
    console.error("Erro ao salvar colaborador:", error.response?.data || error);
    
    // ✅ CAPTURAR ERROS DO BACKEND
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
    
    alert('Erro ao salvar. Verifique os campos e tente novamente.');
  } finally {
    saving.value = false;
  }
};


onMounted(async () => {
  isInitialLoad.value = true;
  if (isEditing.value) {
    await fetchColaborador();
  } else {
    resetForm();
    fetchUnidadeOrganizacionalOptions();
    fetchUnidadeLotacaoOptions();
  }
  isInitialLoad.value = false;
});


</script>


<style scoped>
.card-glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}


.text-danger {
  color: #dc3545 !important;
  font-weight: bold;
}


.is-invalid {
  border-color: #dc3545 !important;
}


.invalid-feedback {
  display: block !important;
  margin-top: 0.25rem;
  font-size: 0.875em;
  color: #dc3545;
}


/* ✅ CORRIGIR COR DOS SELECTS E INPUTS */
.form-select,
.form-control {
  background-color: rgba(255, 255, 255, 0.15) !important;
  color: white !important;
  border: 1px solid rgba(255, 255, 255, 0.3) !important;
}


.form-select option,
.form-control option {
  background-color: #1a1a2e !important;
  color: white !important;
}


.form-select:focus,
.form-control:focus {
  background-color: rgba(255, 255, 255, 0.2) !important;
  color: white !important;
  border-color: rgba(102, 126, 234, 0.6) !important;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25) !important;
}


/* ✅ V-SELECT STYLE */
:deep(.vs__dropdown-toggle) {
  background-color: rgba(255, 255, 255, 0.15) !important;
  border: 1px solid rgba(255, 255, 255, 0.3) !important;
  color: white !important;
}


:deep(.vs__search),
:deep(.vs__search:focus) {
  color: white !important;
}


:deep(.vs__selected) {
  color: white !important;
}


:deep(.vs__dropdown-menu) {
  background-color: #1a1a2e !important;
  border: 1px solid rgba(255, 255, 255, 0.3) !important;
}


:deep(.vs__dropdown-option) {
  color: white !important;
}


:deep(.vs__dropdown-option--highlight) {
  background-color: rgba(102, 126, 234, 0.8) !important;
  color: white !important;
}


:deep(.vs__no-options) {
  color: rgba(255, 255, 255, 0.6) !important;
}


/* ✅ PREVIEW DA FOTO */
.img-thumbnail {
  background-color: rgba(255, 255, 255, 0.1) !important;
  padding: 0.5rem;
}
</style>