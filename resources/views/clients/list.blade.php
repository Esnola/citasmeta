@extends('layouts.app')

@section('content')

  @livewire('client-list', ['showAllClients' => true])

@endsection
