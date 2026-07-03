@extends('layouts.app')

@section('content')
    <livewire:appointment-list :client-id="(int) request()->route('client')" />
@endsection
