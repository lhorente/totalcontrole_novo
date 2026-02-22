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

            $data->push([
                'data_banco' => $row[0] ?? '',
                'descricao_banco' => $row[1] ?? '',
                'valor' => $valorNormalizado,
                'data' => null,
                'id_categoria' => '',
                'tipo_lancamento' => 'despesa',
                'id_pessoa' => null,
            ]);
        }

        fclose($file);

        return $data;
    }
}
