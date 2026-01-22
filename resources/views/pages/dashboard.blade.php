@extends('layouts.app')

@section('title', 'Dashboard Warehouse')

@section('content')
<section class="section">

  {{-- STATISTIC --}}
  <div class="row">

    {{-- TOTAL USERS --}}
    <div class="col-lg-3 col-md-6 col-sm-6">
      <div class="card card-statistic-1">
        <div class="card-icon bg-primary">
          <i class="fas fa-users"></i>
        </div>
        <div class="card-wrap">
          <div class="card-header">
            <h4>Total Users</h4>
          </div>
          <div class="card-body">
            {{ $total_users }}
          </div>
        </div>
      </div>
    </div>

    {{-- TOTAL PRODUCTS --}}
    <div class="col-lg-3 col-md-6 col-sm-6">
      <div class="card card-statistic-1">
        <div class="card-icon bg-info">
          <i class="fas fa-box"></i>
        </div>
        <div class="card-wrap">
          <div class="card-header">
            <h4>Total Products</h4>
          </div>
          <div class="card-body">
            {{ $total_products }}
          </div>
        </div>
      </div>
    </div>

    {{-- PENDING REQUEST --}}
    <div class="col-lg-3 col-md-6 col-sm-6">
      <div class="card card-statistic-1">
        <div class="card-icon bg-warning">
          <i class="fas fa-hourglass-half"></i>
        </div>
        <div class="card-wrap">
          <div class="card-header">
            <h4>Pending Requests</h4>
          </div>
          <div class="card-body">
            {{ $pending_requests }}
          </div>
        </div>
      </div>
    </div>

    {{-- TOTAL STOCK --}}
    <div class="col-lg-3 col-md-6 col-sm-6">
      <div class="card card-statistic-1">
        <div class="card-icon bg-success">
          <i class="fas fa-warehouse"></i>
        </div>
        <div class="card-wrap">
          <div class="card-header">
            <h4>Total Stock</h4>
          </div>
          <div class="card-body">
            {{ $total_stock }}
          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- MAIN CONTENT --}}
  <div class="row">

    {{-- LATEST REQUESTS --}}
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header">
          <h4>Latest Item Requests</h4>
          <div class="card-header-action">
            <a href="/reports/request" class="btn btn-primary btn-sm">
              View All
            </a>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped mb-0">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                @forelse($latest_requests as $req)
                <tr>
                  <td>{{ $req->user->name }}</td>
                  <td>
                    <span class="badge 
                      {{ $req->status === 'approved' ? 'badge-success' : 
                         ($req->status === 'rejected' ? 'badge-danger' : 'badge-warning') }}">
                      {{ ucfirst($req->status) }}
                    </span>
                  </td>
                  <td>{{ $req->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="3" class="text-center text-muted">
                    No request data
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>

    {{-- LOW STOCK --}}
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">
          <h4>Low Stock Alert</h4>
        </div>
        <div class="card-body">
          @forelse($low_stock_products as $product)
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <strong>{{ $product->name }}</strong>
                <div class="text-muted small">Remaining stock</div>
              </div>
              <span class="badge badge-danger">
                {{ $product->stock }}
              </span>
            </div>
          @empty
            <div class="text-center text-muted">
              <i class="fas fa-check-circle fa-2x mb-2"></i>
              <p>All product stock is safe</p>
            </div>
          @endforelse
        </div>
      </div>
    </div>

  </div>

</section>
@endsection
