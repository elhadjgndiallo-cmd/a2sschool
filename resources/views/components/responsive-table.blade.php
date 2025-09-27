@props([
    'headers' => [],
    'data' => [],
    'actions' => [],
    'emptyMessage' => 'Aucune donnée disponible',
    'emptyIcon' => 'fas fa-inbox',
    'tableClass' => 'table table-striped',
    'responsiveClass' => 'table-responsive',
    'hideColumns' => [], // Colonnes à masquer sur mobile
    'hideColumnsSm' => [], // Colonnes à masquer sur petit écran
    'id' => 'responsiveTable'
])

<div class="{{ $responsiveClass }}">
    <table class="{{ $tableClass }}" id="{{ $id }}">
        <thead class="thead-dark">
            <tr>
                @foreach($headers as $index => $header)
                    <th class="{{ 
                        in_array($index, $hideColumns) ? 'hide-mobile' : '' 
                    }} {{ 
                        in_array($index, $hideColumnsSm) ? 'hide-sm' : '' 
                    }}">
                        {{ $header }}
                    </th>
                @endforeach
                @if(!empty($actions))
                    <th class="text-center">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    @foreach($row as $index => $cell)
                        <td class="{{ 
                            in_array($index, $hideColumns) ? 'hide-mobile' : '' 
                        }} {{ 
                            in_array($index, $hideColumnsSm) ? 'hide-sm' : '' 
                        }}">
                            {!! $cell !!}
                        </td>
                    @endforeach
                    @if(!empty($actions))
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                @foreach($actions as $action)
                                    @if(isset($action['condition']) && !$action['condition']($row))
                                        @continue
                                    @endif
                                    
                                    @if(isset($action['route']))
                                        <a href="{{ $action['route']($row) }}" 
                                           class="btn btn-sm {{ $action['class'] ?? 'btn-outline-primary' }}"
                                           title="{{ $action['title'] ?? '' }}">
                                            <i class="{{ $action['icon'] ?? 'fas fa-eye' }}"></i>
                                        </a>
                                    @elseif(isset($action['button']))
                                        <button type="button" 
                                                class="btn btn-sm {{ $action['class'] ?? 'btn-outline-primary' }}"
                                                title="{{ $action['title'] ?? '' }}"
                                                {{ isset($action['disabled']) && $action['disabled']($row) ? 'disabled' : '' }}
                                                onclick="{{ $action['onclick'] ?? '' }}">
                                            <i class="{{ $action['icon'] ?? 'fas fa-eye' }}"></i>
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) + (!empty($actions) ? 1 : 0) }}" class="text-center text-muted">
                        <i class="{{ $emptyIcon }} fa-2x mb-2"></i>
                        <br>
                        {{ $emptyMessage }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<style>
    @media (max-width: 768px) {
        .hide-mobile {
            display: none !important;
        }
    }
    
    @media (max-width: 576px) {
        .hide-sm {
            display: none !important;
        }
    }
</style>
