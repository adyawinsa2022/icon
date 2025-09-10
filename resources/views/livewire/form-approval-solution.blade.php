<div class="card mt-4 shadow-sm">
    <div class="card-body">
        <form wire:submit.prevent="submit">
            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Tiket ini telah diselesaikan oleh Teknisi. Apakah anda menyetujuinya?
                </label>
                <textarea class="form-control" wire:model.defer="content" rows="3" placeholder="Komentar (Opsional)"></textarea>
            </div>

            <div class="d-flex justify-content-center gap-3">
                <button class="btn btn-success" type="submit" wire:click="$set('answer', 'approve')">
                    Ya, setuju
                </button>

                <button class="btn btn-danger" type="submit" wire:click="$set('answer', 'refuse')">
                    Tidak setuju
                </button>
            </div>
        </form>
    </div>
</div>
