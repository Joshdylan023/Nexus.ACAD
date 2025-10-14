<template>
  <div>
    <label v-if="label" class="form-label">{{ label }}</label>
    <input
      :type="type"
      class="form-control"
      :class="{ 'is-invalid': error }"
      :value="modelValue"
      @input="handleInput"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      ref="inputRef"
    />
    <div v-if="error" class="invalid-feedback">
      {{ error }}
    </div>
    <small v-if="hint" class="form-text text-muted">{{ hint }}</small>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useMask } from '@/composables/useMask';

const props = defineProps({
  modelValue: String,
  label: String,
  placeholder: String,
  type: { type: String, default: 'text' },
  mask: String, // 'cnpj', 'cep', 'telefone'
  required: Boolean,
  disabled: Boolean,
  error: String,
  hint: String
});

const emit = defineEmits(['update:modelValue']);

const { masks, formatCnpj, formatCep, formatTelefone, removeMask } = useMask();
const inputRef = ref(null);

const applyMask = (value) => {
  if (!value || !props.mask) return value;

  const cleanValue = removeMask(value);

  switch (props.mask) {
    case 'cnpj':
      return formatCnpj(cleanValue);
    case 'cep':
      return formatCep(cleanValue);
    case 'telefone':
    case 'celular':
      return formatTelefone(cleanValue);
    default:
      return value;
  }
};

const handleInput = (event) => {
  const value = event.target.value;
  const maskedValue = applyMask(value);
  
  // Atualiza o input com valor formatado
  if (inputRef.value) {
    inputRef.value.value = maskedValue;
  }
  
  // Emite valor sem máscara para o v-model
  emit('update:modelValue', removeMask(maskedValue));
};

// Formata o valor inicial
onMounted(() => {
  if (props.modelValue && inputRef.value) {
    inputRef.value.value = applyMask(props.modelValue);
  }
});

watch(() => props.modelValue, (newValue) => {
  if (inputRef.value && newValue) {
    inputRef.value.value = applyMask(newValue);
  }
});
</script>
