@extends('backend.layouts.master')

@section('title', 'E-SHOP || Banner Create')

@section('main-content')

    <div class="card">
        <h5 class="card-header">Add Banner</h5>
        <div class="card-body">
            <form method="POST" action="{{ route('banner.store') }}" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="inputTitle" class="col-form-label">Title <span class="text-danger">*</span></label>
                    <input id="inputTitle" type="text" name="title" placeholder="Enter title"
                        value="{{ old('title') }}" class="form-control">
                    @error('title')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="inputDesc" class="col-form-label">Description</label>
                    <textarea class="form-control" id="description" name="description">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="photo" class="col-form-label">Photo <span class="text-danger">*</span></label>

                    <input type="file" id="photo" name="photo" class="form-control" accept="image/*"
                        onchange="previewImage(event)">

                    <div id="holder" style="margin-top:15px;">
                        <img id="preview" src="" alt="" style="max-height:100px; display:none;">
                    </div>

                    @error('photo')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <script>
                    function previewImage(event) {
                        const preview = document.getElementById('preview');
                        const file = event.target.files[0];

                        if (file) {
                            preview.src = URL.createObjectURL(file);
                            preview.style.display = 'block';
                        } else {
                            preview.style.display = 'none';
                        }
                    }
                </script>

                <div class="form-group">
                    <label for="status" class="col-form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    @error('status')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <button type="reset" class="btn btn-warning">Reset</button>
                    <button class="btn btn-success" type="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/summernote/summernote.min.css') }}">
@endpush
@push('scripts')
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script src="{{ asset('backend/summernote/summernote.min.js') }}"></script>
    <script>
        $('#lfm').filemanager('image');

        $(document).ready(function() {
            $('#description').summernote({
                placeholder: "Write short description.....",
                tabsize: 2,
                height: 150
            });
        });
    </script>
@endpush
