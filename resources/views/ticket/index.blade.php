<x-layout>
    <div class="container pt-3 flex-grow-1 d-flex flex-column">

        {{-- Komponen daftar tiket --}}
        @livewire('ticket-list', ['deviceName' => $deviceName ?? null])

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
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
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
                    // Minta akses kamera
                    const stream = await navigator.mediaDevices.getUserMedia({
                        video: true
                    });

                    // Setelah izin diberikan, stop dulu streamnya biar tidak bentrok
                    stream.getTracks().forEach(track => track.stop());

                    const devices = await Html5Qrcode.getCameras();

                    if (devices.length === 0) {
                        alert("Tidak ada kamera ditemukan!");
                        return;
                    }

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

            // Cari semua URL dengan domain adyawinsa.com
            const linkRegex = /https?:\/\/(?:[\w\-]+\.)*adyawinsa\.com\/[^\s]+/gi;
            const linkMatches = decodedText.match(linkRegex);

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
                        let assetUrl = "{{ url('assets-info') }}";
                        // Ambil data aset
                        fetch(`${assetUrl}/${kode}`)
                            .then(res => res.json())
                            .then(data => {
                                const device = data.data
                                if (data.status === "success") {
                                    // Mengirim event ke komponen Livewire yang akan memicu logika riwayat perangkat
                                    Livewire.dispatch('showDeviceHistory', {
                                        deviceName: kode
                                    });

                                    // Opsional: Memperbarui URL di browser tanpa reload halaman
                                    const historyUrl = "{{ url('ticket/history') }}";
                                    window.history.pushState(null, '', `${historyUrl}/${kode}`);
                                } else {
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
                    console.warn("QR tidak valid");
                    alert("QR tidak valid");
                }
            } else {
                console.warn("QR tidak valid");
                alert("QR tidak valid");
            }

            // Loading
            overlay.classList.remove("d-flex");
            overlay.classList.add("d-none");
        }
    </script>
</x-layout>
