<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaReceber extends Model
{
    use HasFactory;

    protected $table = 'contas_receber';

    protected $fillable = [
        'cliente',
        'descricao',
        'documento',
        'valor',
        'data_vencimento',
        'data_pagamento',
        'status',
        'forma_pagamento',
        'observacao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
    ];

    public const STATUS = [
        'pendente' => 'Pendente',
        'pago' => 'Recebido',
        'cancelado' => 'Cancelado',
    ];

    public const FORMAS_PAGAMENTO = [
        'Pix',
        'Boleto',
        'Cartão de crédito',
        'Cartão de débito',
        'Transferência',
        'Dinheiro',
    ];

    /**
     * Apply the listing filters.
     */
    public function scopeFiltros(Builder $query, array $filtros): Builder
    {
        $status = $filtros['status'] ?? null;

        return $query
            ->when($filtros['busca'] ?? null, function (Builder $query, string $busca) {
                $query->where(function (Builder $query) use ($busca) {
                    $query->where('cliente', 'like', "%{$busca}%")
                        ->orWhere('descricao', 'like', "%{$busca}%")
                        ->orWhere('documento', 'like', "%{$busca}%");
                });
            })
            // "Vencido" is not stored: it is a pending account whose due date has passed.
            ->when($status === 'vencido', function (Builder $query) {
                $query->where('status', 'pendente')
                    ->whereDate('data_vencimento', '<', today());
            })
            // Virtual filter used by the clickable "Total a receber" card: every pending account.
            ->when($status === 'a_receber', function (Builder $query) {
                $query->where('status', 'pendente');
            })
            // Keep the "Pendente" filter exclusive from "Vencido": overdue pendentes show up as vencidas.
            ->when($status === 'pendente', function (Builder $query) {
                $query->where('status', 'pendente')
                    ->whereDate('data_vencimento', '>=', today());
            })
            ->when(in_array($status, ['pago', 'cancelado'], true), function (Builder $query) use ($status) {
                $query->where('status', $status);
            })
            ->when($filtros['vencimento_de'] ?? null, function (Builder $query, string $data) {
                $query->whereDate('data_vencimento', '>=', $data);
            })
            ->when($filtros['vencimento_ate'] ?? null, function (Builder $query, string $data) {
                $query->whereDate('data_vencimento', '<=', $data);
            });
    }

    /**
     * Resolve the display status. Overdue is derived from pending + past due date,
     * so it updates automatically without a scheduled database update.
     */
    public function getSituacaoAttribute(): string
    {
        if ($this->status === 'pendente' && $this->data_vencimento?->lt(today())) {
            return 'vencido';
        }

        return $this->status;
    }

    public function getSituacaoLabelAttribute(): string
    {
        return [
            'pendente' => 'Pendente',
            'pago' => 'Recebido',
            'vencido' => 'Vencido',
            'cancelado' => 'Cancelado',
        ][$this->situacao] ?? 'Pendente';
    }

    public function getSituacaoBadgeAttribute(): string
    {
        return [
            'pendente' => 'warning',
            'pago' => 'success',
            'vencido' => 'danger',
            'cancelado' => 'secondary',
        ][$this->situacao] ?? 'warning';
    }

    public function getValorFormatadoAttribute(): string
    {
        return 'R$ ' . number_format((float) $this->valor, 2, ',', '.');
    }

    public function getIniciaisAttribute(): string
    {
        return collect(explode(' ', trim($this->cliente)))
            ->filter()
            ->take(2)
            ->map(fn (string $parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
            ->implode('');
    }

    public function isVencida(): bool
    {
        return $this->situacao === 'vencido';
    }

    /**
     * Register the receipt of a pending account.
     */
    public function registrarRecebimento(?string $dataPagamento = null): bool
    {
        return $this->update([
            'status' => 'pago',
            'data_pagamento' => $dataPagamento ?: today()->toDateString(),
        ]);
    }
}
