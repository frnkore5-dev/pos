<?php

namespace Modules\Sale\DataTables;

use Modules\Sale\Entities\CashRegisterSession;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CashRegisterSessionsDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('cashier', function ($data) {
                return $data->user?->name ?? '—';
            })
            ->addColumn('opened_at_formatted', function ($data) {
                return $data->opened_at?->format('d/m/Y H:i') ?? '—';
            })
            ->addColumn('closed_at_formatted', function ($data) {
                return $data->closed_at?->format('d/m/Y H:i') ?? '—';
            })
            ->addColumn('opening_amount_formatted', function ($data) {
                return format_currency($data->opening_amount);
            })
            ->addColumn('cash_total_formatted', function ($data) {
                return format_currency(((int) $data->cash_payments_cents) / 100);
            })
            ->addColumn('card_total_formatted', function ($data) {
                return format_currency(((int) $data->card_payments_cents) / 100);
            })
            ->addColumn('pending_formatted', function ($data) {
                return format_currency(((int) $data->pending_cents) / 100);
            })
            ->addColumn('expected_cash_formatted', function ($data) {
                return format_currency($data->expected_cash_amount);
            })
            ->addColumn('counted_cash_formatted', function ($data) {
                return format_currency($data->closing_amount_counted);
            })
            ->addColumn('difference_formatted', function ($data) {
                $difference = $data->cash_difference;

                if ($difference === null) {
                    return '—';
                }

                $class = $difference > 0 ? 'text-success' : ($difference < 0 ? 'text-danger' : 'text-muted');

                return '<span class="' . $class . '">' . format_currency($difference) . '</span>';
            })
            ->addColumn('action', function ($data) {
                return view('sale::cash-register.partials.actions', compact('data'));
            })
            ->rawColumns(['difference_formatted']);
    }

    public function query(CashRegisterSession $model)
    {
        return $model->newQuery()
            ->with('user')
            ->whereNotNull('closed_at')
            ->when(request('start_date'), function ($query, $startDate) {
                $query->whereDate('closed_at', '>=', $startDate);
            })
            ->when(request('end_date'), function ($query, $endDate) {
                $query->whereDate('closed_at', '<=', $endDate);
            })
            ->when(request('user_id'), function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->select('cash_register_sessions.*')
            ->selectRaw('(SELECT COALESCE(SUM(sp.amount), 0) FROM sale_payments sp INNER JOIN sales s ON s.id = sp.sale_id WHERE s.cash_register_session_id = cash_register_sessions.id AND sp.payment_method = ?) as cash_payments_cents', ['Cash'])
            ->selectRaw('(SELECT COALESCE(SUM(sp.amount), 0) FROM sale_payments sp INNER JOIN sales s ON s.id = sp.sale_id WHERE s.cash_register_session_id = cash_register_sessions.id AND sp.payment_method = ?) as card_payments_cents', ['Credit Card'])
            ->selectRaw('(SELECT COALESCE(SUM(s.due_amount), 0) FROM sales s WHERE s.cash_register_session_id = cash_register_sessions.id) as pending_cents');
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('cash-register-sessions-table')
            ->columns($this->getColumns())
            ->minifiedAjax('', "data:function(d) {
                d.start_date = $('#filter_start_date').val();
                d.end_date = $('#filter_end_date').val();
                d.user_id = $('#filter_user_id').val();
            }")
            ->dom("<'row'<'col-md-3'l><'col-md-5 mb-2'B><'col-md-4'f>> .
                                'tr' .
                                <'row'<'col-md-5'i><'col-md-7 mt-2'p>>")
            ->orderBy(10, 'desc')
            ->language([
                'url' => asset('js/i18n/' . app()->getLocale() . '.json'),
            ])
            ->buttons(
                Button::make('excel')
                    ->text('<i class="bi bi-file-earmark-excel-fill"></i> Excel'),
                Button::make('print')
                    ->text('<i class="bi bi-printer-fill"></i> ' . __('sale::messages.print')),
                Button::make('reset')
                    ->text('<i class="bi bi-x-circle"></i> ' . __('sale::messages.reset')),
                Button::make('reload')
                    ->text('<i class="bi bi-arrow-repeat"></i> ' . __('sale::messages.reload'))
            );
    }

    protected function getColumns()
    {
        return [
            Column::make('id')
                ->title('#')
                ->className('text-center align-middle'),

            Column::computed('cashier')
                ->title(__('sale::messages.cashier'))
                ->className('text-center align-middle'),

            Column::computed('opened_at_formatted')
                ->title(__('sale::messages.opened_at'))
                ->className('text-center align-middle'),

            Column::computed('closed_at_formatted')
                ->title(__('sale::messages.closed_at'))
                ->className('text-center align-middle'),

            Column::computed('opening_amount_formatted')
                ->title(__('sale::messages.opening_balance'))
                ->className('text-center align-middle'),

            Column::computed('cash_total_formatted')
                ->title(__('sale::messages.total_cash'))
                ->className('text-center align-middle'),

            Column::computed('card_total_formatted')
                ->title(__('sale::messages.total_card'))
                ->className('text-center align-middle'),

            Column::computed('pending_formatted')
                ->title(__('sale::messages.pending_amount'))
                ->className('text-center align-middle'),

            Column::computed('expected_cash_formatted')
                ->title(__('sale::messages.expected_cash_in_drawer'))
                ->className('text-center align-middle'),

            Column::computed('counted_cash_formatted')
                ->title(__('sale::messages.counted_cash'))
                ->className('text-center align-middle'),

            Column::computed('difference_formatted')
                ->title(__('sale::messages.over_short'))
                ->className('text-center align-middle'),

            Column::computed('action')
                ->title(__('sale::messages.action'))
                ->exportable(false)
                ->printable(false)
                ->className('text-center align-middle'),

            Column::make('closed_at')
                ->visible(false),
        ];
    }

    protected function filename(): string
    {
        return 'CashRegisterSessions_' . date('YmdHis');
    }
}
