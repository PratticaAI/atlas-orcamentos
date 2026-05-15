# ATLAS — Gerador de Orçamentos com IA

> Produto Prattica AI · SaaS para engenheiros civis e arquitetos

Gere orçamentos paramétricos com tabela SINAPI atualizada, PDF profissional com sua marca — em menos de 20 minutos.

---

## Stack

| Camada | Tecnologia |
|--------|-----------|
| Backend | Laravel 12 · PHP 8.2 |
| Banco | PostgreSQL |
| Cache | Redis |
| Frontend | Blade · Bootstrap 5 · Alpine.js · ApexCharts |
| IA | Claude API (claude-sonnet-4-20250514) |
| PDF | WeasyPrint (Python bridge) |
| Pagamentos | Pagar.me Subscriptions v5 |
| Deploy | GitHub Actions · Hostinger VPS |

---

## Estrutura de módulos

```
Budget/         → CRUD de orçamentos + geração IA
Sinapi/         → Tabela SINAPI local + sync mensal
Subscription/   → Planos + Pagar.me webhooks
Profile/        → Marca, logo, CREA/CAU do profissional
```

---

## Setup local

```bash
git clone https://github.com/PratticaAI/atlas-orcamentos.git
cd atlas-orcamentos
cp .env.example .env
composer install
npm install && npm run dev
php artisan key:generate
php artisan migrate --seed
php artisan queue:work
```

### Variáveis de ambiente obrigatórias

```env
APP_URL=https://atlas.prattica.ai

DB_CONNECTION=pgsql
DB_DATABASE=atlas_db

REDIS_HOST=127.0.0.1

CLAUDE_API_KEY=sk-ant-...
CLAUDE_MODEL=claude-sonnet-4-20250514

PAGARME_API_KEY=sk_...
PAGARME_WEBHOOK_SECRET=...

WEASYPRINT_PATH=/usr/local/bin/weasyprint
```

---

## Planos

| Plano | Preço | Limite |
|-------|-------|--------|
| Solo | R$97/mês | 10 orçamentos/mês |
| Profissional | R$197/mês | Ilimitado + memorial IA |
| Escritório | R$397/mês | Multi-usuário (até 10) |

Trial gratuito: 14 dias · Oferta fundador: 40% desconto vitalício (primeiros 50)

---

## Regras críticas de desenvolvimento

1. **Nunca** usar `cat >>` em arquivos PHP — sempre `str_replace` ou reescrita completa
2. **Sempre** `hasColumn()`/`hasTable()` em migrations
3. **Sempre** `DB::transaction()` em operações multi-tabela
4. **Sempre** `SoftDeletes` em models de dados do usuário
5. **Nunca** commitar secrets — usar `.env` + GitHub Secrets
6. Webhook Pagar.me: **sempre** validar HMAC antes de processar
7. PDFs: **nunca** URL pública permanente — sempre URL temporária assinada

---

## Time

| Agente | Responsabilidade |
|--------|-----------------|
| BLADE | Backend Laravel · Services · Models |
| FORGE | Frontend Blade · Bootstrap 5 · Alpine.js |
| AEGIS | Segurança · LGPD · Pagar.me |
| NEXUS | QA · Auditoria de fluxo |
| PIXEL | UX/UI · Especificação de telas |
| MAVERICK | GTM · Marketing · Métricas |
| VENUS | Comunicação · Pitch · Landing page |

**Produto:** Prattica AI Solutions · Campo Grande, MS
**Contato:** contato@prattica.ai
