const addSobra = document.querySelector('.texto-menu-add-sobra')
const iconAddSobra = document.querySelector('.menu-icon-add-sobra')
const cardAddSobra = document.querySelector('.card-add-sobra')
const fecharCardSobra = document.querySelector('.fechar-card-sobra')
const cardPerfis = document.querySelector('.card-perfis')
const menuIconAddSobra = document.querySelector('.menu-icon-add-sobra')
const menuIconPerfil = document.querySelector('.menu-icon-perfil')
const tipoPerfil = document.querySelector('.tipo-perfil')
const tipoPerfil2 = document.querySelector('.tipo-perfil2')
const tipoPerfil3 = document.querySelector('.tipo-perfil3')
const boxSenha = document.querySelector('.box-senha')

addSobra.addEventListener('click', () => {
    cardAddSobra.classList.toggle('active')} )

iconAddSobra.addEventListener('click', () => {
    cardAddSobra.classList.add('active')} )

fecharCardSobra.addEventListener('click', () => {
    cardAddSobra.classList.remove('active')} )

    tipoPerfil.addEventListener('click', () => {
        boxSenha.classList.toggle('active')
    })

    tipoPerfil2.addEventListener('click', () => {
        boxSenha.classList.toggle('active')
    })

    tipoPerfil3.addEventListener('click', () => {
        boxSenha.classList.toggle('active')
    })




    document.addEventListener("DOMContentLoaded", function () {
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








function abrirModal(src) {
    const modal = document.getElementById('modal-imagem');
    const imgExpandida = document.getElementById('imagem-expandida');
    imgExpandida.src = src;
    modal.style.display = 'flex';
}