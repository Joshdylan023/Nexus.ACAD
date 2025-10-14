<template>
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card card-glass mb-4">
          <div class="card-body">
            <h4 class="mb-4">
              <i class="bi bi-palette me-2"></i>
              Identidade Visual
            </h4>

            <!-- ⭐ SELETOR DE ENTIDADE -->
            <div class="row mb-4">
              <div class="col-md-4">
                <label class="form-label">Tipo de Entidade</label>
                <select v-model="tipoEntidade" @change="resetarSelecao" class="form-select">
                  <option value="">Selecione...</option>
                  <option value="App\Models\GrupoEducacional">Grupo Educacional</option>
                  <option value="App\Models\Mantenedora">Mantenedora</option>
                  <option value="App\Models\Instituicao">Instituição</option>
                </select>
              </div>

              <div class="col-md-6" v-if="tipoEntidade">
                <label class="form-label">{{ getLabelEntidade() }}</label>
                <select v-model="entidadeSelecionadaId" @change="carregarIdentidade" class="form-select">
                  <option value="">Selecione...</option>
                  <option v-for="item in listaEntidades" :key="item.id" :value="item.id">
                    {{ item.nome }}
                  </option>
                </select>
              </div>

              <div class="col-md-2" v-if="entidadeSelecionadaId">
                <label class="form-label">&nbsp;</label>
                <button @click="carregarIdentidade" class="btn btn-primary w-100">
                  <i class="bi bi-arrow-clockwise me-2"></i>
                  Recarregar
                </button>
              </div>
            </div>

            <!-- ⭐ AVISO DE HERANÇA -->
            <div v-if="origem && origem.origem !== 'propria'" class="alert alert-info">
              <i class="bi bi-info-circle me-2"></i>
              <strong>Herança Ativa:</strong> Esta entidade está usando a identidade visual 
              {{ origem.origem === 'herdada' ? 'herdada' : 'padrão' }} 
              {{ origem.origem === 'herdada' ? `de ${origem.entidade}: ${origem.nome}` : 'do sistema' }}.
              <button @click="criarIdentidadePropria" class="btn btn-sm btn-outline-primary ms-3">
                <i class="bi bi-plus-circle me-1"></i>
                Criar identidade própria
              </button>
            </div>

            <!-- ⭐ LOADING -->
            <div v-if="loading" class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
              </div>
            </div>

            <!-- ⭐ FORMULÁRIO -->
            <div v-else-if="entidadeSelecionadaId">
              <!-- UPLOAD DE LOGOS -->
              <div class="row mb-4">
                <div class="col-12">
                  <h5 class="mb-3">
                    <i class="bi bi-image me-2"></i>
                    Logos
                  </h5>
                </div>

                <!-- Logo Principal -->
                <div class="col-md-3">
                  <div class="logo-upload-box">
                    <label class="form-label fw-bold">Logo Principal</label>
                    <div class="upload-area" @click="$refs.logoPrincipal.click()">
                      <img v-if="form.logo_principal" :src="getLogoUrl(form.logo_principal)" class="preview-img">
                      <div v-else class="upload-placeholder">
                        <i class="bi bi-cloud-upload"></i>
                        <p>Clique para enviar</p>
                      </div>
                    </div>
                    <input 
                      ref="logoPrincipal" 
                      type="file" 
                      accept="image/*" 
                      @change="uploadLogo($event, 'logo_principal')"
                      style="display: none"
                    >
                    <button 
                      v-if="form.logo_principal" 
                      @click="deletarLogo('logo_principal')"
                      class="btn btn-sm btn-outline-danger mt-2 w-100"
                    >
                      <i class="bi bi-trash"></i> Remover
                    </button>
                  </div>
                </div>

                <!-- Logo Horizontal -->
                <div class="col-md-3">
                  <div class="logo-upload-box">
                    <label class="form-label fw-bold">Logo Horizontal</label>
                    <div class="upload-area" @click="$refs.logoHorizontal.click()">
                      <img v-if="form.logo_horizontal" :src="getLogoUrl(form.logo_horizontal)" class="preview-img">
                      <div v-else class="upload-placeholder">
                        <i class="bi bi-cloud-upload"></i>
                        <p>Clique para enviar</p>
                      </div>
                    </div>
                    <input 
                      ref="logoHorizontal" 
                      type="file" 
                      accept="image/*" 
                      @change="uploadLogo($event, 'logo_horizontal')"
                      style="display: none"
                    >
                    <button 
                      v-if="form.logo_horizontal" 
                      @click="deletarLogo('logo_horizontal')"
                      class="btn btn-sm btn-outline-danger mt-2 w-100"
                    >
                      <i class="bi bi-trash"></i> Remover
                    </button>
                  </div>
                </div>

                <!-- Logo Ícone -->
                <div class="col-md-3">
                  <div class="logo-upload-box">
                    <label class="form-label fw-bold">Logo Ícone</label>
                    <div class="upload-area" @click="$refs.logoIcone.click()">
                      <img v-if="form.logo_icone" :src="getLogoUrl(form.logo_icone)" class="preview-img">
                      <div v-else class="upload-placeholder">
                        <i class="bi bi-cloud-upload"></i>
                        <p>Clique para enviar</p>
                      </div>
                    </div>
                    <input 
                      ref="logoIcone" 
                      type="file" 
                      accept="image/*" 
                      @change="uploadLogo($event, 'logo_icone')"
                      style="display: none"
                    >
                    <button 
                      v-if="form.logo_icone" 
                      @click="deletarLogo('logo_icone')"
                      class="btn btn-sm btn-outline-danger mt-2 w-100"
                    >
                      <i class="bi bi-trash"></i> Remover
                    </button>
                  </div>
                </div>

                <!-- Logo Marca d'Água -->
                <div class="col-md-3">
                  <div class="logo-upload-box">
                    <label class="form-label fw-bold">Marca d'Água</label>
                    <div class="upload-area" @click="$refs.logoMarcaDagua.click()">
                      <img v-if="form.logo_marca_dagua" :src="getLogoUrl(form.logo_marca_dagua)" class="preview-img">
                      <div v-else class="upload-placeholder">
                        <i class="bi bi-cloud-upload"></i>
                        <p>Clique para enviar</p>
                      </div>
                    </div>
                    <input 
                      ref="logoMarcaDagua" 
                      type="file" 
                      accept="image/*" 
                      @change="uploadLogo($event, 'logo_marca_dagua')"
                      style="display: none"
                    >
                    <button 
                      v-if="form.logo_marca_dagua" 
                      @click="deletarLogo('logo_marca_dagua')"
                      class="btn btn-sm btn-outline-danger mt-2 w-100"
                    >
                      <i class="bi bi-trash"></i> Remover
                    </button>
                  </div>
                </div>
              </div>

              <hr class="my-4">

              <!-- PALETA DE CORES -->
              <div class="row mb-4">
                <div class="col-12">
                  <h5 class="mb-3">
                    <i class="bi bi-palette-fill me-2"></i>
                    Paleta de Cores
                  </h5>
                </div>

                <div class="col-md-3">
                  <label class="form-label">Cor Primária</label>
                  <div class="color-picker-wrapper">
                    <input type="color" v-model="form.cor_primaria" class="form-control form-control-color">
                    <input type="text" v-model="form.cor_primaria" class="form-control mt-2">
                  </div>
                </div>

                <div class="col-md-3">
                  <label class="form-label">Cor Secundária</label>
                  <div class="color-picker-wrapper">
                    <input type="color" v-model="form.cor_secundaria" class="form-control form-control-color">
                    <input type="text" v-model="form.cor_secundaria" class="form-control mt-2">
                  </div>
                </div>

                <div class="col-md-3">
                  <label class="form-label">Cor de Acento</label>
                  <div class="color-picker-wrapper">
                    <input type="color" v-model="form.cor_acento" class="form-control form-control-color">
                    <input type="text" v-model="form.cor_acento" class="form-control mt-2">
                  </div>
                </div>

                <div class="col-md-3">
                  <label class="form-label">Cor do Texto</label>
                  <div class="color-picker-wrapper">
                    <input type="color" v-model="form.cor_texto" class="form-control form-control-color">
                    <input type="text" v-model="form.cor_texto" class="form-control mt-2">
                  </div>
                </div>
              </div>

              <hr class="my-4">

              <!-- TIPOGRAFIA -->
              <div class="row mb-4">
                <div class="col-12">
                  <h5 class="mb-3">
                    <i class="bi bi-fonts me-2"></i>
                    Tipografia
                  </h5>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Fonte Principal</label>
                  <select v-model="form.fonte_principal" class="form-select">
                    <option value="Inter">Inter</option>
                    <option value="Roboto">Roboto</option>
                    <option value="Poppins">Poppins</option>
                    <option value="Montserrat">Montserrat</option>
                    <option value="Open Sans">Open Sans</option>
                    <option value="Lato">Lato</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Fonte Secundária</label>
                  <select v-model="form.fonte_secundaria" class="form-select">
                    <option value="Poppins">Poppins</option>
                    <option value="Roboto">Roboto</option>
                    <option value="Inter">Inter</option>
                    <option value="Montserrat">Montserrat</option>
                    <option value="Open Sans">Open Sans</option>
                    <option value="Lato">Lato</option>
                  </select>
                </div>
              </div>

              <hr class="my-4">

              <!-- CONFIGURAÇÕES DE DOCUMENTOS -->
              <div class="row mb-4">
                <div class="col-12">
                  <h5 class="mb-3">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Configurações de Documentos
                  </h5>
                </div>

                <div class="col-md-4">
                  <div class="form-check form-switch">
                    <input 
                      class="form-check-input" 
                      type="checkbox" 
                      v-model="form.usar_logo_documentos"
                      id="usarLogo"
                    >
                    <label class="form-check-label" for="usarLogo">
                      Usar logo em documentos
                    </label>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-check form-switch">
                    <input 
                      class="form-check-input" 
                      type="checkbox" 
                      v-model="form.usar_marca_dagua"
                      id="usarMarca"
                    >
                    <label class="form-check-label" for="usarMarca">
                      Usar marca d'água
                    </label>
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Posição do Logo</label>
                  <select v-model="form.posicao_logo" class="form-select">
                    <option value="topo-esquerda">Topo Esquerda</option>
                    <option value="topo-centro">Topo Centro</option>
                    <option value="topo-direita">Topo Direita</option>
                  </select>
                </div>
              </div>

              <hr class="my-4">

              <!-- RODAPÉ -->
              <div class="row mb-4">
                <div class="col-12">
                  <h5 class="mb-3">
                    <i class="bi bi-arrow-down-square me-2"></i>
                    Rodapé de Documentos
                  </h5>
                </div>

                <div class="col-md-12 mb-3">
                  <label class="form-label">Texto do Rodapé</label>
                  <textarea v-model="form.texto_rodape" class="form-control" rows="2" 
                    placeholder="© 2025 Nome da Instituição"></textarea>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Site</label>
                  <input type="url" v-model="form.site" class="form-control" 
                    placeholder="https://www.exemplo.com">
                </div>

                <div class="col-md-4">
                  <label class="form-label">Telefone</label>
                  <input type="text" v-model="form.telefone" class="form-control" 
                    placeholder="(11) 9999-9999">
                </div>

                <div class="col-md-4">
                  <label class="form-label">Email</label>
                  <input type="email" v-model="form.email" class="form-control" 
                    placeholder="contato@exemplo.com">
                </div>
              </div>

              <hr class="my-4">

              <!-- OBSERVAÇÕES -->
              <div class="row mb-4">
                <div class="col-12">
                  <label class="form-label">Observações</label>
                  <textarea v-model="form.observacoes" class="form-control" rows="3"></textarea>
                </div>
              </div>

              <!-- BOTÕES -->
              <div class="row">
                <div class="col-12 text-end">
                  <button @click="resetarFormulario" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Resetar
                  </button>
                  <button @click="salvarIdentidade" class="btn btn-primary" :disabled="salvando">
                    <span v-if="salvando" class="spinner-border spinner-border-sm me-2"></span>
                    <i v-else class="bi bi-save me-2"></i>
                    {{ salvando ? 'Salvando...' : 'Salvar Identidade Visual' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- EMPTY STATE -->
            <div v-else class="text-center py-5">
              <i class="bi bi-palette" style="font-size: 4rem; opacity: 0.3;"></i>
              <p class="mt-3 text-muted">Selecione uma entidade para configurar a identidade visual</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const tipoEntidade = ref('');
const entidadeSelecionadaId = ref('');
const loading = ref(false);
const salvando = ref(false);
const origem = ref(null);

const entidadesDisponiveis = ref({
  grupos: [],
  mantenedoras: [],
  instituicoes: [],
});

const form = ref({
  logo_principal: null,
  logo_horizontal: null,
  logo_icone: null,
  logo_marca_dagua: null,
  cor_primaria: '#667EEA',
  cor_secundaria: '#764BA2',
  cor_acento: '#F59E0B',
  cor_texto: '#1F2937',
  fonte_principal: 'Inter',
  fonte_secundaria: 'Poppins',
  usar_logo_documentos: true,
  usar_marca_dagua: false,
  posicao_logo: 'topo-esquerda',
  texto_rodape: '',
  site: '',
  telefone: '',
  email: '',
  observacoes: '',
});

const listaEntidades = computed(() => {
  if (tipoEntidade.value === 'App\\Models\\GrupoEducacional') {
    return entidadesDisponiveis.value.grupos;
  } else if (tipoEntidade.value === 'App\\Models\\Mantenedora') {
    return entidadesDisponiveis.value.mantenedoras;
  } else if (tipoEntidade.value === 'App\\Models\\Instituicao') {
    return entidadesDisponiveis.value.instituicoes;
  }
  return [];
});

const getLabelEntidade = () => {
  if (tipoEntidade.value === 'App\\Models\\GrupoEducacional') return 'Grupo Educacional';
  if (tipoEntidade.value === 'App\\Models\\Mantenedora') return 'Mantenedora';
  if (tipoEntidade.value === 'App\\Models\\Instituicao') return 'Instituição';
  return 'Entidade';
};

const resetarSelecao = () => {
  entidadeSelecionadaId.value = '';
  origem.value = null;
  resetarFormulario();
};

const resetarFormulario = () => {
  form.value = {
    logo_principal: null,
    logo_horizontal: null,
    logo_icone: null,
    logo_marca_dagua: null,
    cor_primaria: '#667EEA',
    cor_secundaria: '#764BA2',
    cor_acento: '#F59E0B',
    cor_texto: '#1F2937',
    fonte_principal: 'Inter',
    fonte_secundaria: 'Poppins',
    usar_logo_documentos: true,
    usar_marca_dagua: false,
    posicao_logo: 'topo-esquerda',
    texto_rodape: '',
    site: '',
    telefone: '',
    email: '',
    observacoes: '',
  };
};

const carregarEntidades = async () => {
  try {
    const response = await axios.get('/api/v1/identidade-visual/entidades');
    entidadesDisponiveis.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar entidades:', error);
  }
};

const carregarIdentidade = async () => {
  if (!tipoEntidade.value || !entidadeSelecionadaId.value) return;

  loading.value = true;
  try {
    const response = await axios.get('/api/v1/identidade-visual/show', {
      params: {
        entidade_type: tipoEntidade.value,
        entidade_id: entidadeSelecionadaId.value,
      },
    });

    if (response.data.identidade) {
      Object.assign(form.value, response.data.identidade);
      origem.value = response.data.origem || null;
    } else {
      Object.assign(form.value, response.data);
      origem.value = null;
    }
  } catch (error) {
    console.error('Erro ao carregar identidade:', error);
    Swal.fire('Erro', 'Não foi possível carregar a identidade visual', 'error');
  } finally {
    loading.value = false;
  }
};

const criarIdentidadePropria = () => {
  origem.value = null;
  Swal.fire({
    icon: 'success',
    title: 'Pronto!',
    text: 'Agora você pode configurar uma identidade visual própria para esta entidade.',
    timer: 2000,
  });
};

const uploadLogo = async (event, tipoLogo) => {
  const file = event.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('entidade_type', tipoEntidade.value);
  formData.append('entidade_id', entidadeSelecionadaId.value);
  formData.append('tipo_logo', tipoLogo);
  formData.append('logo', file);

  try {
    const response = await axios.post('/api/v1/identidade-visual/upload-logo', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    form.value[tipoLogo] = response.data.path;
    
    Swal.fire({
      icon: 'success',
      title: 'Logo enviado!',
      timer: 1500,
      showConfirmButton: false,
    });
  } catch (error) {
    console.error('Erro ao fazer upload:', error);
    Swal.fire('Erro', 'Não foi possível enviar o logo', 'error');
  }
};

const deletarLogo = async (tipoLogo) => {
  const result = await Swal.fire({
    title: 'Remover logo?',
    text: 'Esta ação não pode ser desfeita',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sim, remover',
    cancelButtonText: 'Cancelar',
  });

  if (!result.isConfirmed) return;

  try {
    await axios.delete('/api/v1/identidade-visual/delete-logo', {
      data: {
        entidade_type: tipoEntidade.value,
        entidade_id: entidadeSelecionadaId.value,
        tipo_logo: tipoLogo,
      },
    });

    form.value[tipoLogo] = null;
    
    Swal.fire({
      icon: 'success',
      title: 'Logo removido!',
      timer: 1500,
      showConfirmButton: false,
    });
  } catch (error) {
    console.error('Erro ao deletar:', error);
    Swal.fire('Erro', 'Não foi possível remover o logo', 'error');
  }
};

const salvarIdentidade = async () => {
  salvando.value = true;
  try {
    await axios.post('/api/v1/identidade-visual/store', {
      entidade_type: tipoEntidade.value,
      entidade_id: entidadeSelecionadaId.value,
      ...form.value,
    });

    Swal.fire({
      icon: 'success',
      title: 'Identidade Visual Salva!',
      text: 'As configurações foram aplicadas com sucesso',
      timer: 2000,
    });

    await carregarIdentidade();
  } catch (error) {
    console.error('Erro ao salvar:', error);
    Swal.fire('Erro', 'Não foi possível salvar as configurações', 'error');
  } finally {
    salvando.value = false;
  }
};

const getLogoUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  return `/storage/${path}`;
};

onMounted(() => {
  carregarEntidades();
});
</script>

<style scoped>
.card-glass {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* UPLOAD DE LOGOS */
.logo-upload-box {
  margin-bottom: 1rem;
}

.upload-area {
  width: 100%;
  height: 200px;
  border: 2px dashed rgba(255, 255, 255, 0.3);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.05);
}

.upload-area:hover {
  border-color: rgba(102, 126, 234, 0.6);
  background: rgba(102, 126, 234, 0.1);
}

.upload-placeholder {
  text-align: center;
  color: rgba(255, 255, 255, 0.5);
}

.upload-placeholder i {
  font-size: 3rem;
  margin-bottom: 0.5rem;
  display: block;
}

.upload-placeholder p {
  margin: 0;
  font-size: 0.875rem;
}

.preview-img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

/* COLOR PICKER */
.color-picker-wrapper {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-control-color {
  width: 100%;
  height: 50px;
  border-radius: 8px;
  cursor: pointer;
}

/* FORM ENHANCEMENTS */
.form-control,
.form-select {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.9);
  transition: all 0.3s ease;
}

.form-control:focus,
.form-select:focus {
  background: rgba(255, 255, 255, 0.12);
  border-color: rgba(102, 126, 234, 0.6);
  color: rgba(255, 255, 255, 1);
  box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
}

.form-control::placeholder {
  color: rgba(255, 255, 255, 0.4);
}

/* SWITCH */
.form-check-input {
  background-color: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.3);
  cursor: pointer;
}

.form-check-input:checked {
  background-color: #667EEA;
  border-color: #667EEA;
}

.form-check-input:focus {
  box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
}

/* ALERT */
.alert-info {
  background: rgba(102, 126, 234, 0.2);
  border: 1px solid rgba(102, 126, 234, 0.4);
  color: rgba(255, 255, 255, 0.9);
}

/* BUTTONS */
.btn {
  transition: all 0.3s ease;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

/* HR */
hr {
  border-color: rgba(255, 255, 255, 0.1);
  opacity: 1;
}

/* LABELS */
.form-label {
  color: rgba(255, 255, 255, 0.8);
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.fw-bold {
  color: rgba(255, 255, 255, 0.95);
}

/* H5 HEADERS */
h5 {
  color: rgba(255, 255, 255, 0.95);
  font-weight: 600;
}

/* ANIMATIONS */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.card {
  animation: fadeIn 0.5s ease;
}
</style>
