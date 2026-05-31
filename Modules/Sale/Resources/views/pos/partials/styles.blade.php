<style>
    /* ── POS product grid ── */
    .pos-product-grid .pos-product-card {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        border: 1px solid #eef1f4;
        overflow: hidden;
    }

    .pos-product-grid .pos-product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1) !important;
    }

    .pos-product-grid .pos-product-card.is-out-of-stock {
        opacity: 0.6;
    }

    .pos-product-grid .pos-product-image-wrap {
        position: relative;
        background: #f8f9fa;
    }

    .pos-product-grid .pos-product-image-wrap img {
        width: 100%;
        height: 100px;
        object-fit: cover;
        display: block;
    }

    .pos-product-grid .pos-product-stock {
        position: absolute;
        top: 6px;
        right: 6px;
        min-width: 24px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
        padding: 5px 7px;
        border-radius: 999px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
    }

    .pos-product-grid .pos-product-body {
        padding: 0.55rem 0.65rem 0.65rem;
    }

    .pos-product-grid .pos-product-name {
        font-size: 12px;
        font-weight: 600;
        line-height: 1.35;
        margin: 0 0 0.35rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.7em;
        color: #2f353a;
    }

    .pos-product-grid .pos-product-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.35rem;
    }

    .pos-product-grid .pos-product-code {
        font-size: 10px;
        color: #8a93a2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 55%;
    }

    .pos-product-grid .pos-product-price {
        font-size: 13px;
        font-weight: 700;
        color: #2eb85c;
        white-space: nowrap;
    }

    .pos-grid-loader {
        top: 0;
        right: 0;
        left: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.5);
        z-index: 99;
    }

    .pos-idle-icon {
        font-size: 3rem;
        color: #adb5bd;
    }

    .pos-category-chip {
        border-radius: 999px;
        font-size: 12px;
        max-width: 140px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pos-toolbar {
        gap: 0.5rem;
    }

    /* ── POS dark mode ── */
    .pos-app.pos-dark {
        color: #e4e7eb;
    }

    .pos-app.pos-dark .pos-search-card,
    .pos-app.pos-dark .pos-product-grid .card,
    .pos-app.pos-dark .pos-checkout-card {
        background: #2a2d35 !important;
        border-color: #3a3f4b !important;
    }

    .pos-app.pos-dark .pos-search-input,
    .pos-app.pos-dark .pos-checkout-card .form-control,
    .pos-app.pos-dark .pos-count-select {
        background: #1e2128;
        border-color: #3a3f4b;
        color: #e4e7eb;
    }

    .pos-app.pos-dark .pos-search-input::placeholder {
        color: #8a93a2;
    }

    .pos-app.pos-dark .input-group-text {
        background: #1e2128;
        border-color: #3a3f4b;
        color: #adb5bd;
    }

    .pos-app.pos-dark .pos-product-grid .pos-product-card {
        background: #32363f;
        border-color: #3a3f4b;
    }

    .pos-app.pos-dark .pos-product-grid .pos-product-image-wrap {
        background: #1e2128;
    }

    .pos-app.pos-dark .pos-product-grid .pos-product-name {
        color: #e4e7eb;
    }

    .pos-app.pos-dark .pos-product-grid .pos-product-code,
    .pos-app.pos-dark .text-muted,
    .pos-app.pos-dark .form-text.text-muted,
    .pos-app.pos-dark small.text-muted {
        color: #9aa3b2 !important;
    }

    .pos-app.pos-dark .pos-idle-icon {
        color: #6c757d;
    }

    .pos-app.pos-dark .pos-grid-loader {
        background-color: rgba(30, 33, 40, 0.65);
    }

    .pos-app.pos-dark .pos-checkout-card .table {
        color: #e4e7eb;
    }

    .pos-app.pos-dark .pos-checkout-card .table thead th {
        background: #1e2128;
        border-color: #3a3f4b;
        color: #cbd2dc;
    }

    .pos-app.pos-dark .pos-checkout-card .table td,
    .pos-app.pos-dark .pos-checkout-card .table-striped tbody tr:nth-of-type(odd) {
        background: transparent;
        border-color: #3a3f4b;
    }

    .pos-app.pos-dark .pos-checkout-card .table-striped tbody tr:nth-of-type(odd) {
        background: rgba(255, 255, 255, 0.03);
    }

    .pos-app.pos-dark .alert-info {
        background: #1a3a52;
        border-color: #2a5a7a;
        color: #b8daff;
    }

    .pos-app.pos-dark .btn-outline-secondary {
        color: #cbd2dc;
        border-color: #4a5060;
    }

    .pos-app.pos-dark .btn-outline-secondary:hover {
        background: #3a3f4b;
        color: #fff;
    }

    .pos-app.pos-dark .btn-link.text-muted {
        color: #9aa3b2 !important;
    }

    .pos-app.pos-dark #pos-dark-toggle {
        color: #f0c040;
        border-color: #6a5a20;
    }

    .pos-scan-modal {
        z-index: 1060;
    }

    .pos-app.pos-dark .pos-scan-modal .modal-content {
        background: #2a2d35;
        color: #e4e7eb;
    }

    .pos-app.pos-dark .pos-scan-modal .modal-header.bg-danger,
    .pos-app.pos-dark .pos-scan-modal .modal-header.bg-warning {
        border-bottom-color: #3a3f4b;
    }

    .pos-app.pos-dark .pos-scan-modal .alert-light {
        background: #1e2128;
        border-color: #3a3f4b !important;
        color: #e4e7eb;
    }
</style>
