@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Gestion des sauvegardes</h4>
                    <a href="{{ route('admin.parametres') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Retour aux paramètres
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <a href="{{ route('admin.backups.create') }}" class="btn btn-success">
                            <i class="fas fa-database"></i> Créer une nouvelle sauvegarde
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nom du fichier</th>
                                    <th>Taille</th>
                                    <th>Date de création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($backups) > 0)
                                    @foreach($backups as $backup)
                                        <tr>
                                            <td>{{ $backup['name'] }}</td>
                                            <td>{{ number_format($backup['size'] / 1024, 2) }} KB</td>
                                            <td>{{ date('d/m/Y H:i:s', $backup['date']) }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.backups.download', ['filename' => $backup['name']]) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-download"></i> Télécharger
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteBackupModal{{ str_replace('.', '', $backup['name']) }}">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </button>
                                                </div>

                                                <!-- Modal de confirmation de suppression -->
                                                <div class="modal fade" id="deleteBackupModal{{ str_replace('.', '', $backup['name']) }}" tabindex="-1" role="dialog" aria-labelledby="deleteBackupModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteBackupModalLabel">Confirmer la suppression</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Êtes-vous sûr de vouloir supprimer la sauvegarde <strong>{{ $backup['name'] }}</strong> ? Cette action est irréversible.
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                                <form action="{{ route('admin.backups.delete', ['filename' => $backup['name']]) }}" method="POST" style="display: inline;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">Supprimer</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center">Aucune sauvegarde disponible</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Informations sur les sauvegardes</h5>
                            </div>
                            <div class="card-body">
                                <p><i class="fas fa-info-circle text-info"></i> Les sauvegardes sont stockées dans le répertoire <code>storage/app/backups</code> de l'application.</p>
                                <p><i class="fas fa-exclamation-triangle text-warning"></i> Il est recommandé de télécharger régulièrement les sauvegardes et de les stocker dans un emplacement sécurisé.</p>
                                <p><i class="fas fa-clock text-secondary"></i> La création d'une sauvegarde peut prendre quelques instants en fonction de la taille de la base de données.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection