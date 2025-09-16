<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\LengthAwarePaginator;

class EncyclopediaList extends Component
{
    // public $articles = [];
    public $search = null;
    public $glpiApiUrl;
    public $appToken;
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

    public function mount()
    {
        $this->glpiApiUrl = config('glpi.api_url');
        $this->appToken = config('glpi.api_app_token');
        $this->loadArticles();
    }

    public function loadArticles()
    {
        $sessionToken = Session::get('glpi_session_token');

        if (!$sessionToken) {
            return new LengthAwarePaginator([], 0, $this->perPage, $this->page, [
                'path' => request()->url(),
            ]);
        }

        $params = [
            'forcedisplay[0]' => 2, // id
            'forcedisplay[1]' => 6, // subject
            'forcedisplay[2]' => 9, // view
        ];

        if ($this->search) {
            // Pecah keyword jadi array kata
            $keywords = explode(' ', $this->search);

            foreach ($keywords as $index => $word) {
                $params["criteria[$index][field]"] = 6; // 6 = subject
                $params["criteria[$index][searchtype]"] = 'contains';
                $params["criteria[$index][value]"] = $word;

                // Jika bukan kata pertama, tambahkan operator AND
                if ($index > 0) {
                    $params["criteria[$index][link]"] = 'AND';
                }
            }
        }
        $params['range'] = (($this->page - 1) * $this->perPage) . '-' . (($this->page * $this->perPage) - 1);


        $query = http_build_query($params);
        $url = rtrim($this->glpiApiUrl, '/') . '/search/KnowbaseItem?' . $query;
        // dd($url);

        $response = Http::withHeaders([
            'App-Token' => $this->appToken,
            'Session-Token' => $sessionToken,
        ])->get($url);

        $data = $response->json();
        $rawArticles = $data['data'] ?? [];
        $totalArticles = $data['totalcount'] ?? 0;
        $this->totalPages = ceil($totalArticles / $this->perPage);

        if ($this->page > $this->totalPages && $this->totalPages > 0) {
            $this->page = $this->totalPages;
        } elseif ($this->totalPages === 0) {
            $this->page = 1;
        }

        $articles = collect($rawArticles)->map(function ($article) {
            return [
                'id' => $article['2'] ?? null,
                'subject' => $article['6'] ?? null,
                'view' => $article['9'] ?? null,
            ];
        })->values()->toArray();

        return new LengthAwarePaginator(
            $articles,
            $totalArticles,
            $this->perPage,
            $this->page,
            ['path' => request()->url()]
        );
    }

    public function render()
    {
        $articles = $this->loadArticles();
        return view('livewire.encyclopedia-list', [
            'articles' => $articles,
            'totalPages' => $this->totalPages,
            'page' => $this->page,
        ]);
    }
}
