@extends('layout.app')

@section('content')
    <div class="container">
        <div class="edit-wrapper">
            <div class="edit-title">
                Add <div class="title">Contract</div>
            </div>

            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {!! Form::open(['route' => 'contract.store', 'files' => true]) !!}
            @include('contract.form', ['action'=>'add'])
            {!! Form::close() !!}

        </div>

    </div>


@stop
