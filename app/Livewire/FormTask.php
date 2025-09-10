<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class FormTask extends Component
{
    public $ticketId;
    public $content = '';
    public $planCheckbox = false;
    public $begin = null;
    public $duration_hours;
    public $duration_minutes;

    protected $rules = [
        'content' => 'required|string|max:2000',
    ];

    protected $messages = [
        'content.required' => 'Kolom Task harus diisi.',
        'content.max' => 'Task maksimal :max karakter.',
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

            // Jika ada jadwal
            $isPlanned = $this->planCheckbox;
            $actiontime = null;
            $begin = null;
            $end = null;
            if ($isPlanned) {
                // Hitung end dari begin dan actiontime
                if (!$this->begin) {
                    $this->planCheckbox = false;
                    return $this->dispatch('show-toast', message: 'Jadwal tidak lengkap.', type: 'error');
                }
                $begin = Carbon::parse($this->begin);
                $actiontime = ($this->duration_hours * 3600) + ($this->duration_minutes * 60);
                if ($actiontime < 1) {
                    $this->planCheckbox = false;
                    return $this->dispatch('show-toast', message: 'Jadwal tidak lengkap.', type: 'error');
                }
                $end = $begin->copy()->addSeconds($actiontime)->format('Y-m-d H:i:s');
                $begin = $begin->format('Y-m-d H:i:s');
            }

            $payload = [
                'input' => [
                    'tickets_id'    => $this->ticketId,
                    'content'       => $this->content,
                    'users_id'      => $userId,
                    'users_id_tech' => $userId,
                ]
            ];

            // Kalau ada jadwal, tambahkan key ke payload
            if ($actiontime && $begin && $end) {
                $payload['input']['actiontime'] = $actiontime;
                $payload['input']['begin'] = $begin;
                $payload['input']['end'] = $end;
            }

            $res = Http::withHeaders([
                'App-Token' => config('glpi.api_app_token'),
                'Session-Token' => $sessionToken,
            ])->post(config('glpi.api_url') . '/TicketTask', $payload);

            // Jika task tidak ada jadwal, cek status tiket
            if (!$actiontime && !$begin && !$end) {
                // Update Status Tiket ke Proses
                $payloadStatus = [
                    'input' => [
                        'status' => 2,
                    ]
                ];

                // Request ke GLPI, Update Status Tiket
                Http::withHeaders([
                    'App-Token' => config('glpi.api_app_token'),
                    'Session-Token' => $sessionToken,
                ])->put(config('glpi.api_url') . "/Ticket/{$this->ticketId}", $payloadStatus);
            }

            if ($res->successful()) {
                $this->content = '';
                $this->planCheckbox = false;
                $this->begin = null;
                $this->duration_hours = null;
                $this->duration_minutes = null;
                $this->dispatch('update-ticket');
                $this->dispatch('show-toast', message: 'Task berhasil dikirim.', type: 'success');
            } else {
                $this->dispatch('show-toast', message: 'Task gagal dikirim.', type: 'error');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('show-toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.form-task');
    }
}
