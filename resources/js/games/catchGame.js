let score = 0;
let time = 30;
let interval, spawn;

const answers = [
    { text: "HTML", correct: true },
    { text: "Perro", correct: false },
    { text: "CSS", correct: false },
    { text: "PHP", correct: false }
];

document.getElementById('startGame')?.addEventListener('click', startGame);

function startGame(){
    score = 0;
    time = 30;
    document.getElementById('gameScore').innerText = "PUNTOS: 0";
    document.getElementById('gameTime').innerText = "TIEMPO: 30";

    interval = setInterval(()=>{
        time--;
        document.getElementById('gameTime').innerText = "TIEMPO: "+time;
        if(time <= 0) endGame();
    },1000);

    spawn = setInterval(createCard, 900);
}

function createCard(){
    const area = document.getElementById('gameArea');
    const card = document.createElement('div');
    const pick = answers[Math.floor(Math.random()*answers.length)];

    card.className = 'card';
    card.innerText = pick.text;
    card.style.left = Math.random() * (area.clientWidth - 60) + 'px';
    card.style.animationDuration = (Math.random()*2+2)+'s';

    card.onclick = ()=>{
        if(pick.correct){
            score += 10;
        }else{
            score -= 5;
        }
        document.getElementById('gameScore').innerText = "PUNTOS: "+score;
        card.remove();
    };

    area.appendChild(card);

    setTimeout(()=>card.remove(),4000);
}

function endGame(){
    clearInterval(interval);
    clearInterval(spawn);
    alert("JUEGO TERMINADO\nPUNTAJE: "+score);
}
