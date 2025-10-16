<template>
  <div class="minha-area">
    <div class="card card-glass mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="text-white mb-0">
          <i class="fas fa-user-circle me-2"></i>Minha Área
        </h4>
        <div v-if="!loading">
          <button v-if="!editMode" @click="editMode = true" class="btn btn-primary btn-sm">
            <i class="fas fa-edit me-1"></i>Editar Perfil
          </button>
          <button v-if="editMode" @click="cancelEdit" class="btn btn-secondary btn-sm me-2">
            <i class="fas fa-times me-1"></i>Cancelar
          </button>
          <button v-if="editMode" @click="saveProfile" class="btn btn-success btn-sm" :disabled="saving">
            <i class="fas fa-save me-1"></i>
            {{ saving ? 'Salvando...' : 'Salvar' }}
          </button>
        </div>
      </div>
      <div class="card-body">
        <!-- Skeleton Loading -->
        <div v-if="loading" class="placeholder-glow">
          <div class="row mb-4">
            <div class="col-md-3 text-center">
              <div class="rounded-circle mx-auto placeholder" style="width: 150px; height: 150px;"></div>
              <div class="mt-3">
                <h5 class="placeholder col-8"></h5>
                <p class="placeholder col-6"></p>
                <p class="placeholder col-4"></p>
              </div>
            </div>
            <div class="col-md-9">
              <div class="info-group mb-4">
                <h6 class="placeholder col-4 mb-3"></h6>
                <div class="row g-3">
                  <div v-for="n in 6" :key="n" class="col-md-6">
                    <div class="info-item">
                      <label class="placeholder col-5"></label>
                      <p class="placeholder col-7"></p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="info-group">
                <h6 class="placeholder col-4 mb-3"></h6>
                <div class="row g-3">
                  <div v-for="n in 6" :key="n" class="col-md-6">
                    <div class="info-item">
                      <label class="placeholder col-5"></label>
                      <p class="placeholder col-7"></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Conteúdo -->
        <div v-else>
          <!-- Foto e Informações Principais -->
          <div class="row mb-4">
            <div class="col-md-3 text-center">
              <!-- Foto de Perfil -->
              <div class="profile-photo-container mb-3">
                <img 
                  v-if="user.colaborador?.foto_registro_rh" 
                  :src="`/storage/${user.colaborador.foto_registro_rh}`" 
                  alt="Foto"
                  class="rounded-circle foto-perfil"
                >
                <div v-else class="rounded-circle foto-placeholder mx-auto">
                  <i class="fas fa-user fa-4x text-white-50"></i>
                </div>
                
                <!-- Botões de Upload/Remover Foto -->
                <div class="photo-actions mt-2">
                  <input 
                    ref="photoInput" 
                    type="file" 
                    accept="image/*" 
                    @change="handlePhotoUpload" 
                    style="display: none;"
                  >
                  <button 
                    v-if="!uploadingPhoto" 
                    @click="$refs.photoInput.click()" 
                    class="btn btn-sm btn-outline-light me-1"
                  >
                    <i class="fas fa-camera me-1"></i>{{ user.colaborador?.foto_registro_rh ? 'Alterar' : 'Adicionar' }}
                  </button>
                  <button 
                    v-if="uploadingPhoto" 
                    class="btn btn-sm btn-outline-light me-1" 
                    disabled
                  >
                    <span class="spinner-border spinner-border-sm me-1"></span>
                    Enviando...
                  </button>
                  <button 
                    v-if="user.colaborador?.foto_registro_rh && !uploadingPhoto" 
                    @click="removePhoto" 
                    class="btn btn-sm btn-outline-danger"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>

              <!-- Nome (editável) -->
              <div v-if="!editMode">
                <h5 class="text-white">{{ user.name }}</h5>
              </div>
              <div v-else class="mb-3">
                <input 
                  v-model="editForm.name" 
                  type="text" 
                  class="form-control text-center" 
                  placeholder="Nome Completo"
                >
              </div>

              <p class="text-white-50 mb-1" v-if="user.colaborador">{{ user.colaborador.cargo }}</p>
              <p class="text-white-50 small" v-if="user.colaborador">Matrícula: {{ user.colaborador.matricula_funcional }}</p>
              
              <button @click="showPasswordModal = true" class="btn btn-sm btn-outline-light mt-2">
                <i class="fas fa-lock me-1"></i>Alterar Senha
              </button>
            </div>

            <div class="col-md-9">
              <!-- Informações Profissionais -->
              <div class="info-group mb-4">
                <h6 class="text-white mb-3"><i class="fas fa-briefcase me-2"></i>Informações Profissionais</h6>
                <div class="row g-3">
                  <div class="col-md-6" v-if="user.colaborador">
                    <div class="info-item">
                      <label class="text-white-50 small">Cargo</label>
                      <p class="text-white mb-0">{{ user.colaborador.cargo }}</p>
                    </div>
                  </div>
                  <div class="col-md-6" v-if="user.colaborador">
                    <div class="info-item">
                      <label class="text-white-50 small">Matrícula Funcional</label>
                      <p class="text-white mb-0">{{ user.colaborador.matricula_funcional }}</p>
                    </div>
                  </div>
                  <div class="col-md-6" v-if="user.colaborador">
                    <div class="info-item">
                      <label class="text-white-50 small">Setor</label>
                      <p class="text-white mb-0">{{ user.colaborador.setor_vinculo?.setor?.nome || 'Não informado' }}</p>
                    </div>
                  </div>
                  <div class="col-md-6" v-if="user.colaborador">
                    <div class="info-item">
                      <label class="text-white-50 small">Data de Admissão</label>
                      <p class="text-white mb-0">{{ formatDate(user.colaborador.data_admissao) }}</p>
                    </div>
                  </div>
                  <div class="col-md-6" v-if="user.colaborador">
                    <div class="info-item">
                      <label class="text-white-50 small">Status</label>
                      <span :class="getStatusClass(user.colaborador.status)">
                        {{ user.colaborador.status }}
                      </span>
                    </div>
                  </div>
                  <div class="col-md-6" v-if="user.colaborador?.is_gestor">
                    <div class="info-item">
                      <label class="text-white-50 small">Perfil</label>
                      <p class="text-white mb-0">
                        <span class="badge bg-primary">
                          <i class="fas fa-crown me-1"></i>Gestor de Equipe
                        </span>
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Informações Pessoais -->
              <div class="info-group">
                <h6 class="text-white mb-3"><i class="fas fa-user me-2"></i>Informações Pessoais</h6>
                <div class="row g-3">
                  <!-- E-mail Pessoal -->
                  <div class="col-md-6">
                    <div class="info-item">
                      <label class="text-white-50 small">E-mail Pessoal</label>
                      <p class="text-white mb-0">{{ user.email }}</p>
                    </div>
                  </div>
                  
                  <!-- E-mail Funcional -->
                  <div class="col-md-6" v-if="user.colaborador">
                    <div class="info-item">
                      <label class="text-white-50 small">E-mail Funcional</label>
                      <p class="text-white mb-0">{{ user.colaborador.email_funcional }}</p>
                    </div>
                  </div>

                  <!-- CPF -->
                  <div class="col-md-6">
                    <div class="info-item">
                      <label class="text-white-50 small">CPF</label>
                      <p class="text-white mb-0">{{ formatCpf(user.cpf) }}</p>
                    </div>
                  </div>

                  <!-- Data de Nascimento -->
                  <div class="col-md-6">
                    <div class="info-item">
                      <label class="text-white-50 small">Data de Nascimento</label>
                      <div v-if="!editMode">
                        <p class="text-white mb-0">{{ formatDate(user.data_nascimento) }}</p>
                      </div>
                      <div v-else>
                        <input 
                          v-model="editForm.data_nascimento" 
                          type="date" 
                          class="form-control form-control-sm"
                        >
                      </div>
                    </div>
                  </div>

                  <!-- Nome Social -->
                  <div class="col-md-6">
                    <div class="info-item">
                      <label class="text-white-50 small">Nome Social</label>
                      <div v-if="!editMode">
                        <p class="text-white mb-0">{{ user.nome_social || 'Não informado' }}</p>
                      </div>
                      <div v-else>
                        <input 
                          v-model="editForm.nome_social" 
                          type="text" 
                          class="form-control form-control-sm"
                          placeholder="Nome Social (opcional)"
                        >
                      </div>
                    </div>
                  </div>

                  <!-- Telefone Principal -->
                  <div class="col-md-6">
                    <div class="info-item">
                      <label class="text-white-50 small">Telefone Principal</label>
                      <div v-if="!editMode">
                        <p class="text-white mb-0">{{ user.telefone_principal || 'Não informado' }}</p>
                      </div>
                      <div v-else>
                        <input 
                          v-model="editForm.telefone_principal" 
                          type="text" 
                          class="form-control form-control-sm"
                          placeholder="(00) 00000-0000"
                        >
                      </div>
                    </div>
                  </div>

                  <!-- Telefone Secundário -->
                  <div class="col-md-6">
                    <div class="info-item">
                      <label class="text-white-50 small">Telefone Secundário</label>
                      <div v-if="!editMode">
                        <p class="text-white mb-0">{{ user.telefone_secundario || 'Não informado' }}</p>
                      </div>
                      <div v-else>
                        <input 
                          v-model="editForm.telefone_secundario" 
                          type="text" 
                          class="form-control form-control-sm"
                          placeholder="(00) 00000-0000"
                        >
                      </div>
                    </div>
                  </div>

                  <!-- Endereço -->
                  <div class="col-md-12">
                    <div class="info-item">
                      <label class="text-white-50 small">Endereço</label>
                      <div v-if="!editMode">
                        <p class="text-white mb-0">{{ user.endereco_completo || 'Não informado' }}</p>
                      </div>
                      <div v-else>
                        <textarea 
                          v-model="editForm.endereco_completo" 
                          class="form-control form-control-sm"
                          rows="2"
                          placeholder="Endereço completo"
                        ></textarea>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Alteração de Senha -->
    <div v-if="showPasswordModal" class="modal-overlay" @click.self="closePasswordModal">
      <div class="modal-content-custom">
        <div class="modal-header-custom">
          <h5 class="text-white mb-0">
            <i class="fas fa-lock me-2"></i>Alterar Senha
          </h5>
          <button @click="closePasswordModal" class="btn-close btn-close-white"></button>
        </div>
        <div class="modal-body-custom">
          <div class="mb-3">
            <label class="form-label text-white-50">Senha Atual</label>
            <input 
              v-model="passwordForm.current_password" 
              type="password" 
              class="form-control"
              placeholder="Digite sua senha atual"
              :class="{ 'is-invalid': passwordErrors.current_password }"
            >
            <div v-if="passwordErrors.current_password" class="invalid-feedback">
              {{ passwordErrors.current_password }}
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label text-white-50">Nova Senha</label>
            <input 
              v-model="passwordForm.new_password" 
              type="password" 
              class="form-control"
              placeholder="Digite a nova senha (mín. 8 caracteres)"
              :class="{ 'is-invalid': passwordErrors.new_password }"
            >
            <div v-if="passwordErrors.new_password" class="invalid-feedback">
              {{ passwordErrors.new_password }}
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label text-white-50">Confirmar Nova Senha</label>
            <input 
              v-model="passwordForm.new_password_confirmation" 
              type="password" 
              class="form-control"
              placeholder="Digite a nova senha novamente"
            >
          </div>
        </div>
        <div class="modal-footer-custom">
          <button @click="closePasswordModal" class="btn btn-secondary">Cancelar</button>
          <button @click="changePassword" class="btn btn-primary" :disabled="changingPassword">
            <i class="fas fa-check me-1"></i>
            {{ changingPassword ? 'Alterando...' : 'Alterar Senha' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import emitter from '@/eventBus';

const router = useRouter();
const loading = ref(true);
const saving = ref(false);
const editMode = ref(false);
const user = ref({ colaborador: null });
const editForm = ref({});
const originalData = ref({});

// Alteração de senha
const showPasswordModal = ref(false);
const changingPassword = ref(false);
const passwordForm = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});
const passwordErrors = ref({});

