<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class FormApprovalSolution extends Component
{
    public $ticketId;
    public $solutionId;
    public $content = '';
    public $answer = null; // "approve" atau "refuse"

    protected $rules = [
        'answer' => 'required|in:approve,refuse',
        'content' => 'nullable|string|max:2000',
    ];

    protected $messages = [
        'answer.required' => 'Pilih setuju atau tidak setuju.',
        'answer.in' => 'Jawaban tidak valid.',
        'content.max' => 'Komentar maksimal :max karakter.',
    ];

    public function mount($ticketId, $solutionId)
    {
        $this->ticketId = $ticketId;
        $this->solutionId = $solutionId;
    }

    public function submit()
    {
        try {
            $this->validate();

            $sessionToken = Session::get('glpi_session_token');
            $userId = Session::get('glpi_user_id');

            // === 1. Tentukan status tiket & pesan follow-up ===
            if ($this->answer === 'approve') {
                $status = 6; // Tiket ditutup
                $contentPayload = $this->content ?: 'Tiket telah ditutup atas persetujuan User';
            } else {
                $status = 2; // Kembali ke proses
                $contentPayload = $this->content ?: 'Tiket gagal ditutup atas penolakan User';
            }

            // === 2. Tambah Followup ===
            $payloadFollowup = [
                'input' => [
                    'items_id' => $this->ticketId,
                    'itemtype' => 'Ticket',
                    'content'  => $contentPayload,
                    'users_id' => $userId,
                ]
            ];

            $resFollowup = Http::withHeaders([
                'App-Token' => config('glpi.api_app_token'),
                'Session-Token' => $sessionToken,
            ])->post(config('glpi.api_url') . '/ITILFollowup', $payloadFollowup);

            // === 3. Update solusi jika ada ===
            if ($this->solutionId) {
                $solutionStatus = $this->answer === 'approve' ? 4 : 3; // 4=approved, 3=refused

                $payloadSolution = [
                    'input' => [
                        'status' => $solutionStatus,
                    ]
                ];

                $resSolution = Http::withHeaders([
                    'App-Token' => config('glpi.api_app_token'),
                    'Session-Token' => $sessionToken,
                ])->put(config('glpi.api_url') . "/ITILSolution/{$this->solutionId}", $payloadSolution);
            }

            // === 4. Update status tiket ===
            $payloadStatus = [
                'input' => [
                    'status' => $status,
                ]
            ];

            $resStatus = Http::withHeaders([
                'App-Token' => config('glpi.api_app_token'),
                'Session-Token' => $sessionToken,
            ])->put(config('glpi.api_url') . "/Ticket/{$this->ticketId}", $payloadStatus);

            if ($resStatus->failed()) {
                return $this->dispatch(
                    'show-toast',
                    message: 'Gagal membuat approval.',
                    type: 'error'
                );
            }

            if ($resStatus->successful() && $resFollowup->successful() && (!isset($resSolution) || $resSolution->successful())) {
                $this->content = '';
                $this->answer = 'approve';
                $this->dispatch('update-ticket');
                $this->dispatch('show-toast', message: 'Approval berhasil dikirim.', type: 'success');
            } else {
                $this->dispatch('show-toast', message: 'Approval gagal dikirim.', type: 'error');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('show-toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.form-approval-solution');
    }
}
