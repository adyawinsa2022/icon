<?php

namespace App\Livewire;

use App\Models\Container;
use Livewire\Component;

class ContainerSearch extends Component
{
    public $search = NULL;
    public $page = 1;
    public $perPage = 15;
    public $totalPages = 1;

    public function updatedSearch()
    {
        $this->page = 1;
    }

    public function gotoPage($page)
    {
        $this->page = $page;
    }

    public function nextPage()
    {
        $this->page++;
    }

    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function render()
    {
        $containers = collect();

        if (strlen($this->search) >= 2) {
            $q = strtolower($this->search);

            $containers = Container::whereRaw(
                "JSON_SEARCH(LOWER(items), 'one', CONCAT('%', ?, '%')) IS NOT NULL",
                [$q]
            )->get();
        }

        return view('livewire.container-search', compact('containers'));
    }
}
