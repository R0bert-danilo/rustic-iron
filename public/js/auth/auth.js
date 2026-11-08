/**
 * RUSTIC IRON - JAVASCRIPT DE AUTENTICAÇÃO
 */

function alternarTelaAuth(tela) {
    const secaoLogin = document.getElementById('secao-login');
    const secaoRegistro = document.getElementById('secao-registro');
    const btnToggleLogin = document.getElementById('btn-toggle-login');
    const btnToggleRegistro = document.getElementById('btn-toggle-registro');

    if (tela === 'registro') {
        secaoLogin?.classList.remove('active');
        secaoRegistro?.classList.add('active');
        btnToggleLogin?.classList.remove('active');
        btnToggleRegistro?.classList.add('active');

        desativarInputs(secaoLogin);
        ativarInputs(secaoRegistro);
    } else {
        secaoRegistro?.classList.remove('active');
        secaoLogin?.classList.add('active');
        btnToggleRegistro?.classList.remove('active');
        btnToggleLogin?.classList.add('active');

        ativarInputs(secaoLogin);
        desativarInputs(secaoRegistro);
    }
}

function desativarInputs(secao) {
    if (!secao) return;
    secao.querySelectorAll('input').forEach(input => {
        input.dataset.eraRequired = input.required;
        input.required = false;
    });
}

function ativarInputs(secao) {
    if (!secao) return;
    secao.querySelectorAll('input').forEach(input => {
        if (input.dataset.eraRequired === 'true') {
            input.required = true;
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const formLogin = document.getElementById('form-login');
    const inputUsername = document.getElementById('username');
    const checkRemember = document.getElementById('remember-me');

    const usuarioSalvo = localStorage.getItem('rustic_iron_remember_user');
    if (usuarioSalvo && inputUsername) {
        inputUsername.value = usuarioSalvo;
        if (checkRemember) checkRemember.checked = true;
    }

    if (formLogin) {
        formLogin.addEventListener('submit', () => {
            const usuario = inputUsername?.value.trim();
            if (checkRemember?.checked && usuario) {
                localStorage.setItem('rustic_iron_remember_user', usuario);
            } else {
                localStorage.removeItem('rustic_iron_remember_user');
            }
        });
    }

    const inputCnpj = document.getElementById('cnpj');
    if (inputCnpj) {
        inputCnpj.addEventListener('input', (e) => {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length > 14) v = v.slice(0, 14);
            v = v.replace(/^(\d{2})(\d)/, '$1.$2');
            v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
            v = v.replace(/(\d{4})(\d)/, '$1-$2');
            e.target.value = v;
        });
    }
});