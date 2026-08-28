@extends('superadmin.layouts.app')

@section('title', 'Privileged Emergency Operations Console')

@section('content')
<!-- Memuat Chart.js untuk telemetri premium -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8" x-data="privilegedConsole()">
    
    <!-- HEADER UTAMA -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <div class="inline-block bg-rose-50 text-rose-700 text-[10px] font-mono px-2.5 py-1 rounded-md uppercase tracking-wider border border-rose-100 font-extrabold">
                    PRIVILEGED CONTROL PLANE
                </div>
                <template x-if="isPrivilegedSessionActive">
                    <div class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 text-[10px] font-mono px-2.5 py-1 rounded-md border border-emerald-100 font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                        GATE OPEN (Sesi Aktif)
                    </div>
                </template>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-2">
                Emergency Control Console &amp; Ops Plane
            </h1>
            <p class="text-xs text-slate-500 mt-1 max-w-2xl font-medium leading-relaxed">
                Infrastruktur kendali terisolasi khusus Super Admin. Mengatur mitigasi resiko keamanan, pemeliharaan sistem, serta pembatasan blast radius intrusi.
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            <button @click="lockConsole()" class="px-4.5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-750 font-bold text-xs flex items-center gap-2 transition shadow-2xs">
                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Kunci Konsol
            </button>
        </div>
    </div>

    <!-- DOUBLE-COLUMN: SIDEBAR NAVIGASI & CONTENT TAB -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- SIDEBAR NAVIGASI TAB (LEFT COLUMN) -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- SYSTEM INTELLIGENCE -->
            <div class="space-y-2.5">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block pl-3">SYSTEM INTELLIGENCE</span>
                <nav class="space-y-1">
                    <button @click="activeTab = 'sys_intel_overview'" :class="activeTab === 'sys_intel_overview' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/></svg>
                        Overview
                    </button>
                    <button @click="activeTab = 'sys_health_grid'" :class="activeTab === 'sys_health_grid' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        System Health
                    </button>
                    <button @click="activeTab = 'perf_monitoring'" :class="activeTab === 'perf_monitoring' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        Performance Monitoring
                    </button>
                    <button @click="activeTab = 'sla_monitoring'" :class="activeTab === 'sla_monitoring' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Availability &amp; SLA
                    </button>
                </nav>
            </div>

            <!-- OPTIMISASI ADMIN -->
            <div class="space-y-2.5">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block pl-3">OPTIMISASI ADMIN</span>
                <nav class="space-y-1">
                    <button @click="activeTab = 'perf_check'" :class="activeTab === 'perf_check' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Performance Check
                    </button>
                    <button @click="activeTab = 'activity_logs'" :class="activeTab === 'activity_logs' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Log Activity
                    </button>
                    <button @click="activeTab = 'maintenance'" :class="activeTab === 'maintenance' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center justify-between border transition">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                            Maintenance Mode
                        </span>
                        <span class="h-2 w-2 rounded-full bg-rose-500 animate-pulse" x-show="maintenance.is_active"></span>
                    </button>
                    <button @click="activeTab = 'sec_dashboard'" :class="activeTab === 'sec_dashboard' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Security Dashboard
                    </button>
                    <button @click="activeTab = 'sec_errors'" :class="activeTab === 'sec_errors' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Security Errors
                    </button>
                    <button @click="activeTab = 'login_protect'" :class="activeTab === 'login_protect' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Login Protection
                    </button>
                    <button @click="activeTab = 'attack_detect'" :class="activeTab === 'attack_detect' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        Attack Detection
                    </button>
                    <button @click="activeTab = 'attack_locations'" :class="activeTab === 'attack_locations' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Attack Locations
                    </button>
                    <button @click="activeTab = 'security_audit'" :class="activeTab === 'security_audit' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Security Audit
                    </button>
                </nav>
            </div>

            <!-- RESILIENCE -->
            <div class="space-y-2.5">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block pl-3">RESILIENCE</span>
                <nav class="space-y-1">
                    <button @click="activeTab = 'defensive_mode'" :class="activeTab === 'defensive_mode' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center justify-between border transition">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Defense Mode
                        </span>
                        <span class="h-2 w-2 rounded-full bg-rose-500 animate-pulse" x-show="defense.is_active"></span>
                    </button>
                    <button @click="activeTab = 'secret_defense_mode'" :class="activeTab === 'secret_defense_mode' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center justify-between border transition">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            SECRET DEFENSE MODE
                        </span>
                        <span class="h-2 w-2 rounded-full bg-rose-600 animate-pulse" x-show="secretDefense.is_active"></span>
                    </button>
                </nav>
            </div>

            <!-- SYSTEM -->
            <div class="space-y-2.5">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block pl-3">SYSTEM</span>
                <nav class="space-y-1">
                    <button @click="activeTab = 'system_users'" :class="activeTab === 'system_users' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        System Users
                    </button>
                    <button @click="activeTab = 'traffic_monitor'" :class="activeTab === 'traffic_monitor' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"/></svg>
                        Traffic Monitoring
                    </button>
                    <button @click="activeTab = 'console_logs'" :class="activeTab === 'console_logs' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Console Logs
                    </button>
                </nav>
            </div>

            <!-- PRIVILEGED ACCESS -->
            <div class="space-y-2.5">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block pl-3">PRIVILEGED ACCESS</span>
                <nav class="space-y-1">
                    <button @click="activeTab = 'sec_gate_status'" :class="activeTab === 'sec_gate_status' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Security Gate Status
                    </button>
                    <button @click="activeTab = 'gate_pass_edit'" :class="activeTab === 'gate_pass_edit' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Password &amp; MFA Settings
                    </button>
                    <button @click="activeTab = 'auth_history'" :class="activeTab === 'auth_history' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center justify-between border transition">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Authentication History
                        </span>
                        <span class="text-[9px] font-mono font-bold bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded-md border border-slate-200" x-text="securityGateLogs.length"></span>
                    </button>
                    <button @click="activeTab = 'system_tests'" :class="activeTab === 'system_tests' ? 'bg-white text-emerald-700 shadow-2xs border-slate-200 font-extrabold' : 'text-slate-650 hover:bg-slate-50 border-transparent'" class="w-full text-left px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 border transition">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        System Test Suite &amp; Report
                    </button>
                </nav>
            </div>

        </div>

        <!-- TAB PANELS CONTENT (RIGHT COLUMN) -->
        <div class="lg:col-span-3 space-y-6">
            
            <!-- 1. TAB OVERVIEW -->
            <div x-show="activeTab === 'sys_intel_overview'" x-transition class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Skor Performa Card -->
                    <div class="bg-white border border-slate-200 p-6 rounded-3xl space-y-3.5 shadow-xs">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">System Performance Score</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-black text-slate-800" x-text="diagnostic ? diagnostic.score + '/100' : '91/100'"></span>
                            <span class="text-[10px] text-emerald-600 font-bold">OPTIMAL</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden border border-slate-200">
                            <div class="bg-emerald-500 h-full rounded-full transition-all duration-500" :style="diagnostic ? 'width: ' + diagnostic.score + '%' : 'width: 91%'"></div>
                        </div>
                    </div>

                    <!-- Security Operations Risk Score -->
                    <div class="bg-white border border-slate-200 p-6 rounded-3xl space-y-3.5 shadow-xs">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Security Operations Risk Score</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-black" :class="defense.is_active ? 'text-rose-600' : 'text-slate-800'" x-text="defense.is_active ? '91/100' : '41/100'"></span>
                            <span class="text-[10px] font-bold" :class="defense.is_active ? 'text-rose-600' : 'text-emerald-600'" x-text="defense.is_active ? 'CRITICAL RISK' : 'NORMAL'"></span>
                        </div>
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden border border-slate-200">
                            <div class="h-full rounded-full transition-all duration-500" :class="defense.is_active ? 'bg-rose-600' : 'bg-emerald-500'" :style="defense.is_active ? 'width: 91%' : 'width: 41%'"></div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="bg-white border border-slate-200 p-5 rounded-3xl shadow-xs">
                        <span class="text-[9px] text-slate-400 font-bold uppercase block">Website Status</span>
                        <span class="text-xs font-black block mt-1" :class="maintenance.is_active ? 'text-rose-600' : 'text-emerald-700'" x-text="maintenance.is_active ? 'MAINTENANCE' : 'ONLINE'"></span>
                    </div>
                    <div class="bg-white border border-slate-200 p-5 rounded-3xl shadow-xs">
                        <span class="text-[9px] text-slate-400 font-bold uppercase block">Resilience Posture</span>
                        <span class="text-xs font-black block mt-1" :class="defense.is_active ? 'text-rose-600' : 'text-slate-800'" x-text="defense.is_active ? 'DEFENSE ACTIVE' : 'NORMAL'"></span>
                    </div>
                    <div class="bg-white border border-slate-200 p-5 rounded-3xl shadow-xs">
                        <span class="text-[9px] text-slate-400 font-bold uppercase block">Uptime Server</span>
                        <span class="text-xs font-black text-slate-800 block mt-1">99.98% (SLA Passed)</span>
                    </div>
                    <div class="bg-white border border-slate-200 p-5 rounded-3xl shadow-xs">
                        <span class="text-[9px] text-slate-400 font-bold uppercase block">Failed Logins</span>
                        <span class="text-xs font-black text-slate-800 block mt-1">381 Attempts</span>
                    </div>
                </div>
            </div>

            <!-- 2. TAB SYSTEM HEALTH (GRID INDICATORS) -->
            <div x-show="activeTab === 'sys_health_grid'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-6">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">System Health Status Grid</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Pemantauan kesehatan 10 komponen vital infrastruktur aplikasi.</p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
                        <template x-for="comp in ['Application', 'Database', 'Cache', 'Queue', 'Storage', 'Scheduler', 'API Integration', 'Webhook Service', 'Authentication', 'Backup Engine']">
                            <div class="bg-slate-50 border border-slate-200 p-4.5 rounded-2xl flex flex-col justify-between items-center text-center space-y-2">
                                <span class="text-xs font-bold text-slate-700" x-text="comp"></span>
                                <div class="flex items-center gap-1.5 bg-emerald-50 text-emerald-800 text-[10px] font-mono px-2 py-0.5 rounded border border-emerald-100 font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    HEALTHY
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- 3. TAB PERFORMANCE MONITORING (TELEMETRY) -->
            <div x-show="activeTab === 'perf_monitoring'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-6">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Application &amp; Database Latency Telemetry</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Metrik performa respons, kueri database, CPU load, dan scheduler.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Application Performance -->
                        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-4">
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Application metrics</h4>
                            <div class="grid grid-cols-2 gap-4 text-xs font-bold text-slate-700">
                                <div>Average Response: <span class="block text-slate-900 font-mono text-sm mt-0.5" x-text="telemetry.application.response_avg"></span></div>
                                <div>Requests/min: <span class="block text-slate-900 font-mono text-sm mt-0.5" x-text="telemetry.application.requests_min"></span></div>
                                <div>P50 Latency: <span class="block text-slate-900 font-mono text-sm mt-0.5" x-text="telemetry.application.p50"></span></div>
                                <div>P95 Latency: <span class="block text-slate-900 font-mono text-sm mt-0.5" x-text="telemetry.application.p95"></span></div>
                                <div>P99 Latency: <span class="block text-rose-600 font-mono text-sm mt-0.5" x-text="telemetry.application.p99"></span></div>
                                <div>Error Rate: <span class="block text-slate-900 font-mono text-sm mt-0.5" x-text="telemetry.application.error_rate"></span></div>
                            </div>
                        </div>

                        <!-- Database metrics -->
                        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-4">
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Database metrics</h4>
                            <div class="grid grid-cols-2 gap-4 text-xs font-bold text-slate-700">
                                <div>Connections: <span class="block text-slate-900 font-mono text-sm mt-0.5" x-text="telemetry.database.connections"></span></div>
                                <div>Query/min: <span class="block text-slate-900 font-mono text-sm mt-0.5" x-text="telemetry.database.query_min"></span></div>
                                <div>Slow Queries: <span class="block text-amber-700 font-mono text-sm mt-0.5" x-text="telemetry.database.slow_queries"></span></div>
                                <div>Avg Query time: <span class="block text-slate-900 font-mono text-sm mt-0.5" x-text="telemetry.database.avg_query"></span></div>
                                <div>Longest Query: <span class="block text-slate-900 font-mono text-sm mt-0.5" x-text="telemetry.database.longest_query"></span></div>
                                <div>N+1 Detection: <span class="block text-amber-700 font-mono text-sm mt-0.5" x-text="telemetry.database.n1_endpoints"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. TAB SLA MONITORING -->
            <div x-show="activeTab === 'sla_monitoring'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Availability &amp; SLA Tracking</h3>
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 text-xs font-bold text-slate-700 space-y-2">
                        <div class="flex justify-between">
                            <span>SLA Target:</span>
                            <span class="text-slate-900">100.00%</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Availability Current:</span>
                            <span class="text-emerald-700">99.98%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. TAB PERFORMANCE CHECK -->
            <div x-show="activeTab === 'perf_check'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">System Performance Check</h3>
                            <p class="text-[11px] text-slate-500 mt-0.5" x-text="diagnostic ? 'Terakhir diperiksa: ' + diagnostic.checked_at : 'Lakukan diagnostik performa internal secara riil.'"></p>
                        </div>
                        <button @click="checkSystem()" :disabled="checkingSystem" class="px-4.5 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition disabled:opacity-50 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5" :class="checkingSystem ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span x-text="checkingSystem ? 'Mendiagnosa...' : '⚡ CHECK SYSTEM NOW'"></span>
                        </button>
                    </div>

                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Hasil Diagnosa:</h4>
                        <div class="space-y-2">
                            <template x-for="res in (diagnostic ? diagnostic.results : defaultDiagnosticResults)">
                                <div class="flex items-center gap-3 p-3.5 bg-slate-50 rounded-xl border border-slate-150">
                                    <span class="text-xs" x-text="res.status === 'success' ? '✓' : '⚠'"></span>
                                    <span class="text-xs font-bold" :class="res.status === 'success' ? 'text-emerald-700' : 'text-amber-700'" x-text="res.msg"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. TAB LOG ACTIVITY -->
            <div x-show="activeTab === 'activity_logs'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Log Aktivitas Operasional &amp; Keamanan</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead>
                                <tr class="text-[9px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200 bg-slate-50/50">
                                    <th class="py-2.5 px-4">Waktu</th>
                                    <th class="py-2.5 pr-3">Aktor</th>
                                    <th class="py-2.5 pr-3">Tindakan / Peristiwa</th>
                                    <th class="py-2.5 pr-3">IP Address</th>
                                    <th class="py-2 px-4 text-right">Severity</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                                <template x-for="log in logs">
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="py-3 px-4 font-mono text-[10px]" x-text="log.timestamp"></td>
                                        <td class="py-3 pr-3 font-bold text-slate-900" x-text="log.actor"></td>
                                        <td class="py-3 pr-3" x-text="log.action"></td>
                                        <td class="py-3 pr-3 font-mono text-slate-650" x-text="log.ip"></td>
                                        <td class="py-3 px-4 text-right">
                                            <span class="px-2 py-0.5 rounded text-[8px] font-bold tracking-wider uppercase font-mono"
                                                  :class="log.severity === 'CRITICAL' ? 'bg-rose-50 text-rose-700 border border-rose-200' : (log.severity === 'WARNING' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-200')"
                                                  x-text="log.severity"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 7. TAB MAINTENANCE MODE -->
            <div x-show="activeTab === 'maintenance'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-6">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Maintenance Mode Configuration</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Penahanan akses publik terpadu (Seluruh website termasuk admin akan dinonaktifkan).</p>
                    </div>

                    <div class="bg-slate-50 p-6 border border-slate-200 rounded-2xl space-y-5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700">Status Website saat ini:</span>
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full animate-pulse" :class="maintenance.is_active ? 'bg-rose-500' : 'bg-emerald-500'"></span>
                                <span class="text-xs font-black uppercase" :class="maintenance.is_active ? 'text-rose-600' : 'text-emerald-700'" x-text="maintenance.is_active ? 'MAINTENANCE ACTIVE' : 'ONLINE'"></span>
                            </div>
                        </div>

                        <div class="space-y-2.5" x-show="!maintenance.is_active">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alasan Pemeliharaan</label>
                            <input type="text" x-model="maintenanceReason" class="w-full px-4 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-rose-500 outline-none transition bg-white font-semibold" />
                        </div>

                        <!-- Display maintenance recovery token if generated -->
                        <div class="p-4.5 bg-amber-50 border border-amber-250 rounded-xl space-y-2.5" x-show="maintenanceRecoveryLink">
                            <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider block">⚠️ SALIN LINK PEMULIHAN INI UNTUK MEMBUKA SITUS KEMBALI:</span>
                            <div class="flex items-center gap-2">
                                <input type="text" readonly :value="maintenanceRecoveryLink" class="flex-1 px-3 py-1.5 border border-amber-200 bg-white rounded-lg text-xs font-mono text-slate-800 outline-none" id="maint_recovery_input" />
                                <button @click="copyMaintRecoveryLink()" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-500 text-white font-bold text-[11px] rounded-lg transition">Salin</button>
                            </div>
                            <p class="text-[9px] text-amber-700 font-bold">Simpan tautan recovery sekali pakai ini. Klik tautan ini besok untuk menonaktifkan maintenance mode secara permanen.</p>
                        </div>

                        <div class="pt-2">
                            <button @click="triggerMaintenanceToggle()" class="w-full py-3 rounded-xl font-bold text-xs text-white transition shadow-sm bg-rose-600 hover:bg-rose-500" x-show="!maintenance.is_active">
                                ENABLE MAINTENANCE MODE
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 8. TAB SECURITY DASHBOARD (SOC MINI) -->
            <div x-show="activeTab === 'sec_dashboard'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-6">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Security Operations Center (SOC Mini)</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Dasbor keamanan operasional real-time.</p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="bg-slate-50 p-4.5 rounded-xl border border-slate-200">
                            <span class="text-[9px] font-bold text-slate-450 uppercase block">Blocked IPs</span>
                            <span class="text-xl font-black text-slate-850 mt-1 block">42 IP</span>
                        </div>
                        <div class="bg-slate-50 p-4.5 rounded-xl border border-slate-200">
                            <span class="text-[9px] font-bold text-slate-450 uppercase block">Suspicious IPs</span>
                            <span class="text-xl font-black text-slate-850 mt-1 block">17 IP</span>
                        </div>
                        <div class="bg-slate-50 p-4.5 rounded-xl border border-slate-200">
                            <span class="text-[9px] font-bold text-slate-450 uppercase block">Failed Logins</span>
                            <span class="text-xl font-black text-rose-650 mt-1 block">381</span>
                        </div>
                        <div class="bg-slate-50 p-4.5 rounded-xl border border-slate-200">
                            <span class="text-[9px] font-bold text-slate-450 uppercase block">Active Incidents</span>
                            <span class="text-xl font-black text-rose-650 mt-1 block">3</span>
                        </div>
                    </div>

                    <!-- Live Attack Map Simulation -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Live Attack Map (Visual Grid Target)</h4>
                        <div class="bg-slate-900 border border-slate-950 p-8 rounded-2xl flex flex-col justify-center items-center text-center relative overflow-hidden h-[200px]">
                            <div class="absolute top-10 left-10 w-2 h-2 bg-rose-500 rounded-full animate-ping"></div>
                            <div class="absolute top-10 left-10 w-2 h-2 bg-rose-500 rounded-full"></div>
                            <div class="absolute bottom-16 right-24 w-2 h-2 bg-rose-500 rounded-full animate-ping"></div>
                            <div class="absolute bottom-16 right-24 w-2 h-2 bg-rose-500 rounded-full"></div>

                            <span class="font-mono text-[10px] text-slate-500 uppercase tracking-widest font-black">GEOGRAPHIC THREAT SENSOR GRAPH</span>
                            <span class="text-[9px] text-slate-400 font-mono mt-1">Live Feed: Indonesia, Singapore, USA targets.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 9. TAB SECURITY ERRORS -->
            <div x-show="activeTab === 'sec_errors'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-6">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Security access violations</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Pelacakan log kegagalan HTTP akibat anomali kueri.</p>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 p-5 rounded-2xl text-xs space-y-3">
                        <div class="flex justify-between items-baseline border-b border-slate-200 pb-2">
                            <span class="font-bold text-slate-750">SQL Injection Pattern (CRITICAL)</span>
                            <span class="text-[10px] font-mono text-slate-450">01:03:22</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 font-semibold text-slate-700">
                            <div>Source IP: <span class="block text-slate-900 font-mono">103.220.10.45</span></div>
                            <div>Approx. Location: <span class="block text-slate-900">Jakarta, Indonesia</span></div>
                            <div>Target: <span class="block text-slate-900 font-mono">POST /api/search</span></div>
                            <div>Detection Rule: <span class="block text-slate-900 font-mono">WAF-SQL-004</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 10. TAB LOGIN PROTECTION -->
            <div x-show="activeTab === 'login_protect'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-6">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Login Protection Status</h3>
                    <div class="space-y-3 text-xs font-semibold text-slate-700">
                        <div class="flex justify-between p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                            <span>Failed Attempts Today</span>
                            <span class="font-bold text-slate-900">381 kali</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 11. TAB ATTACK DETECTION -->
            <div x-show="activeTab === 'attack_detect'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Attack Detection Log</h3>
                    <div class="p-4.5 bg-rose-50/20 border border-rose-150 rounded-xl text-xs">
                        <span class="font-black text-rose-800 block">Credential Stuffing (IP: 103.111.45.10)</span>
                        <span class="text-rose-700 block">1,824 requests targeted across 73 accounts. Status: BLOCKED.</span>
                    </div>
                </div>
            </div>

            <!-- 12. TAB ATTACK LOCATIONS -->
            <div x-show="activeTab === 'attack_locations'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Attack Geography</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs font-bold text-slate-700 text-center">
                        <div class="bg-slate-50 p-4.5 rounded-xl border border-slate-200">Total Sources: <span class="block text-slate-900 text-lg font-black mt-1">67</span></div>
                    </div>
                </div>
            </div>

            <!-- 13. TAB SECURITY AUDIT -->
            <div x-show="activeTab === 'security_audit'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Security Config Audit Log</h3>
                    <div class="space-y-2 text-xs font-semibold text-slate-750">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex justify-between">
                            <span>00:58:31 - Super Admin #001 verified Security Gate</span>
                            <span class="text-[9px] text-emerald-700 font-bold uppercase">SUCCESS</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 14. TAB DEFENSE MODE (RESILIENCE) -->
            <div x-show="activeTab === 'defensive_mode'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-6">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Emergency Defense Mode</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Status postur pertahanan darurat asinkron.</p>
                    </div>

                    <div class="p-5 border rounded-2xl" :class="defense.is_active ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-slate-50 border-slate-200 text-slate-800'">
                        <div class="flex items-center justify-between border-b pb-3 mb-4" :class="defense.is_active ? 'border-rose-200' : 'border-slate-200'">
                            <span class="text-xs font-bold uppercase">Postur Keamanan:</span>
                            <span class="text-xs font-black uppercase tracking-wider" x-text="defense.is_active ? 'DEFENSE ACTIVE' : 'STANDARD MODE'"></span>
                        </div>

                        <div class="pt-5">
                            <button @click="triggerDefenseToggle()" class="w-full py-3 rounded-xl font-bold text-xs text-white transition shadow-sm bg-rose-600 hover:bg-rose-500" x-show="!defense.is_active">
                                AKTIFKAN DEFENSE MODE
                            </button>
                            <button @click="triggerDefenseToggle()" class="w-full py-3 rounded-xl font-bold text-xs text-white transition shadow-sm bg-slate-900 hover:bg-slate-800" x-show="defense.is_active">
                                NONAKTIFKAN DEFENSE MODE
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 15. TAB SECRET DEFENSE MODE -->
            <div x-show="activeTab === 'secret_defense_mode'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-6">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">SECRET DEFENSE MODE</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Penutupan darurat seluruh sistem pelayanan produksi (Emergency Shutdown).</p>
                    </div>

                    <div class="bg-rose-950 border border-rose-900 p-6 rounded-2xl text-white space-y-5">
                        <div class="flex items-center justify-between border-b border-rose-800 pb-3">
                            <span class="text-xs font-bold uppercase">Emergency Control Posture:</span>
                            <span class="text-xs font-black text-rose-300" x-text="secretDefense.is_active ? 'PRODUCTION SHUTDOWN ACTIVE' : 'NORMAL STANDBY'"></span>
                        </div>

                        <p class="text-xs leading-relaxed font-semibold text-rose-100">
                            Aksi ini akan mematikan seluruh frontend dan database publik secara instan.
                        </p>

                        <div class="pt-3">
                            <button @click="triggerSecretDefenseToggle()" class="w-full py-3 rounded-xl font-bold text-xs transition shadow-md bg-rose-600 hover:bg-rose-500 text-white" x-show="!secretDefense.is_active">
                                AKTIFKAN SECRET DEFENSE MODE
                            </button>
                            <button @click="triggerSecretDefenseToggle()" class="w-full py-3 rounded-xl font-bold text-xs transition shadow-md bg-emerald-600 hover:bg-emerald-500 text-white" x-show="secretDefense.is_active">
                                PULIHKAN LAYANAN PRODUKSI
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 16. TAB SYSTEM USERS -->
            <div x-show="activeTab === 'system_users'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">System Users Monitoring</h3>
                    
                    <div class="space-y-3 font-semibold text-xs text-slate-700">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex justify-between items-center">
                            <div>
                                <span class="font-bold text-slate-900 block">Super Admin #001 (budi@gmail.com)</span>
                                <span class="text-[10px] text-slate-400 font-mono">IP: 127.0.0.1 - Chrome (Windows 11)</span>
                            </div>
                            <span class="text-[10px] text-emerald-700 font-bold uppercase">Active Session</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 17. TAB TRAFFIC MONITORING -->
            <div x-show="activeTab === 'traffic_monitor'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-6">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Live Traffic Monitor</h3>
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 text-xs font-bold text-slate-700">
                        Requests/sec: <span class="text-slate-900 font-mono text-base block mt-0.5">21.4 requests</span>
                    </div>
                </div>
            </div>

            <!-- 18. TAB CONSOLE LOGS -->
            <div x-show="activeTab === 'console_logs'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Console Audit Logs</h3>
                    <div class="bg-slate-950 p-5 rounded-2xl text-emerald-400 font-mono text-[10px] space-y-1">
                        <div>[00:58:31] Super Admin #001 - Security Gate verify success.</div>
                    </div>
                </div>
            </div>

            <!-- 19. TAB SECURITY GATE STATUS -->
            <div x-show="activeTab === 'sec_gate_status'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Security Gate Status</h3>
                    <div class="bg-slate-50 p-5 border border-slate-200 rounded-2xl space-y-3 text-xs font-bold text-slate-700">
                        <div class="flex justify-between">
                            <span>Status Sesi Security Gate:</span>
                            <span :class="isPrivilegedSessionActive ? 'text-emerald-700' : 'text-slate-400'" x-text="isPrivilegedSessionActive ? 'AKTIF (Terverifikasi)' : 'MATI (Butuh Verifikasi)'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 20. TAB EDIT PASSWORD SECURITY GATE -->
            <div x-show="activeTab === 'gate_pass_edit'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-6">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Update Security Gate Password</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Ubah sandi otorisasi bertingkat demi keamanan maksimal console.</p>
                    </div>

                    <div class="bg-slate-50 p-6 border border-slate-200 rounded-2xl space-y-4">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Password Baru</label>
                            <input type="password" x-model="newGatePassword" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-rose-500 outline-none transition" />
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Konfirmasi Password Baru</label>
                            <input type="password" x-model="newGatePasswordConfirm" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-rose-500 outline-none transition" />
                        </div>

                        <div class="pt-2">
                            <button @click="triggerUpdateGatePassword()" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition shadow-sm">
                                Simpan Sandi Baru
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 21. TAB AUTHENTICATION HISTORY -->
            <div x-show="activeTab === 'auth_history'" x-transition class="space-y-6">
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Privileged Authentication Events History</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead>
                                <tr class="text-[9px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200 bg-slate-50/50">
                                    <th class="py-2 px-3">Waktu</th>
                                    <th class="py-2 pr-3">User</th>
                                    <th class="py-2 pr-3">IP Address</th>
                                    <th class="py-2 pr-3">OS/Browser</th>
                                    <th class="py-2 pr-3">Approx. Location</th>
                                    <th class="py-2 px-3 text-right">Result</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                                <template x-for="ev in securityGateLogs">
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="py-3 px-3 font-mono text-[10px]" x-text="ev.time"></td>
                                        <td class="py-3 pr-3 font-bold text-slate-900" x-text="ev.user"></td>
                                        <td class="py-3 pr-3 font-mono" x-text="ev.ip"></td>
                                        <td class="py-3 pr-3" x-text="ev.os + ' / ' + ev.browser"></td>
                                        <td class="py-3 pr-3" x-text="ev.location"></td>
                                        <td class="py-3 px-3 text-right">
                                            <span class="px-2 py-0.5 rounded text-[8px] font-bold tracking-wider uppercase font-mono"
                                                  :class="ev.result === 'SUCCESS' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'"
                                                  x-text="ev.result"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 22. TAB SYSTEM TESTS & DIAGNOSTICS -->
            <div x-show="activeTab === 'system_tests'" x-transition class="space-y-6">
                
                <!-- Gatekeeper Upload Section -->
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-6">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Gatekeeper Authorization File Upload</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Uji keabsahan berkas otorisasi kriptografis Gatekeeper Anda (.key, .pem, atau .json).</p>
                    </div>

                    <div class="bg-slate-50 p-6 border border-slate-200 rounded-2xl space-y-4">
                        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                            <div class="flex items-center gap-3">
                                <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">Unggah Berkas Otorisasi Gatekeeper</span>
                                    <span class="text-[10px] text-slate-400 font-medium">Format: JSON atau Plaintext Key. Maks. 2MB.</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="file" id="gatekeeperFileInput" @change="uploadGatekeeperFile()" class="hidden" accept=".json,.pem,.key,.txt" />
                                <button @click="document.getElementById('gatekeeperFileInput').click()" class="px-4.5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition shadow-sm">
                                    Pilih &amp; Unggah File
                                </button>
                            </div>
                        </div>

                        <!-- Gatekeeper Test Report Box -->
                        <div class="p-5 bg-white border border-slate-200 rounded-xl space-y-4" x-show="gatekeeperReport">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                <span class="text-xs font-black text-slate-900">Hasil Analisis Otorisasi Berkas</span>
                                <span class="px-2.5 py-0.5 rounded text-[8px] font-bold font-mono tracking-wider"
                                      :class="gatekeeperReport && gatekeeperReport.status.includes('VERIFIED') ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                                      x-text="gatekeeperReport ? gatekeeperReport.status : ''"></span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-semibold text-slate-700">
                                <div>ID Kunci: <span class="block text-slate-900 font-mono" x-text="gatekeeperReport ? gatekeeperReport.key_id : ''"></span></div>
                                <div>Issuer CA: <span class="block text-slate-900" x-text="gatekeeperReport ? gatekeeperReport.issuer : ''"></span></div>
                                <div>Algoritma Tanda Tangan: <span class="block text-slate-900 font-mono" x-text="gatekeeperReport ? gatekeeperReport.algorithm : ''"></span></div>
                                <div>Peran Terkait: <span class="block text-slate-900" x-text="gatekeeperReport ? gatekeeperReport.role : ''"></span></div>
                            </div>

                            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-150 space-y-2">
                                <span class="text-[10px] font-bold text-slate-450 uppercase tracking-wider block">Wewenang Aktif (Scopes)</span>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="scope in (gatekeeperReport ? gatekeeperReport.scopes : [])">
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-800 text-[10px] rounded border border-emerald-100 font-bold" x-text="scope"></span>
                                    </template>
                                </div>
                            </div>

                            <div class="space-y-1 bg-amber-50/30 p-3 rounded-lg border border-amber-100 text-xs" x-show="gatekeeperReport">
                                <span class="font-bold text-amber-800 block">Rekomendasi Keamanan:</span>
                                <span class="text-slate-650 font-medium" x-text="gatekeeperReport ? gatekeeperReport.analysis.recommendation : ''"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Automated Feature Test Suite -->
                <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-xs space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Automated System Feature Test Suite</h3>
                            <p class="text-[11px] text-slate-550 mt-0.5">Lakukan pengujian fungsionalitas dari awal untuk setiap modul kritis.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="runAllDiagnosticsSuite()" :disabled="runningTestSuite" class="px-4.5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl transition disabled:opacity-50 flex items-center gap-2 shadow-xs">
                                <span x-show="runningTestSuite" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                <span x-text="runningTestSuite ? 'Menguji Sistem...' : '⚡ RUN ALL DIAGNOSTICS SUITE'"></span>
                            </button>
                            <a :href="'{{ route('superadmin.optimization.download-test-report') }}'" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition flex items-center gap-2 shadow-xs">
                                Unduh Laporan
                            </a>
                        </div>
                    </div>

                    <!-- Progress bar for run all -->
                    <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden border border-slate-200" x-show="runningTestSuite">
                        <div class="bg-emerald-500 h-full rounded-full transition-all duration-300" :style="'width: ' + testSuiteProgress + '%'"></div>
                    </div>

                    <!-- Individual Diagnostic Tests -->
                    <div class="space-y-4">
                        <template x-for="module in testModules">
                            <div class="bg-slate-50 border border-slate-200 p-4.5 rounded-2xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4 transition hover:border-slate-300">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2.5">
                                        <span class="text-xs font-black text-slate-850" x-text="module.name"></span>
                                        <span class="px-2 py-0.5 rounded text-[8px] font-bold font-mono tracking-wider uppercase"
                                              :class="module.status === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : (module.status === 'warning' ? 'bg-amber-50 text-amber-700 border border-amber-200' : (module.status === 'failed' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-405 border-slate-200'))"
                                              x-text="module.status ? module.status : 'PENDING'"></span>
                                    </div>
                                    <p class="text-[11px] text-slate-550 font-medium" x-text="module.details ? module.details : module.description"></p>
                                    <template x-if="module.recommendation">
                                        <p class="text-[10px] text-emerald-700 font-bold" x-text="'💡 Rekomendasi: ' + module.recommendation"></p>
                                    </template>
                                </div>
                                <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                                    <span class="text-xs font-mono text-slate-450 font-bold" x-text="module.latency ? module.latency : ''"></span>
                                    <button @click="runIndividualTest(module.id)" :disabled="module.running" class="px-3.5 py-1.5 bg-slate-900 text-white font-bold text-[10px] rounded-lg hover:bg-slate-800 transition disabled:opacity-50">
                                        <span x-text="module.running ? 'Menguji...' : 'Uji Modul'"></span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Real-Time Console Output -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Test Suite Real-Time Execution Console</h4>
                        <div class="bg-slate-950 p-5 rounded-2xl text-emerald-400 font-mono text-[10px] space-y-1.5 h-[160px] overflow-y-auto" id="testConsoleLogs">
                            <div>[INFO] Diagnostik test plane diaktifkan. Konsol siap berjalan.</div>
                            <template x-for="log in testConsoleLogs">
                                <div :class="log.includes('[ERROR]') ? 'text-rose-450 font-bold' : (log.includes('[SUCCESS]') ? 'text-emerald-300 font-bold' : 'text-emerald-400')" x-text="log"></div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- ======================================================== -->
    <!-- 🔑 MODAL PRIVILEGED SECURITY GATE                        -->
    <!-- ======================================================== -->
    <div x-show="showSecurityGateModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-transition x-cloak>
        <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden flex flex-col p-8 space-y-6">
            
            <div class="text-center space-y-2">
                <span class="text-rose-600 font-mono text-[10px] font-black tracking-widest block uppercase">PRIVILEGED SECURITY GATE</span>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Otorisasi Wewenang Diperlukan</h3>
                <p class="text-xs text-slate-400 max-w-xs mx-auto font-medium">Anda mencoba melakukan operasi tingkat tinggi yang dilindungi.</p>
            </div>

            <div class="space-y-4">
                <div class="space-y-2">
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Kata Sandi Super Admin</label>
                    <input type="password" x-model="gatePassword" placeholder="Masukkan kata sandi Anda..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold focus:ring-2 focus:ring-rose-500 outline-none transition" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button @click="verifyGatePassword()" class="flex-1 py-3 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs transition shadow-sm">
                        VERIFY IDENTITY
                    </button>
                    <button @click="cancelGate()" class="px-4.5 py-3 rounded-2xl border border-slate-200 hover:bg-slate-50 text-slate-500 font-bold text-xs transition">
                        Batal
                    </button>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-4 text-center">
                <span class="text-[9px] font-mono text-slate-400 font-bold block">OPERASI DILINDUNGI: Kendali Sistem Darurat</span>
            </div>

        </div>
    </div>

    <!-- ======================================================== -->
    <!-- ⏱ COUNTDOWN OVERLAY FOR SECRET DEFENSE MODE               -->
    <!-- ======================================================== -->
    <div x-show="showCountdown" class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-slate-950 text-white p-6" x-transition x-cloak>
        <span class="text-[10px] font-mono text-rose-500 font-black tracking-widest animate-pulse">EMERGENCY SYSTEM CONTROL PLAN</span>
        <h2 class="text-xl font-black mt-2">MENGAKTIFKAN SECRET DEFENSE MODE</h2>
        
        <!-- Display the generated recovery link during countdown so the admin can copy it quickly! -->
        <div class="bg-rose-950/50 border border-rose-800 p-6 rounded-2xl my-6 max-w-md w-full space-y-3 text-center" x-show="secretDefenseRecoveryLink">
            <span class="text-[9px] font-bold text-rose-400 uppercase tracking-widest block">⚠️ SALIN SEGERA KUNCI PEMULIHAN INI (5 DETIK SEBELUM SHUTDOWN):</span>
            <div class="flex items-center gap-2">
                <input type="text" readonly :value="secretDefenseRecoveryLink" class="flex-1 px-3 py-1.5 border border-rose-800 bg-slate-900 rounded-lg text-xs font-mono text-rose-300 outline-none text-center" id="sec_defense_recovery_input" />
                <button @click="copySecretDefenseRecoveryLink()" class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-[11px] rounded-lg transition">Salin</button>
            </div>
        </div>

        <div class="text-[120px] font-black tracking-tighter my-2 select-none font-mono text-rose-650" x-text="countdownVal"></div>
    </div>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('privilegedConsole', () => ({
            init() {
                this.startRealtimePolling();
            },
            startRealtimePolling() {
                setInterval(() => {
                    fetch('{{ route("superadmin.optimization.api") }}')
                        .then(res => res.json())
                        .then(data => {
                            if (data && !data.error) {
                                this.maintenance = data.maintenance;
                                this.defense = data.defense;
                                this.secretDefense = data.secretDefense;
                                this.diagnostic = data.diagnostic;
                                this.logs = data.logs;
                                this.securityGateLogs = data.securityGateLogs;
                                this.telemetry = data.telemetry;
                                this.isPrivilegedSessionActive = data.isPrivilegedSessionActive;
                            }
                        })
                        .catch(err => console.error("Optimization Polling Error:", err));
                }, 3000);
            },
            activeTab: 'sys_intel_overview',
            maintenance: @json($maintenance),
            defense: @json($defense),
            secretDefense: @json($secretDefense),
            diagnostic: @json($diagnostic),
            logs: @json($logs),
            securityGateLogs: @json($securityGateLogs),
            telemetry: @json($telemetry),
            
            isPrivilegedSessionActive: @json($isPrivilegedSessionActive),
            
            // Password edit fields
            newGatePassword: '',
            newGatePasswordConfirm: '',

            // Modal state
            showSecurityGateModal: false,
            gatePassword: '',
            pendingAction: null, 
            
            // Recovery Links
            maintenanceRecoveryLink: '',
            secretDefenseRecoveryLink: '',

            // Countdown state
            showCountdown: false,
            countdownVal: 5,

            // Form inputs
            maintenanceReason: 'System maintenance',
            checkingSystem: false,

            // State for Feature Testing
            gatekeeperReport: null,
            runningTestSuite: false,
            testSuiteProgress: 0,
            testConsoleLogs: [],
            testModules: [
                { id: 'gatekeeper', name: '1. Gatekeeper Cryptographic Module', description: 'Memeriksa integritas enkripsi kustom dan validasi sandi Security Gate.', status: '', latency: '', details: '', recommendation: '', running: false },
                { id: 'database', name: '2. Database Latency & Queries', description: 'Menguji performa baca/tulis database utama dan latensi query.', status: '', latency: '', details: '', recommendation: '', running: false },
                { id: 'cache', name: '3. Cache Core & Session Expiry', description: 'Menguji efisiensi caching driver dan masa kadaluarsa 5 menit sesi.', status: '', latency: '', details: '', recommendation: '', running: false },
                { id: 'maintenance_mode', name: '4. Maintenance Mode Redirector', description: 'Memverifikasi status tulis berkas json maintenance dan recovery.', status: '', latency: '', details: '', recommendation: '', running: false },
                { id: 'secret_defense', name: '5. Secret Defense Isolation Mode', description: 'Menguji pemisahan kontrol plane dari front-end publik.', status: '', latency: '', details: '', recommendation: '', running: false },
                { id: 'security_gate', name: '6. Security Gate & Log Auditing', description: 'Menguji pencatatan sidik jari perangkat dan log akses keamanan.', status: '', latency: '', details: '', recommendation: '', running: false },
                { id: 'api_wilayah', name: '7. API Wilayah (Region Data Load Check)', description: 'Menguji ketersediaan berkas wilayah lokal dan konektivitas fallback API Ibnux.', status: '', latency: '', details: '', recommendation: '', running: false },
                { id: 'storage_writable', name: '8. Storage Writable & Disk Check', description: 'Memeriksa kapasitas tulis folder penyimpanan publik dan berkas profil.', status: '', latency: '', details: '', recommendation: '', running: false },
                { id: 'assets_integrity', name: '9. Public Assets & Template Integrity', description: 'Memverifikasi integritas berkas CSS, JS, dan kompilasi Blade view.', status: '', latency: '', details: '', recommendation: '', running: false }
            ],

            defaultDiagnosticResults: [
                {status: 'success', msg: 'Tidak ditemukan masalah performa kritis (No critical performance issue)'},
                {status: 'success', msg: 'Koneksi basis data stabil dan responsif (Database healthy)'},
                {status: 'success', msg: 'Distribusi memori cache berjalan optimal (Cache healthy)'},
                {status: 'success', msg: 'Antrean pekerja Laravel Queue berjalan lancar (Queue healthy)'},
                {status: 'warning', msg: 'Terdeteksi kueri lambat (slow queries) pada modul pendaftaran'},
                {status: 'warning', msg: 'Ditemukan aset CSS/JS berukuran besar (oversized assets)'},
                {status: 'success', msg: 'Tidak terdeteksi adanya kebocoran memori PHP (No memory leak detected)'}
            ],

            async lockConsole() {
                try {
                    const response = await fetch('{{ route('superadmin.secret-gate.lock') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    if (response.ok) {
                        window.location.reload();
                    }
                } catch(e) {
                    alert('Gagal mengunci sesi.');
                }
            },

            // Trigger password update
            triggerUpdateGatePassword() {
                if (this.isPrivilegedSessionActive) {
                    this.executeUpdateGatePassword();
                } else {
                    this.pendingAction = 'update_password';
                    this.showSecurityGateModal = true;
                }
            },

            async executeUpdateGatePassword() {
                if (!this.newGatePassword || this.newGatePassword !== this.newGatePasswordConfirm) {
                    alert('Password tidak cocok atau kosong!');
                    return;
                }

                try {
                    const response = await fetch('{{ route('superadmin.privileged-access.update-password') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            password: this.newGatePassword,
                            password_confirmation: this.newGatePasswordConfirm
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        alert(result.message);
                        this.newGatePassword = '';
                        this.newGatePasswordConfirm = '';
                        window.location.reload();
                    } else {
                        alert(result.message);
                    }
                } catch(e) {
                    alert('Gagal memperbarui password gate.');
                }
            },

            triggerMaintenanceToggle() {
                if (this.isPrivilegedSessionActive) {
                    this.executeMaintenanceToggle();
                } else {
                    this.pendingAction = 'maintenance';
                    this.showSecurityGateModal = true;
                }
            },

            triggerDefenseToggle() {
                if (this.isPrivilegedSessionActive) {
                    this.executeDefenseToggle();
                } else {
                    this.pendingAction = 'defense';
                    this.showSecurityGateModal = true;
                }
            },

            triggerSecretDefenseToggle() {
                if (this.isPrivilegedSessionActive) {
                    this.executeSecretDefenseToggle();
                } else {
                    this.pendingAction = 'secret_defense';
                    this.showSecurityGateModal = true;
                }
            },

            async verifyGatePassword() {
                if (!this.gatePassword) {
                    alert('Masukkan kata sandi Anda.');
                    return;
                }

                try {
                    const response = await fetch('{{ route('superadmin.privileged-access.verify-gate') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ password: this.gatePassword })
                    });
                    const result = await response.json();
                    
                    if (result.success) {
                        this.isPrivilegedSessionActive = true;
                        this.showSecurityGateModal = false;
                        this.gatePassword = '';
                        
                        if (this.pendingAction === 'maintenance') {
                            this.executeMaintenanceToggle();
                        } else if (this.pendingAction === 'defense') {
                            this.executeDefenseToggle();
                        } else if (this.pendingAction === 'secret_defense') {
                            this.executeSecretDefenseToggle();
                        } else if (this.pendingAction === 'update_password') {
                            this.executeUpdateGatePassword();
                        }
                        this.pendingAction = null;
                    } else {
                        alert(result.message);
                    }
                } catch(e) {
                    alert('Gagal memverifikasi identitas. Sandi salah.');
                }
            },

            cancelGate() {
                this.showSecurityGateModal = false;
                this.gatePassword = '';
                this.pendingAction = null;
            },

            async executeMaintenanceToggle() {
                try {
                    const response = await fetch('{{ route('superadmin.optimization.toggle-maintenance') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            is_active: !this.maintenance.is_active,
                            reason: this.maintenanceReason
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.maintenance = result.maintenance;
                        if (result.recovery_url) {
                            this.maintenanceRecoveryLink = result.recovery_url;
                            alert("MAINTENANCE AKTIF!\n\nSalin Recovery Link ini untuk memulihkan kembali website besok:\n" + result.recovery_url);
                        } else {
                            window.location.reload();
                        }
                    }
                } catch(e) {
                    alert('Gagal memperbarui status pemeliharaan.');
                }
            },

            async executeDefenseToggle() {
                try {
                    const response = await fetch('{{ route('superadmin.optimization.toggle-defense') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            is_active: !this.defense.is_active
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.defense = result.defense;
                        window.location.reload();
                    }
                } catch(e) {
                    alert('Gagal memperbarui Defense Mode.');
                }
            },

            async executeSecretDefenseToggle() {
                if (this.secretDefense.is_active) {
                    // Menonaktifkan langsung (pemulihan)
                    this.saveSecretDefenseState(false);
                } else {
                    try {
                        const response = await fetch('{{ route('superadmin.optimization.toggle-secret-defense') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                is_active: true
                            })
                        });
                        const result = await response.json();
                        if (result.success) {
                            this.secretDefense = result.secretDefense;
                            this.secretDefenseRecoveryLink = result.recovery_url;
                            
                            // Jalankan countdown 5 detik
                            this.showCountdown = true;
                            this.countdownVal = 5;
                            
                            const interval = setInterval(() => {
                                this.countdownVal--;
                                if (this.countdownVal <= 0) {
                                    clearInterval(interval);
                                    this.showCountdown = false;
                                    window.location.reload();
                                }
                            }, 1000);
                        }
                    } catch(e) {
                        alert('Gagal mengaktifkan Secret Defense Mode.');
                    }
                }
            },

            async saveSecretDefenseState(activeVal) {
                try {
                    const response = await fetch('{{ route('superadmin.optimization.toggle-secret-defense') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            is_active: activeVal
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.secretDefense = result.secretDefense;
                        window.location.reload();
                    }
                } catch(e) {
                    alert('Gagal memperbarui Secret Defense Mode.');
                }
            },

            copyMaintRecoveryLink() {
                const copyText = document.getElementById("maint_recovery_input");
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(copyText.value);
                alert("Link recovery maintenance berhasil disalin!");
            },

            copySecretDefenseRecoveryLink() {
                const copyText = document.getElementById("sec_defense_recovery_input");
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(copyText.value);
                alert("Link recovery secret defense berhasil disalin!");
            },

            // Diagnosa Performa
            async checkSystem() {
                this.checkingSystem = true;
                try {
                    const response = await fetch('{{ route('superadmin.optimization.check-system') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.diagnostic = result.diagnostic;
                        window.location.reload();
                    }
                } catch(e) {
                    alert('Gagal mendiagnosa performa.');
                } finally {
                    this.checkingSystem = false;
                }
            },

            // uploadGatekeeperFile
            async uploadGatekeeperFile() {
                const fileInput = document.getElementById('gatekeeperFileInput');
                if (fileInput.files.length === 0) return;

                const formData = new FormData();
                formData.append('gatekeeper_file', fileInput.files[0]);

                try {
                    const response = await fetch('{{ route('superadmin.optimization.test-gatekeeper-upload') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.gatekeeperReport = result.result;
                        this.testConsoleLogs.push(`[SUCCESS] File Gatekeeper ${fileInput.files[0].name} berhasil diverifikasi. ID Kunci: ${result.result.key_id}`);
                        alert(result.message);
                    } else {
                        alert(result.message || 'Gagal mengunggah file.');
                    }
                } catch(e) {
                    alert('Terjadi kesalahan saat mengunggah file.');
                }
            },

            // runIndividualTest
            async runIndividualTest(testId) {
                const module = this.testModules.find(m => m.id === testId);
                if (!module) return;

                module.running = true;
                this.testConsoleLogs.push(`[INFO] Menjalankan pengujian untuk ${module.name}...`);

                try {
                    const response = await fetch('{{ route('superadmin.optimization.run-test') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ test_id: testId })
                    });
                    const result = await response.json();
                    if (result.success) {
                        module.status = result.test.status;
                        module.latency = result.test.latency;
                        module.details = result.test.details;
                        module.recommendation = result.test.recommendation;

                        const prefix = module.status === 'success' ? '[SUCCESS]' : (module.status === 'warning' ? '[WARNING]' : '[ERROR]');
                        this.testConsoleLogs.push(`${prefix} Modul ${module.id} selesai diuji. Latensi: ${module.latency}. Status: ${module.status.toUpperCase()}`);
                    }
                } catch(e) {
                    module.status = 'failed';
                    this.testConsoleLogs.push(`[ERROR] Modul ${module.id} gagal diuji karena kesalahan koneksi.`);
                } finally {
                    module.running = false;
                }
            },

            // runAllDiagnosticsSuite
            async runAllDiagnosticsSuite() {
                this.runningTestSuite = true;
                this.testSuiteProgress = 0;
                this.testConsoleLogs.push(`[INFO] Memulai Suite Diagnostik Sistem Menyeluruh...`);

                const total = this.testModules.length;
                for (let i = 0; i < total; i++) {
                    const module = this.testModules[i];
                    await this.runIndividualTest(module.id);
                    this.testSuiteProgress = Math.round(((i + 1) / total) * 100);
                }

                this.testConsoleLogs.push(`[SUCCESS] Suite Diagnostik selesai! 100% modul berhasil dievaluasi.`);
                this.runningTestSuite = false;
                alert('Seluruh pengujian modul berhasil diselesaikan! Silakan unduh laporan untuk analisis lengkap.');
            }
        }));
    });
</script>
@endsection
