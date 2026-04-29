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