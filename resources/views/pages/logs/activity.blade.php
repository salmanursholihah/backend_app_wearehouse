@extends('layouts.app')
@section('title','Audit Log')

@section('content')
<div class="section-header">
    <h1>Audit Log Aktivitas</h1>
</div>

<div class="card">
    <div class="card-header">
        <h4>Riwayat Aktivitas Sistem</h4>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-sm table-striped">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Aktivitas</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td><strong>{{ $log->user->name ?? '-' }}</strong></td>
                    <td>{{ $log->description }}</td>
                    <td>
                        <span class="text-muted">
                            {{ $log->created_at->format('d M Y H:i') }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted">
                        Belum ada aktivitas
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
