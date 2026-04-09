@extends('layouts.app')

@section('content')

<div class="main-content">

    <div class="history-page">
        <h1>Analysis History</h1>

        <div class="history-grid">

            @forelse($scans as $scan)

            <div class="history-card" onclick="window.location.href='{{ route("scans.show",$scan->id) }}'">

                <img src="{{ asset('storage/'.$scan->image_path) }}" class="history-image">

                <div class="history-content">

                    <div class="history-id">Scan #{{ $scan->id }}</div>

                    <div class="history-date">
                        {{ $scan->created_at->format('Y-m-d H:i') }}
                    </div>

                    @if($scan->status == 'completed')
                        <div class="status-badge-full status-completed">Completed</div>
                    @elseif($scan->status == 'pending')
                        <div class="status-badge-full status-pending">Pending</div>
                    @else
                        <div class="status-badge-full status-detected">Failed</div>
                    @endif

                </div>

            </div>

            @empty
                <p>لا يوجد فحوصات حتى الآن</p>
            @endforelse

        </div>

    </div>

</div>

@endsection
