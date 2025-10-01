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

        #dropdownOptions {
            max-height: 200px;
            overflow-y: auto;
        }

        .dropdown-item:hover {
            color: white;
            background-color: #0D6EFD;
        }

        .dropdown-item.no-hover:hover {
            color: inherit;
            background-color: transparent;
            pointer-events: none;
        }

        .tooltip-wide .tooltip-inner {
            max-width: 350px;
            white-space: normal;
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

                <!-- Perangkat -->
                <div class="mb-3">
                    <label class="form-label">Perangkat (Opsional)</label>
                    <div class="input-group dropdown">
                        <!-- Tombol dropdown -->
                        <button id="device_dropdown" class="form-select text-start dropdown-button" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Tanpa Perangkat
                        </button>

                        <ul class="dropdown-menu w-100">
                            <div class="dropdown-options device-dropdown-options"
                                style="max-height: 200px; overflow-y: auto;">
                                <li>
                                    <a class="dropdown-item" data-value="" data-type="">
                                        Tanpa Perangkat
                                    </a>
                                </li>
                                @foreach ($items as $item)
                                    <li>
                                        <a class="dropdown-item" data-value="{{ $item['name'] }}"
                                            data-type="{{ $item['type'] ?? '' }}">
                                            {{ $item['name'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </div>
                            <li class="no-data-message" style="display: none;">
                                <span class="dropdown-item no-hover">Data tidak ada</span>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li class="px-2">
                                <input type="text" class="form-control dropdown-search"
                                    placeholder="Cari Perangkat Saya...">
                            </li>
                        </ul>

                        <!-- Hidden input -->
                        <input type="hidden" name="device_id" id="device_id" class="dropdown-hidden-id">
                        <input type="hidden" name="device_type" id="device_type" class="dropdown-hidden-type">

                        <!-- Tombol QR -->
                        <button type="button" id="scanBtn" class="btn btn-outline-primary">
                            <i class="bi bi-qr-code-scan"></i>
                        </button>
                    </div>
                </div>

                <!-- Kategori -->
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <div class="dropdown w-100">
                        <button id="category_dropdown" class="form-select text-start dropdown-button" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            -- Pilih Kategori --
                        </button>

                        <ul class="dropdown-menu w-100">
                            <div class="dropdown-options category-dropdown-options"
                                style="max-height: 200px; overflow-y: auto;">
                                @foreach ($categories as $cat)
                                    <li>
                                        <a class="dropdown-item" data-value="{{ $cat['id'] }}">
                                            {{ $cat['name'] ?? 'Tanpa Nama' }}
                                        </a>
                                    </li>
                                @endforeach
                            </div>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li class="px-2">
                                <input type="text" class="form-control dropdown-search"
                                    placeholder="Cari Kategori...">
                            </li>
                        </ul>
                        <input type="hidden" name="category_id" id="category_id" class="dropdown-hidden-id">
                    </div>
                </div>

                <!-- Lokasi -->
                <div class="mb-3">
                    <label class="form-label">Lokasi</label>
                    <div class="dropdown w-100">
                        <button id="location_dropdown" class="form-select text-start dropdown-button" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            -- Pilih Lokasi --
                        </button>

                        <ul class="dropdown-menu w-100">
                            <div class="dropdown-options location-dropdown-options"
                                style="max-height: 200px; overflow-y: auto;">
                                @foreach ($locations as $loc)
                                    <li>
                                        <a class="dropdown-item" data-value="{{ $loc['id'] }}">
                                            {!! html_entity_decode($loc['name'] ?? 'Tanpa Nama') !!}
                                        </a>
                                    </li>
                                @endforeach
                            </div>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li class="px-2">
                                <input type="text" class="form-control dropdown-search"
                                    placeholder="Cari Lokasi...">
                            </li>
                        </ul>
                        <input type="hidden" name="location_id" id="location_id" class="dropdown-hidden-id">
                    </div>
                </div>

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
            document.addEventListener('DOMContentLoaded', function() {
                // Jika ada device dari controller (url mengandung kode aset)
                @if (!empty($device))
                    (async () => {
                        // Tampilkan loading
                        overlay.classList.remove("d-none");
                        overlay.classList.add("d-flex");

                        const kode = "{{ $device }}";
                        await fetchDevice(kode);

                        overlay.classList.remove("d-flex");
                        overlay.classList.add("d-none");
                    })();
                @endif

                // ====== FILTER PENCARIAN DROPDOWN ======
                document.querySelectorAll('.dropdown-search').forEach(searchInput => {
                    searchInput.addEventListener('input', function() {
                        const filter = this.value.toLowerCase();
                        const dropdownMenu = this.closest('.dropdown-menu');
                        const optionsContainer = dropdownMenu.querySelector('.dropdown-options');
                        // Items yang dicari sekarang adalah <li> di dalam optionsContainer
                        const items = optionsContainer.querySelectorAll('li');

                        let visibleCount = 0;

                        items.forEach(item => {
                            // Ambil teks dari elemen <a> di dalam <li>
                            const link = item.querySelector('.dropdown-item');
                            // Pastikan elemen <a> ditemukan, terutama untuk <li> yang tidak mengandung <a>
                            if (link) {
                                const text = link.textContent.toLowerCase();
                                const isVisible = text.includes(filter);

                                // Tampilkan/sembunyikan <li>
                                item.style.display = isVisible ? '' : 'none';

                                // Hitung <li> yang terlihat
                                if (isVisible) {
                                    visibleCount++;
                                }
                            } else {
                                // Jika <li> tidak punya .dropdown-item (misalnya divider atau li tanpa link), 
                                // kita biarkan dia terlihat (atau sembunyikan sesuai kebutuhan desain lain)
                            }
                        });

                        // Inisiasi atau Dapatkan Elemen Pesan "Data tidak ada"
                        const noDataMessage = dropdownMenu.querySelector('.no-data-message');
                        if (!noDataMessage) {
                            console.log("null");
                        } else {
                            // Logika untuk menampilkan/menyembunyikan pesan
                            if (visibleCount === 0) {
                                noDataMessage.style.display = 'block'; // Tampilkan pesan <li>
                            } else {
                                noDataMessage.style.display = 'none'; // Sembunyikan pesan <li>
                            }
                        }

                    });
                });

                document.querySelectorAll('.no-data-message').forEach(noDataItem => {
                    noDataItem.addEventListener('click', function(event) {
                        // Mencegah event klik menyebar ke atas (ke div dropdown-menu atau dropdown)
                        // Inilah yang menghentikan dropdown dari penutupan.
                        event.stopPropagation();

                        // Opsional: Mencegah tindakan default, meskipun pada <li>/<span> ini biasanya tidak ada.
                        event.preventDefault();
                    });
                });

                //  Reset search input ketika dropdown ditutup
                document.querySelectorAll('.dropdown').forEach(dropdown => {
                    dropdown.addEventListener('hide.bs.dropdown', function() {
                        const searchInput = this.querySelector('.dropdown-search');
                        if (searchInput) {
                            searchInput.value = ''; // kosongkan input

                            // Reset semua item agar tampil lagi
                            const optionsContainer = this.querySelector('.dropdown-options');
                            const items = optionsContainer.querySelectorAll('li');
                            items.forEach(item => {
                                item.style.display = ''; // tampilkan semua kembali
                            });
                        }

                        const noDataMessage = this.querySelector('.no-data-message');
                        if (noDataMessage) {
                            noDataMessage.style.display = 'none'; // Sembunyikan pesan <li>
                        }
                    });
                });

                // const deviceDropdownBtn = document.getElementById('device_dropdown');
                const deviceButton = document.getElementById('device_dropdown');
                const deviceDropdown = document.querySelector('.device-dropdown-options');
                let deviceTooltip = null;

                deviceButton.addEventListener('show.bs.dropdown', function() {
                    // Buat tooltip dinamis
                    // deviceDropdown.style.setProperty('--bs-tooltip-max-width', '350px');
                    // deviceDropdown.style.setProperty('white-space', 'normal');
                    deviceTooltip = new bootstrap.Tooltip(deviceDropdown, {
                        title: 'Jika tidak ada Perangkat Anda di bawah, hubungi ICT',
                        trigger: 'manual',
                        customClass: 'tooltip-wide'
                    });
                    deviceTooltip.show();
                });

                deviceButton.addEventListener('hide.bs.dropdown', function() {
                    // Sembunyikan dan destroy tooltip saat dropdown ditutup
                    if (deviceTooltip) {
                        deviceTooltip.dispose();
                        deviceTooltip = null;
                    }
                });


                // ====== PILIH ITEM DROPDOWN ======
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.addEventListener('click', function(e) {
                        const target = e.target.closest('.dropdown-item');
                        if (!target) return;

                        e.preventDefault();

                        const dropdown = this.closest('.dropdown');
                        const button = dropdown.querySelector('.dropdown-button');
                        const hiddenId = dropdown.querySelector('.dropdown-hidden-id');
                        const hiddenType = dropdown.querySelector('.dropdown-hidden-type');

                        // Hapus active dari semua item
                        this.querySelectorAll('.dropdown-item').forEach(item => item.classList.remove(
                            'active'));

                        // Tandai yang dipilih
                        target.classList.add('active');

                        // Update tombol teks
                        button.textContent = target.textContent;

                        // Simpan value ke hidden input
                        hiddenId.value = target.dataset.value;

                        // Jika ada type, simpan juga
                        if (hiddenType) {
                            hiddenType.value = target.dataset.type || '';
                        }
                    });
                });

                // Elemen tombol dan hidden input
                const deviceIdInput = document.getElementById('device_id');
                const deviceTypeInput = document.getElementById('device_type');

                const categoryButton = document.getElementById('category_dropdown');
                const categoryIdInput = document.getElementById('category_id');

                const locationButton = document.getElementById('location_dropdown');
                const locationIdInput = document.getElementById('location_id');

                // Handle ketika klik pada item perangkat
                document.querySelectorAll('.device-dropdown-options .dropdown-item').forEach(item => {
                    item.addEventListener('click', async function() {
                        const kode = this.dataset.value; // Ambil kode perangkat

                        // Tampilkan loading
                        overlay.classList.remove("d-none");
                        overlay.classList.add("d-flex");

                        if (kode) {
                            // Fetch data perangkat
                            await fetchDevice(kode);
                        } else {
                            locationButton.classList.remove('form-control');
                            locationButton.classList.add('form-select');
                            locationButton.classList.remove('disabled');
                            locationButton.style.pointerEvents = 'auto';
                            categoryButton.classList.remove('form-control');
                            categoryButton.classList.add('form-select');
                            categoryButton.classList.remove('disabled');
                            categoryButton.style.pointerEvents = 'auto';

                        }

                        // Sembunyikan loading
                        overlay.classList.remove("d-flex");
                        overlay.classList.add("d-none");
                    });
                });

                // Fungsi untuk mencari teks berdasarkan ID dari dropdown
                function findDropdownText(containerId, id) {
                    const el = document.querySelector(`.${containerId} .dropdown-item[data-value="${id}"]`);
                    return el ? el.textContent.trim() : "Tanpa Nama";
                }

                // Variabel untuk compress foto
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

                // Variabel untuk QR code
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
                            let backCamera = devices.find(d => /back|rear/i.test(d.label) && !
                                /wide|obs|virtual/i.test(d
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
                        document.getElementById('scannerModal').addEventListener('hidden.bs.modal',
                            function() {
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
                        if (html5QrCode.getState() === Html5QrcodeScannerState.SCANNING && currentCameraId !==
                            cameraId) {
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

                async function onScanSuccess(decodedText) {
                    // Loading
                    overlay.classList.remove("d-none");
                    overlay.classList.add("d-flex");

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

                            // Fetch data perangkat
                            await fetchDevice(kode);

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

                async function fetchDevice(kode) {
                    try {
                        const assetUrl = "{{ url('assets-info') }}";
                        const response = await fetch(`${assetUrl}/${kode}`);
                        const data = await response.json();

                        if (data.status === "success") {
                            const device = data.data;

                            // Tampilkan nama perangkat di tombol
                            deviceButton.textContent = device.name;

                            // Set hidden input
                            deviceIdInput.value = device.id;
                            deviceTypeInput.value = device.type;

                            // Lokasi
                            if (device.locations_id) {
                                const locationName = findDropdownText(
                                    'location-dropdown-options', device.locations_id);
                                locationButton.textContent = locationName;
                                locationIdInput.value = device.locations_id;

                                // Disable lokasi agar tidak bisa dipilih manual
                                locationButton.classList.remove('form-select');
                                locationButton.classList.add('form-control');
                                locationButton.classList.add('disabled');
                                locationButton.style.pointerEvents = 'none';
                            }

                            // Kategori
                            if (device.itilcategoryid) {
                                const categoryName = findDropdownText(
                                    'category-dropdown-options', device.itilcategoryid);
                                categoryButton.textContent = categoryName;
                                categoryIdInput.value = device.itilcategoryid;

                                // Disable kategori agar tidak bisa dipilih manual
                                categoryButton.classList.remove('form-select');
                                categoryButton.classList.add('form-control');
                                categoryButton.classList.add('disabled');
                                categoryButton.style.pointerEvents = 'none';
                            }
                        } else {
                            alert("Error: " + data.message + ` (${kode})`);
                        }
                    } catch (err) {
                        console.error("Fetch error:", err);
                        alert("Fetch error: " + (err.message || JSON.stringify(err)));
                    }
                }
            });
        </script>
    </div>
</x-layout>
