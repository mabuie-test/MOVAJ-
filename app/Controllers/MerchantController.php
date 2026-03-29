<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;

class MerchantController extends Controller
{
    public function dashboard(Request $request): void { $this->view('merchant/dashboard'); }
    public function reports(Request $request): void { $this->view('merchant/reports'); }
}
