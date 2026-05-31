<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('sale::messages.cash_register_close_report') }}</title>
    <style>
        * {
            font-size: 12px;
            line-height: 1.5;
            font-family: 'Ubuntu', sans-serif;
            color: #333;
        }
        h2 {
            font-size: 18px;
            margin: 0 0 4px;
        }
        h3 {
            font-size: 14px;
            margin: 20px 0 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            padding: 8px 10px;
            border: 1px solid #ddd;
        }
        th {
            background: #f5f5f5;
            text-align: left;
            width: 45%;
        }
        td.amount {
            text-align: right;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 12px;
        }
        .highlight {
            background: #fff8e1;
        }
        .positive { color: #2e7d32; }
        .negative { color: #c62828; }
        .meta {
            margin-bottom: 16px;
        }
        .meta p {
            margin: 2px 0;
        }
        .note {
            margin-top: 16px;
            padding: 10px;
            background: #fafafa;
            border: 1px dashed #ccc;
        }
        .footer {
            margin-top: 24px;
            font-size: 11px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="header">
    <h2>{{ settings()->company_name }}</h2>
    <p style="margin:0;font-size:11px;">
        {{ settings()->company_address }}<br>
        {{ settings()->company_phone }} · {{ settings()->company_email }}
    </p>
    <p style="margin-top:10px;font-weight:bold;font-size:14px;">
        {{ __('sale::messages.cash_register_close_report') }}
    </p>
</div>

<div class="meta">
    <p><strong>{{ __('sale::messages.cashier') }}:</strong> {{ $session->user->name }}</p>
    <p><strong>{{ __('sale::messages.opened_at') }}:</strong> {{ $session->opened_at->format('d/m/Y H:i') }}</p>
    <p><strong>{{ __('sale::messages.closed_at') }}:</strong> {{ $session->closed_at->format('d/m/Y H:i') }}</p>
    <p><strong>{{ __('sale::messages.sales_count') }}:</strong> {{ $summary['sales_count'] }}</p>
</div>

<h3>{{ __('sale::messages.close_summary_title') }}</h3>
<table>
    <tr>
        <th>{{ __('sale::messages.opening_balance') }}</th>
        <td class="amount">{{ format_currency($summary['opening_cents'] / 100) }}</td>
    </tr>
    <tr>
        <th>{{ __('sale::messages.total_cash') }}</th>
        <td class="amount positive">{{ format_currency($summary['cash_cents'] / 100) }}</td>
    </tr>
    <tr>
        <th>{{ __('sale::messages.total_card') }}</th>
        <td class="amount">{{ format_currency($summary['card_cents'] / 100) }}</td>
    </tr>
    @if($summary['other_cents'] > 0)
    <tr>
        <th>{{ __('sale::messages.other_payments') }}</th>
        <td class="amount">{{ format_currency($summary['other_cents'] / 100) }}</td>
    </tr>
    @endif
    <tr>
        <th>{{ __('sale::messages.pending_amount') }}</th>
        <td class="amount negative">{{ format_currency($summary['pending_cents'] / 100) }}</td>
    </tr>
    <tr>
        <th>{{ __('sale::messages.total_sales') }}</th>
        <td class="amount">{{ format_currency($summary['total_sales_cents'] / 100) }}</td>
    </tr>
</table>

<h3>{{ __('sale::messages.cash_register_close_title') }}</h3>
<table>
    <tr class="highlight">
        <th>{{ __('sale::messages.expected_cash_in_drawer') }}</th>
        <td class="amount">{{ format_currency($session->expected_cash_amount) }}</td>
    </tr>
    <tr class="highlight">
        <th>{{ __('sale::messages.counted_cash') }}</th>
        <td class="amount">{{ format_currency($session->closing_amount_counted) }}</td>
    </tr>
    <tr class="highlight">
        <th>{{ __('sale::messages.over_short') }}</th>
        <td class="amount {{ $session->cash_difference >= 0 ? 'positive' : 'negative' }}">
            {{ format_currency($session->cash_difference) }}
        </td>
    </tr>
</table>

@if($session->closing_note)
<div class="note">
    <strong>{{ __('sale::messages.closing_note') }}:</strong><br>
    {{ $session->closing_note }}
</div>
@endif

<div class="footer">
    {{ __('sale::messages.cash_register_close_report') }} · #{{ $session->id }} · {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>