// ⭐ NOVO: Upload de foto
const photoInput = ref(null);
const uploadingPhoto = ref(false);

const fetchUserData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/me');
    const userData = response.data.user || response.data;
    user.value = userData;
    
    // Inicializa o formulário de edição
    editForm.value = {
      name: userData.name,
      nome_social: userData.nome_social,
      data_nascimento: userData.data_nascimento,
      telefone_principal: userData.telefone_principal,
      telefone_secundario: userData.telefone_secundario,
      endereco_completo: userData.endereco_completo
    };
    
    // Guarda uma cópia dos dados originais
    originalData.value = { ...editForm.value };
    
  } catch (error) {
    console.error('Erro ao carregar dados do usuário:', error);
  } finally {
    loading.value = false;
  }
};

const saveProfile = async () => {
  saving.value = true;
  try {
    const response = await axios.put('/api/v1/profile/update', editForm.value);
    user.value = response.data.user;
    editMode.value = false;
    
    // Atualiza dados originais
    originalData.value = { ...editForm.value };
    emitter.emit('profile-updated');
    alert('Perfil atualizado com sucesso!');
  } catch (error) {
    console.error('Erro ao salvar perfil:', error);
    alert('Erro ao salvar perfil: ' + (error.response?.data?.message || 'Erro desconhecido'));
  } finally {
    saving.value = false;
  }
};

