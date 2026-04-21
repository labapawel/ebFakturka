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
                @php
                    $sellerName = $invoice->seller_name ?: ($invoice->type === 'purchase' ? ($invoice->contractor->name ?? '') : config('company.name'));
                    $sellerNip = $invoice->seller_nip ?: ($invoice->type === 'purchase' ? ($invoice->contractor->nip ?? '') : config('company.nip'));
                    $sellerStreet = $invoice->seller_street ?: ($invoice->type === 'purchase' ? ($invoice->contractor->address_street ?? '') : config('company.street'));
                    $sellerBuilding = $invoice->seller_building ?: ($invoice->type === 'purchase' ? ($invoice->contractor->address_building ?? '') : config('company.building_number'));
                    $sellerApartment = $invoice->seller_apartment ?: ($invoice->type === 'purchase' ? ($invoice->contractor->address_apartment ?? '') : null);
                    $sellerPostalCode = $invoice->seller_postal_code ?: ($invoice->type === 'purchase' ? ($invoice->contractor->postal_code ?? '') : config('company.postal_code'));
                    $sellerCity = $invoice->seller_city ?: ($invoice->type === 'purchase' ? ($invoice->contractor->city ?? '') : config('company.city'));
                    $bankName = $invoice->bank_name ?: ($invoice->type === 'sales' ? config('company.bank_name') : null);
                    $bankAccount = $invoice->bank_account ?: ($invoice->type === 'sales' ? config('company.bank_account') : null);
                @endphp
                {{ $sellerName }}<br>
                {{ $sellerStreet }} {{ $sellerBuilding }}@if($sellerApartment)/{{ $sellerApartment }}@endif<br>
                {{ $sellerPostalCode }} {{ $sellerCity }}<br>
                NIP: {{ $sellerNip }}
                @if($bankAccount)
                    <br>Bank: {{ $bankName }}
                    <br>Konto: {{ $bankAccount }}
                @endif
            </td>
            <td width="50%" class="text-right">
                <strong>Nabywca:</strong><br>
                @php
                    $buyerName = $invoice->buyer_name ?: ($invoice->type === 'sales' ? ($invoice->contractor->name ?? '') : config('company.name'));
                    $buyerNip = $invoice->buyer_nip ?: ($invoice->type === 'sales' ? ($invoice->contractor->nip ?? '') : config('company.nip'));
                    $buyerStreet = $invoice->buyer_street ?: ($invoice->type === 'sales' ? ($invoice->contractor->address_street ?? '') : config('company.street'));
                    $buyerBuilding = $invoice->buyer_building ?: ($invoice->type === 'sales' ? ($invoice->contractor->address_building ?? '') : config('company.building_number'));
                    $buyerApartment = $invoice->buyer_apartment ?: ($invoice->type === 'sales' ? ($invoice->contractor->address_apartment ?? '') : null);
                    $buyerPostalCode = $invoice->buyer_postal_code ?: ($invoice->type === 'sales' ? ($invoice->contractor->postal_code ?? '') : config('company.postal_code'));
                    $buyerCity = $invoice->buyer_city ?: ($invoice->type === 'sales' ? ($invoice->contractor->city ?? '') : config('company.city'));
                @endphp
                {{ $buyerName }}<br>
                {{ $buyerStreet }} {{ $buyerBuilding }}@if($buyerApartment)/{{ $buyerApartment }}@endif<br>
                {{ $buyerPostalCode }} {{ $buyerCity }}<br>
                NIP: {{ $buyerNip }}

                @if($invoice->buyer_recipient_name)
                <br><br>
                <strong>Odbiorca (JST):</strong><br>
                {{ $invoice->buyer_recipient_name }}<br>
                @if($invoice->buyer_recipient_nip) NIP: {{ $invoice->buyer_recipient_nip }}<br> @endif
                {{ $invoice->buyer_recipient_street }} {{ $invoice->buyer_recipient_building }}@if($invoice->buyer_recipient_apartment)/{{ $invoice->buyer_recipient_apartment }}@endif<br>
                {{ $invoice->buyer_recipient_postal_code }} {{ $invoice->buyer_recipient_city }}
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
            <th style="text-align: right;">Cena @if(!$isVatExempt) Netto @endif</th>
            @if(!$isVatExempt)
            <th style="text-align: right;">VAT</th>
            <th style="text-align: right;">Wartość Netto</th>
            @endif
            <th style="text-align: right;">Wartość @if(!$isVatExempt) Brutto @endif</th>
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
            @if(!$isVatExempt)
            <td style="text-align: right;">{{ $item->vat_rate ? ($item->vat_rate * 100) . '%' : 'ZW' }}</td>
            <td style="text-align: right;">{{ number_format($item->quantity * $item->net_price, 2) }}</td>
            @endif
            <td style="text-align: right;">{{ number_format($item->gross_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        @if(!$isVatExempt)
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

    <div style="margin-top: 20px;">
        <strong>Dane do przelewu:</strong><br>
        Bank: {{ $bankName ?: 'Brak nazwy banku' }}<br>
        Konto: <strong>{{ $bankAccount ?: 'Brak numeru konta' }}</strong>
    </div>

    @if($isVatExempt && !empty($vatExemptionReason))
        <div style="margin-top: 50px; font-size: 10px; border-top: 1px solid #ccc; padding-top: 10px;">
            Podstawa prawna zwolnienia z VAT: {{ $vatExemptionReason }}.
        </div>
    @endif
</body>
</html>


