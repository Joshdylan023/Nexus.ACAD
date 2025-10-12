<template>
  <div class="card card-glass mb-4">
    <div class="card-header"><h4 class="text-white">{{ isEditing ? 'Editar Colaborador' : 'Adicionar Novo Colaborador' }}</h4></div>
    <div class="card-body">
      <form @submit.prevent="submitForm">
        
        <h5 class="mt-2 text-white">Dados Pessoais (Pessoa)</h5>
        <div class="row">
          <div class="col-md-6 mb-3"><input type="text" class="form-control" v-model="form.name" placeholder="Nome Completo *" required></div>
          <div class="col-md-6 mb-3"><input type="text" class="form-control" v-model="form.nome_social" placeholder="Nome Social"></div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3"><input type="text" class="form-control" v-model="form.cpf" placeholder="CPF *" required></div>
          <div class="col-md-6 mb-3"><input type="date" class="form-control" v-model="form.data_nascimento" placeholder="Data de Nascimento"></div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3"><input type="text" class="form-control" v-model="form.rg" placeholder="RG"></div>
            <div class="col-md-4 mb-3"><input type="text" class="form-control" v-model="form.rg_orgao_expedidor" placeholder="Órgão Expedidor"></div>
            <div class="col-md-4 mb-3"><input type="date" class="form-control" v-model="form.rg_data_expedicao" placeholder="Data de Expedição"></div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3"><input type="text" class="form-control" v-model="form.nacionalidade" placeholder="Nacionalidade"></div>
            <div class="col-md-4 mb-3"><input type="text" class="form-control" v-model="form.naturalidade_cidade" placeholder="Cidade Natal"></div>
            <div class="col-md-4 mb-3"><input type="text" class="form-control" v-model="form.naturalidade_uf" placeholder="UF Natal"></div>
        </div>
        <div class="mb-3"><input type="email" class="form-control" v-model="form.email" placeholder="E-mail Pessoal *" required></div>
        <div class="mb-3"><input type="text" class="form-control" v-model="form.endereco_completo" placeholder="Endereço Completo"></div>
        <div class="row">
            <div class="col-md-6 mb-3"><input type="text" class="form-control" v-model="form.telefone_principal" placeholder="Telefone Principal"></div>
            <div class="col-md-6 mb-3"><input type="text" class="form-control" v-model="form.telefone_secundario" placeholder="Telefone Secundário"></div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3"><input type="text" class="form-control" v-model="form.nome_pai" placeholder="Nome do Pai"></div>
            <div class="col-md-6 mb-3"><input type="text" class="form-control" v-model="form.nome_mae" placeholder="Nome da Mãe"></div>
        </div>
        <h6 class="mt-2 text-white">Formação</h6>
        <div class="row">
            <div class="col-md-8 mb-3"><input type="text" class="form-control" v-model="form.ensino_medio_instituicao" placeholder="Instituição do Ensino Médio"></div>
            <div class="col-md-4 mb-3"><input type="number" class="form-control" v-model="form.ensino_medio_ano_conclusao" placeholder="Ano de Conclusão"></div>
        </div>

        <hr class="my-4" style="border-color: rgba(255,255,255,0.3);">
        
        <h5 class="mt-2 text-white">Dados do Vínculo (Colaborador)</h5>
        <div class="row">
          <div class="col-md-6 mb-3"><input type="text" class="form-control" v-model="form.matricula_funcional" placeholder="Matrícula Funcional *" required></div>
          <div class="col-md-6 mb-3"><input type="email" class="form-control" v-model="form.email_funcional" placeholder="E-mail Funcional *" required></div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3"><input type="password" class="form-control" v-model="form.password" placeholder="Senha de Acesso *" :required="!isEditing"></div>
          <div class="col-md-6 mb-3"><input type="text" class="form-control" v-model="form.cargo" placeholder="Cargo *" required></div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
              <label class="form-label text-white">Data de Admissão</label>
              <input type="date" class="form-control" v-model="form.data_admissao" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label text-white">Unidade Organizacional (Empresa)</label>
            <div class="input-group">
                <select class="form-select" v-model="form.unidade_organizacional_type">
                    <option v-for="(label, type) in unidadeOrganizacionalTypes" :key="type" :value="type">{{ label }}</option>
                </select>
                <select class="form-select" v-model="form.unidade_organizacional_id">
                    <option :value="null">-- Selecione --</option>
                    <option v-for="option in unidadeOrganizacionalOptions" :key="option.id" :value="option.id">{{ option.nome }}</option>
                </select>
            </div>
          </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label text-white">Unidade de Lotação (Local de Trabalho)</label>
                <div class="input-group">
                    <select class="form-select" v-model="form.unidade_lotacao_type">
                        <option v-for="(label, type) in unidadeLotacaoTypes" :key="type" :value="type">{{ label }}</option>
                    </select>
                    <select class="form-select" v-model="form.unidade_lotacao_id">
                        <option :value="null">-- Selecione --</option>
                        <option v-for="option in unidadeLotacaoOptions" :key="option.id" :value="option.id">{{ option.nome }}</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label text-white">Setor de Vínculo</label>
                <select class="form-select" v-model="form.setor_vinculo_id" required>
                    <option :value="null">-- Selecione --</option>
                    <option v-for="option in setorVinculoOptions" :key="option.id" :value="option.id">{{ option.nome }}</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label text-white">Status</label>
                <select class="form-select" v-model="form.status" required>
                    <option value="Ativo">Ativo</option>
                    <option value="Afastado">Afastado</option>
                    <option value="Desligado">Desligado</option>
                </select>
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
            placeholder="Digite para buscar um gestor..."
            :filterable="false"
          ></v-select>
        </div>
        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="is_gestor" v-model="form.is_gestor">
          <label class="form-label text-white" for="is_gestor">É Gestor de Equipa</label>
        </div>

        <hr class="my-4" style="border-color: rgba(255,255,255,0.3);">

        <h5 class="mt-2 text-white">Fotos</h5>
        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="foto_registro_rh" class="form-label text-white">Foto para Registro RH</label>
                <input type="file" class="form-control" @change="handleRhPhotoUpload">
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Salvar Colaborador</button>
        <router-link to="/admin/pessoas-acessos/colaboradores" class="btn btn-secondary ms-2">Cancelar</router-link>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, defineProps, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import vSelect from 'vue-select';

