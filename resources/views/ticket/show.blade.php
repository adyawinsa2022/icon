<x-layout title="Detail Tiket">
    <div class="container pt-3">
        <!-- Livewire Isi Tiket -->
        @livewire('ticket-show', ['ticketId' => $id])
    </div>
</x-layout>