const cancelEdit = () => {
  editForm.value = { ...originalData.value };
  editMode.value = false;
};

const changePassword = async () => {
  passwordErrors.value = {};
  
  if (!passwordForm.value.current_password) {
    passwordErrors.value.current_password = 'Senha atual é obrigatória';
    return;
  }
  
  if (!passwordForm.value.new_password || passwordForm.value.new_password.length < 8) {
    passwordErrors.value.new_password = 'Nova senha deve ter no mínimo 8 caracteres';
    return;
  }
  
  if (passwordForm.value.new_password !== passwordForm.value.new_password_confirmation) {
    alert('As senhas não conferem!');
    return;
  }
  
  changingPassword.value = true;
  
  try {
    await axios.post('/api/v1/profile/change-password', passwordForm.value);
    alert('Senha alterada com sucesso!');
    closePasswordModal();
  } catch (error) {
    console.error('Erro ao alterar senha:', error);
    
    if (error.response?.status === 422) {
      passwordErrors.value.current_password = 'Senha atual incorreta';
    } else {
      alert('Erro ao alterar senha: ' + (error.response?.data?.message || 'Erro desconhecido'));
    }
  } finally {
    changingPassword.value = false;
  }
};

// ⭐ NOVO: Método de upload
const handlePhotoUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validação do tamanho (max 2MB)
  if (file.size > 2 * 1024 * 1024) {
    alert('Arquivo muito grande! Máximo 2MB.');
    return;
  }

  // Validação do tipo
  if (!file.type.startsWith('image/')) {
    alert('Por favor, selecione uma imagem válida.');
    return;
  }

  uploadingPhoto.value = true;

  try {
    const formData = new FormData();
    formData.append('photo', file);

    const response = await axios.post('/api/v1/profile/upload-photo', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    // Atualiza a foto localmente
    user.value.colaborador.foto_registro_rh = response.data.photo_url.replace('/storage/', '');
    
    alert('Foto atualizada com sucesso!');
    emitter.emit('profile-updated');
    // Recarrega os dados para garantir
    await fetchUserData();
    
  } catch (error) {
    console.error('Erro ao fazer upload da foto:', error);
    alert('Erro ao fazer upload da foto: ' + (error.response?.data?.message || 'Erro desconhecido'));
  } finally {
    uploadingPhoto.value = false;
    // Limpa o input para permitir upload da mesma imagem novamente
    if (photoInput.value) {
      photoInput.value.value = '';
    }
  }
};

