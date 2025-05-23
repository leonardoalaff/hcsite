document.addEventListener("DOMContentLoaded", function () {
    const addSobra = document.querySelector('.texto-menu-add-sobra');
    const iconAddSobra = document.querySelector('.menu-icon-add-sobra');
    const cardAddSobra = document.querySelector('.card-add-sobra');
    const fecharCardSobra = document.querySelector('.fechar-card-sobra');
    const cardPerfis = document.querySelector('.card-perfis');
    const menuIconAddSobra = document.querySelector('.menu-icon-add-sobra');
    const menuIconPerfil = document.querySelector('.menu-icon-perfil');
    const tipoPerfil = document.querySelector('.tipo-perfil');
    const tipoPerfil2 = document.querySelector('.tipo-perfil2');
    const tipoPerfil3 = document.querySelector('.tipo-perfil3');
    const boxSenha = document.querySelector('.box-senha');
    const btnEntrar = document.querySelector('#btnEntrar'); // Adicione o ID no botão no HTML
    const campoSenha = document.querySelector('#campoSenha'); // Adicione o ID no campo de senha
    let perfilSelecionado = null;
    const abrirMenu = document.querySelector('.abrir-menu');
    const linhaMenu = document.querySelector('.linha-menu');
    const linhaMenu2 = document.querySelector('.linha-menu2');
    const menu = document.querySelector('.menu');

    abrirMenu.addEventListener('click', () => {
        menu.classList.toggle('active');
})

    // Perfil selecionado
    tipoPerfil.addEventListener('click', () => {
        perfilSelecionado = "detalhamento";
        document.getElementById("perfil_escolhido").value = perfilSelecionado;
        boxSenha.style.display = "block";
    });

    tipoPerfil2.addEventListener('click', () => {
        perfilSelecionado = "encarregado";
        document.getElementById("perfil_escolhido").value = perfilSelecionado;
        boxSenha.style.display = "block";
    });

    tipoPerfil3.addEventListener('click', () => {
        perfilSelecionado = "operador";
        document.getElementById("perfil_escolhido").value = perfilSelecionado;
        boxSenha.style.display = "block";
    });

  
    // Ações de exibir/esconder card de sobra
    addSobra.addEventListener('click', () => {
        cardAddSobra.classList.toggle('active');
    });

    iconAddSobra.addEventListener('click', () => {
        cardAddSobra.classList.add('active');
    });

    fecharCardSobra.addEventListener('click', () => {
        cardAddSobra.classList.remove('active');
    });

    // Miniaturas de imagem
    const miniaturas = document.querySelectorAll(".miniatura");
    const modal = document.getElementById("modal-imagem");
    const imagemExpandida = document.getElementById("imagem-expandida");

    miniaturas.forEach(function (img) {
        img.addEventListener("click", function () {
            imagemExpandida.src = img.dataset.img;
            modal.style.display = "flex";
        });
    });

    imagemExpandida.addEventListener("click", function () {
        modal.style.display = "none";
        imagemExpandida.src = "";
    });
});

// Função global caso precise ser chamada diretamente
function abrirModal(src) {
    const modal = document.getElementById('modal-imagem');
    const imgExpandida = document.getElementById('imagem-expandida');
    imgExpandida.src = src;
    modal.style.display = 'flex';
}