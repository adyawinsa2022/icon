<div class="card mt-4 shadow-sm">
    <div class="card-body">
        <form wire:submit.prevent="submit">
            <div class="mb-3">
                <textarea class="form-control" wire:model="content" rows="3" placeholder="Tambah Solusi..."></textarea>
            </div>
            <div class="d-flex justify-content-end">
                <button class="btn btn-success" type="submit">Kirim Solusi</button>
            </div>
        </form>
    </div>
</div>
