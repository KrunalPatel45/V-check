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
                            <label class="col-sm-2 col-form-label" for="name">Company</label>
                            <div class="col-sm-10">
                                <select id="company" name="company" class="form-control">
                                    <option value="" selected>Select Company</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->CompanyID }}" id="added_company">
                                            {{ $company->Name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($errors->has('company'))
                                    <span class="text-danger">
                                        {{ $errors->first('company') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="phone_number">Phone Number</label>
                            <div class="col-sm-10">
                                <input type="text" name="phone_number" id="phone_number" class="form-control"
                                    value="{{ old('phone_number') }}" />
                                @if ($errors->has('phone_number'))
                                    <span class="text-danger">
                                        {{ $errors->first('phone_number') }}
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
