<?php

namespace App\Http\Controllers;

use App\Http\Requests\EleveImportRequest;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Services\EleveImportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EleveImportController extends Controller
{
    public function __construct(
        private EleveImportService $importService
    ) {}

    public function create()
    {
        if (!auth()->user()->hasPermission('eleves.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à importer des élèves.');
        }

        $anneeScolaire = AnneeScolaire::where('active', true)->first();
        if (!$anneeScolaire) {
            return redirect()->route('eleves.index')
                ->with('error', 'Aucune année scolaire active. Impossible d\'importer des élèves.');
        }

        $classes = Classe::actif()->orderBy('nom')->get();
        $preview = session('eleve_import_preview');

        return view('eleves.import', compact('classes', 'anneeScolaire', 'preview'));
    }

    public function preview(EleveImportRequest $request)
    {
        try {
            $rows = $this->importService->preview($request->file('fichier'), (int) $request->classe_id);
            $errorCount = collect($rows)->where('status', 'error')->where('line', '>', 0)->count();
            $warningCount = collect($rows)->where('status', 'warning')->where('line', '>', 0)->count();
            $validCount = collect($rows)->where('status', 'ok')->count();
            $importableCount = collect($rows)->whereIn('status', ['ok', 'warning'])->where('line', '>', 0)->count();

            session([
                'eleve_import_preview' => [
                    'classe_id' => (int) $request->classe_id,
                    'rows' => $rows,
                    'error_count' => $errorCount,
                    'warning_count' => $warningCount,
                    'valid_count' => $validCount,
                    'importable_count' => $importableCount,
                    'filename' => $request->file('fichier')->getClientOriginalName(),
                ],
            ]);

            return redirect()->route('eleves.import.create')
                ->with('success', 'Fichier analysé. Vérifiez la prévisualisation avant de confirmer l\'import.');
        } catch (\Throwable $e) {
            return redirect()->route('eleves.import.create')
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function confirm(Request $request)
    {
        if (!auth()->user()->hasPermission('eleves.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à importer des élèves.');
        }

        $preview = session('eleve_import_preview');
        if (!$preview || empty($preview['rows'])) {
            return redirect()->route('eleves.import.create')
                ->with('error', 'Aucune prévisualisation en cours. Veuillez téléverser un fichier.');
        }

        if (($preview['error_count'] ?? 0) > 0) {
            return redirect()->route('eleves.import.create')
                ->with('error', 'Corrigez les erreurs du fichier avant de confirmer l\'import.');
        }

        try {
            $result = $this->importService->importRows($preview['rows'], (int) $preview['classe_id']);
            session()->forget('eleve_import_preview');

            return redirect()->route('eleves.index')
                ->with('success', $result['imported'] . ' élève(s) importé(s) avec succès.');
        } catch (\Throwable $e) {
            return redirect()->route('eleves.import.create')
                ->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        session()->forget('eleve_import_preview');

        return redirect()->route('eleves.import.create')
            ->with('success', 'Prévisualisation annulée.');
    }

    public function modele(): BinaryFileResponse
    {
        if (!auth()->user()->hasPermission('eleves.view')) {
            abort(403);
        }

        $path = $this->importService->generateTemplatePath();

        return response()->download($path, 'modele_import_eleves.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
