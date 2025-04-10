@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Manage Authentication</h1>

    @if(session('success'))
        <div class="p-4 mb-4 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="p-4 mb-4 bg-red-100 text-red-700 rounded">{{ session('error') }}</div>
    @endif

    @if($valid)
        <div class="p-4 bg-green-50 border border-green-200 rounded">
            <p><strong>Connection Active</strong></p>
            <p>Token expires at: {{ $token->expires_at->format('Y-m-d H:i:s') }}</p>
        </div>
    @else
        <a href="{{ route('amiqus.authorize') }}" class="inline-block mt-4 bg-blue-600 text-white px-4 py-2 rounded shadow">
            Connect to Amiqus
        </a>
    @endif
@endsection
