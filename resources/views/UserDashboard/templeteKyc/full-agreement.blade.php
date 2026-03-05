<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Research Analyst Agreement</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        h2, h3 {
            margin-bottom: 6px;
        }
        .page-break {
            page-break-after: always;
        }
        .agreement-header {
            border-bottom: 1px solid #000;
            margin-bottom: 12px;
            padding-bottom: 6px;
            font-size: 11px;
        }
    </style>
</head>

<body>

    {{-- Agreement Meta Header --}}
    <div class="agreement-header">
        <strong>Agreement No:</strong> {{ $agreement->agreement_number ?? 'AGREEMENT-0001' }} <br>
        <strong>Invoice No:</strong> {{ $agreement->invoice_number ?? 'INV-0001' }} <br>
    </div>

    {{-- STEP 1 --}}
    @include('UserDashboard.templeteKyc.steps.step-1-overview', [
        'user' => $user,
        'kyc' => $kyc,
        'pdfMode' => true,
    ])
    <div class="page-break"></div>

    {{-- STEP 2 --}}
    @include('UserDashboard.templeteKyc.steps.step-2-fees-invoice', [
        'subscription' => $subscription,
        'invoice' => $invoice,
        'pdfMode' => true,
    ])
    <div class="page-break"></div>

    {{-- STEP 3 --}}
    @include('UserDashboard.templeteKyc.steps.step-3-mitc-risk', ['pdfMode' => true])
    <div class="page-break"></div>

    {{-- STEP 4 --}}
    @include('UserDashboard.templeteKyc.steps.step-4-legal-confidentiality', ['pdfMode' => true])
    <div class="page-break"></div>

    {{-- STEP 5 --}}
    @include('UserDashboard.templeteKyc.steps.step-5-digital-signature', [
        'user' => $user,
        'kyc' => $kyc,
        'signedAt' => $signedAt,
        'pdfMode' => true,
    ])

</body>
</html>
