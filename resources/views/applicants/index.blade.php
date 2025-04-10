@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Applicants</h1>
    <div id="app">
        <applicants-list :initial-applicants='@json($applicants)'></applicants-list>
    </div>
@endsection
