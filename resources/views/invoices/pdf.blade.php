<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 5px; }
        .header { margin-bottom: 20px; }
        .title { font-size: 20px; font-weight: bold; margin-bottom: 10px; }
        .details-table td { vertical-align: top; }
        .items-table th { background-color: #f0f0f0; border-bottom: 1px solid #ccc; text-align: left; }
        .items-table td { border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .totals-table { width: 40%; margin-left: auto; margin-top: 20px; }
        .totals-table td { border-bottom: 1px solid #eee; }
        .grand-total { font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Faktura VAT nr {{ $invoice->number }}</div>
        @if($invoice->ksef_status === 'sent')
            <div style="font-size: 12px; margin-top: 5px; color: #555;">Numer KSeF: {{ $invoice->ksef_number }}</div>
        @endif
    </div>

    <table class="details-table" style="margin-bottom: 30px;">
        <tr>
            <td width="50%">
                <strong>Sprzedawca:</strong><br>
                @if($invoice->type === 'purchase')
                    {{ $invoice->contractor->name }}<br>
                    {{ $invoice->contractor->address_street }} {{ $invoice->contractor->address_building }}<br>
                    {{ $invoice->contractor->postal_code }} {{ $invoice->contractor->city }}<br>
                    NIP: {{ $invoice->contractor->nip }}
                @else
                    {{ config('company.name') }}<br>
                    {{ config('company.address_line_1') }}<br>
                    {{ config('company.address_line_2') }}<br>
                    NIP: {{ config('company.nip') }}<br>
                    @if(config('company.bank_account'))
                    Bank: {{ config('company.bank_name') }}<br>
                    Konto: {{ config('company.bank_account') }}
                    @endif
                @endif
            </td>
            <td width="50%" class="text-right">
                <strong>Nabywca:</strong><br>
                @if($invoice->type === 'purchase')
                    {{ config('company.name') }}<br>
                    {{ config('company.address_line_1') }}<br>
                    {{ config('company.address_line_2') }}<br>
                    NIP: {{ config('company.nip') }}
                @else
                    {{ $invoice->contractor->name }}<br>
                    {{ $invoice->contractor->address_street }} {{ $invoice->contractor->address_building }}<br>
                    {{ $invoice->contractor->postal_code }} {{ $invoice->contractor->city }}<br>
                    NIP: {{ $invoice->contractor->nip }}
                @endif
            </td>
        </tr>
    </table>

    <table class="details-table" style="margin-bottom: 30px; background-color: #f9f9f9;">
        <tr>
            <td><strong>Data wystawienia:</strong><br> {{ $invoice->issue_date->format('Y-m-d') }}</td>
            <td><strong>Data sprzedaży:</strong><br> {{ $invoice->sale_date->format('Y-m-d') }}</td>
            <td><strong>Termin płatności:</strong><br> {{ $invoice->due_date->format('Y-m-d') }}</td>
            <td><strong>Metoda płatności:</strong><br> {{ $invoice->payment_method }}</td>
        </tr>
    </table>

    @if($invoice->description)
    <div style="margin-bottom: 30px; padding: 10px; background-color: #f9f9f9; border: 1px solid #eee;">
        <strong>Uwagi:</strong><br>
        {{ $invoice->description }}
    </div>
    @endif

    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">Lp.</th>
                <th width="35%">{{ __('content.invoices.name') }}</th>
                <th style="text-align: right;">{{ __('content.common.quantity') }}</th>
            <th style="text-align: left;">J.M.</th>
            <th style="text-align: right;">Cena @if(!$isVatExempt || $invoice->type === 'purchase') Netto @else @endif</th>
            @if(!$isVatExempt || $invoice->type === 'purchase')
            <th style="text-align: right;">VAT</th>
            <th style="text-align: right;">Wartość Netto</th>
            @endif
            <th style="text-align: right;">Wartość @if(!$isVatExempt || $invoice->type === 'purchase') Brutto @else @endif</th>
        </tr>
    </thead>
        <tbody>
            @foreach ($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td style="text-align: right;">{{ number_format($item->quantity, 2) }}</td>
            <td style="text-align: left;">{{ $item->unit }}</td>
            <td style="text-align: right;">{{ number_format($item->net_price, 2) }}</td>
            @if(!$isVatExempt || $invoice->type === 'purchase')
            <td style="text-align: right;">{{ $item->vat_rate ? ($item->vat_rate * 100) . '%' : 'ZW' }}</td>
            <td style="text-align: right;">{{ number_format($item->quantity * $item->net_price, 2) }}</td>
            @endif
            <td style="text-align: right;">{{ number_format($item->gross_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        @if(!$isVatExempt || $invoice->type === 'purchase')
        <tr>
            <td>Razem Netto:</td>
            <td class="text-right">{{ number_format($invoice->net_total, 2) }} {{ $invoice->currency->code }}</td>
        </tr>
        <tr>
            <td>Razem VAT:</td>
            <td class="text-right">{{ number_format($invoice->vat_total, 2) }} {{ $invoice->currency->code }}</td>
        </tr>
        @endif
        <tr class="grand-total">
            <td>Do Zapłaty:</td>
            <td class="text-right">{{ number_format($invoice->gross_total, 2) }} {{ $invoice->currency->code }}</td>
        </tr>
    </table>

    <div style="margin-top: 20px; font-size: 12px;">
        <strong>Słownie:</strong> {{ $invoice->amount_in_words }}
    </div>

    @if($invoice->type !== 'purchase')
    <div style="margin-top: 20px;">
        <strong>Dane do przelewu:</strong><br>
        Bank: {{ config('company.bank_name', 'Brak nazwy banku') }}<br>
        Konto: <strong>{{ config('company.bank_account', 'Brak numeru konta') }}</strong>
    </div>
    @endif

    @if($isVatExempt && $invoice->type !== 'purchase' && !empty($vatExemptionReason))
        <div style="margin-top: 50px; font-size: 10px; border-top: 1px solid #ccc; padding-top: 10px;">
            Podstawa prawna zwolnienia z VAT: {{ $vatExemptionReason }}.
        </div>
    @endif
</body>
</html>
