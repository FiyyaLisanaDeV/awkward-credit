@extends('layouts.app', ['title' => 'Analisis '.$permohonan->no_permohonan])

@section('content')
    @livewire('analisis.workbench', ['permohonan' => $permohonan])
@endsection
