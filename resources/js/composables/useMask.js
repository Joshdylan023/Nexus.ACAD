export function useMask() {
  const masks = {
    cnpj: '##.###.###/####-##',
    cpf: '###.###.###-##',
    cep: '#####-###',
    telefone: '(##) ####-####',
    celular: '(##) #####-####',
    telefoneOuCelular: ['(##) ####-####', '(##) #####-####']
  };

  const formatCnpj = (value) => {
    if (!value) return '';
    const cnpj = value.replace(/\D/g, '');
    return cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
  };

  const formatCep = (value) => {
    if (!value) return '';
    const cep = value.replace(/\D/g, '');
    return cep.replace(/^(\d{5})(\d{3})$/, '$1-$2');
  };

  const formatTelefone = (value) => {
    if (!value) return '';
    const tel = value.replace(/\D/g, '');
    if (tel.length === 11) {
      return tel.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
    }
    return tel.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
  };

  const removeMask = (value) => {
    return value ? value.replace(/\D/g, '') : '';
  };

  return {
    masks,
    formatCnpj,
    formatCep,
    formatTelefone,
    removeMask
  };
}
