@if($errors->any())
    @php
        $careerSpecificMessage = null;
        foreach (['official_id', 'name'] as $field) {
            if ($errors->has($field)) {
                $first = (string) $errors->first($field);
                if (str_starts_with($first, 'Ya existe')) {
                    $careerSpecificMessage = $first;
                    break;
                }
            }
        }
    @endphp
    <div class="error-message" role="alert">
        <p class="error-message-single">{{ $careerSpecificMessage ?? 'Te falta rellenar campos.' }}</p>
    </div>
@endif
