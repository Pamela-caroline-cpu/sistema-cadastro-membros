// Confirmação antes de excluir um membro
function confirmarExclusao(nome) {
    return confirm('Tem certeza que deseja excluir o membro "' + nome + '"? Esta ação não pode ser desfeita.');
}

// máscaras de CPF, telefone e CEP na mão mesmo, sem lib
document.addEventListener('DOMContentLoaded', function () {
    const cpf = document.getElementById('cpf');
    if (cpf) {
        cpf.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '').slice(0, 11);
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            this.value = v;
        });
    }

    const telefone = document.getElementById('telefone');
    if (telefone) {
        telefone.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '').slice(0, 11);
            if (v.length > 10) {
                v = v.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (v.length > 5) {
                v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (v.length > 2) {
                v = v.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            this.value = v;
        });
    }

    const cep = document.getElementById('cep');
    if (cep) {
        cep.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '').slice(0, 8);
            v = v.replace(/(\d{5})(\d{1,3})$/, '$1-$2');
            this.value = v;
        });
    }

    // só mostra o campo de senha se a função não for Membro
    const funcao = document.getElementById('funcao');
    const fieldsetAcesso = document.getElementById('fieldset-acesso');
    if (funcao && fieldsetAcesso) {
        funcao.addEventListener('change', function () {
            fieldsetAcesso.style.display = (this.value === 'Membro') ? 'none' : '';
        });
    }
});
