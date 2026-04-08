@extends('layouts.app')

@section('content')

<div class="main-content">

    <div class="upload-page-content">

        <h1>Upload Dental Image</h1>
        <p>Upload image for analysis</p>

        <form method="POST" action="{{ route('scans.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="drop-zone" onclick="document.getElementById('imageInput').click()">

                <div class="drop-zone-icon">☁️</div>
                <h3>Click to Upload</h3>
                <p>PNG - JPG only</p>

                <input type="file" name="image" id="imageInput" class="file-input" accept="image/*" required>

            </div>

            <div class="image-preview" id="previewBox">
                <div class="preview-container">
                    <img id="previewImage" class="preview-image">
                    <button class="btn-analyze">Upload Now</button>
                </div>
            </div>
        </form>

    </div>

</div>

<script>
document.getElementById('imageInput').addEventListener('change', function (e) {

    const input = e.target;
    const preview = document.getElementById("previewBox");
    const image = document.getElementById("previewImage");

    if(input.files && input.files[0]){
        const reader = new FileReader();
        reader.onload = function (e) {
            image.src = e.target.result;
            preview.classList.add("active");
        }
        reader.readAsDataURL(input.files[0]);
    }
});
</script>

@endsection
