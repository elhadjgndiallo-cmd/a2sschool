<style>
    .carte-id {
        --card-gold: {{ $couleurs['document']['document_card_border'] ?? '#c4a45a' }};
        --card-gold-dark: #a8883e;
        --card-text: {{ $couleurs['document']['document_card_text'] ?? '#1a1a1a' }};
        --card-muted: {{ $couleurs['document']['document_card_muted'] ?? '#555' }};
        --card-bg: {{ $couleurs['document']['document_card_bg'] ?? '#ffffff' }};
        --status-active: {{ $couleurs['resultat']['resultat_success_bg'] ?? '#2e7d32' }};
        --status-expiree: {{ $couleurs['resultat']['resultat_danger_bg'] ?? '#dc3545' }};
        --status-suspendue: {{ $couleurs['resultat']['resultat_warning_bg'] ?? '#ffc107' }};
        --status-annulee: {{ $couleurs['resultat']['resultat_secondary_bg'] ?? '#6c757d' }};

        width: 86mm;
        height: 54mm;
        box-sizing: border-box;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        font-family: Arial, Helvetica, sans-serif;
        color: var(--card-text);
        background: var(--card-bg);
        border: 0.45mm solid var(--card-gold);
        border-radius: 2.8mm;
        box-shadow: inset 0 0 0 0.85mm #fff, inset 0 0 0 1.25mm var(--card-gold);
        page-break-inside: avoid;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .carte-id-filigrane {
        position: absolute;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        overflow: hidden;
    }

    .carte-id-filigrane-cs {
        position: absolute;
        left: 38%;
        top: 54%;
        transform: translate(-50%, -50%);
        font-size: 22mm;
        font-weight: 800;
        letter-spacing: -0.8mm;
        color: #c4a45a;
        opacity: 0.13;
        line-height: 1;
        text-transform: uppercase;
        font-family: Georgia, "Times New Roman", serif;
    }

    .carte-id-filigrane-cs::before {
        content: "";
        position: absolute;
        left: 50%;
        top: 50%;
        width: 28mm;
        height: 28mm;
        transform: translate(-50%, -50%);
        border: 0.45mm solid rgba(196, 164, 90, 0.22);
        border-radius: 50%;
        box-shadow: inset 0 0 0 0.7mm rgba(196, 164, 90, 0.08);
    }

    .carte-id-filigrane-emblem {
        position: absolute;
        right: 3mm;
        top: 58%;
        transform: translateY(-50%);
        width: 16mm;
        height: 16mm;
        object-fit: contain;
        opacity: 0.12;
        filter: grayscale(0.2);
    }

    .carte-id-header,
    .carte-id-body,
    .carte-id-footer {
        position: relative;
        z-index: 1;
    }

    .carte-id-header {
        display: flex;
        align-items: center;
        gap: 1.6mm;
        padding: 1.6mm 3mm 1.2mm;
        border-bottom: 0.28mm solid var(--card-gold);
        flex-shrink: 0;
    }

    .carte-id-logo {
        width: 8.2mm;
        height: 8.2mm;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
        border: 0.35mm solid var(--card-gold);
        background: #fff8e8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.4mm;
        box-shadow: 0 0 0 0.35mm #fff, 0 0 0 0.7mm var(--card-gold);
    }

    .carte-id-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .carte-id-header-text {
        flex: 1;
        min-width: 0;
        text-align: center;
        line-height: 1.15;
    }

    .carte-id-country {
        font-size: 1.7mm;
        font-weight: 600;
        letter-spacing: 0.2mm;
        margin: 0;
        text-transform: uppercase;
    }

    .carte-id-school {
        font-size: 2.35mm;
        font-weight: 800;
        margin: 0.25mm 0 0;
        text-transform: uppercase;
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.15;
    }

    .carte-id-title {
        font-size: 1.9mm;
        font-weight: 700;
        margin: 0.3mm 0 0;
        letter-spacing: 0.12mm;
        text-transform: uppercase;
    }

    .carte-id-body {
        display: flex;
        flex: 1;
        min-height: 0;
        padding: 1.6mm 3mm 1.2mm;
        gap: 2.4mm;
        align-items: stretch;
    }

    .carte-id-left {
        width: 20mm;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .carte-id-photo {
        width: 18.5mm;
        height: 24mm;
        border: 0.35mm solid var(--card-gold);
        background: #f4f4f4;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--card-muted);
        font-size: 2mm;
        text-align: center;
    }

    .carte-id-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center top;
        display: block;
    }

    .carte-id-photo-caption {
        margin-top: 1mm;
        font-size: 1.45mm;
        font-weight: 700;
        letter-spacing: 0.05mm;
        text-align: center;
        text-transform: uppercase;
        line-height: 1.15;
    }

    .carte-id-center {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        font-size: 2.05mm;
        line-height: 1.38;
    }

    .carte-id-center div {
        margin: 0 0 0.2mm;
    }

    .carte-id-center strong {
        font-weight: 800;
    }

    .carte-id-row {
        display: flex;
        justify-content: space-between;
        gap: 2mm;
    }

    .carte-id-row span {
        min-width: 0;
    }

    .carte-id-qr-wrap {
        width: 16mm;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-top: 0.4mm;
    }

    .carte-id-qr {
        width: 14mm;
        height: 14mm;
        background: #fff;
        overflow: hidden;
    }

    .carte-id-qr img,
    .carte-id-qr svg {
        width: 100% !important;
        height: 100% !important;
        object-fit: contain;
        display: block;
    }

    .carte-id-qr-caption {
        margin-top: 0.8mm;
        font-size: 1.2mm;
        font-weight: 700;
        text-align: center;
        line-height: 1.15;
        letter-spacing: 0.02mm;
        text-transform: uppercase;
        max-width: 16mm;
    }

    .carte-id-footer {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2mm;
        padding: 1mm 3mm 1.4mm;
        border-top: 0.28mm solid var(--card-gold);
        font-size: 1.75mm;
        color: var(--card-text);
    }

    .carte-id-footer-num {
        font-weight: 800;
        font-size: 2.15mm;
        letter-spacing: 0.08mm;
    }

    .carte-id-footer-right {
        display: flex;
        align-items: center;
        gap: 1.4mm;
        color: var(--card-muted);
    }

    .carte-id-status {
        display: inline-block;
        padding: 0.25mm 1.6mm;
        border-radius: 2mm;
        font-size: 1.55mm;
        font-weight: 700;
        color: #fff;
        text-transform: capitalize;
        background: var(--status-annulee);
    }

    .carte-id-status.is-active { background: var(--status-active); }
    .carte-id-status.is-expiree { background: var(--status-expiree); }
    .carte-id-status.is-suspendue { background: var(--status-suspendue); color: #111; }
    .carte-id-status.is-annulee { background: var(--status-annulee); }

    .carte-preview-wrap {
        overflow-x: auto;
        padding: 16px 8px 24px;
        background: radial-gradient(circle at top, #f7f3e8 0%, #fafafa 70%);
        border-radius: 8px;
    }

    .carte-preview-stage {
        --preview-scale: 1.55;
        width: calc(86mm * var(--preview-scale));
        height: calc(54mm * var(--preview-scale));
        margin: 0 auto 18px;
    }

    .carte-preview-stage .carte-id {
        transform: scale(var(--preview-scale));
        transform-origin: top left;
    }

    @media (max-width: 640px) {
        .carte-preview-stage {
            --preview-scale: 1;
        }
    }
</style>
