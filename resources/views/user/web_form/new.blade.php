@extends('layouts/layoutMaster')

@section('title', 'Add Web Form')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/quill/editor.scss'])
    @vite(['resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js'])
    @vite(['resources/assets/vendor/libs/quill/katex.js', 'resources/assets/vendor/libs/quill/quill.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/form-layouts.js'])
    @vite(['resources/assets/js/forms-editors.js'])

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var quillContainer = document.getElementById('editor-container');

            if (quillContainer) {
                var quill = new Quill('#editor-container', {
                    theme: 'snow',
                    placeholder: 'Type Something...',
                    modules: {
                        toolbar: [
                            [{
                                font: []
                            }, {
                                size: []
                            }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{
                                color: []
                            }, {
                                background: []
                            }],
                            [{
                                script: 'super'
                            }, {
                                script: 'sub'
                            }],
                            [{
                                header: '1'
                            }, {
                                header: '2'
                            }, 'blockquote', 'code-block'],
                            [{
                                list: 'ordered'
                            }, {
                                list: 'bullet'
                            }, {
                                indent: '-1'
                            }, {
                                indent: '+1'
                            }],
                            [{
                                direction: 'rtl'
                            }],
                            ['link', 'image', 'video'],
                            ['clean']
                        ]
                    }
                });

                // Save Quill content in hidden textarea before submitting form
                document.querySelector('form').onsubmit = function() {
                    document.getElementById('page_desc').value = quill.root.innerHTML;
                };
            } else {
                console.error(
                    "❌ Error: Quill container not found! Check if #editor-container exists in your HTML.");
            }
        });
    </script>

    <script>
        document.getElementById('logo').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('preview');

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block'; // Show the image
                };

                reader.readAsDataURL(file); // Convert file to Base64
            } else {
                preview.src = '';
                preview.style.display = 'none'; // Hide the image if no file selected
            }
        });
    </script>

@endsection

@section('content')
    <div class="row">
        <form action="{{ route('store_web_form') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <!-- Basic Layout -->
            <div class="col-xxl">
                <div class="card mb-6">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Add Web Form</h5>
                        <div class="d-flex align-items-center">
                            <button type="submit" class="btn btn-primary">Save</button>
                            &nbsp;&nbsp;
                            <a href="{{ route('get_web_forms') }}" class="btn btn-primary mr-4">
                                Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="name">Name</label>
                            <div class="col-sm-10">
                                <input type="text" name="name" id="name" class="form-control"
                                    value="{{ old('name') }}" />
                                @if ($errors->has('name'))
                                    <span class="text-danger">
                                        {{ $errors->first('name') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="name">LOGO</label>
                            <div class="col-sm-10">
                                <input type="file" name="logo" id="logo" class="form-control"
                                    value="{{ old('logo') }}" />
                                @if ($errors->has('logo'))
                                    <span class="text-danger">
                                        {{ $errors->first('logo') }}
                                    </span>
                                @endif

                                <div id="preview-container"
                                    style="padding: 14px; margin-top: 20px; border: 1px solid #efe6e6; width: 130px; text-align: center;">
                                    <img id="preview"
                                        src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGh5WFH8TOIfRKxUrIgJZoDCs1yvQ4hIcppw&s"
                                        alt="Company Logo" style="width: 100px;">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="address">Address</label>
                            <div class="col-sm-10">
                                <textarea id="address" name="address" class="form-control">{{ old('address') }}</textarea>
                                @if ($errors->has('address'))
                                    <span class="text-danger">
                                        {{ $errors->first('address') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="city">City</label>
                            <div class="col-sm-10">
                                <input type="text" name="city" id="city" class="form-control"
                                    value="{{ old('city') }}" />
                                @if ($errors->has('city'))
                                    <span class="text-danger">
                                        {{ $errors->first('city') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="state">State</label>
                            <div class="col-sm-10">
                                <input type="text" name="state" id="state" class="form-control"
                                    value="{{ old('state') }}" />
                                @if ($errors->has('state'))
                                    <span class="text-danger">
                                        {{ $errors->first('state') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="zip">Zip</label>
                            <div class="col-sm-10">
                                <input type="text" name="zip" id="zip" class="form-control"
                                    value="{{ old('zip') }}" />
                                @if ($errors->has('zip'))
                                    <span class="text-danger">
                                        {{ $errors->first('zip') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="page_desc">Page Description</label>
                            <div class="col-sm-10">
                                <div class="card">
                                    <div class="card-body">
                                        <div id="editor-container">
                                        </div>
                                        <textarea name="page_desc" id="page_desc" style="display:none;"></textarea>
                                    </div>
                                    @if ($errors->has('page_desc'))
                                        <span class="text-danger">
                                            {{ $errors->first('page_desc') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
        </form>
    </div>
@endsection
