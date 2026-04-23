<div class="game-box">
    <h2>🎮 ATRAPA LA RESPUESTA</h2>
    <p id="gameScore">PUNTOS: 0</p>
    <p id="gameTime">TIEMPO: 30</p>

    <div id="gameArea"></div>

    <button id="startGame">INICIAR JUEGO</button>
</div>

<style>
.game-box{
    width:100%;
    height:500px;
    background:#111;
    color:white;
    padding:20px;
    border-radius:12px;
}
#gameArea{
    position:relative;
    height:350px;
    overflow:hidden;
    background:#222;
    margin:10px 0;
}
.card{
    position:absolute;
    padding:10px 16px;
    background:#0d6efd;
    border-radius:8px;
    cursor:pointer;
    user-select:none;
    animation: fall linear forwards;
}
@keyframes fall{
    from{ top:-40px; }
    to{ top:350px; }
}
</style>
