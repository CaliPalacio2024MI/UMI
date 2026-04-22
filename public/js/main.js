function mostrarProgramas(tipo){

    const section = document.getElementById("careersSection");
    const offset = 100;

    window.scrollTo({
        top: section.offsetTop - offset,
        behavior: "smooth"
    });

    const titulo = document.getElementById("tituloCarreras");
    const grid = document.getElementById("careersGrid");

    grid.innerHTML = "";

    let data = [];

    if(tipo === "licenciatura"){
        titulo.innerText = "Licenciaturas";
        data = ["Negocios", "Turismo", "Marketing", "Finanzas"];
    }

    if(tipo === "posgrado"){
        titulo.innerText = "Posgrados";
        data = ["MBA", "Luxury Management", "Finance Master"];
    }

    if(tipo === "ejecutivo"){
        titulo.innerText = "Educación Ejecutiva";
        data = ["Leadership", "Business Strategy"];
    }

    if(tipo === "online"){
        titulo.innerText = "Cursos en línea";
        data = ["Digital Marketing", "Hospitality Online"];
    }

    data.forEach(item => {
        grid.innerHTML += `
            <div class="career-card">
                <h3>${item}</h3>
                <p>Programa profesional</p>
            </div>
        `;
    });
}
function revealOnScroll() {
    const reveals = document.querySelectorAll('.reveal');

    reveals.forEach(el => {
        const windowHeight = window.innerHeight;
        const elementTop = el.getBoundingClientRect().top;
        const elementBottom = el.getBoundingClientRect().bottom;

        const visiblePoint = 100;

        
        if (elementTop < windowHeight - visiblePoint && elementBottom > 0) {
            el.classList.add('active');
        } 
      
        else {
            el.classList.remove('active');
        }
    });
}

window.addEventListener('scroll', revealOnScroll);
window.addEventListener('load', revealOnScroll);