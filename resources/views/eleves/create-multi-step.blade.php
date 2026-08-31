@extends('layouts.app')

@section('title', 'Inscription d\'un Élève - Étape ' . $currentStep . '/4')

@section('content')
<div class="container-fluid">
    <div class="row d-none d-md-block">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('eleves.index') }}">Élèves</a></li>
                        <li class="breadcrumb-item active">Inscription - Étape {{ $currentStep }}/4</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="fas fa-user-plus me-2"></i>
                    Inscription d'un Élève - Étape {{ $currentStep }}/4
                </h4>
            </div>
        </div>
    </div>

    <h5 class="d-md-none mb-2">
        <i class="fas fa-user-plus me-1"></i>Inscription · {{ $currentStep }}/4
    </h5>

    {{-- Indicateur de progression --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2 py-md-3">
                    <div class="progress mb-2 mb-md-3" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" 
                             style="width: {{ ($currentStep / 4) * 100 }}%" 
                             aria-valuenow="{{ $currentStep }}" 
                             aria-valuemin="0" 
                             aria-valuemax="4">
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="step-indicator {{ $currentStep >= 1 ? 'active' : '' }}">
                                <i class="fas fa-camera {{ $currentStep >= 1 ? 'text-primary' : 'text-muted' }}"></i>
                                <div class="step-label">Photo</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="step-indicator {{ $currentStep >= 2 ? 'active' : '' }}">
                                <i class="fas fa-user {{ $currentStep >= 2 ? 'text-primary' : 'text-muted' }}"></i>
                                <div class="step-label">Élève</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="step-indicator {{ $currentStep >= 3 ? 'active' : '' }}">
                                <i class="fas fa-users {{ $currentStep >= 3 ? 'text-primary' : 'text-muted' }}"></i>
                                <div class="step-label">Parent</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="step-indicator {{ $currentStep >= 4 ? 'active' : '' }}">
                                <i class="fas fa-check {{ $currentStep >= 4 ? 'text-primary' : 'text-muted' }}"></i>
                                <div class="step-label">Finaliser</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenu de l'étape --}}
    <div class="row">
        <div class="col-12">
            @switch($currentStep)
                @case(1)
                    @include('eleves.steps.step1-photo')
                    @break
                @case(2)
                    @include('eleves.steps.step2-student-info')
                    @break
                @case(3)
                    @include('eleves.steps.step3-parent-info')
                    @break
                @case(4)
                    @include('eleves.steps.step4-finalize')
                    @break
                @default
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Étape invalide. Veuillez recommencer l'inscription.
                    </div>
                    <div class="text-center">
                        <a href="{{ route('eleves.create') }}" class="btn btn-primary">
                            <i class="fas fa-redo me-2"></i>Recommencer
                        </a>
                    </div>
            @endswitch
        </div>
    </div>
</div>

<style>
.step-indicator {
    padding: 6px 4px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.step-indicator.active {
    background-color: #f8f9fa;
    border: 2px solid #007bff;
}

.step-indicator i {
    font-size: 18px;
    margin-bottom: 4px;
}

.step-label {
    font-size: 11px;
    font-weight: 600;
    color: #6c757d;
}

.step-indicator.active .step-label {
    color: #007bff;
}

.progress {
    background-color: #e9ecef;
    border-radius: 4px;
}

.progress-bar {
    border-radius: 4px;
    transition: width 0.6s ease;
}

@media (max-width: 576px) {
    .step-indicator {
        padding: 4px 2px;
    }
    .step-indicator i {
        font-size: 14px;
        margin-bottom: 2px;
    }
    .step-label {
        font-size: 10px;
    }
}
</style>
@endsection
