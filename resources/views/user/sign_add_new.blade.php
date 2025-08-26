@extends('layouts/layoutMaster')

@section('title', 'Add Signature')

@php
    $base_url = url('/');
@endphp

<!-- Vendor Styles -->
@section('vendor-style')
    <link
        href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Dancing+Script&family=Allura&family=Pacifico&family=Sacramento&family=Alex+Brush&family=Parisienne&family=Marck+Script&family=Tangerine&family=Pinyon+Script&family=Courgette&family=Kaushan+Script&family=Yellowtail&family=Satisfy&family=Italianno&family=Arizonia&family=Cookie&family=Meddon&family=Bilbo&family=Norican&family=Herr+Von+Muellerhoff&family=Rochester&family=Fondamento&family=Euphoria+Script&family=Bad+Script&family=Over+the+Rainbow&family=Calligraffitti&family=Homemade+Apple&family=Patrick+Hand&family=Indie+Flower&family=Gloria+Hallelujah&family=Reenie+Beanie&family=La+Belle+Aurore&family=Rock+Salt&family=Waiting+for+the+Sunrise&family=Allan&family=Shadows+Into+Light&family=Shadows+Into+Light+Two&family=Loved+by+the+King&family=Give+You+Glory&family=Mr+Dafoe&family=Mr+De+Haviland&family=Mrs+Saint+Delafield&family=Petit+Formal+Script&family=Rouge+Script&family=Ruthie&family=Seaweed+Script&family=Stalemate&family=Nanum+Pen+Script&family=Caveat&family=Covered+By+Your+Grace&family=Amatic+SC&family=Architects+Daughter&family=Patrick+Hand+SC&family=Chewy&family=Sue+Ellen+Francisco&family=Just+Another+Hand&family=Pangolin&family=Kalam&family=Cedarville+Cursive&family=Zeyada&family=Nothing+You+Could+Do&family=Just+Me+Again+Down+Here&family=The+Girl+Next+Door&family=Square+Peg&family=Charmonman&family=Dekko&family=Gaegu&family=Birthstone+Bounce&family=Comforter+Brush&family=Yomogi&family=Moon+Dance&family=Swanky+and+Moo+Moo&family=Delius+Swash+Caps&family=Sunshiney&family=Edu+SA+Beginner&family=Water+Brush&family=Twinkle+Star&family=Ms+Madi&family=Grand+Hotel&family=Send+Flowers&family=Playwrite&family=Niconne&family=Kristi&display=swap"
        rel="stylesheet">
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss'])

    <style>
        .signature-preview {
            transition: background-color 0.3s ease;
        }
        .signature-preview:hover {
            background-color: #e3dede;
        }
    </style>
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
<script>
    const fonts = [
        'Great Vibes', 'Dancing Script', 'Allura', 'Pacifico', 'Sacramento', 'Alex Brush', 'Parisienne',
        'Marck Script', 'Tangerine', 'Pinyon Script', 'Courgette', 'Kaushan Script', 'Yellowtail',
        'Satisfy', 'Italianno', 'Arizonia', 'Cookie', 'Meddon', 'Bilbo', 'Norican',
        'Herr Von Muellerhoff', 'Rochester', 'Fondamento', 'Euphoria Script', 'Bad Script',
        'Over the Rainbow', 'Calligraffitti', 'Homemade Apple', 'Patrick Hand', 'Indie Flower',
        'Gloria Hallelujah', 'Reenie Beanie', 'La Belle Aurore', 'Rock Salt', 'Waiting for the Sunrise',
        'Allan', 'Shadows Into Light', 'Shadows Into Light Two', 'Loved by the King', 'Give You Glory',
        'Mr Dafoe', 'Mr De Haviland', 'Mrs Saint Delafield', 'Petit Formal Script', 'Rouge Script',
        'Ruthie', 'Seaweed Script', 'Stalemate', 'Nanum Pen Script', 'Caveat', 'Covered By Your Grace',
        'Amatic SC', 'Architects Daughter', 'Patrick Hand SC', 'Covered By Your Grace', 'Chewy',
        'Sue Ellen Francisco', 'Just Another Hand', 'Pangolin', 'Kalam', 'Cedarville Cursive', 'Zeyada',
        'Nothing You Could Do', 'Just Me Again Down Here', 'The Girl Next Door', 'Square Peg',
        'Charmonman', 'Dekko', 'Gaegu', 'Birthstone Bounce', 'Comforter Brush', 'Yomogi', 'Moon Dance',
        'Swanky and Moo Moo', 'Delius Swash Caps', 'Sunshiney', 'Edu SA Beginner', 'Water Brush',
        'Twinkle Star', 'Ms Madi', 'Grand Hotel', 'Send Flowers', 'Playwrite', 'Niconne', 'Kristi'
    ];

    let selectedSignature = null;
    let nameInput = '';

    function generatePreviews() {
        const name = document.getElementById('nameInput').value.trim();
        const container = document.getElementById('previewContainer');

        if (!name) {
            $('.alert-danger').text('Please enter name').fadeIn().delay(4000).fadeOut();
            return;
        }

        selectedSignature = null;
        container.innerHTML = '';
        container.classList.add('row', 'g-2');

        fonts.forEach(font => {
            const col = document.createElement('div');
            col.classList.add('col-md-4', 'col-sm-6', 'col-12', 'd-flex');

            const card = document.createElement('div');
            card.classList.add('card', 'signature-preview', 'shadow', 'mb-2', 'h-100', 'w-100');
            card.style.cursor = 'pointer';

            const cardBody = document.createElement('div');
            cardBody.classList.add('card-body', 'p-2', 'd-flex', 'align-items-center', 'justify-content-center');
            card.appendChild(cardBody);

            const div = document.createElement('div');
            div.classList.add('text-center');
            div.style.fontFamily = font;
            div.style.fontSize = '60px';
            div.style.lineHeight = 'normal';
            div.style.overflowWrap = 'anywhere';
            div.innerText = name;

            card.onclick = () => {
                selectedSignature = { text: name, font: font, size: 45 };
                
                // Draw initial canvas preview
                drawModalCanvas();

                // Initialize slider and value text
                document.getElementById('fontSizeSlider').value = selectedSignature.size;
                document.getElementById('fontSizeValue').innerText = selectedSignature.size + 'px';

                // Show modal
                const myModal = new bootstrap.Modal(document.getElementById('fontSizeModal'));
                myModal.show();
            };

            cardBody.appendChild(div);
            col.appendChild(card);
            container.appendChild(col);
        });
    }

    document.getElementById('fontSizeSlider').addEventListener('input', function() {
        selectedSignature.size = parseInt(this.value);
        document.getElementById('fontSizeValue').innerText = this.value + 'px';
        drawModalCanvas();
    });

    function drawModalCanvas() {
        const canvas = document.getElementById('signaturePreviewModalCanvas');
        const ctx = canvas.getContext('2d');

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // White background
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Draw the signature text
        ctx.font = `${selectedSignature.size}px '${selectedSignature.font}'`;
        ctx.fillStyle = '#000';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(selectedSignature.text, canvas.width / 2, canvas.height / 2);
    }

    document.getElementById('confirmSaveBtn').addEventListener('click', function() {
        nameInput = document.getElementById('nameInput').value.trim();

        if (!selectedSignature) {
            $('.alert-danger').text('Please select signature').fadeIn().delay(4000).fadeOut();
            return;
        }

        const canvas = document.getElementById('signaturePreviewModalCanvas');
        const dataUrl = canvas.toDataURL('image/png');

        fetch("{{ route('store_sign') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                signature: dataUrl,
                name: nameInput
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                $('#fontSizeModal').modal('hide');
                $('html, body').scrollTop(0);
                $('.alert-success').text('Signature saved successfully!').fadeIn().delay(4000).fadeOut();
                setTimeout(() => {
                    window.location.href = "{{ route('get_web_forms') }}";
                }, 2000);
            }
        });
    });
</script>
@endsection

@section('content')
<div class="alert alert-danger" style="display: none;"></div>
<div class="alert alert-success" style="display: none;"></div>

<div class="card mb-6">
    <div class="card-header">
        <h5>Add Signature</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-8">
                <input type="text" id="nameInput" class="form-control" placeholder="Enter your name" autocomplete="off">
            </div>
            <div class="col-4">
                <button class="btn btn-primary" onclick="generatePreviews()">Generate</button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="preview-container text-center" id="previewContainer">
            Enter your name and click Generate to preview
        </div>
    </div>
</div>

<!-- Font Size Modal -->
<div class="modal fade" id="fontSizeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Adjust Font Size</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <canvas id="signaturePreviewModalCanvas" width="350" height="100" style="border:1px solid #ccc;"></canvas>
        <div class="mt-3">
            <input type="range" id="fontSizeSlider" min="20" max="100" value="45" class="form-range">
            <div class="mt-1">Font Size: <span id="fontSizeValue">45px</span></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmSaveBtn">Confirm & Save</button>
      </div>
    </div>
  </div>
</div>
@endsection
