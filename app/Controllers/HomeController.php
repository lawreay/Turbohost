<?php

namespace App\Controllers;

use App\Core\Controller;

/**
 * Handles public home page requests.
 */
class HomeController extends Controller
{
    /**
     * Show the platform home page.
     */
    public function index(): void
    {
        $this->view('home/index', [
            'title' => 'Welcome to Instaweb',
        ]);
    }
}
