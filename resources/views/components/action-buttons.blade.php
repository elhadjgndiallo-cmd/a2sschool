@props([
    'submitText' => 'Enregistrer',
    'submitIcon' => 'fas fa-save',
    'cancelRoute' => null,
    'cancelText' => 'Annuler',
    'cancelIcon' => 'fas fa-times',
    'submitClass' => 'btn-primary',
    'cancelClass' => 'btn-secondary',
    'submitDisabled' => false,
    'showCancel' => true,
    'additionalButtons' => []
])

<div class="d-flex flex-column flex-sm-row justify-content-center justify-content-md-end gap-2 mt-4">
    @if($showCancel && $cancelRoute)
        <a href="{{ $cancelRoute }}" class="btn {{ $cancelClass }} order-2 order-sm-1">
            <i class="{{ $cancelIcon }} me-1"></i>
            <span class="d-none d-sm-inline">{{ $cancelText }}</span>
        </a>
    @endif
    
    @foreach($additionalButtons as $button)
        <a href="{{ $button['route'] ?? '#' }}" 
           class="btn {{ $button['class'] ?? 'btn-outline-secondary' }} order-2 order-sm-1">
            @if(isset($button['icon']))
                <i class="{{ $button['icon'] }} me-1"></i>
            @endif
            <span class="d-none d-sm-inline">{{ $button['text'] ?? 'Bouton' }}</span>
        </a>
    @endforeach
    
    <button type="submit" 
            class="btn {{ $submitClass }} order-1 order-sm-2" 
            {{ $submitDisabled ? 'disabled' : '' }}>
        <i class="{{ $submitIcon }} me-1"></i>
        <span class="d-none d-sm-inline">{{ $submitText }}</span>
        <span class="d-sm-none">OK</span>
    </button>
</div>
