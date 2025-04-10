@if(isset($breadcrumbs) && count($breadcrumbs))
    <nav class="mb-4 text-sm text-gray-600">
        <ol class="flex items-center space-x-2">
            @foreach ($breadcrumbs as $breadcrumb)
                <li class="flex items-center">
                    @if (!$loop->last)
                        <a href="{{ $breadcrumb['url'] }}" class="text-blue-600 hover:text-blue-800 underline">
                            {{ $breadcrumb['label'] }}
                        </a>
                        <span class="mx-1">/</span>
                    @else
                        <span class="text-gray-500">{{ $breadcrumb['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
