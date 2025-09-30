<x-layout :show-bottom-navbar="false">
    <div class="container pt-3">
        <h5 class="fw-bold mb-2">Counter Fotokopi</h5>
        <span class="text-muted">Update: {{ $date }}</span>
        <div class="col-12 col-md-4 my-3">
            <input type="text" id="search" class="form-control" placeholder="Cari nama...">
        </div>
        <div class="table-responsive">
            <table class="table table-bordered" id="fotocopy-table">
                <thead>
                    <tr>
                        <td>Nama</td>
                        <td>B/W</td>
                        <td>Color</td>
                        <td>Total</td>
                        <td>Limit</td>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <script>
        const allData = @json($data);

        const tbody = document.querySelector('#fotocopy-table tbody');
        const searchInput = document.getElementById("search");

        function renderFotocopy(data) {
            tbody.innerHTML = '';
            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
              <td>${item.name}</td>
              <td>${item.bw}</td>
              <td>${item.color}</td>
              <td>${item.total}</td>
              <td>${item.limit}</td>
          `;
                tbody.appendChild(row);
            });
        }

        renderFotocopy(allData);

        // Event pencarian
        searchInput.addEventListener("input", () => {
            const keyword = searchInput.value.toLowerCase();
            const filtered = allData.filter(item =>
                item.name.toLowerCase().includes(keyword)
            );
            renderFotocopy(filtered);
        });
    </script>
</x-layout>
