<style>
    * { box-sizing: border-box; }

    body {
        font-family: Arial, Helvetica, sans-serif;
        margin: 0;
        padding: 16px;
        background: #e9ecef;
        color: #222;
    }

    .recu-container {
        max-width: 210mm;
        min-height: 148mm;
        margin: 0 auto;
        background: #fff;
        border: 1px solid #222;
        display: flex;
        flex-direction: column;
    }

    .header {
        padding: 10px 16px;
        border-bottom: 2px solid #222;
        flex-shrink: 0;
    }

    .header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .header-logo-col { flex: 0 0 70px; }

    .header-logo-col img {
        max-width: 68px;
        max-height: 68px;
        object-fit: contain;
    }

    .header-center-col {
        flex: 1;
        text-align: center;
    }

    .school-name {
        margin: 0;
        font-size: 18px;
        font-weight: bold;
    }

    .school-slogan,
    .school-year {
        margin: 2px 0 0;
        font-size: 11px;
        color: #555;
    }

    .doc-title {
        margin: 8px 0 0;
        font-size: 15px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .doc-num {
        margin: 3px 0 0;
        font-size: 11px;
        color: #555;
    }

    .header-right-col {
        flex: 0 0 28%;
        text-align: right;
        font-size: 11px;
        line-height: 1.4;
    }

    .header-right-col p { margin: 0 0 3px; }

    .print-controls {
        text-align: center;
        padding: 10px;
        background: #f8f9fa;
        border-bottom: 1px solid #ddd;
    }

    .btn-print {
        background: #007bff;
        border: 1px solid #007bff;
        color: #fff;
        padding: 7px 14px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
        font-weight: bold;
        margin: 0 4px;
    }

    .btn-print.btn-secondary,
    .btn-print.secondary {
        background: #6c757d;
        border-color: #6c757d;
    }

    .content {
        flex: 1;
        display: flex;
        align-items: center;
        padding: 16px 22px;
    }

    .fiche {
        width: 100%;
    }

    .fiche-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px 28px;
    }

    .fiche-item {
        min-width: 0;
        padding-bottom: 8px;
        border-bottom: 1px solid #ddd;
    }

    .fiche-item-full {
        grid-column: 1 / -1;
    }

    .fiche-item span {
        display: block;
        font-size: 11px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 3px;
    }

    .fiche-item strong {
        display: block;
        font-size: 15px;
        font-weight: bold;
        line-height: 1.3;
    }

    .fiche-item strong.fiche-nom {
        font-size: 16px;
        text-transform: uppercase;
    }

    .fiche-montant {
        margin-top: 16px;
        padding: 10px 14px;
        border: 2px solid #222;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .fiche-montant span {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #555;
    }

    .fiche-montant strong {
        font-size: 20px;
        white-space: nowrap;
    }

    .footer {
        padding: 8px 16px;
        text-align: center;
        border-top: 1px solid #222;
        font-size: 11px;
        color: #444;
        flex-shrink: 0;
    }

    .footer p { margin: 2px 0; }

    @media print {
        @page {
            size: A5 landscape;
            margin: 6mm;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            background: #fff !important;
            padding: 0 !important;
        }

        .no-print,
        .print-controls { display: none !important; }

        .recu-container {
            max-width: none;
            min-height: auto;
            height: 100%;
            border: none;
            box-shadow: none;
        }
    }
</style>
