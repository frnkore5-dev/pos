<div class="btn-group dropleft">
    <button type="button" class="btn btn-ghost-primary dropdown rounded" data-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-three-dots-vertical"></i>
    </button>
    <div class="dropdown-menu">
        @can('access_sales')
            <a href="{{ route('cash-register-sessions.show', $data->id) }}" class="dropdown-item">
                <i class="bi bi-eye mr-2 text-info" style="line-height: 1;"></i> {{ __('sale::messages.details') }}
            </a>
        @endcan
        <a target="_blank" href="{{ route('app.pos.cash-register.pdf', $data->id) }}" class="dropdown-item">
            <i class="bi bi-file-earmark-pdf mr-2 text-success" style="line-height: 1;"></i> {{ __('sale::messages.print_report') }}
        </a>
    </div>
</div>
