<?php

namespace App\Http\Controllers;

use App\Models\JournalAudit;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $q = JournalAudit::with('utilisateur')->orderByDesc('survenu_le');

        if ($action = $request->query('action')) {
            $q->where('action', $action);
        }

        return view('audit.index', [
            'lignes' => $q->paginate(50)->withQueryString(),
            'action' => $action,
        ]);
    }
}
