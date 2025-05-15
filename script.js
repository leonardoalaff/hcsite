const addSobra = document.querySelector('.texto-menu-add-sobra')
const iconAddSobra = document.querySelector('.menu-icon-add-sobra')
const cardAddSobra = document.querySelector('.card-add-sobra')
const fecharCardSobra = document.querySelector('.fechar-card-sobra')

addSobra.addEventListener('click', () => {
    cardAddSobra.classList.toggle('active')} )

iconAddSobra.addEventListener('click', () => {
    cardAddSobra.classList.add('active')} )

fecharCardSobra.addEventListener('click', () => {
    cardAddSobra.classList.remove('active')} )