@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Background Check Details</h1>

    <div class="mb-6">
        <p><strong>Applicant:</strong> {{ $check['applicant']['first_name'] }} {{ $check['applicant']['last_name'] }}</p>
        <p><strong>Status:</strong> {{ ucfirst($check['status']) }}</p>
        <p><strong>Perform URL:</strong>
            <a href="{{ $check['perform_url'] }}" target="_blank" class="text-blue-600 underline">Open</a>
        </p>
    </div>

    <h2 class="text-xl font-semibold mb-2">Steps</h2>

    <table class="min-w-full text-sm border bg-white shadow rounded">
        <thead class="bg-gray-100">
        <tr>
            <th class="text-left py-2 px-3">Type</th>
            <th class="text-left py-2 px-3">Step ID</th>
            <th class="text-left py-2 px-3">Cost</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($check['steps'] as $step)
            <tr class="border-t">
                <td class="py-2 px-3">{{ $step['type'] }}</td>
                <td class="py-2 px-3">{{ $step['amiqus_step_id'] }}</td>
                <td class="py-2 px-3">£{{ number_format($step['cost'], 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="mt-6">
        <a href="{{ route('applicants.show', $check['applicant']['id']) }}"
           class="text-blue-600 hover:text-blue-800 underline">
            ← Back to Applicant
        </a>
    </div>
@endsection
