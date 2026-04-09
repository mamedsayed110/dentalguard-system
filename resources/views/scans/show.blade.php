@extends('layouts.app')

@section('content')

<div class="main-content fade-in">

<div class="results-container">

    <!-- LEFT CARD -->
    <div class="results-left glow-card">

        <h2>Dental Image</h2>
        <p class="subtitle">AI Ready Scan</p>

        <div class="analyzed-image-container">
            <img id="scanImage" src="{{ asset('storage/'.$scan->image_path) }}" class="analyzed-image">
            <div id="loaderOverlay" class="image-overlay hidden">
                <div class="spinner"></div>
                <span>Analyzing...</span>
            </div>
        </div>

        <div class="analysis-meta">
            <span>📅 {{ $scan->created_at->format('Y-m-d') }}</span>
            <span class="status {{ $scan->status }}">
                {{ ucfirst($scan->status) }}
            </span>
        </div>

    </div>


    <!-- RIGHT CARD -->
    <div class="results-right glow-card">

        <h2>AI Analysis Result</h2>

        @if($scan->status != 'completed')

            <p class="pending-text">⚡ Ready for AI analysis</p>

            <form method="POST" action="{{ route('scans.analyze', $scan->id) }}" onsubmit="startAI()">
                @csrf
                <button class="btn-download pulse">🚀 Start AI Analysis</button>
            </form>

        @else

            <!-- Confidence -->
            <div class="confidence-box">
                AI Confidence
                <span>{{ $scan->result['confidence'] ?? 'N/A' }}%</span>

                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $scan->result['confidence'] }}%"></div>
                </div>
            </div>

            <!-- Findings -->
            <ul class="detections">
                @foreach($scan->result['detections'] as $item)
                    <li>✔ {{ $item }}</li>
                @endforeach
            </ul>

            <div class="description">
                {{ $scan->result['description'] }}
            </div>

            <button class="btn-download glow">📄 Download AI Report</button>

        @endif

    </div>

</div>

</div>



{{-- CSS --}}
<style>
.fade-in { animation: fadeup .7s ease; }
@keyframes fadeup { from{opacity:0;transform:translateY(30px)} to{opacity:1} }

.glow-card {
    background:white;
    border-radius:18px;
    padding:28px;
    box-shadow:0 0 30px rgba(0,212,255,.08);
    transition:.3s;
}
.glow-card:hover {
    box-shadow:0 0 50px rgba(0,212,255,.2);
}

.subtitle {color:#aaa;font-size:13px;margin-bottom:15px}

.analyzed-image-container{
    border-radius:16px;
    overflow:hidden;
    position:relative;
    background:black;
}
.analyzed-image{
    width:100%;
    transition:.5s;
}
.glow-card:hover .analyzed-image {
    transform:scale(1.05);
}

/* LOADER */
.image-overlay{
    position:absolute;
    inset:0;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    background:rgba(0,0,0,.7);
    color:white;
}
.hidden{display:none}
.spinner {
    width:40px;height:40px;
    border:4px solid rgba(255,255,255,.3);
    border-top-color:#00d4ff;
    border-radius:50%;
    animation:spin 1s linear infinite;
}
@keyframes spin {to{transform:rotate(360deg)}}

/* META */
.analysis-meta{
    display:flex;
    justify-content:space-between;
    margin-top:15px;
    color:#777
}
.status.completed{color:#10b981}
.status.pending{color:#f59e0b}

/* BUTTONS */
.btn-download{
    width:100%;
    margin-top:20px;
    padding:14px;
    border:none;
    border-radius:10px;
    background:#00d4ff;
    color:white;
    font-weight:bold;
    font-size:15px;
    cursor:pointer;
    transition:.3s;
}
.btn-download:hover{
    background:#00b8e0;
    transform:scale(1.03);
    box-shadow:0 0 20px rgba(0,212,255,.5);
}

.pulse{
    animation:pulse 2s infinite;
}
@keyframes pulse{
  0%{box-shadow:0 0 0 rgba(0,212,255,.7)}
  70%{box-shadow:0 0 25px rgba(0,212,255,0)}
  100%{}
}

.glow{box-shadow:0 0 20px rgba(0,212,255,.4)}

/* CONFIDENCE */
.confidence-box{
    background:#0ea5e9;
    color:white;
    padding:15px;
    border-radius:12px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.progress-bar{
    width:100%;
    height:6px;
    background:rgba(255,255,255,.2);
    border-radius:10px;
    margin-top:8px;
    overflow:hidden;
}
.progress-fill{
    height:100%;
    background:#22c55e;
}

/* RESULTS */
.detections{margin-top:20px}
.detections li{
    list-style:none;
    padding:8px;
    margin-bottom:5px;
    background:#f0fdff;
    border-left:4px solid #00d4ff;
    border-radius:6px;
}

.description{
    background:#f9fafb;
    padding:15px;
    margin-top:15px;
    border-radius:10px;
    color:#444;
    line-height:1.8;
}
</style>


{{-- JS --}}
<script>
function startAI(){
    document.getElementById("loaderOverlay").classList.remove("hidden");
}
</script>

@endsection