const props = defineProps({ id: String });
const isEditing = ref(!!props.id);

const router = useRouter();
const form = ref({});
const gestorOptions = ref([]);

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
    'campus': 'Campus',
    'setor': 'Setor',
});
const unidadeOrganizacionalOptions = ref([]);
const unidadeLotacaoOptions = ref([]);
const setorVinculoOptions = ref([]);

// Photo refs
const foto_registro_rh_file = ref(null);

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
};

const fetchColaborador = async () => {
  if (!isEditing.value) return;
  try {
    const response = await axios.get(`/api/v1/colaboradores/${props.id}`);
    const colaborador = response.data;
    form.value = {
        ...colaborador,
        name: colaborador.usuario.name,
        nome_social: colaborador.usuario.nome_social,
        cpf: colaborador.usuario.cpf,
        data_nascimento: colaborador.usuario.data_nascimento,
        rg: colaborador.usuario.rg,
        rg_orgao_expedidor: colaborador.usuario.rg_orgao_expedidor,
        rg_data_expedicao: colaborador.usuario.rg_data_expedicao,
        nacionalidade: colaborador.usuario.nacionalidade,
        naturalidade_cidade: colaborador.usuario.naturalidade_cidade,
        naturalidade_uf: colaborador.usuario.naturalidade_uf,
        email: colaborador.usuario.email,
        endereco_completo: colaborador.usuario.endereco_completo,
        telefone_principal: colaborador.usuario.telefone_principal,
        telefone_secundario: colaborador.usuario.telefone_secundario,
        nome_pai: colaborador.usuario.nome_pai,
        nome_mae: colaborador.usuario.nome_mae,
        ensino_medio_instituicao: colaborador.usuario.ensino_medio_instituicao,
        ensino_medio_ano_conclusao: colaborador.usuario.ensino_medio_ano_conclusao,
    };
  } catch (error) {
    console.error("Erro ao buscar dados do colaborador para edição:", error);
  }
};

