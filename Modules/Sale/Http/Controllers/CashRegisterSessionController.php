<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Modules\Sale\Entities\CashRegisterSession;
use Modules\Sale\Http\Requests\CloseCashRegisterRequest;
use Modules\Sale\Http\Requests\OpenCashRegisterRequest;
use Symfony\Component\HttpFoundation\Response;

class CashRegisterSessionController extends Controller
{
    public function summary(): JsonResponse
    {
        abort_if(Gate::denies('create_pos_sales'), 403);

        $session = CashRegisterSession::openSessionForUser(auth()->id());

        abort_if(!$session, 404);

        return response()->json($this->formatSummaryResponse($session));
    }

    public function open(OpenCashRegisterRequest $request): RedirectResponse
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

    public function close(CloseCashRegisterRequest $request): RedirectResponse
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

        return redirect()
            ->route('app.pos.index')
            ->with('cash_register_closed_id', $session->id);
    }

    public function pdf(CashRegisterSession $session): Response
    {
        abort_if(! $this->canViewSession($session), 403);

        $session->load('user');
        $summary = $session->buildCloseSummary();

        $pdf = \PDF::loadView('sale::print-cash-register-close', [
            'session' => $session,
            'summary' => $summary,
        ])->setPaper('a4');

        return $pdf->stream('cash-register-close-' . $session->id . '.pdf');
    }

    private function canViewSession(CashRegisterSession $session): bool
    {
        if ($session->closed_at === null) {
            return false;
        }

        if (Gate::allows('access_sales')) {
            return true;
        }

        return auth()->id() === $session->user_id && Gate::allows('create_pos_sales');
    }

    private function formatSummaryResponse(CashRegisterSession $session): array
    {
        $summary = $session->buildCloseSummary();

        return [
            'opening_cents' => $summary['opening_cents'],
            'opening_formatted' => format_currency($summary['opening_cents'] / 100),
            'cash_cents' => $summary['cash_cents'],
            'cash_formatted' => format_currency($summary['cash_cents'] / 100),
            'card_cents' => $summary['card_cents'],
            'card_formatted' => format_currency($summary['card_cents'] / 100),
            'other_cents' => $summary['other_cents'],
            'other_formatted' => format_currency($summary['other_cents'] / 100),
            'pending_cents' => $summary['pending_cents'],
            'pending_formatted' => format_currency($summary['pending_cents'] / 100),
            'expected_cents' => $summary['expected_cents'],
            'expected_formatted' => format_currency($summary['expected_cents'] / 100),
            'sales_count' => $summary['sales_count'],
            'total_sales_formatted' => format_currency($summary['total_sales_cents'] / 100),
        ];
    }
}
