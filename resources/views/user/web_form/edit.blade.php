@extends('layouts/layoutMaster')

@section('title', 'Edit Web Form')

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

                // Get existing content from textarea
                var existingContent = document.getElementById('page_desc').value;
                if (existingContent) {
                    quill.clipboard.dangerouslyPasteHTML(existingContent);
                }

                // On form submit, save Quill HTML into textarea
                document.querySelector('form').onsubmit = function(e) {
                    e.preventDefault();
                    document.getElementById('page_desc').value = quill.root.innerHTML;
                    e.target.submit();
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
        <form id="webForm" action="{{ route('store_web_form') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="web_form_id" id="web_form_id" value="{{ $webform->Id }}" />
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
                                    value="{{ $payee->Name }}" />
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
                                    <img id="preview" src="{{ asset($webform->Logo) }}" alt="Company Logo"
                                        style="width: 100px;">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="address">Address</label>
                            <div class="col-sm-10">
                                <textarea id="address" name="address" class="form-control">{{ $payee->Address1 }}</textarea>
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
                                    value="{{ $payee->City }}" />
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
                                <select name="state" id="state" class="form-control">
                                    <option value="">-- Select State --</option>
                                    @php
                                        $states = [
                                            'Alabama',
                                            'Alaska',
                                            'Arizona',
                                            'Arkansas',
                                            'California',
                                            'Colorado',
                                            'Connecticut',
                                            'Delaware',
                                            'Florida',
                                            'Georgia',
                                            'Hawaii',
                                            'Idaho',
                                            'Illinois',
                                            'Indiana',
                                            'Iowa',
                                            'Kansas',
                                            'Kentucky',
                                            'Louisiana',
                                            'Maine',
                                            'Maryland',
                                            'Massachusetts',
                                            'Michigan',
                                            'Minnesota',
                                            'Mississippi',
                                            'Missouri',
                                            'Montana',
                                            'Nebraska',
                                            'Nevada',
                                            'New Hampshire',
                                            'New Jersey',
                                            'New Mexico',
                                            'New York',
                                            'North Carolina',
                                            'North Dakota',
                                            'Ohio',
                                            'Oklahoma',
                                            'Oregon',
                                            'Pennsylvania',
                                            'Rhode Island',
                                            'South Carolina',
                                            'South Dakota',
                                            'Tennessee',
                                            'Texas',
                                            'Utah',
                                            'Vermont',
                                            'Virginia',
                                            'Washington',
                                            'West Virginia',
                                            'Wisconsin',
                                            'Wyoming',
                                        ];
                                    @endphp

                                    @foreach ($states as $state)
                                        <option value="{{ $state }}"
                                            {{ $payee->State == $state ? 'selected' : '' }}>
                                            {{ $state }}
                                        </option>
                                    @endforeach
                                </select>
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
                                    value="{{ $payee->Zip }}" />
                                @if ($errors->has('zip'))
                                    <span class="text-danger">
                                        {{ $errors->first('zip') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="phone_number">Phone Number</label>
                            <div class="col-sm-10">
                                <input type="text" name="phone_number" id="phone_number" class="form-control"
                                    value="{{ $payee->PhoneNumber }}" />
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
                                        <textarea name="page_desc" id="page_desc" style="display:none;">{{ old('page_desc', $webform->page_desc ?? '') }}</textarea>
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
     <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js" integrity="sha512-F5Ul1uuyFlGnIT1dk2c4kB4DBdi5wnBJjVhL7gQlGh46Xn0VhvD8kgxLtjdZ5YN83gybk/aASUAlpdoWUjRR3g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        Inputmask({
            mask: "999-999-9999",
            placeholder: "",             // No placeholders
            showMaskOnHover: false,      // Don't show mask on hover
            showMaskOnFocus: false,      // Don't show mask on focus
        }).mask("#phone_number");
    </script>
     <script src="https://cdn.jsdelivr.net/npm/just-validate@3.3.3/dist/just-validate.production.min.js"></script>
    <script>
        const validation = new JustValidate('#webForm', {
            errorLabelCssClass: 'text-danger'
        });

        validation
            .addField('[name="name"]', [
                { rule: 'required', errorMessage: 'Please enter name' }
            ])
            .addField('[name="address"]', [
                { rule: 'required', errorMessage: 'Please enter address' }
            ])
            .addField('[name="city"]', [
                { rule: 'required', errorMessage: 'Please enter city' }
            ])
            .addField('[name="state"]', [
                { rule: 'required', errorMessage: 'Please enter state' }
            ])
            .addField('[name="zip"]', [
                { rule: 'required', errorMessage: 'Please enter zip' }
            ])
            .addField('[name="phone_number"]', [
                {
                    rule: 'customRegexp',
                    value: /^\d{3}-\d{3}-\d{4}$/,
                    errorMessage: 'The phone number field format is invalid.'
                }
            ]).onSuccess((event) => {
                event.target.submit();
            });

    </script>
@endsection
