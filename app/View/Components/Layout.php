<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Layout extends Component
{
    public $showNavbar;
    public $showBottomNavbar;

    public function __construct($showNavbar = true, $showBottomNavbar = true)
    {
        $this->showNavbar = $showNavbar;
        $this->showBottomNavbar = $showBottomNavbar;
    }

    public function render()
    {
        return view('components.layout');
    }
}
