<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Services\BudgetGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BudgetController extends Controller
{
    public function __construct(
        private readonly BudgetGeneratorService $generator,
    ) {}

    /**
     * Tela 4 — Histórico de orçamentos
     */
    public function index(Request $request)
    {
        $budgets = Budget::where('user_id', Auth::id())
            ->when($request->work_type, fn($q) => $q->where('work_type', $request->work_type))
            ->when($request->search, fn($q) => $q->where('title', 'ilike', "%{$request->search}%"))
            ->latest()
            ->paginate(15);

        return view('budgets.index', compact('budgets'));
    }

    /**
     * Tela 2 — Formulário de novo orçamento
     */
    public function create()
    {
        return view('budgets.create');
    }

    /**
     * Processa formulário → chama IA → redireciona para preview
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'work_type'   => 'required|in:residential,commercial,renovation,industrial',
            'area_m2'     => 'required|numeric|min:10|max:50000',
            'standard'    => 'required|in:simple,medium,high',
            'state'       => 'required|string|size:2',
            'description' => 'nullable|string|max:1000',
            'bdi_percent' => 'nullable|numeric|min:0|max:100',
            'title'       => 'nullable|string|max:255',
        ]);

        try {
            $budget = $this->generator->generate(Auth::user(), $validated);

            return redirect()
                ->route('orcamentos.show', $budget)
                ->with('success', "Orçamento gerado com {$budget->items->count()} itens SINAPI. Revise e exporte o PDF.");

        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Tela 3 — Preview editável do orçamento
     */
    public function show(Budget $budget)
    {
        $this->authorize('view', $budget);
        $budget->load('items');

        return view('budgets.show', compact('budget'));
    }

    /**
     * Edição inline de item (AJAX — Alpine.js)
     */
    public function updateItem(Request $request, Budget $budget, int $itemId)
    {
        $this->authorize('update', $budget);

        $validated = $request->validate([
            'quantity'   => 'required|numeric|min:0.001',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $item = $budget->items()->findOrFail($itemId);
        $item->update([
            'quantity'    => $validated['quantity'],
            'unit_price'  => $validated['unit_price'],
            'total_price' => round($validated['quantity'] * $validated['unit_price'], 2),
            'source'      => 'manual',
        ]);

        $budget = $this->generator->recalculateTotals($budget);

        return response()->json([
            'success'   => true,
            'subtotal'  => $budget->subtotal,
            'bdi_value' => $budget->bdi_value,
            'total'     => $budget->total,
        ]);
    }

    /**
     * Gera PDF e retorna URL temporária para download
     */
    public function downloadPdf(Budget $budget)
    {
        $this->authorize('view', $budget);
        $budget->load(['items', 'user.profile']);

        // Dispara job assíncrono se PDF ainda não existe
        if (!$budget->hasPdf()) {
            \App\Jobs\GenerateBudgetPdfJob::dispatch($budget);

            return response()->json([
                'status'  => 'processing',
                'message' => 'PDF sendo gerado. Aguarde alguns segundos.',
            ]);
        }

        // Gera URL temporária assinada (15 minutos)
        $url = Storage::temporaryUrl(
            $budget->pdf_path,
            now()->addMinutes(config('atlas.pdf_signed_minutes', 15))
        );

        return response()->json([
            'status' => 'ready',
            'url'    => $url,
        ]);
    }

    /**
     * Duplica orçamento existente (Tela 4)
     */
    public function duplicate(Budget $budget)
    {
        $this->authorize('view', $budget);

        $new = $budget->replicate(['pdf_path', 'status']);
        $new->title  = $budget->title . ' (cópia)';
        $new->status = 'draft';
        $new->save();

        foreach ($budget->items as $item) {
            $newItem = $item->replicate();
            $newItem->budget_id = $new->id;
            $newItem->save();
        }

        return redirect()
            ->route('orcamentos.show', $new)
            ->with('success', 'Orçamento duplicado. Faça os ajustes necessários.');
    }

    /**
     * Soft delete
     */
    public function destroy(Budget $budget)
    {
        $this->authorize('delete', $budget);
        $budget->delete();

        return redirect()
            ->route('orcamentos.index')
            ->with('success', 'Orçamento removido.');
    }
}
