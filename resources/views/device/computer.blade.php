<x-layout :show-bottom-navbar="false">
    <div class="container pt-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4 text-center">{{ $device['name'] }}</h5>
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <td class="fw-semibold">Model</td>
                            <td>{{ $device['model'] }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">OS</td>
                            <td>{{ $device['os'] }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">CPU</td>
                            <td>{{ $device['cpu'] }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Memory</td>
                            <td>{{ $device['memory'] }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Storage</td>
                            <td>{{ $device['storage'] }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">User</td>
                            <td>{{ $device['user'] }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Lokasi</td>
                            <td>{!! html_entity_decode($device['location']) !!}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="d-flex justify-content-center gap-3 mt-5">
                    <a href="{{ route('ticket.create', $device['name']) }}" class="btn btn-primary">Buat Tiket</a>
                    <a href="{{ route('ticket.device', $device['name']) }}" class="btn btn-primary">Riwayat Tiket</a>
                </div>
            </div>
        </div>
    </div>
</x-layout>
