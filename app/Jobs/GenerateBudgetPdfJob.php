<?php

namespace App\Jobs;

use App\Models\Budget;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class GenerateBudgetPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        private readonly Budget $budget
    ) {}

    public function handle(): void
    {
        // Idempotente — evita arquivos órfãos em retries
        if ($this->budget->hasPdf()) {
            return;
        }

        $this->budget->load(['items', 'user.profile']);

        // 1. Renderiza HTML do orçamento
        $html = view('budgets.pdf-template', ['budget' => $this->budget])->render();

        // 2. Salva HTML temporário
        $tmpHtml = sys_get_temp_dir() . "/atlas_budget_{$this->budget->id}.html";
        $tmpPdf  = sys_get_temp_dir() . "/atlas_budget_{$this->budget->id}.pdf";
        file_put_contents($tmpHtml, $html);

        // 3. Gera PDF via WeasyPrint
        $process = new Process([
            config('atlas.weasyprint_path', '/usr/local/bin/weasyprint'),
            $tmpHtml,
            $tmpPdf,
        ]);
        $process->setTimeout(90)->run();

        if (!$process->isSuccessful() || !file_exists($tmpPdf)) {
            Log::error('WeasyPrint falhou', [
                'budget_id' => $this->budget->id,
                'output'    => $process->getErrorOutput(),
            ]);
            throw new \RuntimeException('Falha ao gerar PDF: ' . $process->getErrorOutput());
        }

        // 4. Move para storage privado
        $path = "pdfs/user_{$this->budget->user_id}/orcamento_{$this->budget->id}.pdf";
        Storage::put($path, file_get_contents($tmpPdf));

        // 5. Atualiza budget com caminho do PDF
        $this->budget->update([
            'pdf_path' => $path,
            'status'   => 'exported',
        ]);

        // 6. Limpa arquivos temporários
        @unlink($tmpHtml);
        @unlink($tmpPdf);

        Log::info("PDF gerado para orçamento #{$this->budget->id}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateBudgetPdfJob falhou para orçamento #{$this->budget->id}", [
            'error' => $exception->getMessage(),
        ]);
    }
}
