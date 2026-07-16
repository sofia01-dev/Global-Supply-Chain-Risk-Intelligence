<?php
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Services\Port\PortService;

class PortController extends Controller
{
    protected $portService;

    public function __construct(PortService $portService)
    {
        $this->portService = $portService;
    }

    public function index()
    {
        $ports = $this->portService->getAllPorts();
        return view('user.ports.index', compact('ports'));
    }

    public function show($id)
    {
        $port = $this->portService->getPortById($id);
        return view('user.ports.show', compact('port'));
    }
}