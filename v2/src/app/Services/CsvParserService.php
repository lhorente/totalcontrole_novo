<?php

namespace App\Services;

use Illuminate\Support\Collection;

class CsvParserService
{
    /**
     * Parse CSV file to preview array.
     *
     * @param string $filePath
     * @return Collection
     */
    public function toPreviewArray(string $filePath): Collection
    {
        $data = collect();

        if (!file_exists($filePath)) {
            return $data;
        }

        $file = fopen($filePath, 'r');

        if ($file === false) {
            return $data;
        }

        // Ignora o cabeçalho
        fgetcsv($file, 0, ';');

        while (($row = fgetcsv($file, 0, ';')) !== false) {
            // Pula linhas vazias
            if (empty(array_filter($row))) {
                continue;
            }

            // Normaliza valor no formato brasileiro: "1.234,56" → 1234.56
            $valorRaw = $row[2] ?? '0';
            $valorRaw = str_replace('.', '', $valorRaw);   // remove separador de milhar
            $valorRaw = str_replace(',', '.', $valorRaw); // troca decimal , por .
            $valorNormalizado = (float) $valorRaw;

            $descricao = $row[1] ?? '';

            $data->push([
                'data_banco' => $row[0] ?? '',
                'descricao_banco' => $descricao,
                'valor' => $valorNormalizado,
                'data' => null,
                'id_categoria' => '',
                'tipo_lancamento' => 'despesa',
                'id_pessoa' => null,
                'installment' => $this->detectInstallment($descricao),
            ]);
        }

        fclose($file);

        return $data;
    }

    /**
     * Detect an installment pattern (e.g. "1/3", "2/10") in a description.
     *
     * Returns ['current' => int, 'total' => int] or null if not found.
     */
    public function detectInstallment(string $description): ?array
    {
        // Match patterns like "1/3", "2/10" anywhere in the description.
        // Require current >= 1, total >= 2 and current <= total.
        if (preg_match('/\b(\d{1,3})\/(\d{1,3})\b/', $description, $matches)) {
            $current = (int) $matches[1];
            $total   = (int) $matches[2];

            if ($current >= 1 && $total >= 2 && $current <= $total) {
                return ['current' => $current, 'total' => $total];
            }
        }

        return null;
    }
}