// ⭐ NOVO: Método para remover foto
const removePhoto = async () => {
  if (!confirm('Tem certeza que deseja remover sua foto de perfil?')) {
    return;
  }

  try {
    await axios.delete('/api/v1/profile/delete-photo');
    user.value.colaborador.foto_registro_rh = null;
    alert('Foto removida com sucesso!');
    emitter.emit('profile-updated');
    await fetchUserData();
  } catch (error) {
    console.error('Erro ao remover foto:', error);
    alert('Erro ao remover foto: ' + (error.response?.data?.message || 'Erro desconhecido'));
  }
};

const closePasswordModal = () => {
  showPasswordModal.value = false;
  passwordForm.value = {
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
  };
  passwordErrors.value = {};
};

const formatDate = (date) => {
  if (!date) return 'Não informado';
  return new Date(date).toLocaleDateString('pt-BR');
};

const formatCpf = (cpf) => {
  if (!cpf) return 'Não informado';
  return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
};

const getStatusClass = (status) => {
  const classes = {
    'Ativo': 'badge bg-success',
    'Afastado': 'badge bg-warning',
    'Desligado': 'badge bg-danger'
  };
  return classes[status] || 'badge bg-secondary';
};

onMounted(() => {
  fetchUserData();
});
</script>

<style scoped>
.minha-area {
  padding: 1rem;
}

.card-glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 15px;
}

.foto-perfil {
  width: 150px;
  height: 150px;
  object-fit: cover;
  border: 3px solid rgba(255, 255, 255, 0.3);
}

.foto-placeholder {
  width: 150px;
  height: 150px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  border: 3px solid rgba(255, 255, 255, 0.3);
}

.profile-photo-container {
  position: relative;
  display: inline-block;
}

.photo-actions {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
}

.info-group {
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
}

.info-item {
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
}

.info-item label {
  display: block;
  margin-bottom: 0.25rem;
  font-weight: 600;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  backdrop-filter: blur(5px);
}

.modal-content-custom {
  background: rgba(30, 30, 45, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 15px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

.modal-header-custom {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-body-custom {
  padding: 1.5rem;
}

.modal-footer-custom {
  padding: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}
</style>