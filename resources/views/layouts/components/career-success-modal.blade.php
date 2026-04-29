{{-- Modal "Operación exitosa": Carreras (redirect), Materias (redirect), clasificación AJAX --}}
<div id="careerSuccessModal" class="modal-overlay career-success-modal" style="display: none;">
    <div class="modal-container career-success-modal__box">
        <div class="career-success-modal__icon-wrap">
            <svg class="career-success-modal__check" width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="#a8d5a2" stroke-width="2"/>
                <path d="M8 12l3 3 5-6" stroke="#27ae60" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h3 class="career-success-modal__title">Operación exitosa</h3>
        <p class="career-success-modal__message" id="careerSuccessModalMessage">Operación completada.</p>
        <button type="button" class="career-success-modal__ok" id="careerSuccessModalOk">OK</button>
    </div>
</div>
<style>
/* Centrado respecto al main (no a toda la ventana): el sidebar ya ocupa ~270px a la izquierda */
#careerSuccessModal.career-success-modal {
    left: 270px;
    width: calc(100% - 270px);
    box-sizing: border-box;
}
@media (max-width: 900px) {
    #careerSuccessModal.career-success-modal {
        left: 70px;
        width: calc(100% - 70px);
    }
}
@media (max-width: 600px) {
    #careerSuccessModal.career-success-modal {
        left: 0;
        width: 100%;
    }
}
.career-success-modal {
    align-items: center;
    justify-content: center;
    z-index: 12050;
}
.career-success-modal__box {
    max-width: 420px;
    max-height: 280px;
    text-align: center;
    padding: 16px 24px 20px;
    border: 1px solid #ddd;
}
.career-success-modal__icon-wrap { margin-bottom: 16px; }
.career-success-modal__check { display: inline-block; width: 72px; height: 72px; }
.career-success-modal__title { font-size: 1.15rem; font-weight: 700; color: #333; margin: 0 0 6px 0; }
.career-success-modal__message { font-size: 0.9rem; color: #666; margin: 0 0 14px 0; }
.career-success-modal__ok {
    background: #223F70;
    color: #fff;
    border: none;
    width: 80px;
    padding: 8px 28px;
    border-radius: 6px;
    font-size: 0.95rem;
    cursor: pointer;
    display: block;
    margin: 0 auto;
}
.career-success-modal__ok:hover { background: #15213F; }
</style>
@if(session('success') && request('modal') === 'success')
<script>window.careerSuccessMessage = @json(session('success'));</script>
@endif
