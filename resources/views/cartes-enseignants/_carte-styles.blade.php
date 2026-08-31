<style>
    .carte-ens {
        --ens-blue: #1d6fe3;
        --ens-blue-dark: #0d4fc4;
        --ens-navy: #0a2a72;
        --ens-navy-text: #102a56;
        --ens-muted: #6b7a90;
        --ens-white: #ffffff;

        width: 86mm;
        height: 54mm;
        box-sizing: border-box;
        border-radius: 3.2mm;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        font-family: "Segoe UI", Calibri, Arial, Helvetica, sans-serif;
        background: var(--ens-white);
        box-shadow: 0 4px 18px rgba(10, 42, 114, 0.18);
        page-break-inside: avoid;
        position: relative;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .carte-ens-top {
        display: flex;
        flex: 1;
        min-height: 0;
    }

    .carte-ens-side {
        width: 27.5mm;
        flex-shrink: 0;
        background: linear-gradient(180deg, var(--ens-blue) 0%, #1760d4 100%);
        color: var(--ens-white);
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 2.2mm 2mm 2mm;
        overflow: hidden;
    }

    .carte-ens-side::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 22mm;
        height: 18mm;
        background: var(--ens-blue-dark);
        clip-path: polygon(0 0, 100% 0, 0 100%);
        opacity: 0.55;
        pointer-events: none;
    }

    .carte-ens-brand {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: flex-start;
        gap: 1.1mm;
        width: 100%;
        margin-bottom: 1.4mm;
    }

    .carte-ens-brand-mark {
        width: 5.4mm;
        height: 5.4mm;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.18);
        border: 0.25mm solid rgba(255, 255, 255, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
        font-size: 2.4mm;
        color: #fff;
    }

    .carte-ens-brand-mark img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .carte-ens-brand-text {
        min-width: 0;
        flex: 1;
        line-height: 1.2;
    }

    .carte-ens-brand-name {
        font-size: 2.05mm;
        font-weight: 800;
        margin: 0;
        white-space: normal;
        overflow: visible;
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.2;
        max-width: none;
    }

    .carte-ens-brand-sub {
        font-size: 1.05mm;
        letter-spacing: 0.04mm;
        opacity: 0.9;
        margin: 0.2mm 0 0;
        text-transform: uppercase;
        white-space: normal;
        overflow: visible;
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.2;
        max-width: none;
    }

    .carte-ens-photo {
        position: relative;
        z-index: 1;
        width: 20mm;
        height: 19mm;
        border-radius: 2mm;
        overflow: hidden;
        background: #dbe8fb;
        border: 0.55mm solid #fff;
        box-shadow: 0 1mm 2mm rgba(0, 0, 0, 0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--ens-navy);
        font-weight: 800;
        font-size: 6mm;
    }

    .carte-ens-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center top;
        display: block;
    }

    .carte-ens-role {
        position: relative;
        z-index: 1;
        margin-top: auto;
        display: flex;
        align-items: center;
        gap: 1mm;
        background: rgba(8, 32, 90, 0.35);
        border-radius: 4mm;
        padding: 0.7mm 1.6mm;
        font-size: 1.7mm;
        font-weight: 800;
        letter-spacing: 0.12mm;
        text-transform: uppercase;
    }

    .carte-ens-role i {
        font-size: 1.8mm;
    }

    .carte-ens-main {
        flex: 1;
        min-width: 0;
        position: relative;
        padding: 1.4mm 2mm 1mm 2.4mm;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: #fff;
    }

    .carte-ens-main::after {
        content: "\f19d";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        position: absolute;
        right: 3mm;
        top: 50%;
        transform: translateY(-42%);
        font-size: 26mm;
        color: var(--ens-blue);
        opacity: 0.06;
        pointer-events: none;
        line-height: 1;
    }

    .carte-ens-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 2mm;
        position: relative;
        z-index: 1;
    }

    .carte-ens-identity {
        min-width: 0;
        flex: 1;
    }

    .carte-ens-year-label {
        font-size: 1.45mm;
        color: var(--ens-muted);
        margin: 0;
    }

    .carte-ens-year {
        font-size: 2.15mm;
        font-weight: 800;
        color: var(--ens-blue);
        margin: 0 0 1.1mm;
    }

    .carte-ens-name {
        font-size: 2.6mm;
        font-weight: 800;
        color: var(--ens-navy-text);
        margin: 0;
        line-height: 1.25;
        white-space: normal;
        overflow: visible;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .carte-ens-name span {
        color: var(--ens-muted);
        font-weight: 700;
        font-size: 2.2mm;
    }

    .carte-ens-job {
        font-size: 1.7mm;
        font-weight: 800;
        letter-spacing: 0.35mm;
        color: var(--ens-blue);
        text-transform: uppercase;
        margin: 0.2mm 0 0.4mm;
    }

    .carte-ens-qr {
        width: 13mm;
        flex-shrink: 0;
        text-align: center;
    }

    .carte-ens-qr-box {
        width: 11.5mm;
        height: 11.5mm;
        margin: 0 auto;
        background: #fff;
        overflow: hidden;
    }

    .carte-ens-qr-box img,
    .carte-ens-qr-box svg {
        width: 100% !important;
        height: 100% !important;
        object-fit: contain;
        display: block;
    }

    .carte-ens-qr-caption {
        font-size: 1.15mm;
        color: var(--ens-muted);
        margin: 0.4mm 0 0;
        line-height: 1.1;
    }

    .carte-ens-rows {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .carte-ens-row {
        display: flex;
        align-items: center;
        gap: 1.4mm;
        font-size: 12px;
        font-weight: 700;
        color: var(--ens-navy-text);
        line-height: 1.5;
        min-width: 0;
    }

    .carte-ens-row i {
        width: 12px;
        color: var(--ens-blue);
        font-size: 12px;
        text-align: center;
        flex-shrink: 0;
    }

    .carte-ens-row span {
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.5;
    }

    .carte-ens-row strong {
        font-weight: 700;
    }

    .carte-ens-bottom {
        margin-top: auto;
        position: relative;
        z-index: 1;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 2mm;
        padding-top: 0.4mm;
    }

    .carte-ens-notice {
        display: flex;
        align-items: flex-start;
        gap: 1mm;
        font-size: 1.35mm;
        color: var(--ens-muted);
        font-style: italic;
        line-height: 1.25;
        max-width: 36mm;
    }

    .carte-ens-notice i {
        color: var(--ens-blue);
        margin-top: 0.15mm;
        flex-shrink: 0;
    }

    .carte-ens-sign {
        text-align: center;
        min-width: 18mm;
    }

    .carte-ens-sign-name {
        font-family: "Segoe Script", "Brush Script MT", "Comic Sans MS", cursive;
        font-size: 2.6mm;
        color: var(--ens-blue);
        line-height: 1;
        margin: 0;
    }

    .carte-ens-sign-line {
        border: 0;
        border-top: 0.25mm solid #c5d0e0;
        margin: 0.4mm 0 0.3mm;
    }

    .carte-ens-sign-label {
        font-size: 1.2mm;
        color: var(--ens-muted);
        margin: 0;
        letter-spacing: 0.05mm;
    }

    .carte-ens-row-wrap {
        align-items: flex-start;
    }

    .carte-ens-row-wrap span {
        white-space: normal;
        overflow: visible;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .carte-ens-footer {
        flex-shrink: 0;
        min-height: 5.2mm;
        height: auto;
        background: var(--ens-navy);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2mm;
        padding: 0.8mm 3mm;
        font-size: 1.65mm;
        font-weight: 600;
    }

    .carte-ens-footer span {
        display: flex;
        align-items: flex-start;
        gap: 1.1mm;
        min-width: 0;
        line-height: 1.25;
    }

    .carte-ens-footer span:first-child {
        flex: 1;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .carte-ens-footer span:last-child {
        flex-shrink: 0;
        white-space: nowrap;
    }

    .carte-ens-footer i {
        font-size: 1.7mm;
        opacity: 0.95;
        margin-top: 0.15mm;
    }

    .carte-preview-wrap {
        overflow-x: auto;
        padding: 16px 8px 24px;
        background: radial-gradient(circle at top, #eef3fb 0%, #f7f8fb 70%);
        border-radius: 8px;
    }

    .carte-preview-stage {
        --preview-scale: 1.7;
        width: calc(86mm * var(--preview-scale));
        height: calc(54mm * var(--preview-scale));
        margin: 0 auto;
    }

    .carte-preview-stage .carte-ens {
        transform: scale(var(--preview-scale));
        transform-origin: top left;
        box-shadow: 0 10px 28px rgba(10, 42, 114, 0.22);
    }

    @media (max-width: 640px) {
        .carte-preview-stage {
            --preview-scale: 1;
        }
    }
</style>
