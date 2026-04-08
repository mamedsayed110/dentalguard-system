@extends('layouts.app')

@section('content')

<div class="main-content">

    <section class="upload-section">
        <h2>Upload New Dental Image</h2>
        <p>Start AI analysis using dental images</p>
        <a href="{{ route('scans.upload') }}">
            <button class="btn-upload">Upload Image</button>
        </a>
    </section>

    <section>
        <h2 class="section-title">Recent Analyses</h2>

        <div class="analyses-grid">

            @forelse($scans as $scan)
                <div class="analysis-card">

                    <img src="{{ asset('storage/'.$scan->image_path) }}" class="analysis-image">

                    <div class="analysis-content">

                        <div class="analysis-title">
                            {{ $scan->created_at->format('Y-m-d') }}
                        </div>

                        <div class="analysis-footer">

                            <span class="status-badge 
                                {{ $scan->status == 'completed' ? 'status-healthy' : 'status-minor' }}">
                                {{ ucfirst($scan->status) }}
                            </span>

                            <a href="{{ route('scans.show', $scan->id) }}">
                                <button class="btn-view">View</button>
                            </a>

                        </div>

                    </div>
                </div>
            @empty
                <p>No scans yet.</p>
            @endforelse

        </div>
    </section>

</div>

@endsection
