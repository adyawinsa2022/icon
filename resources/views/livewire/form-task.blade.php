<div>
    <div class="card mt-4 shadow-sm">
        <div class="card-body">
            <form wire:submit.prevent="submit">
                <div class="mb-3">
                    <textarea class="form-control" wire:model="content" rows="3" placeholder="Tambah Task..."></textarea>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="planCheckbox"
                            wire:model="planCheckbox">
                        <label class="form-check-label" for="planCheckbox">
                            Jadwalkan
                        </label>
                    </div>
                </div>
                <div id="plannedTask" class="d-none">
                    <div class="mb-3">
                        <label for="begin" class="form-label">Waktu Mulai</label>
                        <input type="datetime-local" id="begin" wire:model="begin" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="duration" class="form-label">Durasi</label>
                        <div class="d-flex gap-2">
                            <select wire:model="duration_hours" class="form-select">
                                @for ($h = 0; $h <= 23; $h++)
                                    <option value="{{ $h }}">{{ $h }} jam
                                    </option>
                                @endfor
                            </select>

                            <select wire:model="duration_minutes" class="form-select">
                                @for ($m = 0; $m < 60; $m += 5)
                                    <option value="{{ $m }}">{{ $m }} menit
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button class="btn btn-warning" type="submit">Kirim Task</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        const checkbox = document.getElementById('planCheckbox')
        const scheduleFields = document.getElementById('plannedTask')
        checkbox.addEventListener('change', function() {
            scheduleFields.classList.toggle('d-none', !this.checked)
        });
    </script>
</div>
