<?php

namespace Modules\Sale\Http\Controllers;

use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Modules\Sale\DataTables\CashRegisterSessionsDataTable;
use Modules\Sale\Entities\CashRegisterSession;

class CashRegisterHistoryController extends Controller
{
    public function index(CashRegisterSessionsDataTable $dataTable)
    {
        abort_if(Gate::denies('access_sales'), 403);

        $cashierIds = CashRegisterSession::query()
            ->whereNotNull('closed_at')
            ->distinct()
            ->pluck('user_id');

        $cashiers = User::query()
            ->whereIn('id', $cashierIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        return $dataTable->render('sale::cash-register.index', compact('cashiers'));
    }

    public function show(CashRegisterSession $session)
    {
        abort_if(Gate::denies('access_sales'), 403);
        abort_if($session->closed_at === null, 404);

        $session->load(['user', 'sales' => fn ($query) => $query->orderByDesc('created_at')]);
        $summary = $session->buildCloseSummary();

        return view('sale::cash-register.show', compact('session', 'summary'));
    }
}
