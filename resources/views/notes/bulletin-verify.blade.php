@extends('layouts.app')

@section('title', 'Vérification du Bulletin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #1a5490 0%, #2c3e50 100%); color: white;">
                    <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Vérification du Bulletin</h4>
                </div>
                <div class="card-body">
                    @if($valid)
                        <div class="alert alert-success" role="alert">
                            <h5 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Bulletin Authentique</h5>
                            <p class="mb-0">{{ $message }}</p>
                        </div>

                        @if(isset($eleve) && isset($classe) && isset($periode))
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Informations de l'Élève</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 40%;">Nom complet:</th>
                                        <td>{{ $eleve->nom_complet }}</td>
                                    </tr>
                                    <tr>
                                        <th>Numéro étudiant:</th>
                                        <td>{{ $eleve->numero_etudiant }}</td>
                                    </tr>
                                    <tr>
                                        <th>Classe:</th>
                                        <td>{{ $classe->nom }} - {{ $classe->niveau }}</td>
                                    </tr>
                                    <tr>
                                        <th>Période:</th>
                                        <td>
                                            @if($periode == 'trimestre1')
                                                Trimestre 1
                                            @elseif($periode == 'trimestre2')
                                                Trimestre 2
                                            @elseif($periode == 'trimestre3')
                                                Trimestre 3
                                            @else
                                                {{ ucfirst($periode) }}
                                            @endif
                                        </td>
                                    </tr>
                                    @if(isset($anneeScolaire))
                                    <tr>
                                        <th>Année scolaire:</th>
                                        <td>{{ $anneeScolaire->nom }}</td>
                                    </tr>
                                    @endif
                                    @if(isset($moyenneGenerale))
                                    <tr>
                                        <th>Moyenne générale:</th>
                                        <td><strong>{{ number_format($moyenneGenerale, 2) }}/20</strong></td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                            <div class="col-md-6">
                                @if(isset($notes) && count($notes) > 0)
                                <h5>Détails des Notes</h5>
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-bordered table-sm">
                                        <thead style="background: #f8f9fa;">
                                            <tr>
                                                <th>Matière</th>
                                                <th class="text-center">Coef.</th>
                                                <th class="text-center">Note Finale</th>
                                                @if(isset($classe) && !$classe->isPrimaire())
                                                <th class="text-center">Points</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($notes as $matiere => $data)
                                            <tr>
                                                <td><strong>{{ $matiere }}</strong></td>
                                                <td class="text-center">{{ $data['coefficient'] }}</td>
                                                <td class="text-center">{{ number_format($data['note_finale'], 2) }}/20</td>
                                                @if(isset($classe) && !$classe->isPrimaire())
                                                <td class="text-center">{{ $data['points'] }}</td>
                                                @endif
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="text-center">
                            <a href="{{ route('notes.bulletins') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i>Retour aux bulletins
                            </a>
                        </div>
                    @else
                        <div class="alert alert-danger" role="alert">
                            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Bulletin Non Authentique</h5>
                            <p class="mb-0">{{ $message }}</p>
                        </div>

                        @if(isset($eleve))
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Informations Trouvées</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 40%;">Nom complet:</th>
                                        <td>{{ $eleve->nom_complet }}</td>
                                    </tr>
                                    <tr>
                                        <th>Numéro étudiant:</th>
                                        <td>{{ $eleve->numero_etudiant }}</td>
                                    </tr>
                                    @if(isset($periode))
                                    <tr>
                                        <th>Période:</th>
                                        <td>
                                            @if($periode == 'trimestre1')
                                                Trimestre 1
                                            @elseif($periode == 'trimestre2')
                                                Trimestre 2
                                            @elseif($periode == 'trimestre3')
                                                Trimestre 3
                                            @else
                                                {{ ucfirst($periode) }}
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                        @endif

                        <div class="alert alert-warning">
                            <strong><i class="fas fa-info-circle me-2"></i>Note:</strong> Ce bulletin peut avoir été modifié ou falsifié. Veuillez contacter l'administration de l'école pour plus d'informations.
                        </div>

                        <div class="text-center">
                            <a href="{{ route('notes.bulletins') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour aux bulletins
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

