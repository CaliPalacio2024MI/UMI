<div id="createClassificationModal" class="modal-overlay">
    <div class="modal-content-container">
        <div class="modal-header-custom">
            <h5 id="createClassificationModalLabel">Clasificaciones de carrera</h5>
            <button type="button" class="close-custom" aria-label="Cerrar">&times;</button>
        </div>

        <div class="modal-body-custom classification-modal-body">
            <div class="classification-modal-scroll">
                @if(isset($careerClassifications) && $careerClassifications->isNotEmpty())
                    <div class="classification-modal-list">
                        <span class="classification-modal-list__title">Registradas en esta institución</span>
                        <ul class="classification-modal-list__items">
                            @foreach($careerClassifications as $clasificacion)
                                <li class="classification-modal-list__row">
                                    <span class="classification-modal-list__name">{{ $clasificacion->name }}</span>
                                    @if(Auth::user()->hasAnyRole(['master']))
                                        <form method="post"
                                            action="{{ route('control.careers.classifications.destroy', $clasificacion) }}"
                                            class="classification-modal-list__delete-form"
                                            onsubmit="return confirm('¿Eliminar esta clasificación?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="classification-modal-delete-btn" title="Eliminar clasificación" aria-label="Eliminar clasificación">×</button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="classification-modal-empty">Aún no hay clasificación.</p>
                @endif
            </div>

            <form id="classificationStoreForm" method="post" action="{{ route('control.careers.classifications.store') }}" class="classification-modal-form">
                @csrf
                <div class="form-field">
                    <label for="classification_name">Nombre de la clasificación</label>
                    <div id="classification_name_ajax_error" class="error-message" style="display: none;" role="alert"></div>
                    <input type="text"
                        id="classification_name"
                        name="classification_name"
                        class="@error('classification_name') validation-error @enderror"
                        value="{{ old('classification_name') }}"
                        autocomplete="off">
                    @error('classification_name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-footer-custom mt-3">
                    <button type="submit" class="submit-button">Guardar clasificación</button>
                </div>
            </form>
        </div>
    </div>
</div>