const fetchGestores = (search, loading) => {
  if (search && search.length) {
    loading(true);
    // O ideal é buscar por usuários que são colaboradores e marcados como gestores
    // A query `is_gestor=true` pode precisar de ajuste no backend se não existir
    axios.get(`/api/v1/colaboradores?search=${search}`)
      .then(response => {
        // Mapeia a resposta para o formato esperado pelo v-select
        gestorOptions.value = response.data.map(colaborador => ({
            id: colaborador.id, // ou colaborador.user_id, dependendo do que o backend espera
            name: colaborador.usuario.name
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
            unidadeOrganizacionalOptions.value = response.data.data || response.data;
        } catch (error) {
            console.error('Erro ao buscar opções de unidade organizacional:', error);
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
    if (type === 'campus') endpoint = '/api/v1/campi';
    if (type === 'setor') endpoint = '/api/v1/setores';
    
    if(endpoint) {
        try {
            const response = await axios.get(endpoint);
            unidadeLotacaoOptions.value = response.data.data || response.data;
        } catch (error) {
            console.error('Erro ao buscar opções de unidade de lotação:', error);
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
        const response = await axios.get('/api/v1/setor-vinculos', {
            params: {
                vinculavel_type: type,
                vinculavel_id: id,
            }
        });
        setorVinculoOptions.value = response.data.map(vinculo => vinculo.setor);
    } catch (error) {
        console.error('Erro ao buscar opções de setor de vínculo:', error);
        setorVinculoOptions.value = [];
    }
};

watch(() => form.value.unidade_organizacional_type, () => {
    form.value.unidade_organizacional_id = null;
    fetchUnidadeOrganizacionalOptions();
});

watch(() => form.value.unidade_lotacao_type, () => {
    form.value.unidade_lotacao_id = null;
    fetchUnidadeLotacaoOptions();
});

watch(() => form.value.unidade_lotacao_id, () => {
    form.value.setor_vinculo_id = null;
    fetchSetorVinculoOptions();
});

const handleRhPhotoUpload = (event) => {
    foto_registro_rh_file.value = event.target.files[0];
};

const submitForm = async () => {
  const formData = new FormData();
  
  // Append all form fields
  for (const key in form.value) {
      if (form.value[key] !== null && form.value[key] !== undefined) {
          formData.append(key, form.value[key]);
      }
  }

  // Correctly format the is_gestor boolean for the backend
  formData.set('is_gestor', form.value.is_gestor ? 1 : 0);

  // Append photo files
  if (foto_registro_rh_file.value) {
    formData.append('foto_registro_rh', foto_registro_rh_file.value);
  }

  try {
    const config = {
        headers: {
            'Content-Type': 'multipart/form-data',
        }
    };

    if (isEditing.value) {
      formData.append('_method', 'PUT');
      await axios.post(`/api/v1/colaboradores/${props.id}`, formData, config);
    } else {
      await axios.post('/api/v1/colaboradores', formData, config);
    }
    router.push('/admin/pessoas-acessos/colaboradores');
  } catch (error) {
    console.error("Erro ao salvar colaborador:", error.response?.data || error);
    alert('Erro ao salvar. Verifique os campos e tente novamente.');
  }
};

onMounted(async () => {
  if (isEditing.value) {
    await fetchColaborador();
  } else {
    resetForm();
  }
  fetchUnidadeOrganizacionalOptions();
  fetchUnidadeLotacaoOptions();
  fetchSetorVinculoOptions();
});

</script>

<style scoped>
.card-glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
</style>