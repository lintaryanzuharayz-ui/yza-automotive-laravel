@extends('layouts.app')

@section('title', 'YZA Automotive · Operational Command Center')

@section('content')
@php
    $userRole = auth()->user()->roleName();
    $roleLabels = [
        'service_advisor' => 'Service Advisor',
        'mechanic' => 'Mekanik Bengkel',
        'warehouse_staff' => 'Staf Gudang / Inventory',
        'finance' => 'Finance & Billing',
        'admin' => 'Administrator',
        'super_admin' => 'Super Admin',
        'owner' => 'Owner',
        'petugas' => 'Petugas Operasional',
    ];
    $currentRoleLabel = $roleLabels[$userRole] ?? 'Staff Bengkel';
    
    // Tentukan tab aktif default berdasarkan peran user
    $defaultTab = match($userRole) {
        'service_advisor' => 'advisor',
        'mechanic' => 'mechanic',
        'warehouse_staff' => 'warehouse',
        'finance' => 'finance',
        default => 'advisor',
    };
    $activeTab = request('tab', $defaultTab);
@endphp

<div class="occ-wrapper">
    <!-- Scoped Styling for Operational Command Center -->
    <style>
        :root {
            --occ-bg: #f8fafc;
            --occ-card: #ffffff;
            --occ-border: #e2e8f0;
            --occ-dark: #0f172a;
            --occ-dark-soft: #1e293b;
            --occ-red: #ef1b2d;
            --occ-red-dark: #cc1020;
            --occ-red-subtle: #fef2f2;
            --occ-green: #10b981;
            --occ-green-subtle: #ecfdf5;
            --occ-amber: #f59e0b;
            --occ-amber-subtle: #fffbeb;
            --occ-blue: #3b82f6;
            --occ-blue-subtle: #eff6ff;
            --occ-text-main: #0f172a;
            --occ-text-muted: #64748b;
        }

        .occ-wrapper {
            max-width: 1560px;
            margin: 0 auto;
            padding: 8px 12px 64px;
            font-family: inherit;
            color: var(--occ-text-main);
        }

        /* Hero Banner */
        .occ-hero {
            background: linear-gradient(135deg, #090a0f 0%, #151821 55%, #1e2330 100%);
            color: #ffffff;
            border-radius: 20px;
            padding: 28px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 24px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        .occ-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 240px;
            height: 240px;
            background: radial-gradient(circle, rgba(239, 27, 45, 0.18) 0%, rgba(239, 27, 45, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .occ-hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--occ-red);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .occ-hero-eyebrow::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            background: var(--occ-red);
            border-radius: 50%;
        }
        .occ-hero h1 {
            font-size: clamp(24px, 3.2vw, 36px);
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 0 0 8px;
            color: #ffffff;
            line-height: 1.2;
        }
        .occ-hero p {
            color: #94a3b8;
            font-size: 14px;
            margin: 0;
            max-width: 680px;
            line-height: 1.5;
        }
        .occ-hero-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
            text-align: right;
        }
        .occ-role-badge {
            background: rgba(239, 27, 45, 0.15);
            border: 1px solid rgba(239, 27, 45, 0.4);
            color: #ff6b77;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .occ-hero-meta {
            color: #94a3b8;
            font-size: 12px;
            font-weight: 600;
        }

        /* KPI Cards Grid */
        .occ-kpis {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }
        .occ-kpi-card {
            background: var(--occ-card);
            border: 1px solid var(--occ-border);
            border-radius: 14px;
            padding: 16px 18px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 110px;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .occ-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        }
        .occ-kpi-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--occ-text-muted);
        }
        .occ-kpi-value {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.04em;
            color: var(--occ-text-main);
            margin: 6px 0 2px;
        }
        .occ-kpi-hint {
            font-size: 11px;
            color: var(--occ-text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .occ-kpi-card.tone-red .occ-kpi-value { color: var(--occ-red); }
        .occ-kpi-card.tone-amber { border-top: 3px solid var(--occ-amber); }
        .occ-kpi-card.tone-green { border-top: 3px solid var(--occ-green); }
        .occ-kpi-card.tone-dark { border-top: 3px solid var(--occ-dark); }
        .occ-kpi-card.tone-red { border-top: 3px solid var(--occ-red); }

        /* Role Switcher Navigation Bar */
        .occ-nav-tabs {
            display: flex;
            gap: 8px;
            background: #ffffff;
            border: 1px solid var(--occ-border);
            border-radius: 16px;
            padding: 6px;
            margin-bottom: 24px;
            overflow-x: auto;
            scrollbar-width: thin;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
        }
        .occ-tab-btn {
            flex: 1;
            min-width: 170px;
            border: none;
            background: transparent;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            color: var(--occ-text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .occ-tab-btn:hover {
            color: var(--occ-dark);
            background: #f1f5f9;
        }
        .occ-tab-btn.is-active {
            background: var(--occ-dark);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        }
        .occ-tab-pill {
            font-size: 10px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
        }
        .occ-tab-btn:not(.is-active) .occ-tab-pill {
            background: #e2e8f0;
            color: var(--occ-dark);
        }

        /* Section Cards & Panels */
        .occ-section {
            display: none;
        }
        .occ-section.is-active {
            display: block;
            animation: occFadeIn 0.2s ease-in-out;
        }
        @keyframes occFadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .occ-panel {
            background: var(--occ-card);
            border: 1px solid var(--occ-border);
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        }
        .occ-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--occ-border);
        }
        .occ-panel-title-wrap h2 {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin: 0;
            color: var(--occ-dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .occ-panel-title-wrap p {
            margin: 4px 0 0;
            font-size: 12px;
            color: var(--occ-text-muted);
        }
        .occ-panel-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Tables & Responsive Wrappers */
        .occ-table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--occ-border);
            background: #ffffff;
        }
        .occ-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
            min-width: 840px;
        }
        .occ-table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 13px 16px;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .occ-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s ease;
        }
        .occ-table tbody tr:hover {
            background: #f8fafc;
        }
        .occ-table tbody tr:last-child {
            border-bottom: none;
        }
        .occ-table td {
            padding: 14px 16px;
            vertical-align: middle;
            color: var(--occ-text-main);
        }
        .occ-table td strong {
            display: block;
            font-weight: 700;
            color: var(--occ-dark);
        }
        .occ-table td small {
            display: block;
            color: var(--occ-text-muted);
            font-size: 11px;
            margin-top: 3px;
        }

        /* Monospace Identifiers */
        .occ-plat-pill {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            display: inline-block;
            background: #0f172a;
            color: #ffffff;
            font-size: 11.5px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            letter-spacing: 0.05em;
        }
        .occ-mono-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 12.5px;
            font-weight: 800;
            color: #0f172a;
        }
        .occ-mono-price {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-weight: 700;
            color: #0f172a;
        }

        /* Status Badges */
        .occ-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
            white-space: nowrap;
            line-height: 1.3;
        }
        .occ-badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
        }
        .occ-badge.badge-success { background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); }
        .occ-badge.badge-success .occ-badge-dot { background: #10b981; }
        .occ-badge.badge-warning { background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.25); }
        .occ-badge.badge-warning .occ-badge-dot { background: #f59e0b; }
        .occ-badge.badge-danger { background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.25); }
        .occ-badge.badge-danger .occ-badge-dot { background: #ef4444; }
        .occ-badge.badge-info { background: rgba(59, 130, 246, 0.1); color: #2563eb; border: 1px solid rgba(59, 130, 246, 0.25); }
        .occ-badge.badge-info .occ-badge-dot { background: #3b82f6; }
        .occ-badge.badge-neutral { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .occ-badge.badge-neutral .occ-badge-dot { background: #64748b; }

        /* Buttons & Controls */
        .occ-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .occ-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .occ-btn-primary {
            background: var(--occ-red);
            color: #ffffff;
            border-color: var(--occ-red);
        }
        .occ-btn-primary:hover {
            background: var(--occ-red-dark);
            color: #ffffff;
        }
        .occ-btn-dark {
            background: var(--occ-dark);
            color: #ffffff;
            border-color: var(--occ-dark);
        }
        .occ-btn-dark:hover {
            background: var(--occ-dark-soft);
            color: #ffffff;
        }
        .occ-btn-success {
            background: #059669;
            color: #ffffff;
            border-color: #059669;
        }
        .occ-btn-success:hover {
            background: #047857;
            color: #ffffff;
        }
        .occ-btn-outline {
            background: #ffffff;
            color: var(--occ-dark);
            border-color: var(--occ-border);
        }
        .occ-btn-outline:hover {
            border-color: var(--occ-dark);
            background: #f8fafc;
        }
        .occ-btn-outline-danger {
            background: #ffffff;
            color: var(--occ-red);
            border-color: #fecaca;
        }
        .occ-btn-outline-danger:hover {
            background: var(--occ-red-subtle);
            border-color: var(--occ-red);
        }
        .occ-btn-sm {
            padding: 5px 10px;
            font-size: 11px;
            border-radius: 8px;
        }

        /* Work Order Cards for Mekanik */
        .occ-wo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(460px, 1fr));
            gap: 20px;
        }
        .occ-wo-card {
            background: #ffffff;
            border: 1px solid var(--occ-border);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: border-color 0.15s ease;
        }
        .occ-wo-card:hover {
            border-color: #cbd5e1;
        }
        .occ-wo-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1px dashed var(--occ-border);
        }
        .occ-wo-code {
            font-size: 15px;
            font-weight: 800;
            color: var(--occ-dark);
            letter-spacing: -0.02em;
        }
        .occ-wo-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 14px;
            background: #f8fafc;
            padding: 12px;
            border-radius: 10px;
            font-size: 12px;
        }
        .occ-wo-meta-item small {
            display: block;
            color: var(--occ-text-muted);
            font-size: 10.5px;
            text-transform: uppercase;
            font-weight: 700;
        }
        .occ-wo-meta-item strong {
            display: block;
            color: var(--occ-dark);
            margin-top: 2px;
        }
        .occ-wo-section {
            margin-bottom: 14px;
        }
        .occ-wo-section-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--occ-text-muted);
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .occ-wo-box {
            background: #ffffff;
            border: 1px solid var(--occ-border);
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 12px;
            line-height: 1.45;
            color: var(--occ-text-main);
        }
        .occ-wo-items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 6px;
        }
        .occ-wo-items-table th {
            text-align: left;
            background: #f1f5f9;
            padding: 6px 8px;
            font-size: 10px;
            font-weight: 700;
            color: var(--occ-text-muted);
            text-transform: uppercase;
        }
        .occ-wo-items-table td {
            padding: 7px 8px;
            border-bottom: 1px solid #f1f5f9;
        }
        .occ-wo-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid var(--occ-border);
            flex-wrap: wrap;
        }
        .occ-wo-total {
            font-size: 13px;
        }
        .occ-wo-total small {
            display: block;
            color: var(--occ-text-muted);
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 700;
        }
        .occ-wo-total strong {
            font-size: 16px;
            font-weight: 800;
            color: var(--occ-red);
        }

        /* Alert / Callout Boxes */
        .occ-alert-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .occ-alert-box.alert-danger {
            background: #fef2f2;
            border-color: #fecaca;
        }
        .occ-alert-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .occ-alert-icon {
            font-size: 20px;
        }

        /* Search & Filter Bar */
        .occ-filter-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .occ-search-input {
            padding: 9px 14px;
            border: 1px solid var(--occ-border);
            border-radius: 10px;
            font-size: 13px;
            min-width: 260px;
            outline: none;
            transition: border-color 0.15s ease;
        }
        .occ-search-input:focus {
            border-color: var(--occ-dark);
        }

        /* Modals & Dialogs */
        .occ-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .occ-modal-backdrop.is-open {
            display: flex;
            animation: occModalFade 0.2s ease-out;
        }
        @keyframes occModalFade {
            from { opacity: 0; transform: scale(0.97); }
            to { opacity: 1; transform: scale(1); }
        }
        .occ-modal-card {
            background: #ffffff;
            border-radius: 20px;
            width: 100%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.25);
            padding: 26px 30px;
            position: relative;
        }
        .occ-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--occ-border);
        }
        .occ-modal-header h3 {
            font-size: 18px;
            font-weight: 800;
            margin: 0;
            color: var(--occ-dark);
        }
        .occ-modal-close {
            background: transparent;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--occ-text-muted);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }
        .occ-modal-close:hover {
            background: #f1f5f9;
            color: var(--occ-dark);
        }
        .occ-form-group {
            margin-bottom: 16px;
        }
        .occ-form-group label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: var(--occ-dark);
            margin-bottom: 6px;
        }
        .occ-form-group label span {
            color: var(--occ-red);
        }
        .occ-form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--occ-border);
            border-radius: 10px;
            font-size: 13px;
            color: var(--occ-dark);
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.15s ease;
        }
        .occ-form-control:focus {
            border-color: var(--occ-dark);
        }
        textarea.occ-form-control {
            resize: vertical;
            min-height: 85px;
        }
        .occ-form-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 22px;
            padding-top: 16px;
            border-top: 1px solid var(--occ-border);
        }

        /* Empty state */
        .occ-empty {
            text-align: center;
            padding: 44px 20px;
            color: var(--occ-text-muted);
        }
        .occ-empty-icon {
            font-size: 38px;
            margin-bottom: 10px;
            display: block;
            opacity: 0.6;
        }
        .occ-empty strong {
            display: block;
            font-size: 14px;
            color: var(--occ-dark);
            margin-bottom: 4px;
        }
        .occ-empty p {
            font-size: 12px;
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .occ-kpis { grid-template-columns: repeat(3, 1fr); }
            .occ-wo-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .occ-hero { padding: 22px; }
            .occ-hero-right { align-items: flex-start; text-align: left; }
            .occ-kpis { grid-template-columns: repeat(2, 1fr); }
            .occ-panel { padding: 18px; }
            .occ-nav-tabs { flex-wrap: nowrap; overflow-x: auto; }
        }
        @media (max-width: 500px) {
            .occ-kpis { grid-template-columns: 1fr; }
        }
    </style>

    <!-- Header / Hero Section -->
    <header class="occ-hero">
        <div>
            <div class="occ-hero-eyebrow">YZA Service Operations Control</div>
            <h1>Halo, {{ $user->displayName() }}</h1>
            <p>Pusat operasional terintegrasi: Service Advisor, Mekanik Bengkel, Staf Gudang / Suku Cadang, dan Finance.</p>
        </div>
        <div class="occ-hero-right">
            <span class="occ-role-badge">
                <span class="occ-badge-dot" style="background:#ff6b77;"></span>
                {{ $currentRoleLabel }}
            </span>
            <div class="occ-hero-meta">
                {{ now('Asia/Jakarta')->translatedFormat('l, d F Y') }} · {{ now('Asia/Jakarta')->format('H:i') }} WIB
            </div>
            <form method="POST" action="{{ route('logout') }}" style="display:inline-block; margin-top:6px;">
                @csrf
                <button type="submit" style="background:#ef1b2d; color:#ffffff; border:none; padding:6px 14px; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 6px rgba(239,27,45,0.3); transition:all 0.15s ease;" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef1b2d'">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                    Logout / Ganti Akun
                </button>
            </form>
        </div>
    </header>

    <!-- Session Feedback Messages -->
    @if(session('success'))
        <div class="occ-alert-box" style="background:#ecfdf5; border-color:#a7f3d0; margin-bottom:20px;">
            <div class="occ-alert-info">
                <span style="color:#059669; font-weight:800; font-size:18px;">✓</span>
                <span style="color:#065f46; font-size:13px; font-weight:600;">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="occ-modal-close" style="color:#065f46;">✕</button>
        </div>
    @endif
    @if(session('error') || (isset($errors) && $errors->any()))
        <div class="occ-alert-box alert-danger" style="margin-bottom:20px;">
            <div class="occ-alert-info">
                <span style="color:#ef1b2d; font-weight:800; font-size:18px;">⚠</span>
                <span style="color:#991b1b; font-size:13px; font-weight:600;">{{ session('error') ?: ($errors->first() ?? '') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="occ-modal-close" style="color:#991b1b;">✕</button>
        </div>
    @endif

    <!-- Global KPIs Section -->
    <section class="occ-kpis" aria-label="Key Performance Indicators">
        @foreach($metrics as $m)
            <div class="occ-kpi-card tone-{{ $m['tone'] }}">
                <span class="occ-kpi-label">{{ $m['label'] }}</span>
                <div class="occ-kpi-value">{{ number_format($m['value']) }}</div>
                <div class="occ-kpi-hint">{{ $m['hint'] }}</div>
            </div>
        @endforeach
    </section>

    <!-- Role Switcher Navigation Tabs -->
    <nav class="occ-nav-tabs" aria-label="Navigasi Peran Operasional">
        <button type="button" class="occ-tab-btn {{ $activeTab === 'advisor' ? 'is-active' : '' }}" onclick="switchOccTab('advisor')" data-tab="advisor">
            <span>📋 Service Advisor</span>
            <span class="occ-tab-pill">{{ $advisorBookings->count() }}</span>
        </button>
        <button type="button" class="occ-tab-btn {{ $activeTab === 'mechanic' ? 'is-active' : '' }}" onclick="switchOccTab('mechanic')" data-tab="mechanic">
            <span>🔧 Mekanik (WO)</span>
            <span class="occ-tab-pill">{{ $activeWorkOrders->count() }} WO</span>
        </button>
        <button type="button" class="occ-tab-btn {{ $activeTab === 'warehouse' ? 'is-active' : '' }}" onclick="switchOccTab('warehouse')" data-tab="warehouse">
            <span>📦 Staf Gudang</span>
            <span class="occ-tab-pill">{{ $lowStockParts->count() }} Kritis</span>
        </button>
        <button type="button" class="occ-tab-btn {{ $activeTab === 'finance' ? 'is-active' : '' }}" onclick="switchOccTab('finance')" data-tab="finance">
            <span>💳 Finance & Kasir</span>
            <span class="occ-tab-pill">{{ $verificationPayments->count() }} Tagihan</span>
        </button>
        <button type="button" class="occ-tab-btn {{ $activeTab === 'overview' ? 'is-active' : '' }}" onclick="switchOccTab('overview')" data-tab="overview">
            <span>⚡ Agenda & Antrean</span>
        </button>
    </nav>


    <!-- ========================================================================= -->
    <!-- ROLE 1: SERVICE ADVISOR SECTION                                            -->
    <!-- ========================================================================= -->
    <section id="section-advisor" class="occ-section {{ $activeTab === 'advisor' ? 'is-active' : '' }}">
        <!-- Banner Kendaraan Siap Check-in -->
        @if($readyForCheckin->isNotEmpty())
            <div class="occ-alert-box" style="background:#eff6ff; border-color:#bfdbfe; margin-bottom:20px;">
                <div class="occ-alert-info">
                    <span class="occ-alert-icon">🚗</span>
                    <div>
                        <strong style="color:#1e40af; font-size:13px; display:block;">{{ $readyForCheckin->count() }} Kendaraan Dikonfirmasi Siap Check-in Fisik</strong>
                        <span style="color:#3b82f6; font-size:12px;">Booking sudah dikonfirmasi. Saat mobil tiba di bengkel, klik tombol "Penerimaan Kendaraan" untuk membuka Work Order teknisi.</span>
                    </div>
                </div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    @foreach($readyForCheckin->take(3) as $ready)
                        <button type="button" 
                                class="occ-btn occ-btn-sm occ-btn-dark"
                                onclick="openCheckinModal({{ $ready->id_booking_servis }}, '{{ $ready->kode_booking }}', '{{ addslashes($ready->pelanggan?->nama_lengkap ?? 'Customer') }}', '{{ addslashes($ready->kendaraan?->merk . ' ' . $ready->kendaraan?->model) }}', '{{ $ready->kendaraan?->nomor_polisi }}', {{ (int)($ready->kendaraan?->kilometer ?? 0) }})">
                            Check-in: {{ $ready->kendaraan?->nomor_polisi }} →
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="occ-panel">
            <div class="occ-panel-header">
                <div class="occ-panel-title-wrap">
                    <h2>📋 Antrean Booking Servis & Penerimaan Kendaraan</h2>
                    <p>Konfirmasi reservasi pelanggan, catat kedatangan unit, dan terbitkan Surat Perintah Kerja (Work Order).</p>
                </div>
                <div class="occ-panel-actions">
                    <div class="occ-filter-bar" style="margin-bottom:0;">
                        <input type="text" id="search-advisor" class="occ-search-input" placeholder="Cari booking, nama, nopol..." onkeyup="filterTable('search-advisor', 'table-advisor')">
                    </div>
                    <a href="{{ route('petugas.booking-servis.index') }}" class="occ-btn occ-btn-outline occ-btn-sm">Semua Riwayat Booking →</a>
                </div>
            </div>

            <div class="occ-table-responsive">
                <table class="occ-table" id="table-advisor">
                    <thead>
                        <tr>
                            <th style="width:50px;">No</th>
                            <th>Kode Booking</th>
                            <th>Customer</th>
                            <th>Kendaraan</th>
                            <th>Jenis Servis & Keluhan</th>
                            <th>Jadwal Booking</th>
                            <th>Status</th>
                            <th style="text-align:right;">Aksi Service Advisor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($advisorBookings as $b)
                            @php
                                $statusBadge = match($b->status) {
                                    'menunggu' => ['class' => 'badge-warning', 'label' => 'Menunggu Konfirmasi'],
                                    'diterima' => ['class' => 'badge-info', 'label' => 'Dikonfirmasi (Siap Check-in)'],
                                    'diproses' => ['class' => 'badge-success', 'label' => 'Dalam Pengerjaan (WO)'],
                                    'selesai' => ['class' => 'badge-success', 'label' => 'Selesai'],
                                    'ditolak' => ['class' => 'badge-danger', 'label' => 'Ditolak'],
                                    'dibatalkan' => ['class' => 'badge-neutral', 'label' => 'Dibatalkan'],
                                    default => ['class' => 'badge-neutral', 'label' => ucfirst($b->status)],
                                };
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $b->kode_booking }}</strong>
                                    <small>{{ $b->created_at?->translatedFormat('d M Y, H:i') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $b->pelanggan?->nama_lengkap ?: 'Customer Umum' }}</strong>
                                    <small>{{ $b->pelanggan?->no_telepon ?: '—' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $b->kendaraan?->nomor_polisi ?: 'Plat —' }}</strong>
                                    <small>{{ $b->kendaraan?->merk }} {{ $b->kendaraan?->model }} (KM: {{ number_format($b->kendaraan?->kilometer ?? 0) }})</small>
                                </td>
                                <td>
                                    <strong>{{ $b->jenisServis?->nama_servis ?: 'General Service' }}</strong>
                                    <small title="{{ $b->keluhan }}">{{ Str::limit($b->keluhan ?: 'Tidak ada keluhan tertulis', 42) }}</small>
                                </td>
                                <td>
                                    <strong>{{ \Carbon\Carbon::parse($b->tanggal)->translatedFormat('d M Y') }}</strong>
                                    <small>{{ substr((string)$b->jam, 0, 5) }} WIB</small>
                                </td>
                                <td>
                                    <span class="occ-badge {{ $statusBadge['class'] }}">
                                        <span class="occ-badge-dot"></span>
                                        {{ $statusBadge['label'] }}
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <div style="display:inline-flex; gap:6px; justify-content:flex-end; flex-wrap:wrap;">
                                        @if($b->status === 'menunggu')
                                            <!-- Tombol Konfirmasi -->
                                            <form method="POST" action="{{ route('petugas.booking-servis.action', [$b->id_booking_servis, 'confirm']) }}" style="display:inline;" onsubmit="return confirm('Konfirmasi jadwal booking {{ $b->kode_booking }}?')">
                                                @csrf
                                                <button type="submit" class="occ-btn occ-btn-sm occ-btn-success" title="Konfirmasi Reservasi">
                                                    ✓ Terima
                                                </button>
                                            </form>
                                            <!-- Tombol Tolak -->
                                            <form method="POST" action="{{ route('petugas.booking-servis.action', [$b->id_booking_servis, 'reject']) }}" style="display:inline;" onsubmit="return confirm('Tolak booking ini?')">
                                                @csrf
                                                <button type="submit" class="occ-btn occ-btn-sm occ-btn-outline-danger" title="Tolak Reservasi">
                                                    ✕ Tolak
                                                </button>
                                            </form>
                                        @elseif($b->status === 'diterima' && !$b->servis)
                                            <!-- Tombol Check-in Kendaraan -->
                                            <button type="button" 
                                                    class="occ-btn occ-btn-sm occ-btn-primary" 
                                                    onclick="openCheckinModal({{ $b->id_booking_servis }}, '{{ $b->kode_booking }}', '{{ addslashes($b->pelanggan?->nama_lengkap ?? 'Customer') }}', '{{ addslashes($b->kendaraan?->merk . ' ' . $b->kendaraan?->model) }}', '{{ $b->kendaraan?->nomor_polisi }}', {{ (int)($b->kendaraan?->kilometer ?? 0) }})">
                                                🚗 Penerimaan Kendaraan
                                            </button>
                                        @elseif($b->servis)
                                            <a href="{{ route('petugas.servis.show', $b->servis) }}" class="occ-btn occ-btn-sm occ-btn-outline" title="Lihat Work Order">
                                                WO: {{ $b->servis->kode_servis }} →
                                            </a>
                                        @else
                                            <a href="{{ route('petugas.booking-servis.show', $b) }}" class="occ-btn occ-btn-sm occ-btn-outline">
                                                Detail
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="occ-empty">
                                    <span class="occ-empty-icon">📅</span>
                                    <strong>Tidak ada antrean booking servis</strong>
                                    <p>Saat pelanggan melakukan booking servis, data reservasi akan tampil di sini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>


    <!-- ========================================================================= -->
    <!-- ROLE 2: MEKANIK (WORK ORDER) SECTION                                      -->
    <!-- ========================================================================= -->
    <section id="section-mechanic" class="occ-section {{ $activeTab === 'mechanic' ? 'is-active' : '' }}">
        <div class="occ-panel">
            <div class="occ-panel-header">
                <div class="occ-panel-title-wrap">
                    <h2>🔧 Work Order (WO) & Pengerjaan Teknis Bengkel</h2>
                    <p>Input diagnosis teknis, pilih jasa perbaikan, alokasikan spare part yang digunakan, dan finalisasi servis kendaraan.</p>
                </div>
                <div class="occ-panel-actions">
                    <a href="{{ route('petugas.servis.index') }}" class="occ-btn occ-btn-outline occ-btn-sm">Semua Work Order →</a>
                </div>
            </div>

            @if($activeWorkOrders->isEmpty())
                <div class="occ-empty" style="background:#ffffff; border-radius:14px; border:1px dashed var(--occ-border);">
                    <span class="occ-empty-icon">🛠️</span>
                    <strong>Tidak ada Work Order aktif saat ini</strong>
                    <p>Semua pengerjaan mekanik telah diselesaikan atau menunggu unit baru dari Service Advisor.</p>
                </div>
            @else
                <div class="occ-wo-grid">
                    @foreach($activeWorkOrders as $wo)
                        @php
                            $woStatusBadge = match($wo->status) {
                                'pemeriksaan' => ['class' => 'badge-warning', 'label' => 'Inspeksi & Diagnosis'],
                                'dikerjakan' => ['class' => 'badge-danger', 'label' => 'Sedang Dikerjakan'],
                                'selesai' => ['class' => 'badge-success', 'label' => 'Selesai'],
                                default => ['class' => 'badge-neutral', 'label' => ucfirst($wo->status)],
                            };
                            $totalJasa = $wo->details->sum('biaya');
                            $totalPart = $wo->sparepartItems->sum('subtotal');
                            $totalEstimasi = $totalJasa + $totalPart;
                        @endphp
                        <article class="occ-wo-card">
                            <div>
                                <!-- Header WO -->
                                <div class="occ-wo-header">
                                    <div>
                                        <div class="occ-wo-code">{{ $wo->kode_servis }}</div>
                                        <small style="color:var(--occ-text-muted);">Masuk: {{ $wo->tanggal_masuk?->translatedFormat('d M Y, H:i') ?? $wo->created_at?->format('d/m/Y H:i') }}</small>
                                    </div>
                                    <span class="occ-badge {{ $woStatusBadge['class'] }}">
                                        <span class="occ-badge-dot"></span>
                                        {{ $woStatusBadge['label'] }}
                                    </span>
                                </div>

                                <!-- Meta Kendaraan & Pelanggan -->
                                <div class="occ-wo-meta-grid">
                                    <div class="occ-wo-meta-item">
                                        <small>Kendaraan Unit</small>
                                        <strong>{{ $wo->kendaraan?->nomor_polisi ?: ($wo->bookingServis?->kendaraan?->nomor_polisi ?? '—') }}</strong>
                                        <span style="font-size:11px; color:#64748b;">{{ $wo->kendaraan?->merk ?: $wo->bookingServis?->kendaraan?->merk }} {{ $wo->kendaraan?->model ?: $wo->bookingServis?->kendaraan?->model }}</span>
                                    </div>
                                    <div class="occ-wo-meta-item">
                                        <small>Pelanggan / Pemilik</small>
                                        <strong>{{ $wo->pelanggan?->nama_lengkap ?: ($wo->bookingServis?->pelanggan?->nama_lengkap ?? 'Customer') }}</strong>
                                        <span style="font-size:11px; color:#64748b;">KM: {{ number_format($wo->kilometer ?? 0) }}</span>
                                    </div>
                                </div>

                                <!-- Keluhan & Hasil Pemeriksaan -->
                                <div class="occ-wo-section">
                                    <div class="occ-wo-section-title">
                                        <span>Keluhan Awal Unit</span>
                                    </div>
                                    <div class="occ-wo-box" style="background:#f8fafc; font-style:italic;">
                                        "{{ $wo->keluhan ?: 'Tidak ada keluhan khusus dari pelanggan.' }}"
                                    </div>
                                </div>

                                <div class="occ-wo-section">
                                    <div class="occ-wo-section-title">
                                        <span>Diagnosis Mekanik</span>
                                        @if($wo->status === 'pemeriksaan')
                                            <span style="color:var(--occ-amber); font-size:10px;">Perlu Diinspeksi</span>
                                        @endif
                                    </div>
                                    @if($wo->hasil_pemeriksaan)
                                        <div class="occ-wo-box" style="border-left: 3px solid var(--occ-green);">
                                            {{ $wo->hasil_pemeriksaan }}
                                            @if($wo->catatan)
                                                <small style="display:block; color:#64748b; margin-top:4px;">Catatan: {{ $wo->catatan }}</small>
                                            @endif
                                        </div>
                                    @else
                                        <div class="occ-wo-box" style="border-left: 3px solid var(--occ-amber); background:#fffbeb; color:#92400e;">
                                            Belum dilakukan pemeriksaan fisik. Klik tombol <strong>Input Diagnosis</strong> di bawah untuk memulai pengerjaan.
                                        </div>
                                    @endif
                                </div>

                                <!-- Jasa Servis Yang Dikerjakan -->
                                <div class="occ-wo-section">
                                    <div class="occ-wo-section-title">
                                        <span>Daftar Jasa / Pekerjaan ({{ $wo->details->count() }})</span>
                                        @if($wo->status === 'dikerjakan')
                                            <button type="button" 
                                                    class="occ-btn occ-btn-sm occ-btn-outline" 
                                                    onclick="openJobModal({{ $wo->id_servis }}, '{{ $wo->kode_servis }}')" 
                                                    style="padding:2px 8px; font-size:10.5px;">
                                                + Tambah Jasa
                                            </button>
                                        @endif
                                    </div>
                                    @if($wo->details->isNotEmpty())
                                        <table class="occ-wo-items-table">
                                            <thead>
                                                <tr>
                                                    <th>Pekerjaan</th>
                                                    <th style="text-align:right;">Biaya (Rp)</th>
                                                    <th style="width:24px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($wo->details as $d)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $d->deskripsi_pekerjaan }}</strong>
                                                            @if($d->jenisServis)
                                                                <small style="color:#64748b;">{{ $d->jenisServis->nama_servis }}</small>
                                                            @endif
                                                        </td>
                                                        <td style="text-align:right; font-weight:700;">{{ number_format($d->biaya, 0, ',', '.') }}</td>
                                                        <td>
                                                            @if($wo->status === 'dikerjakan' && !$wo->id_transaksi)
                                                                <form method="POST" action="{{ route('petugas.servis.detail.destroy', [$wo->id_servis, $d->id_detail_servis]) }}" onsubmit="return confirm('Hapus detail pekerjaan ini?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" style="background:none; border:none; color:#ef1b2d; cursor:pointer; font-size:13px;" title="Hapus">✕</button>
                                                                </form>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div style="font-size:11.5px; color:#94a3b8; padding:6px 0;">Belum ada rincian jasa servis.</div>
                                    @endif
                                </div>

                                <!-- Spare Part Yang Digunakan -->
                                <div class="occ-wo-section">
                                    <div class="occ-wo-section-title">
                                        <span>Spare Part Digunakan ({{ $wo->sparepartItems->count() }})</span>
                                        @if($wo->status === 'dikerjakan')
                                            <button type="button" 
                                                    class="occ-btn occ-btn-sm occ-btn-outline" 
                                                    onclick="openPartModal({{ $wo->id_servis }}, '{{ $wo->kode_servis }}')" 
                                                    style="padding:2px 8px; font-size:10.5px;">
                                                + Ambil Part
                                            </button>
                                        @endif
                                    </div>
                                    @if($wo->sparepartItems->isNotEmpty())
                                        <table class="occ-wo-items-table">
                                            <thead>
                                                <tr>
                                                    <th>Part</th>
                                                    <th style="text-align:center;">Qty</th>
                                                    <th style="text-align:right;">Subtotal (Rp)</th>
                                                    <th style="width:24px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($wo->sparepartItems as $p)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $p->sparepart?->nama_sparepart ?: 'Spare Part' }}</strong>
                                                            <small style="color:#64748b;">{{ $p->sparepart?->kode_sparepart }}</small>
                                                        </td>
                                                        <td style="text-align:center;">{{ $p->jumlah }}</td>
                                                        <td style="text-align:right; font-weight:700;">{{ number_format($p->subtotal, 0, ',', '.') }}</td>
                                                        <td>
                                                            @if($wo->status === 'dikerjakan' && !$wo->id_transaksi)
                                                                <form method="POST" action="{{ route('petugas.servis.sparepart.destroy', [$wo->id_servis, $p->id_servis_sparepart]) }}" onsubmit="return confirm('Batalkan penggunaan spare part ini?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" style="background:none; border:none; color:#ef1b2d; cursor:pointer; font-size:13px;" title="Hapus">✕</button>
                                                                </form>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div style="font-size:11.5px; color:#94a3b8; padding:6px 0;">Tidak ada spare part yang dialokasikan.</div>
                                    @endif
                                </div>
                            </div>

                            <!-- Footer WO & Actions -->
                            <div class="occ-wo-footer">
                                <div class="occ-wo-total">
                                    <small>Total Biaya Servis</small>
                                    <strong>Rp {{ number_format($totalEstimasi, 0, ',', '.') }}</strong>
                                </div>
                                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                    @if($wo->status === 'pemeriksaan')
                                        <button type="button" 
                                                class="occ-btn occ-btn-sm occ-btn-dark" 
                                                onclick="openInspectionModal({{ $wo->id_servis }}, '{{ $wo->kode_servis }}', '{{ addslashes($wo->hasil_pemeriksaan ?? '') }}', '{{ addslashes($wo->catatan ?? '') }}')">
                                            🔍 Input Diagnosis & Mulai
                                        </button>
                                    @elseif($wo->status === 'dikerjakan')
                                        <form method="POST" action="{{ route('petugas.servis.finalize', $wo->id_servis) }}" onsubmit="return confirm('Finalisasi pengerjaan WO {{ $wo->kode_servis }}? Tagihan transaksi akan dibuat.')">
                                            @csrf
                                            <button type="submit" class="occ-btn occ-btn-sm occ-btn-success" title="Selesaikan Servis & Terbitkan Tagihan">
                                                ✓ Selesai / Finalisasi
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('petugas.servis.show', $wo) }}" class="occ-btn occ-btn-sm occ-btn-outline">
                                        Detail →
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>


    <!-- ========================================================================= -->
    <!-- ROLE 3: STAF GUDANG / INVENTORY SECTION                                    -->
    <!-- ========================================================================= -->
    <section id="section-warehouse" class="occ-section {{ $activeTab === 'warehouse' ? 'is-active' : '' }}">
        <!-- Low-Stock Alert Banner -->
        @if($lowStockParts->isNotEmpty())
            <div class="occ-alert-box alert-danger">
                <div class="occ-alert-info">
                    <span class="occ-alert-icon">⚠️</span>
                    <div>
                        <strong style="color:#991b1b; font-size:14px; display:block;">Peringatan: {{ $lowStockParts->count() }} Spare Part Mencapai Stok Minimum / Kritis</strong>
                        <span style="color:#b91c1c; font-size:12px;">Segera lakukan pengadaan stok ke supplier atau lakukan penyesuaian jika ada selisih opname fisik di gudang.</span>
                    </div>
                </div>
                <button type="button" class="occ-btn occ-btn-sm occ-btn-primary" onclick="openAdjustmentModal('', '', 0, 0)">
                    + Form Penyesuaian Stok
                </button>
            </div>
        @endif

        <div class="occ-panel">
            <div class="occ-panel-header">
                <div class="occ-panel-title-wrap">
                    <h2>📦 Manajemen Stok Spare Part & Inventory Gudang</h2>
                    <p>Pantau persediaan suku cadang, identifikasi stok menipis, dan lakukan penyesuaian fisik (stock adjustment).</p>
                </div>
                <div class="occ-panel-actions">
                    <div class="occ-filter-bar" style="margin-bottom:0;">
                        <input type="text" id="search-warehouse" class="occ-search-input" placeholder="Cari suku cadang, kode, kategori..." onkeyup="filterTable('search-warehouse', 'table-warehouse')">
                    </div>
                    <button type="button" class="occ-btn occ-btn-primary occ-btn-sm" onclick="openAdjustmentModal('', '', 0, 0)">
                        + Penyesuaian Stok
                    </button>
                    <a href="{{ route('petugas.inventory.stock') }}" class="occ-btn occ-btn-outline occ-btn-sm">Katalog Lengkap →</a>
                </div>
            </div>

            <div class="occ-table-responsive">
                <table class="occ-table" id="table-warehouse">
                    <thead>
                        <tr>
                            <th style="width:50px;">No</th>
                            <th>Kode Part</th>
                            <th>Nama Spare Part</th>
                            <th>Kategori</th>
                            <th style="text-align:center;">Stok Saat Ini</th>
                            <th style="text-align:center;">Stok Min</th>
                            <th style="text-align:right;">Harga Jual (Rp)</th>
                            <th>Status Ketersediaan</th>
                            <th style="text-align:right;">Aksi Gudang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warehouseParts as $part)
                            @php
                                $stock = (int)$part->stok;
                                $min = (int)$part->stok_minimum;
                                if ($stock <= 0) {
                                    $stockBadge = ['class' => 'badge-danger', 'label' => 'Habis (0)'];
                                } elseif ($stock <= $min) {
                                    $stockBadge = ['class' => 'badge-warning', 'label' => 'Kritis / Menipis'];
                                } else {
                                    $stockBadge = ['class' => 'badge-success', 'label' => 'Stok Aman'];
                                }
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $part->kode_sparepart }}</strong>
                                    <small>{{ $part->lokasi_rak ?: 'Gudang Utama' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $part->nama_sparepart }}</strong>
                                    <small>{{ $part->merk ?: 'Original Parts' }}</small>
                                </td>
                                <td>
                                    <span class="occ-badge badge-neutral">{{ $part->kategori?->nama_kategori ?: 'General' }}</span>
                                </td>
                                <td style="text-align:center;">
                                    <strong style="font-size:15px; color: {{ $stock <= $min ? 'var(--occ-red)' : 'var(--occ-dark)' }};">
                                        {{ number_format($stock) }}
                                    </strong>
                                    <small>{{ $part->satuan ?: 'Pcs' }}</small>
                                </td>
                                <td style="text-align:center;">
                                    <span style="font-weight:600; color:#64748b;">{{ number_format($min) }}</span>
                                </td>
                                <td style="text-align:right; font-weight:700;">
                                    {{ number_format($part->harga_jual, 0, ',', '.') }}
                                </td>
                                <td>
                                    <span class="occ-badge {{ $stockBadge['class'] }}">
                                        <span class="occ-badge-dot"></span>
                                        {{ $stockBadge['label'] }}
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <button type="button" 
                                            class="occ-btn occ-btn-sm occ-btn-outline"
                                            onclick="openAdjustmentModal({{ $part->id_sparepart }}, '{{ addslashes($part->nama_sparepart) }}', {{ $stock }}, {{ $min }})">
                                        ⚡ Sesuaikan Stok
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="occ-empty">
                                    <span class="occ-empty-icon">📦</span>
                                    <strong>Tidak ada katalog spare part</strong>
                                    <p>Data suku cadang belum tercatat di database sistem.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>


    <!-- ========================================================================= -->
    <!-- ROLE 4: FINANCE (PEMBAYARAN & INVOICE) SECTION                             -->
    <!-- ========================================================================= -->
    <section id="section-finance" class="occ-section {{ $activeTab === 'finance' ? 'is-active' : '' }}">
        <!-- Sub-panel 1: Verifikasi Pembayaran Manual -->
        <div class="occ-panel">
            <div class="occ-panel-header">
                <div class="occ-panel-title-wrap">
                    <h2>💳 Verifikasi Pembayaran Manual (Transfer / Kasir)</h2>
                    <p>Validasi bukti transfer pelanggan untuk transaksi servis kendaraan dan pesanan spare part.</p>
                </div>
                <div class="occ-panel-actions">
                    <span class="occ-badge badge-warning">
                        {{ $verificationPayments->count() }} Pembayaran Menunggu
                    </span>
                </div>
            </div>

            @if($verificationPayments->isEmpty())
                <div class="occ-empty" style="background:#ffffff; border-radius:14px; border:1px dashed var(--occ-border);">
                    <span class="occ-empty-icon">✓</span>
                    <strong>Semua pembayaran manual telah diverifikasi</strong>
                    <p>Tidak ada pengajuan transfer manual yang membutuhkan persetujuan kasir saat ini.</p>
                </div>
            @else
                <div class="occ-table-responsive">
                    <table class="occ-table">
                        <thead>
                            <tr>
                                <th>Kode Bayar</th>
                                <th>Transaksi</th>
                                <th>Pelanggan</th>
                                <th>Metode & Nominal</th>
                                <th>Waktu Pengajuan</th>
                                <th>Bukti Transfer</th>
                                <th style="text-align:right;">Aksi Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($verificationPayments as $pay)
                                <tr>
                                    <td>
                                        <strong>{{ $pay->kode_pembayaran }}</strong>
                                        <small>{{ ucfirst($pay->status) }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $pay->transaksi?->kode_transaksi }}</strong>
                                        <small>Jenis: {{ ucfirst($pay->transaksi?->jenis_transaksi ?? 'Servis') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $pay->transaksi?->pelanggan?->nama_lengkap ?: 'Customer' }}</strong>
                                        <small>{{ $pay->transaksi?->pelanggan?->no_telepon ?: '—' }}</small>
                                    </td>
                                    <td>
                                        <strong style="color:var(--occ-red); font-size:14px;">Rp {{ number_format($pay->jumlah_bayar, 0, ',', '.') }}</strong>
                                        <small>{{ strtoupper(str_replace('_', ' ', $pay->metode_pembayaran ?: 'Transfer Bank')) }}</small>
                                    </td>
                                    <td>
                                        {{ $pay->created_at?->translatedFormat('d M Y') }}
                                        <small>{{ $pay->created_at?->format('H:i') }} WIB</small>
                                    </td>
                                    <td>
                                        @if($pay->bukti_transfer)
                                            <a href="{{ asset('storage/' . $pay->bukti_transfer) }}" target="_blank" class="occ-btn occ-btn-sm occ-btn-outline" style="padding:4px 8px;">
                                                📷 Lihat Bukti
                                            </a>
                                        @else
                                            <span style="color:#94a3b8; font-size:11px;">Tidak ada file</span>
                                        @endif
                                    </td>
                                    <td style="text-align:right;">
                                        <div style="display:inline-flex; gap:6px; justify-content:flex-end;">
                                            <form method="POST" action="{{ route('petugas.payment.verify', $pay->id_pembayaran) }}" onsubmit="return confirm('Verifikasi dan sahkan pembayaran {{ $pay->kode_pembayaran }}?')">
                                                @csrf
                                                <button type="submit" class="occ-btn occ-btn-sm occ-btn-success">
                                                    ✓ Terima
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('petugas.payment.reject', $pay->id_pembayaran) }}" onsubmit="return confirm('Tolak pembayaran {{ $pay->kode_pembayaran }}?')">
                                                @csrf
                                                <button type="submit" class="occ-btn occ-btn-sm occ-btn-outline-danger">
                                                    ✕ Tolak
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Sub-panel 2: Daftar Transaksi & Invoice -->
        <div class="occ-panel">
            <div class="occ-panel-header">
                <div class="occ-panel-title-wrap">
                    <h2>📑 Rincian Transaksi Tagihan Servis & Invoice Resmi</h2>
                    <p>Pantau status pelunasan transaksi perbaikan, sisa tagihan, dan cetak invoice pelanggan.</p>
                </div>
                <div class="occ-panel-actions">
                    <div class="occ-filter-bar" style="margin-bottom:0;">
                        <input type="text" id="search-finance" class="occ-search-input" placeholder="Cari no transaksi, pelanggan..." onkeyup="filterTable('search-finance', 'table-finance')">
                    </div>
                </div>
            </div>

            <div class="occ-table-responsive">
                <table class="occ-table" id="table-finance">
                    <thead>
                        <tr>
                            <th>No Transaksi</th>
                            <th>Customer & Unit</th>
                            <th>Tanggal</th>
                            <th style="text-align:right;">Total Tagihan</th>
                            <th style="text-align:right;">Terbayar</th>
                            <th style="text-align:right;">Sisa</th>
                            <th>Status Tagihan</th>
                            <th style="text-align:right;">Aksi Tagihan / Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($serviceTransactionList as $tx)
                            @php
                                $paid = $tx->pembayarans->where('status', 'terverifikasi')->sum('jumlah_bayar');
                                $remaining = max(0, (float)$tx->total - (float)$paid);
                                $txBadge = match($tx->status) {
                                    'lunas' => ['class' => 'badge-success', 'label' => 'Lunas'],
                                    'dp' => ['class' => 'badge-warning', 'label' => 'DP / Sebagian'],
                                    'belum_bayar' => ['class' => 'badge-danger', 'label' => 'Belum Lunas'],
                                    default => ['class' => 'badge-neutral', 'label' => ucfirst($tx->status)],
                                };
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $tx->kode_transaksi }}</strong>
                                    <small>{{ $tx->servis?->kode_servis ?: 'Non-WO' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $tx->pelanggan?->nama_lengkap ?: 'Customer' }}</strong>
                                    <small>{{ $tx->servis?->kendaraan?->nomor_polisi ?: ($tx->servis?->bookingServis?->kendaraan?->nomor_polisi ?? '—') }}</small>
                                </td>
                                <td>
                                    {{ $tx->created_at?->translatedFormat('d M Y') }}
                                    <small>{{ $tx->created_at?->format('H:i') }} WIB</small>
                                </td>
                                <td style="text-align:right; font-weight:700;">
                                    Rp {{ number_format($tx->total, 0, ',', '.') }}
                                </td>
                                <td style="text-align:right; color:#059669; font-weight:700;">
                                    Rp {{ number_format($paid, 0, ',', '.') }}
                                </td>
                                <td style="text-align:right; color: {{ $remaining > 0 ? 'var(--occ-red)' : '#64748b' }}; font-weight:700;">
                                    Rp {{ number_format($remaining, 0, ',', '.') }}
                                </td>
                                <td>
                                    <span class="occ-badge {{ $txBadge['class'] }}">
                                        <span class="occ-badge-dot"></span>
                                        {{ $txBadge['label'] }}
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <div style="display:inline-flex; gap:6px; justify-content:flex-end;">
                                        @if($tx->status !== 'lunas')
                                            <a href="{{ route('petugas.payment.create', $tx->id_transaksi) }}" class="occ-btn occ-btn-sm occ-btn-primary" title="Catat Pembayaran Masuk">
                                                + Bayar
                                            </a>
                                        @endif
                                        <a href="{{ route('petugas.invoice.servis', $tx->id_transaksi) }}" target="_blank" class="occ-btn occ-btn-sm occ-btn-outline" title="Lihat dan Cetak Invoice Resmi">
                                            📄 Invoice →
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="occ-empty">
                                    <span class="occ-empty-icon">🧾</span>
                                    <strong>Tidak ada riwayat transaksi servis</strong>
                                    <p>Saat servis difinalisasi oleh mekanik, tagihan transaksi akan langsung terdata di sini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>


    <!-- ========================================================================= -->
    <!-- ROLE 5: OVERVIEW & AGENDA GLOBAL SECTION                                  -->
    <!-- ========================================================================= -->
    <section id="section-overview" class="occ-section {{ $activeTab === 'overview' ? 'is-active' : '' }}">
        <div style="display:grid; grid-template-columns: minmax(0, 1.8fr) minmax(320px, 0.9fr); gap:20px;">
            <!-- Antrean Hari Ini -->
            <div class="occ-panel">
                <div class="occ-panel-header">
                    <div class="occ-panel-title-wrap">
                        <h2>⚡ Agenda & Antrean Servis Terkini</h2>
                        <p>Status pengerjaan aktual seluruh unit kendaraan yang sedang berada di bengkel.</p>
                    </div>
                </div>

                <div class="occ-table-responsive">
                    <table class="occ-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Customer</th>
                                <th>Kendaraan</th>
                                <th>Servis</th>
                                <th>Jam</th>
                                <th>Status</th>
                                <th style="text-align:right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($queue as $item)
                                @php($b = $item['booking'])
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $b->pelanggan?->nama_lengkap ?: '—' }}</strong>
                                        <small>{{ $b->kode_booking }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $b->kendaraan?->nomor_polisi ?: '—' }}</strong>
                                        <small>{{ $b->kendaraan?->merk }} {{ $b->kendaraan?->model }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $b->jenisServis?->nama_servis ?: 'General Service' }}</strong>
                                    </td>
                                    <td>{{ substr((string)$b->jam, 0, 5) }}</td>
                                    <td>
                                        <span class="occ-badge badge-{{ $item['tone'] === 'green' ? 'success' : ($item['tone'] === 'amber' ? 'warning' : ($item['tone'] === 'red' ? 'danger' : 'neutral')) }}">
                                            <span class="occ-badge-dot"></span>
                                            {{ $item['statusLabel'] }}
                                        </span>
                                    </td>
                                    <td style="text-align:right;">
                                        <a class="occ-btn occ-btn-sm occ-btn-outline" href="{{ $b->servis ? route('petugas.servis.show', $b->servis) : route('petugas.booking-servis.show', $b) }}">
                                            Buka →
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="occ-empty">
                                        <span class="occ-empty-icon">✓</span>
                                        <strong>Tidak ada antrean hari ini</strong>
                                        <p>Semua antrean servis telah diproses.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Panel Samping: Butuh Perhatian & Quick Navigation -->
            <div>
                <div class="occ-panel" style="margin-bottom:20px;">
                    <div class="occ-panel-header" style="margin-bottom:14px; padding-bottom:10px;">
                        <div class="occ-panel-title-wrap">
                            <h2>🚨 Butuh Perhatian Segera</h2>
                            <p>Item prioritas tinggi yang memerlukan tindakan.</p>
                        </div>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        @forelse($attention as $att)
                            <div style="display:flex; gap:12px; padding:10px 0; border-bottom:1px solid var(--occ-border);">
                                <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:{{ $att['tone'] === 'warning' ? 'var(--occ-amber)' : 'var(--occ-red)' }}; margin-top:5px; flex-shrink:0;"></span>
                                <div>
                                    <strong style="font-size:12.5px; display:block; color:var(--occ-dark);">{{ $att['label'] }}</strong>
                                    <span style="font-size:11.5px; color:var(--occ-text-muted);">{{ $att['detail'] }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="occ-empty" style="padding:20px 0;">
                                <p>Tidak ada isu kritis saat ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="occ-panel">
                    <div class="occ-panel-header" style="margin-bottom:14px; padding-bottom:10px;">
                        <div class="occ-panel-title-wrap">
                            <h2>⚡ Pintasan Operasional</h2>
                        </div>
                    </div>
                    <div style="display:grid; gap:8px;">
                        <button type="button" class="occ-btn occ-btn-outline" style="justify-content:space-between;" onclick="switchOccTab('advisor')">
                            <span>📋 Service Advisor</span>
                            <span>→</span>
                        </button>
                        <button type="button" class="occ-btn occ-btn-outline" style="justify-content:space-between;" onclick="switchOccTab('mechanic')">
                            <span>🔧 Work Order Mekanik</span>
                            <span>→</span>
                        </button>
                        <button type="button" class="occ-btn occ-btn-outline" style="justify-content:space-between;" onclick="switchOccTab('warehouse')">
                            <span>📦 Inventory & Stok Gudang</span>
                            <span>→</span>
                        </button>
                        <button type="button" class="occ-btn occ-btn-outline" style="justify-content:space-between;" onclick="switchOccTab('finance')">
                            <span>💳 Verifikasi Finance & Invoice</span>
                            <span>→</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ========================================================================= -->
    <!-- INTERACTIVE MODALS FOR ACTIONS                                            -->
    <!-- ========================================================================= -->

    <!-- Modal 1: Check-in Penerimaan Kendaraan (Service Advisor) -->
    <div id="modal-checkin" class="occ-modal-backdrop" onclick="if(event.target===this) closeCheckinModal()">
        <div class="occ-modal-card">
            <div class="occ-modal-header">
                <h3>🚗 Form Penerimaan Kendaraan (Check-in Fisik)</h3>
                <button type="button" class="occ-modal-close" onclick="closeCheckinModal()">✕</button>
            </div>
            <form id="form-checkin" method="POST" action="">
                @csrf
                <div style="background:#f8fafc; border:1px solid var(--occ-border); border-radius:12px; padding:12px 14px; margin-bottom:18px;">
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:var(--occ-text-muted);">Data Unit Kendaraan</div>
                    <div id="checkin-unit-title" style="font-size:14px; font-weight:800; color:var(--occ-dark); margin-top:2px;">—</div>
                    <div id="checkin-unit-sub" style="font-size:12px; color:var(--occ-text-muted); margin-top:2px;">—</div>
                </div>

                <div class="occ-form-group">
                    <label for="checkin-kilometer">Kilometer Kendaraan Saat Ini <span>*</span></label>
                    <input type="number" name="kilometer" id="checkin-kilometer" class="occ-form-control" min="0" required placeholder="Contoh: 45000">
                    <small style="color:var(--occ-text-muted); font-size:11px; margin-top:4px; display:block;">Masukkan angka odometer saat mobil diserahkan ke bengkel.</small>
                </div>

                <div class="occ-form-group">
                    <label for="checkin-catatan">Catatan Kondisi Fisik / Keluhan Tambahan</label>
                    <textarea name="catatan" id="checkin-catatan" class="occ-form-control" placeholder="Catat kondisi fisik kendaraan (baret bodi, barang berharga disimpan, bahan bakar, keluhan baru)..."></textarea>
                </div>

                <div class="occ-form-footer">
                    <button type="button" class="occ-btn occ-btn-outline" onclick="closeCheckinModal()">Batal</button>
                    <button type="submit" class="occ-btn occ-btn-primary">✓ Buka Work Order (Mulai Servis)</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Modal 2: Input Diagnosis & Inspeksi Mekanik -->
    <div id="modal-inspection" class="occ-modal-backdrop" onclick="if(event.target===this) closeInspectionModal()">
        <div class="occ-modal-card">
            <div class="occ-modal-header">
                <h3>🔍 Diagnosis & Hasil Pemeriksaan Mekanik</h3>
                <button type="button" class="occ-modal-close" onclick="closeInspectionModal()">✕</button>
            </div>
            <form id="form-inspection" method="POST" action="">
                @csrf
                <div style="margin-bottom:14px;">
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:var(--occ-text-muted);">Work Order</div>
                    <div id="inspection-wo-code" style="font-size:14px; font-weight:800; color:var(--occ-dark);">—</div>
                </div>

                <div class="occ-form-group">
                    <label for="inspection-hasil">Temuan Kerusakan & Hasil Diagnosis <span>*</span></label>
                    <textarea name="hasil_pemeriksaan" id="inspection-hasil" class="occ-form-control" required placeholder="Jelaskan komponen yang rusak, aus, atau membutuhkan servis berkala..."></textarea>
                </div>

                <div class="occ-form-group">
                    <label for="inspection-catatan">Rekomendasi / Catatan Teknisi</label>
                    <textarea name="catatan" id="inspection-catatan" class="occ-form-control" placeholder="Rekomendasi pengerjaan, estimasi waktu, atau catatan suku cadang..."></textarea>
                </div>

                <div class="occ-form-footer">
                    <button type="button" class="occ-btn occ-btn-outline" onclick="closeInspectionModal()">Batal</button>
                    <button type="submit" class="occ-btn occ-btn-dark">Simpan & Mulai Pengerjaan</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Modal 3: Tambah Jasa / Pekerjaan Servis -->
    <div id="modal-job" class="occ-modal-backdrop" onclick="if(event.target===this) closeJobModal()">
        <div class="occ-modal-card">
            <div class="occ-modal-header">
                <h3>🛠️ Tambah Detail Jasa Perbaikan</h3>
                <button type="button" class="occ-modal-close" onclick="closeJobModal()">✕</button>
            </div>
            <form id="form-job" method="POST" action="">
                @csrf
                <div style="margin-bottom:14px;">
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:var(--occ-text-muted);">Work Order</div>
                    <div id="job-wo-code" style="font-size:14px; font-weight:800; color:var(--occ-dark);">—</div>
                </div>

                <div class="occ-form-group">
                    <label for="job-jenis">Pilih Paket / Kategori Jasa Servis</label>
                    <select name="id_jenis_servis" id="job-jenis" class="occ-form-control" onchange="autoFillJobCost(this)">
                        <option value="">-- Jasa Kustom / Manual --</option>
                        @foreach($serviceTypes as $st)
                            <option value="{{ $st->id_jenis_servis }}" data-name="{{ $st->nama_servis }}" data-price="{{ (float)$st->estimasi_harga }}">
                                {{ $st->nama_servis }} (Rp {{ number_format($st->estimasi_harga, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="occ-form-group">
                    <label for="job-deskripsi">Deskripsi Pekerjaan <span>*</span></label>
                    <input type="text" name="deskripsi_pekerjaan" id="job-deskripsi" class="occ-form-control" required placeholder="Contoh: Kuras Oli Mesin & Filter, Ganti Kampas Rem Depan">
                </div>

                <div class="occ-form-group">
                    <label for="job-biaya">Biaya Jasa (Rp) <span>*</span></label>
                    <input type="number" name="biaya" id="job-biaya" class="occ-form-control" min="0" required placeholder="Contoh: 150000">
                </div>

                <div class="occ-form-group">
                    <label for="job-catatan">Catatan Tambahan</label>
                    <input type="text" name="catatan" id="job-catatan" class="occ-form-control" placeholder="Opsional">
                </div>

                <div class="occ-form-footer">
                    <button type="button" class="occ-btn occ-btn-outline" onclick="closeJobModal()">Batal</button>
                    <button type="submit" class="occ-btn occ-btn-primary">✓ Simpan Jasa Servis</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Modal 4: Tambah Spare Part ke WO (Mekanik) -->
    <div id="modal-part" class="occ-modal-backdrop" onclick="if(event.target===this) closePartModal()">
        <div class="occ-modal-card">
            <div class="occ-modal-header">
                <h3>⚙️ Alokasikan Spare Part ke Work Order</h3>
                <button type="button" class="occ-modal-close" onclick="closePartModal()">✕</button>
            </div>
            <form id="form-part" method="POST" action="">
                @csrf
                <div style="margin-bottom:14px;">
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:var(--occ-text-muted);">Work Order</div>
                    <div id="part-wo-code" style="font-size:14px; font-weight:800; color:var(--occ-dark);">—</div>
                </div>

                <div class="occ-form-group">
                    <label for="part-sparepart">Pilih Spare Part Tersedia <span>*</span></label>
                    <select name="id_sparepart" id="part-sparepart" class="occ-form-control" required onchange="updatePartMax(this)">
                        <option value="">-- Pilih Suku Cadang dari Gudang --</option>
                        @foreach($availableParts as $ap)
                            <option value="{{ $ap->id_sparepart }}" data-stock="{{ $ap->stok }}" data-price="{{ (float)$ap->harga_jual }}">
                                {{ $ap->nama_sparepart }} (Stok: {{ $ap->stok }} {{ $ap->satuan }}) - Rp {{ number_format($ap->harga_jual, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="occ-form-group">
                    <label for="part-jumlah">Jumlah (Qty) <span>*</span></label>
                    <input type="number" name="jumlah" id="part-jumlah" class="occ-form-control" min="1" value="1" required>
                    <small id="part-stock-hint" style="color:var(--occ-text-muted); font-size:11px; margin-top:4px; display:block;">Pilih spare part untuk melihat batas stok tersedia.</small>
                </div>

                <div class="occ-form-footer">
                    <button type="button" class="occ-btn occ-btn-outline" onclick="closePartModal()">Batal</button>
                    <button type="submit" class="occ-btn occ-btn-primary">✓ Tambahkan ke WO</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Modal 5: Penyesuaian Stok Gudang (Staf Gudang) -->
    <div id="modal-adjustment" class="occ-modal-backdrop" onclick="if(event.target===this) closeAdjustmentModal()">
        <div class="occ-modal-card">
            <div class="occ-modal-header">
                <h3>📦 Penyesuaian Stok Spare Part (Stock Adjustment)</h3>
                <button type="button" class="occ-modal-close" onclick="closeAdjustmentModal()">✕</button>
            </div>
            <form method="POST" action="{{ route('petugas.inventory.adjust') }}">
                @csrf
                <div class="occ-form-group">
                    <label for="adj-sparepart">Pilih Spare Part <span>*</span></label>
                    <select name="id_sparepart" id="adj-sparepart" class="occ-form-control" required onchange="onAdjPartSelected(this)">
                        <option value="">-- Pilih Spare Part --</option>
                        @foreach($warehouseParts as $wp)
                            <option value="{{ $wp->id_sparepart }}" data-name="{{ $wp->nama_sparepart }}" data-stock="{{ $wp->stok }}" data-satuan="{{ $wp->satuan }}">
                                {{ $wp->nama_sparepart }} (Saat ini: {{ $wp->stok }} {{ $wp->satuan }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="background:#f8fafc; border:1px solid var(--occ-border); border-radius:10px; padding:10px 14px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:12px; color:var(--occ-text-muted);">Stok Terdata Saat Ini:</span>
                    <strong id="adj-current-stock" style="font-size:14px; color:var(--occ-dark);">—</strong>
                </div>

                <div class="occ-form-group">
                    <label for="adj-stok-baru">Stok Fisik Baru (Hasil Opname) <span>*</span></label>
                    <input type="number" name="stok_baru" id="adj-stok-baru" class="occ-form-control" min="0" required placeholder="Masukkan jumlah stok riil fisik">
                </div>

                <div class="occ-form-group">
                    <label for="adj-alasan">Alasan Penyesuaian <span>*</span></label>
                    <select name="alasan" id="adj-alasan" class="occ-form-control" required>
                        <option value="Opname Stok Fisik Rutin">Opname Stok Fisik Rutin</option>
                        <option value="Koreksi Selisih Pencatatan">Koreksi Selisih Pencatatan</option>
                        <option value="Barang Rusak / Cacat Pabrik">Barang Rusak / Cacat Pabrik</option>
                        <option value="Barang Hilang / Selisih Gudang">Barang Hilang / Selisih Gudang</option>
                        <option value="Retur ke Supplier">Retur ke Supplier</option>
                        <option value="Penyesuaian Lainnya">Penyesuaian Lainnya</option>
                    </select>
                </div>

                <div class="occ-form-group">
                    <label for="adj-catatan">Keterangan / Berita Acara Opname <span>*</span></label>
                    <textarea name="catatan" id="adj-catatan" class="occ-form-control" required placeholder="Tuliskan keterangan detail opname atau nomor bukti fisik..."></textarea>
                </div>

                <div class="occ-form-footer">
                    <button type="button" class="occ-btn occ-btn-outline" onclick="closeAdjustmentModal()">Batal</button>
                    <button type="submit" class="occ-btn occ-btn-primary">✓ Simpan Penyesuaian Stok</button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Client-side Interactive Script -->
@push('scripts')
<script>
    // Tab Switcher
    function switchOccTab(tabId) {
        document.querySelectorAll('.occ-tab-btn').forEach(btn => {
            btn.classList.toggle('is-active', btn.dataset.tab === tabId);
        });
        document.querySelectorAll('.occ-section').forEach(sec => {
            sec.classList.toggle('is-active', sec.id === 'section-' + tabId);
        });

        // Update URL query string without reloading page
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);
    }

    // Table quick search filter
    function filterTable(inputId, tableId) {
        const input = document.getElementById(inputId);
        const filter = input.value.toLowerCase();
        const table = document.getElementById(tableId);
        if (!table) return;
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;
            for (let j = 0; j < cells.length; j++) {
                if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
            rows[i].style.display = found ? '' : 'none';
        }
    }

    // Modal 1: Check-in Penerimaan Kendaraan
    function openCheckinModal(bookingId, code, customer, vehicle, nopol, km) {
        const modal = document.getElementById('modal-checkin');
        const form = document.getElementById('form-checkin');
        form.action = "{{ url('petugas/servis/from-booking') }}/" + bookingId;

        document.getElementById('checkin-unit-title').textContent = nopol + ' · ' + vehicle;
        document.getElementById('checkin-unit-sub').textContent = 'Booking ' + code + ' · Customer: ' + customer;
        const kmInput = document.getElementById('checkin-kilometer');
        kmInput.value = km || '';
        kmInput.min = km || 0;

        modal.classList.add('is-open');
    }
    function closeCheckinModal() {
        document.getElementById('modal-checkin').classList.remove('is-open');
    }

    // Modal 2: Diagnosis & Pemeriksaan
    function openInspectionModal(servisId, code, currentDiagnosis, currentNote) {
        const modal = document.getElementById('modal-inspection');
        const form = document.getElementById('form-inspection');
        form.action = "{{ url('petugas/servis') }}/" + servisId + "/inspection";

        document.getElementById('inspection-wo-code').textContent = code;
        document.getElementById('inspection-hasil').value = currentDiagnosis || '';
        document.getElementById('inspection-catatan').value = currentNote || '';

        modal.classList.add('is-open');
    }
    function closeInspectionModal() {
        document.getElementById('modal-inspection').classList.remove('is-open');
    }

    // Modal 3: Tambah Jasa Servis
    function openJobModal(servisId, code) {
        const modal = document.getElementById('modal-job');
        const form = document.getElementById('form-job');
        form.action = "{{ url('petugas/servis') }}/" + servisId + "/detail";

        document.getElementById('job-wo-code').textContent = code;
        document.getElementById('job-deskripsi').value = '';
        document.getElementById('job-biaya').value = '';
        document.getElementById('job-jenis').value = '';
        document.getElementById('job-catatan').value = '';

        modal.classList.add('is-open');
    }
    function closeJobModal() {
        document.getElementById('modal-job').classList.remove('is-open');
    }
    function autoFillJobCost(select) {
        const selected = select.options[select.selectedIndex];
        if (selected.value) {
            document.getElementById('job-deskripsi').value = selected.dataset.name || '';
            document.getElementById('job-biaya').value = selected.dataset.price || '';
        }
    }

    // Modal 4: Tambah Spare Part
    function openPartModal(servisId, code) {
        const modal = document.getElementById('modal-part');
        const form = document.getElementById('form-part');
        form.action = "{{ url('petugas/servis') }}/" + servisId + "/sparepart";

        document.getElementById('part-wo-code').textContent = code;
        document.getElementById('part-sparepart').value = '';
        document.getElementById('part-jumlah').value = 1;
        document.getElementById('part-stock-hint').textContent = 'Pilih spare part untuk melihat batas stok tersedia.';

        modal.classList.add('is-open');
    }
    function closePartModal() {
        document.getElementById('modal-part').classList.remove('is-open');
    }
    function updatePartMax(select) {
        const opt = select.options[select.selectedIndex];
        const stock = parseInt(opt.dataset.stock) || 0;
        const qty = document.getElementById('part-jumlah');
        qty.max = stock;
        document.getElementById('part-stock-hint').textContent = 'Maksimal stok yang dapat dialokasikan: ' + stock;
    }

    // Modal 5: Penyesuaian Stok Gudang
    function openAdjustmentModal(partId, partName, currentStock, minStock) {
        const modal = document.getElementById('modal-adjustment');
        const select = document.getElementById('adj-sparepart');

        if (partId) {
            select.value = partId;
            document.getElementById('adj-current-stock').textContent = currentStock + ' Unit (Min: ' + minStock + ')';
            document.getElementById('adj-stok-baru').value = currentStock;
        } else {
            select.value = '';
            document.getElementById('adj-current-stock').textContent = 'Pilih suku cadang di atas';
            document.getElementById('adj-stok-baru').value = '';
        }
        document.getElementById('adj-catatan').value = '';

        modal.classList.add('is-open');
    }
    function closeAdjustmentModal() {
        document.getElementById('modal-adjustment').classList.remove('is-open');
    }
    function onAdjPartSelected(select) {
        const opt = select.options[select.selectedIndex];
        if (opt.value) {
            const stock = opt.dataset.stock || 0;
            const satuan = opt.dataset.satuan || 'Unit';
            document.getElementById('adj-current-stock').textContent = stock + ' ' + satuan;
            document.getElementById('adj-stok-baru').value = stock;
        } else {
            document.getElementById('adj-current-stock').textContent = 'Pilih suku cadang di atas';
            document.getElementById('adj-stok-baru').value = '';
        }
    }

    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCheckinModal();
            closeInspectionModal();
            closeJobModal();
            closePartModal();
            closeAdjustmentModal();
        }
    });
</script>
@endpush
@endsection
