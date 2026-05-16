@extends('layouts.app')

@section('content')

<div class="container">

    <h1 class="mb-4">Propiedades</h1>

    <div class="row">

        @foreach($propiedades as $propiedad)

            <div class="col-md-4 mb-4">

                <div class="card shadow-sm h-100">

                    <div class="card-body">

                        <h5>
                            {{ $propiedad['name'] ?? 'Sin nombre' }}
                        </h5>

                        <p>
                            ID:
                            {{ $propiedad['id'] ?? 'N/A' }}
                        </p>

                        <a
                            href="{{ url('/propiedades/'.$propiedad['id'].'/departamentos') }}"
                            class="btn btn-primary"
                        >
                            Ver departamentos
                        </a>

                    </div>

                </div>

            </div>

        @endforeach

    </div>

</div>

@endsection
