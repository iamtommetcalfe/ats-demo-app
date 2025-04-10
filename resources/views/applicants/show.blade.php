@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Applicant Details</h1>
    <div id="app">
        <!-- Pass applicant data (including backgroundChecks) to Vue component -->
        <applicant-detail :applicant-data='@json($applicant)'></applicant-detail>
    </div>
    <div class="mt-4">
        <a href="{{ route('applicants.index') }}" class="text-blue-600 hover:text-blue-800">&larr; Back to list</a>
    </div>
@endsection
