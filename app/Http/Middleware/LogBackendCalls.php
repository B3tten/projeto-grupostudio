<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogBackendCalls
{
    /**
     * Log the input, SQL statements and output of each backend call.
     */
    public function handle(Request $request, Closure $next)
    {
        $queries = [];

        DB::listen(function (QueryExecuted $query) use (&$queries) {
            $queries[] = $this->interpolate($query);
        });

        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            $this->writeLog($request, null, $queries, $e);

            throw $e;
        }

        $this->writeLog($request, $response, $queries);

        return $response;
    }

    private function writeLog(Request $request, $response, array $queries, ?\Throwable $exception = null): void
    {
        $route = $request->route();
        $routeName = $route?->getName();
        $api = $routeName ? Str::before($routeName, '.') : ($request->segment(1) ?: 'app');
        $api = preg_replace('/[^a-z0-9]+/', '', Str::lower($api)) ?: 'app';

        if ($routeName && str_contains($routeName, '.')) {
            $method = Str::afterLast($routeName, '.');
        } else {
            $action = $route?->getActionName();
            $method = $action && str_contains($action, '@') ? Str::afterLast($action, '@') : strtolower($request->method());
        }

        $date = now()->format('dmY');
        $prefix = "{$api}_{$method}_{$date}_";

        $entrada = [
            'metodo' => $request->method(),
            'url' => $request->fullUrl(),
            'dados' => $this->inputData($request),
        ];

        $saida = $exception
            ? ['status' => 500, 'erro' => $exception->getMessage()]
            : $this->outputData($response);

        $sql = empty($queries)
            ? 'NENHUMA CONSULTA EXECUTADA'
            : implode(PHP_EOL . $prefix . 'SQL->', $queries);

        $lines = [
            $prefix . 'ENTRADA->' . $this->encode($entrada),
            $prefix . 'SQL->' . $sql,
            $prefix . 'SAIDA->' . $this->encode($saida),
        ];

        try {
            $directory = base_path('logs');

            if (! is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            // One file per API per day; each request appends its ENTRADA/SQL/SAIDA block.
            file_put_contents(
                $directory . DIRECTORY_SEPARATOR . "{$api}_{$date}.txt",
                implode(PHP_EOL, $lines) . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );
        } catch (\Throwable $e) {
            // Logging must never break the request.
        }
    }

    private function inputData(Request $request): array
    {
        $dados = $request->except(['_token', '_method', 'password', 'password_confirmation']);

        if ($request->hasFile('arquivo')) {
            $arquivo = $request->file('arquivo');

            $dados['arquivo'] = [
                'nome' => $arquivo->getClientOriginalName(),
                'extensao' => $arquivo->getClientOriginalExtension(),
                'tamanho' => $arquivo->getSize(),
            ];
        }

        return $dados;
    }

    private function outputData($response): array
    {
        if ($response === null) {
            return ['status' => 500];
        }

        $saida = ['status' => $response->getStatusCode()];

        if ($response->isRedirection()) {
            $saida['redirect'] = $response->headers->get('Location');
            $saida['mensagem'] = session('success') ?? session('warning');

            return $saida;
        }

        $contentType = (string) $response->headers->get('Content-Type');

        if (str_contains($contentType, 'application/json')) {
            $conteudo = $response->getContent();
            $saida['corpo'] = json_decode($conteudo, true) ?? Str::limit((string) $conteudo, 2000);

            return $saida;
        }

        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            $saida['arquivo'] = $response->headers->get('Content-Disposition');
            $saida['tipo'] = $contentType;
            $saida['tamanho'] = $response->headers->get('Content-Length');

            return $saida;
        }

        $original = $response->original ?? null;

        if ($original instanceof View) {
            $saida['view'] = $original->name();
        } else {
            $saida['corpo'] = Str::limit(strip_tags((string) $response->getContent()), 500);
        }

        return $saida;
    }

    private function interpolate(QueryExecuted $query): string
    {
        $partes = explode('?', $query->sql);
        $sql = array_shift($partes);

        foreach ($query->bindings as $binding) {
            $sql .= $this->binding($binding) . array_shift($partes);
        }

        $sql .= implode('', $partes);

        return Str::limit($sql, 4000);
    }

    private function binding($valor): string
    {
        if ($valor instanceof \DateTimeInterface) {
            return "'" . $valor->format('Y-m-d H:i:s') . "'";
        }

        if ($valor === null) {
            return 'NULL';
        }

        if (is_bool($valor)) {
            return $valor ? '1' : '0';
        }

        if (is_int($valor) || is_float($valor)) {
            return (string) $valor;
        }

        return "'" . str_replace("'", "''", (string) $valor) . "'";
    }

    private function encode(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $json === false ? 'ERRO AO CODIFICAR OS DADOS' : $json;
    }
}
