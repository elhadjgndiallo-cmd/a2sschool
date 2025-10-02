@php
    use App\Helpers\ImageHelper;
    
    $photoPath = $photoPath ?? null;
    $name = $name ?? 'Utilisateur';
    $size = $size ?? 'md'; // sm, md, lg, xl
    $class = $class ?? '';
    
    // Définir les tailles et classes CSS
    $sizeClasses = [
        'sm' => 'avatar-sm',
        'md' => 'avatar-md', 
        'lg' => 'avatar-lg',
        'xl' => 'avatar-xl'
    ];
    
    $sizeClass = $sizeClasses[$size] ?? 'avatar-md';
    $combinedClass = "profile-image {$sizeClass} {$class}";
    
    $attributes = [
        'class' => $combinedClass,
        'style' => 'object-fit: cover;'
    ];
@endphp

{!! ImageHelper::profileImage($photoPath, $name, $attributes) !!}
