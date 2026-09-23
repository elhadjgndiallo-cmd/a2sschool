@push('styles')
<style>
    .ae-page { max-width: 1180px; }
    .ae-stat {
        border: 0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
        height: 100%;
    }
    .ae-stat .ae-stat-body {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px 20px;
    }
    .ae-stat-icon {
        width: 48px; height: 48px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 1.15rem; flex-shrink: 0;
    }
    .ae-stat-icon.today { background: linear-gradient(135deg, #ef4444, #f97316); }
    .ae-stat-icon.wait { background: linear-gradient(135deg, #f59e0b, #eab308); }
    .ae-stat-icon.open { background: linear-gradient(135deg, #64748b, #475569); }
    .ae-stat-icon.ok { background: linear-gradient(135deg, #10b981, #14b8a6); }
    .ae-stat h3 { margin: 0; font-size: 1.6rem; font-weight: 700; line-height: 1; }
    .ae-stat small { color: #64748b; }
    .ae-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .07);
    }
    .ae-card .card-header {
        background: transparent;
        border-bottom: 1px solid #eef2f7;
        padding: 18px 22px 12px;
        font-weight: 600;
    }
    .ae-avatar {
        width: 42px; height: 42px; border-radius: 12px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #eef2ff; color: #4338ca; font-weight: 700; flex-shrink: 0;
        overflow: hidden;
    }
    .ae-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .ae-row { transition: background .15s ease; }
    .ae-row:hover { background: #f8fafc; }
    .ae-badge {
        border-radius: 999px;
        padding: .35em .75em;
        font-weight: 600;
        font-size: .78rem;
    }
    .ae-type {
        display: inline-flex; align-items: center; gap: 6px;
        background: #f1f5f9; color: #334155;
        border-radius: 999px; padding: .3em .7em; font-size: .8rem; font-weight: 600;
    }
    .ae-empty {
        text-align: center; padding: 48px 16px; color: #64748b;
    }
    .ae-empty i { font-size: 2.4rem; color: #cbd5e1; display: block; margin-bottom: 12px; }
    .ae-form-section {
        background: #f8fafc;
        border-radius: 14px;
        padding: 16px 18px;
        margin-bottom: 16px;
    }
    .ae-form-section h6 {
        font-size: .78rem;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 12px;
    }
    .ae-hero {
        background: linear-gradient(135deg, #1e3a5f 0%, #0f766e 100%);
        color: #fff;
        border-radius: 18px;
        padding: 22px 24px;
        margin-bottom: 22px;
    }
    .ae-hero h1 { font-size: 1.45rem; margin: 0 0 4px; }
    .ae-hero p { margin: 0; opacity: .85; }
</style>
@endpush
