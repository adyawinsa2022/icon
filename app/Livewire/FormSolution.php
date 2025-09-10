<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class FormSolution extends Component
{
    public $ticketId;
    public $content = '';

    protected $rules = [
        'content' => 'required|string|max:2000',
    ];

    protected $messages = [
        'content.required' => 'Kolom Solution harus diisi.',
        'content.max' => 'Solution maksimal :max karakter.',
    ];

    public function mount($ticketId)
    {
        $this->ticketId = $ticketId;
    }

    public function submit()
    {
        try {
            $this->validate();

            $sessionToken = Session::get('glpi_session_token');
            $userId = Session::get('glpi_user_id');

            $payload = [
                'input' => [
                    'items_id' => $this->ticketId,
                    'itemtype' => 'Ticket',
                    'content' => $this->content,
                    'users_id' => $userId
                ]
            ];

            $res = Http::withHeaders([
                'App-Token' => config('glpi.api_app_token'),
                'Session-Token' => $sessionToken,
            ])->post(config('glpi.api_url') . '/ITILSolution', $payload);

            if ($res->successful()) {
                $this->content = '';
                $this->dispatch('update-ticket');
                $this->dispatch('show-toast', message: 'Solution berhasil dikirim.', type: 'success');
            } else {
                $this->dispatch('show-toast', message: 'Solution gagal dikirim.', type: 'error');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('show-toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.form-solution');
    }
}
