<?php

namespace App\Services;

use App\Models\ContaReceber;
use Carbon\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ContaReceberImportService
{
    public const EXTENSOES = ['csv', 'ofx'];

    /**
     * Import accounts from a CSV or OFX file.
     *
     * @return array{importadas: int, erros: array<int, string>}
     */
    public function import(string $path, string $extension): array
    {
        $rows = $extension === 'ofx' ? $this->parseOfx($path) : $this->parseCsv($path);

        if (empty($rows)) {
            throw new InvalidArgumentException('Nenhum registro encontrado no arquivo.');
        }

        $validas = [];
        $erros = [];
        $rotulo = $extension === 'ofx' ? 'Transação' : 'Linha';

        foreach ($rows as $indice => $row) {
            try {
                $validas[] = $this->normalize($row);
            } catch (InvalidArgumentException $e) {
                $erros[] = $rotulo . ' ' . ($indice + 1) . ': ' . $e->getMessage();
            }
        }

        foreach (array_chunk($validas, 100) as $chunk) {
            ContaReceber::insert($chunk);
        }

        return [
            'importadas' => count($validas),
            'erros' => $erros,
        ];
    }

    /**
     * Parse a semicolon or comma separated CSV file.
     */
    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new InvalidArgumentException('Não foi possível abrir o arquivo CSV.');
        }

        $primeiraLinha = fgets($handle) ?: '';
        rewind($handle);

        // Brazilian spreadsheets commonly use ";" while plain exports use ",".
        $delimiter = substr_count($primeiraLinha, ';') >= substr_count($primeiraLinha, ',') ? ';' : ',';
        $header = fgetcsv($handle, 0, $delimiter);

        if (! is_array($header) || count($header) === 0) {
            fclose($handle);
            throw new InvalidArgumentException('O cabeçalho do CSV está vazio.');
        }

        $header = array_map(fn ($coluna) => $this->normalizeHeader((string) $coluna), $header);
        $obrigatorias = ['cliente', 'valor', 'data_vencimento'];
        $ausentes = array_diff($obrigatorias, $header);

        if (! empty($ausentes)) {
            fclose($handle);
            throw new InvalidArgumentException('Colunas obrigatórias ausentes: ' . implode(', ', $ausentes) . '.');
        }

        $rows = [];

        while (($linha = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($linha) === 1 && trim((string) $linha[0]) === '') {
                continue;
            }

            $row = [];

            foreach ($header as $indice => $coluna) {
                $row[$coluna] = isset($linha[$indice]) ? trim((string) $linha[$indice]) : '';
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Parse transactions from an OFX file.
     */
    private function parseOfx(string $path): array
    {
        $conteudo = file_get_contents($path);

        if ($conteudo === false) {
            throw new InvalidArgumentException('Não foi possível abrir o arquivo OFX.');
        }

        // OFX is usually SGML, not strict XML, so transactions are extracted by block.
        preg_match_all(
            '/<STMTTRN>(.*?)(?=<\/STMTTRN>|<STMTTRN>|<\/BANKTRANLIST>|$)/is',
            $conteudo,
            $matches
        );

        if (empty($matches[1])) {
            throw new InvalidArgumentException('Nenhuma transação OFX encontrada.');
        }

        $rows = [];

        foreach ($matches[1] as $bloco) {
            $tag = function (string $nome) use ($bloco): string {
                return preg_match('/<' . $nome . '>([^<\r\n]+)/i', $bloco, $match)
                    ? trim($match[1])
                    : '';
            };

            $rows[] = [
                'cliente' => $tag('NAME') ?: $tag('PAYEE') ?: 'Cliente OFX',
                'descricao' => $tag('MEMO'),
                'documento' => $tag('FITID'),
                'valor' => $tag('TRNAMT'),
                'data_vencimento' => $tag('DTPOSTED'),
                'data_pagamento' => '',
                'status' => 'pendente',
                'forma_pagamento' => '',
                'observacao' => 'Importado via OFX',
            ];
        }

        return $rows;
    }

    /**
     * Normalize and validate one import row.
     */
    private function normalize(array $row): array
    {
        $cliente = trim((string) ($row['cliente'] ?? ''));

        if ($cliente === '') {
            throw new InvalidArgumentException('o cliente é obrigatório.');
        }

        $valor = $this->parseValor($row['valor'] ?? null);

        if ($valor <= 0) {
            throw new InvalidArgumentException('o valor deve ser maior que zero.');
        }

        $vencimento = $this->parseData($row['data_vencimento'] ?? null, 'vencimento');
        $status = $this->parseStatus($row['status'] ?? 'pendente');
        $pagamento = $this->parseData($row['data_pagamento'] ?? null, 'pagamento', false);

        // A paid row always has a payment date; any other status must not keep one.
        if ($status === 'pago' && ! $pagamento) {
            $pagamento = today()->toDateString();
        }

        if ($status !== 'pago') {
            $pagamento = null;
        }

        return [
            'cliente' => $cliente,
            'descricao' => $this->nullable($row['descricao'] ?? null),
            'documento' => $this->nullable($row['documento'] ?? null),
            'valor' => round($valor, 2),
            'data_vencimento' => $vencimento,
            'data_pagamento' => $pagamento,
            'status' => $status,
            'forma_pagamento' => $this->nullable($row['forma_pagamento'] ?? null),
            'observacao' => $this->nullable($row['observacao'] ?? null),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function parseValor($valor): float
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            throw new InvalidArgumentException('o valor é obrigatório.');
        }

        $valor = preg_replace('/[^\d,.-]/', '', $valor);

        // "1.234,56" (pt-BR) becomes "1234.56"; "1234.56" is kept as is.
        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        if (! is_numeric($valor)) {
            throw new InvalidArgumentException('o valor informado é inválido.');
        }

        return (float) $valor;
    }

    private function parseData($valor, string $campo, bool $obrigatorio = true): ?string
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            if ($obrigatorio) {
                throw new InvalidArgumentException("a data de {$campo} é obrigatória.");
            }

            return null;
        }

        $valor = preg_replace('/\[.*$/', '', $valor);

        if (ctype_digit($valor) && strlen($valor) >= 8) {
            $candidato = substr($valor, 0, 8);

            if (Carbon::hasFormat($candidato, 'Ymd')) {
                return Carbon::createFromFormat('Ymd', $candidato)->toDateString();
            }
        }

        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y'] as $formato) {
            if (Carbon::hasFormat($valor, $formato)) {
                return Carbon::createFromFormat($formato, $valor)->toDateString();
            }
        }

        throw new InvalidArgumentException("a data de {$campo} é inválida.");
    }

    private function parseStatus($status): string
    {
        $status = Str::lower(Str::ascii(trim((string) $status)));

        // Accept Portuguese and English labels from bank/ERP exports.
        $mapa = [
            '' => 'pendente',
            'pendente' => 'pendente',
            'aberto' => 'pendente',
            'open' => 'pendente',
            'pago' => 'pago',
            'recebido' => 'pago',
            'recebida' => 'pago',
            'paid' => 'pago',
            'cancelado' => 'cancelado',
            'cancelada' => 'cancelado',
            'cancelled' => 'cancelado',
            'canceled' => 'cancelado',
        ];

        if (! array_key_exists($status, $mapa)) {
            throw new InvalidArgumentException('o status informado é inválido.');
        }

        return $mapa[$status];
    }

    private function normalizeHeader(string $header): string
    {
        $header = str_replace("\xEF\xBB\xBF", '', trim($header));

        return trim(
            preg_replace('/[^a-z0-9]+/', '_', Str::lower(Str::ascii($header))),
            '_'
        );
    }

    private function nullable($valor): ?string
    {
        $valor = trim((string) $valor);

        return $valor === '' ? null : $valor;
    }
}
