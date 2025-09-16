<x-layout>
    <style>
        .form-card {
            max-width: 600px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
    <div class="container pt-3">
        <div class="form-card">
            <h3 class="text-center mb-4">Buat Tiket</h3>
            <form action="{{ route('ticket.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- Judul -->
                <div class="mb-3">
                    <label for="title" class="form-label">Judul</label>
                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}"
                        placeholder="Masalah yang terjadi" required>
                </div>

                <!-- Deskripsi -->
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="5"
                        placeholder="Jelaskan lebih tentang masalah yang terjadi" required>{{ old('description') }}</textarea>
                </div>

                <!-- Upload Foto -->
                <div class="mb-3">
                    <label for="photo" class="form-label">Upload Foto (Opsional)</label>
                    <input class="form-control" type="file" id="photo" accept="image/*" capture="camera">
                    <input type="hidden" id="compressed_photo" name="photo">
                    <input type="hidden" id="photo_name" name="photo_name">
                </div>

                {{-- Jika ada data Perangkat, isi Kategori dan Lokasi sesuai Perangkat --}}
                @if ($device)
                    {{-- Perangkat --}}
                    <div class="mb-3">
                        <label for="device" class="form-label">Perangkat</label>
                        <input type="text" class="form-control" id="device" value="{{ $device['name'] ?? '-' }}"
                            readonly>
                        <input type="hidden" name="device_id" value="{{ $device['id'] ?? '' }}">
                        <input type="hidden" name="device_type" value="{{ $deviceType ?? '' }}">
                    </div>
                    {{-- Kategori --}}
                    <div class="mb-3">
                        <label for="category" class="form-label">Kategori</label>
                        <input type="text" class="form-control" id="category"
                            value="{{ $categories['name'] ?? '-' }}" readonly>
                        <input type="hidden" name="category_id" value="{{ $categories['id'] ?? '' }}">
                    </div>
                    {{-- Lokasi --}}
                    <div class="mb-3">
                        <label for="location" class="form-label">Lokasi</label>
                        <input type="text" class="form-control" id="location" value="{!! html_entity_decode($locations['name'] ?? '-') !!}"
                            readonly>
                        <input type="hidden" name="location_id" value="{{ $locations['id'] ?? '' }}">
                    </div>
                @else
                    {{-- Perangkat --}}
                    <div class="mb-3">
                        <label for="device" class="form-label">Perangkat (Opsional)</label>
                        <div class="input-group">
                            <input type="text" id="device" name="device" class="form-control"
                                placeholder="Scan QR label aset" readonly>
                            <button type="button" id="scanBtn" class="btn btn-outline-secondary"><i
                                    class="bi bi-qr-code-scan"></i>
                            </button>
                        </div>
                        <input type="hidden" id="device_id" name="device_id">
                        <input type="hidden" id="device_type" name="device_type">
                    </div>
                    <!-- Kategori -->
                    <div class="mb-3">
                        <label for="category" class="form-label">Kategori</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat['id'] }}">{{ $cat['name'] ?? 'Tanpa Nama' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Lokasi -->
                    <div class="mb-3">
                        <label for="location" class="form-label">Lokasi</label>
                        <select class="form-select" id="location_id" name="location_id" required>
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc['id'] }}">{!! html_entity_decode($loc['name'] ?? 'Tanpa Nama') !!}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <button type="submit" class="btn btn-primary w-100">Simpan</button>
            </form>
        </div>

        <!-- Modal kamera -->
        <div id="scannerModal" class="modal fade">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body d-flex flex-column justify-content-center align-items-center">
                        <div id="reader" style="width:300px"></div>
                        <select id="cameraSelect" class="form-select my-3 d-none"></select>
                        <button class="btn btn-primary btn-lg rounded-5" id="toggle-torch-btn">
                            <i class="bi bi-lightbulb-fill" id="torch-icon"></i>
                        </button>
                        <span id="torch-status" style="color: red;"></span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="{{ asset('js/html5-qrcode-2.3.8/html5-qrcode.min.js') }}"></script>
        <script>
            let compressedBlob = null;
            let originalFileName = null;

            document.getElementById('photo').addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = new Image();
                    img.onload = function() {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');

                        const maxWidth = 1280;
                        const scale = maxWidth / img.width;
                        canvas.width = maxWidth;
                        canvas.height = img.height * scale;

                        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                        // convert to base64 (JPEG, quality 0.7)
                        const compressedDataUrl = canvas.toDataURL('image/jpeg', 0.7);
                        document.getElementById('compressed_photo').value = compressedDataUrl;
                        document.getElementById('photo_name').value = file.name;

                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });

            const html5QrCode = new Html5Qrcode("reader");
            const scanButton = document.getElementById('scanBtn');
            const cameraSelect = document.getElementById('cameraSelect');
            let currentCameraId = null;
            let torchOn = false;
            let videoTrack = null;

            if (scanButton) {
                scanButton.addEventListener('click', async function() {
                    // Loading
                    overlay.classList.remove("d-none");
                    overlay.classList.add("d-flex");

                    try {
                        // Minta akses kamera untuk dapat label
                        const stream = await navigator.mediaDevices.getUserMedia({
                            video: true
                        });
                        stream.getTracks().forEach(track => track.stop());

                        const devices = await Html5Qrcode.getCameras();

                        if (devices.length === 0) {
                            alert("Tidak ada kamera ditemukan!");
                            return;
                        }

                        // Tampilkan Modal
                        const modal = new bootstrap.Modal(document.getElementById('scannerModal'));
                        modal.show();

                        // Tampilkan kamera select selalu
                        cameraSelect.classList.remove("d-none");
                        cameraSelect.innerHTML = '';

                        devices.forEach(device => {
                            const option = document.createElement("option");
                            option.value = device.id;
                            option.textContent = device.label || `Kamera ${device.id}`;
                            cameraSelect.appendChild(option);
                        });

                        // Cari kamera belakang dan set sebagai default
                        let backCamera = devices.find(d => /back|rear/i.test(d.label) && !/wide|obs|virtual/i.test(d
                            .label));
                        if (!backCamera) backCamera = devices.find(d => /back|rear/i.test(d.label));
                        if (!backCamera) backCamera = devices.find(d => !/obs|virtual/i.test(d.label));

                        const defaultCameraId = backCamera ? backCamera.id : devices[0].id;

                        cameraSelect.value = defaultCameraId;
                        startScanner(defaultCameraId);

                    } catch (err) {
                        console.error("Kamera gagal diakses:", err);
                        alert("Tidak bisa mengakses kamera.");
                    } finally {
                        overlay.classList.add("d-none");
                        overlay.classList.remove("d-flex");
                    }

                    // Ubah kamera dari Dropdown
                    cameraSelect.addEventListener("change", async function(e) {
                        const newCameraId = e.target.value;
                        if (newCameraId && newCameraId !== currentCameraId) {
                            await startScanner(newCameraId);
                        }
                    });

                    // Stop scanner saat modal ditutup
                    document.getElementById('scannerModal').addEventListener('hidden.bs.modal', function() {
                        html5QrCode.stop().then(() => {
                            console.log("QR scanner stopped.");
                            html5QrCode.clear(); // optional: bersihkan tampilan canvas
                        }).catch(err => {
                            console.error("Stop failed:", err);
                        });
                    });
                });
            }

            async function startScanner(cameraId) {
                try {
                    if (html5QrCode.getState() === Html5QrcodeScannerState.SCANNING && currentCameraId !== cameraId) {
                        await html5QrCode.stop();
                        await html5QrCode.clear();
                    }

                    currentCameraId = cameraId;

                    await html5QrCode.start(cameraId, {
                        fps: 10,
                        qrbox: 250
                    }, onScanSuccess);

                    // Ambil video track
                    const videoElem = document.querySelector("#reader video");
                    if (videoElem && videoElem.srcObject) {
                        const tracks = videoElem.srcObject.getVideoTracks();
                        if (tracks.length > 0) {
                            videoTrack = tracks[0];
                            document.getElementById("torch-status").textContent = "";
                            torchOn = false; // reset status torch saat start
                        }
                    }
                } catch (err) {
                    console.error("Gagal memulai kamera: ", err);
                    alert("Gagal memulai kamera: " + err.message);
                }
            }

            async function toggleTorch() {
                const statusEl = document.getElementById("torch-status");
                const torchIcon = document.getElementById("torch-icon");
                if (!videoTrack) {
                    alert("Video track belum tersedia. Mulai scanner dulu.");
                    return;
                }

                const capabilities = videoTrack.getCapabilities();
                if (!capabilities.torch) {
                    statusEl.textContent = "Senter tidak didukung oleh kamera ini.";
                    return;
                }

                try {
                    await videoTrack.applyConstraints({
                        advanced: [{
                            torch: !torchOn
                        }]
                    });
                    torchOn = !torchOn;
                    torchIcon.classList.toggle("bi-lightbulb-fill");
                    torchIcon.classList.toggle("bi-lightbulb");
                } catch (e) {
                    statusEl.textContent = "Gagal mengubah status Senter: " + e.message;
                }
            }

            document.getElementById("toggle-torch-btn").addEventListener("click", toggleTorch);

            function onScanSuccess(decodedText) {
                // Loading
                overlay.classList.remove("d-none");
                overlay.classList.add("d-flex");

                console.log("QR Code:", decodedText);
                // alert(decodedText);

                // Cari semua URL dengan domain adyawinsa.com
                const linkRegex = /https?:\/\/(?:[\w\-]+\.)*adyawinsa\.com\/[^\s]+/gi;
                const linkMatches = decodedText.match(linkRegex);

                // let domainUrl = window.location.origin;
                if (linkMatches && linkMatches.length > 0) {
                    const lastUrl = linkMatches[linkMatches.length - 1]; // Ambil url yang terakhir
                    const kodeRegex = /(?:asset\/index\.php\?id=|assets\/)([^"\s/]+)/i;
                    const kodeMatches = lastUrl.match(kodeRegex);

                    if (kodeMatches && kodeMatches.length > 0) {
                        const kode = kodeMatches[1];

                        // ✅ Hentikan kamera setelah scan
                        html5QrCode.stop().then(() => console.log("Camera stopped."));

                        // ✅ Tutup modal
                        const modalElement = document.getElementById('scannerModal');
                        bootstrap.Modal.getInstance(modalElement).hide();

                        try {
                            // ✅ Fetch data perangkat
                            let assetUrl = "{{ url('assets-info') }}";
                            fetch(`${assetUrl}/${kode}`)
                                .then(res => res.json())
                                .then(data => {
                                    const device = data.data
                                    if (data.status === "success") {
                                        console.log(device);
                                        document.getElementById("device_id").value = device.id;
                                        // document.getElementById("device_type").value = type;
                                        document.getElementById("device").value = device.name;
                                        if (scanButton) {
                                            scanButton.classList.add("d-none");
                                        }
                                        if (device.locations_id) {
                                            document.querySelector('select[name="location_id"]').value = device
                                                .locations_id;
                                            document.getElementById("location_id").style.pointerEvents = "none";
                                            document.getElementById("location_id").classList.remove("form-select");
                                            document.getElementById("location_id").classList.add("form-control");
                                        }

                                        // Isi kategori default sesuai tipe
                                        document.querySelector('select[name="category_id"]').value = device.itilcategoryid;
                                        document.getElementById("category_id").style.pointerEvents = "none";
                                        document.getElementById("category_id").classList.remove("form-select");
                                        document.getElementById("category_id").classList.add("form-control");

                                    } else if (data.status === "error") {
                                        alert("Error: " + data.message + ` (${kode})`);
                                    }
                                })
                                .catch(err => {
                                    console.error("Fetch error:", err);
                                    alert("Fetch error: " + (err.message || JSON.stringify(err)));
                                });
                        } catch (e) {
                            console.warn("Error Fetch Asset: ", e);
                            alert("Error Tarik Data: " + e.message);
                        }
                    } else {
                        alert("QR code tidak valid!");
                    }
                } else {
                    alert("QR code tidak valid!");
                }

                // Stop Loading
                overlay.classList.remove("d-flex");
                overlay.classList.add("d-none");
            }
        </script>
    </div>
</x-layout>
