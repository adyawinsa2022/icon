<div>
    @if ($ticket['status'] != 6)
        @if ($userProfile != 'User' && $ticket['status'] < 5 && in_array($userName, $assignedTechs))
            {{-- Nav Pills untuk memilih form --}}
            <ul class="nav nav-pills mt-4" id="ticketFormTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if ($activeTab === 'followup') active @endif"
                        wire:click="setActiveTab('followup')" id="followup-tab" data-bs-toggle="pill"
                        data-bs-target="#followup" type="button" role="tab">
                        Followup
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if ($activeTab === 'task') active @endif"
                        wire:click="setActiveTab('task')" id="task-tab" data-bs-toggle="pill" data-bs-target="#task"
                        type="button" role="tab">
                        Task
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if ($activeTab === 'solution') active @endif"
                        wire:click="setActiveTab('solution')" id="solution-tab" data-bs-toggle="pill"
                        data-bs-target="#solution" type="button" role="tab">
                        Solution
                    </button>
                </li>
            </ul>

            {{-- Isi masing-masing tab form --}}
            <div class="tab-content" id="ticketFormTabsContent">
                {{-- FORM FOLLOWUP --}}
                <div class="tab-pane fade @if ($activeTab === 'followup') show active @endif" id="followup"
                    role="tabpanel">
                    @livewire('form-followup', ['ticketId' => $ticket['id']])
                </div>

                {{-- FORM TASK --}}
                <div class="tab-pane fade @if ($activeTab === 'task') show active @endif" id="task"
                    role="tabpanel">
                    @livewire('form-task', ['ticketId' => $ticket['id']])
                </div>

                {{-- FORM SOLUTION --}}
                <div class="tab-pane fade @if ($activeTab === 'solution') show active @endif" id="solution"
                    role="tabpanel">
                    @livewire('form-solution', ['ticketId' => $ticket['id']])
                </div>
            </div>
        @else
            @if ($ticket['is_requester'])
                @if ($ticket['status'] == 5)
                    {{-- Form Approval --}}
                    @livewire('form-approval-solution', [
                        'ticketId' => $ticket['id'],
                        'solutionId' => $ticket['lastSolutionId'],
                    ])
                @else
                    {{-- Form Followup --}}
                    @livewire('form-followup', ['ticketId' => $ticket['id']])
                @endif
            @endif
        @endif
    @endif
</div>
