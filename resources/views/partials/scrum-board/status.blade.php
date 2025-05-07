<div class="kanban-statuses">
    <div class="status-header"
         style="border-color: {{ $status['color'] }}66;">
        <span>{{ $status['title'] }}</span>
        @if($status['size'])
            {{ $status['size'] }} {{ __($status['size'] > 1 ? 'tickets' : 'ticket') }}
        @endif
    </div>
    <div class="status-container"
         data-status="{{ $status['id'] }}"
         id="status-records-{{ $status['id'] }}"
         style="border-color: {{ $status['color'] }}66;">
        @foreach($this->getRecords()->where('status', $status['id']) as $record)
            @include('partials.scrum-board.record')
        @endforeach
    </div>
</div>
