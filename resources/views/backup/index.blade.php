@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Database Backup & Restore
        </h2>
        <div class="text-sm text-gray-500">
            System / Backup
        </div>
    </div>
@endsection

@section('content')
<style>
    .backup-card {
        background: white;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        padding: 20px;
        margin-bottom: 20px;
    }
    .backup-card h3 {
        font-size: 16px;
        font-weight: 600;
        color: #1a1f36;
        margin-bottom: 12px;
    }
    .btn-primary {
        background: #4f46e5;
        color: white;
        padding: 8px 20px;
        border-radius: 6px;
        border: none;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-primary:hover {
        background: #4338ca;
    }
    .btn-danger {
        background: #dc2626;
        color: white;
        padding: 6px 14px;
        border-radius: 6px;
        border: none;
        font-size: 13px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-danger:hover {
        background: #b91c1c;
    }
    .btn-success {
        background: #16a34a;
        color: white;
        padding: 6px 14px;
        border-radius: 6px;
        border: none;
        font-size: 13px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-success:hover {
        background: #15803d;
    }
    .btn-info {
        background: #2563eb;
        color: white;
        padding: 6px 14px;
        border-radius: 6px;
        border: none;
        font-size: 13px;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .btn-info:hover {
        background: #1d4ed8;
    }
    .backup-table {
        width: 100%;
        font-size: 13px;
    }
    .backup-table thead th {
        background: #f1f5f9;
        color: #475569;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        padding: 10px 12px;
        border-bottom: 2px solid #e2e8f0;
        text-align: left;
    }
    .backup-table tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .backup-table tbody tr:hover {
        background: #f8fafc;
    }
    .action-buttons {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }
    .stat-box {
        background: #f8fafc;
        border-radius: 6px;
        padding: 12px 16px;
        text-align: center;
        border: 1px solid #e5e7eb;
    }
    .stat-box .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #1a1f36;
    }
    .stat-box .stat-label {
        font-size: 10px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }
    .empty-state .icon {
        font-size: 48px;
        color: #d1d5db;
        margin-bottom: 12px;
    }
    .empty-state h3 {
        font-size: 18px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }
    .empty-state p {
        color: #6b7280;
        font-size: 14px;
    }
    .grid-3col {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }
    @media (max-width: 768px) {
        .grid-3col { grid-template-columns: 1fr; }
        .action-buttons { flex-direction: column; }
    }
</style>

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Stats -->
        <div class="grid-3col">
            <div class="stat-box">
                <div class="stat-value">{{ count($backups) }}</div>
                <div class="stat-label">Total Backups</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ number_format($totalSize / 1024, 2) }} MB</div>
                <div class="stat-label">Total Size</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">
                    @if(count($backups) > 0)
                        {{ $backups[0]->formatted_date }}
                    @else
                        N/A
                    @endif
                </div>
                <div class="stat-label">Latest Backup</div>
            </div>
        </div>

        <!-- Create Backup Button -->
        <div class="backup-card">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h3>Create New Backup</h3>
                    <p class="text-sm text-gray-500">Create a full database backup including all tables.</p>
                </div>
                <form action="{{ route('backup.create') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-plus-circle"></i> Create Backup
                    </button>
                </form>
            </div>
        </div>

        <!-- Backup List -->
        <div class="backup-card">
            <h3>Backup History</h3>
            
            @if(count($backups) > 0)
                <div class="overflow-x-auto">
                    <table class="backup-table">
                        <thead>
                            <tr>
                                <th>Filename</th>
                                <th>Date Created</th>
                                <th>Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($backups as $backup)
                                <tr>
                                    <td>
                                        <span class="font-medium">{{ $backup->filename }}</span>
                                    </td>
                                    <td>{{ $backup->formatted_date }}</td>
                                    <td>{{ number_format($backup->size / 1024, 2) }} KB</td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('backup.download', $backup->filename) }}" class="btn-info">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <form action="{{ route('backup.restore') }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="filename" value="{{ $backup->filename }}">
                                                <button type="submit" class="btn-success" onclick="return confirm('⚠️ WARNING: This will overwrite the entire database!\n\nAre you sure you want to restore this backup?')">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('backup.destroy', $backup->filename) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-danger" onclick="return confirm('Delete this backup file?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <div class="icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <h3>No Backups Found</h3>
                    <p>Create your first backup to protect your data.</p>
                    <form action="{{ route('backup.create') }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-plus-circle"></i> Create First Backup
                        </button>
                    </form>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection