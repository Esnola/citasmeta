@extends('layouts.app')

@section('content')
    <livewire:client-list />
    <section id="programar-whatsapp">
        <livewire:client-message-scheduler />
    </section>
@endsection
