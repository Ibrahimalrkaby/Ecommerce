@extends('backend.layouts.master')
@section('main-content')
    <div class="container-fluid">
        <input type="file" name="images[]" id="images" accept="image/*" multiple>
    </div>
@endsection
