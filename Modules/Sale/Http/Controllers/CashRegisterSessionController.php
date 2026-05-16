<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Modules\Sale\Entities\CashRegisterSession;
use Modules\Sale\Http\Requests\CloseCashRegisterRequest;
use Modules\Sale\Http\Requests\OpenCashRegisterRequest;

class CashRegisterSessionController extends Controller
{
    public function summary(): JsonResponse
    {
        abort_if(Gate::denies('create_pos_sales'), 403);

        $session = CashRegisterSession::openSessionForUser(auth()->id());

        abort_if(!$session, 404);

        $expectedCents = $session->computeExpectedCashCents();
        $openingCents = (int) ($session->attributes['opening_amount'] ?? 0);

        return response()->json([
            'expected_cents' => $expectedCents,
            'expected_formatted' => format_currency($expectedCents / 100),
            'opening_formatted' => format_currency($openingCents / 100),
        ]);
    }

    public function open(OpenCashRegisterRequest $request)
    {
        if (CashRegisterSession::openSessionForUser(auth()->id())) {
            toast(__('sale::messages.cash_register_already_open'), 'warning');

            return redirect()->route('app.pos.index');
        }

        CashRegisterSession::create([
            'user_id' => auth()->id(),
            'opening_amount' => $request->opening_amount,
            'opening_note' => $request->opening_note,
            'opened_at' => now(),
        ]);

        toast(__('sale::messages.cash_register_opened'), 'success');

        return redirect()->route('app.pos.index');
    }

    public function close(CloseCashRegisterRequest $request)
    {
        $session = CashRegisterSession::openSessionForUser(auth()->id());

        abort_if(!$session, 404);

        $expectedCents = $session->computeExpectedCashCents();
        $countedCents = (int) round(((float) $request->closing_amount_counted) * 100);

        $session->update([
            'closing_amount_counted' => $request->closing_amount_counted,
            'expected_cash_amount' => $expectedCents,
            'cash_difference' => $countedCents - $expectedCents,
            'closing_note' => $request->closing_note,
            'closed_at' => now(),
        ]);

        toast(__('sale::messages.cash_register_closed'), 'success');

        return redirect()->route('app.pos.index');
    }
}
