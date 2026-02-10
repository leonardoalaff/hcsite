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
        linhaMenu.classList.toggle('active')
        linhaMenu2.classList.toggle('active')
        abrirMenu.classList.toggle('active')
})

    menuIconPerfil.addEventListener('click', () => {
        cardPerfis.classList.toggle('active')
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












    function fracaoParaDecimal(valor) {
        valor = valor.toString().trim();

        // Ex: "2 3/8"
        if (valor.includes(" ")) {
            const partes = valor.split(" ");
            return parseFloat(partes[0]) + fracaoParaDecimal(partes[1]);
        }

        // Ex: "3/8"
        if (valor.includes("/")) {
            const [num, den] = valor.split("/");
            return parseFloat(num) / parseFloat(den);
        }

        // Ex: "0.5"
        return parseFloat(valor);
    }

    function decimalParaFracao(decimal, maxDen = 64) {
        let melhorNum = 1;
        let melhorDen = 1;
        let menorErro = Math.abs(decimal - 1);

        for (let den = 1; den <= maxDen; den++) {
            let num = Math.round(decimal * den);
            let erro = Math.abs(decimal - num / den);

            if (erro < menorErro) {
                menorErro = erro;
                melhorNum = num;
                melhorDen = den;
            }
        }

        return `${melhorNum}/${melhorDen}`;
    }

    function mmParaPol() {
        const mm = document.getElementById("mm").value;
        if (mm === "") return;

        const polegadasDecimal = mm / 25.4;
        const polegadasFracao = decimalParaFracao(polegadasDecimal);

        document.getElementById("resultado").innerText =
            `${mm} mm = ${polegadasFracao}" (${polegadasDecimal.toFixed(4)})`;
    }

    function polParaMm() {
        const polInput = document.getElementById("pol").value;
        if (polInput === "") return;

        const polegadasDecimal = fracaoParaDecimal(polInput);
        const mm = polegadasDecimal * 25.4;

        document.getElementById("resultado").innerText =
            `${polInput}" = ${mm.toFixed(2)} mm`;
    }








    const btnAcoTubo = document.querySelector('#btnacotubo');
const boxIframe1 = document.querySelector('#boxiframe1');
const btnDjafer = document.querySelector('#btndjafer');
const boxIframe2 = document.querySelector('#boxiframe2');


btnAcoTubo.addEventListener('click', () => {
   
    boxIframe1.classList.toggle("active");
});

btnDjafer.addEventListener('click', () => {
   
    boxIframe2.classList.toggle("active");
});



    





    document.addEventListener("DOMContentLoaded", () => {
    const container = document.querySelector(".s-ferramentas .container");

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                container.classList.add("aparecer");
            }
        });
    }, {
        threshold: 0.3
    });

    observer.observe(container);
});




const slider = document.getElementById("carrossel");

let isDown = false;
let startX;
let scrollLeft;

slider.addEventListener("mousedown", (e) => {
    isDown = true;
    startX = e.pageX - slider.offsetLeft;
    scrollLeft = slider.scrollLeft;
});

slider.addEventListener("mouseleave", () => {
    isDown = false;
});

slider.addEventListener("mouseup", () => {
    isDown = false;
});

slider.addEventListener("mousemove", (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - slider.offsetLeft;
    const walk = (x - startX) * 2; // velocidade do drag
    slider.scrollLeft = scrollLeft - walk;
});


tipoPerfil.addEventListener('click', () => {
    perfilSelecionado = "detalhamento";
    document.getElementById("perfil_escolhido").value = perfilSelecionado;
    boxSenha.classList.add("active");
});



const video = document.getElementById("bgVideo");
const gradiente = document.getElementById("gradiente");

video.addEventListener("ended", () => {
    gradiente.style.background = "black";
    gradiente.style.transition = "2s";
});