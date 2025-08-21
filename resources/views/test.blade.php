<x-layout>
    <div class="container pt-3 flex-grow-1 d-flex flex-column">
        <h5 class="fw-bold mb-2">Tiket Saya</h5>
        <div class="card shadow-sm flex-grow-1" {{-- style="max-height: 800px; min-height: 400px; overflow-y: auto;" --}}>
            <div class="card-body">
                <table class="table table-sm table-bordered table-hover align-middle"></table>
            </div>
        </div>
    </div>
    <script>
        let baseurl = window.location.origin;
        console.log(baseurl);
    </script>
</x-layout>
