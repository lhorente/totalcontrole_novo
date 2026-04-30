<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class SmartposImportController extends Controller
{
    public function index()
    {
        return view('smartpos.import');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ], [
            'file.required' => 'Selecione um arquivo.',
            'file.mimes'    => 'O arquivo deve estar no formato XLSX ou XLS.',
            'file.max'      => 'O arquivo não pode ser maior que 10MB.',
        ]);

        $path        = $request->file('file')->store('temp/smartpos');
        $workspaceId = session('active_workspace_id');

        try {
            $parsed = $this->parseFile($path, $workspaceId);
        } catch (\Exception $e) {
            Storage::delete($path);
            return back()->withErrors(['file' => 'Não foi possível ler o arquivo: ' . $e->getMessage()]);
        }

        session(['smartpos_file' => $path]);

        $rows          = collect($parsed);
        $totalLinhas   = $rows->count();
        $totalValidas  = $rows->where('action', 'importar')->count();
        $totalIgnoradas = $totalLinhas - $totalValidas;
        $valorTotal    = $rows->where('action', 'importar')->sum('valor');

        return view('smartpos.preview', compact(
            'parsed',
            'totalLinhas',
            'totalValidas',
            'totalIgnoradas',
            'valorTotal'
        ));
    }

    public function store(Request $request)
    {
        $filePath    = session('smartpos_file');
        $workspaceId = session('active_workspace_id');

        if (!$filePath || !Storage::exists($filePath)) {
            return redirect()->route('smartpos.import')
                ->withErrors(['Sessão expirada ou arquivo não encontrado. Faça o upload novamente.']);
        }

        try {
            $parsed = $this->parseFile($filePath, $workspaceId);
        } catch (\Exception $e) {
            return redirect()->route('smartpos.import')
                ->withErrors(['Erro ao processar arquivo: ' . $e->getMessage()]);
        }

        $userId   = Auth::id();
        $imported = 0;

        DB::transaction(function () use ($parsed, $workspaceId, $userId, &$imported) {
            foreach ($parsed as $row) {
                if ($row['action'] !== 'importar') {
                    continue;
                }

                $inserted = DB::table('transacoes')->insertOrIgnore([[
                    'id_workspace' => $workspaceId,
                    'id_usuario'   => $userId,
                    'id_categoria' => $row['id_categoria'],
                    'data'         => $row['data'],
                    'valor'        => $row['valor'],
                    'descricao'    => $row['descricao'],
                    'tipo'         => 'venda',
                    'status'       => 'disponivel',
                    'origem'       => 'smartpos',
                    'id_externo'   => $row['codigo'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]]);

                $imported += $inserted;
            }
        });

        Storage::delete($filePath);
        session()->forget('smartpos_file');

        return redirect()->route('smartpos.import')
            ->with('success', "{$imported} venda(s) importada(s) com sucesso.");
    }

    // -------------------------------------------------------------------------

    private function parseFile(string $filePath, $workspaceId): array
    {
        $spreadsheet = IOFactory::load(Storage::path($filePath));
        $sheet       = $spreadsheet->getActiveSheet();
        $allRows     = $sheet->toArray(null, true, false, false); // raw values

        if (empty($allRows)) {
            return [];
        }

        $headerRow = array_map('trim', array_shift($allRows));
        $headerMap = array_flip($headerRow);

        $requiredColumns = ['Código', 'Data', 'Valor', 'Cliente', 'Status'];
        foreach ($requiredColumns as $col) {
            if (!array_key_exists($col, $headerMap)) {
                throw new \RuntimeException("Coluna obrigatória não encontrada: \"{$col}\"");
            }
        }

        // Load categories
        $catFeira  = Category::where('id_workspace', $workspaceId)
            ->where('nome', 'like', '%Vendas feira%')
            ->first();
        $catDireta = Category::where('id_workspace', $workspaceId)
            ->where('nome', 'like', '%Venda%')
            ->first();

        // Load existing id_externo keys for duplicate check
        $existingKeys = DB::table('transacoes')
            ->where('id_workspace', $workspaceId)
            ->where('origem', 'smartpos')
            ->whereNotNull('id_externo')
            ->pluck('id_externo')
            ->flip()
            ->toArray();

        $parsed = [];

        foreach ($allRows as $row) {
            $rawCodigo = $row[$headerMap['Código']] ?? '';
            $rawData   = $row[$headerMap['Data']]   ?? '';
            $rawValor  = $row[$headerMap['Valor']]  ?? '';
            $rawCliente = $row[$headerMap['Cliente']] ?? '';
            $rawStatus  = $row[$headerMap['Status']]  ?? '';

            // Normalize Código (may come as numeric float)
            if (is_numeric($rawCodigo)) {
                $codigo = (string)(int)$rawCodigo;
            } else {
                $codigo = trim((string)$rawCodigo);
            }

            $cliente   = trim((string)$rawCliente);
            $statusRow = trim((string)$rawStatus);

            // Skip completely empty rows
            if ($codigo === '' && $rawData === '' && $rawValor === '') {
                continue;
            }

            $action   = 'importar';
            $errorMsg = null;
            $data     = null;
            $valor    = null;

            // Status check
            if (mb_strtolower($statusRow) !== 'finalizada') {
                $action = 'ignorada_status';
            }

            // Duplicate check
            if ($action === 'importar' && isset($existingKeys[$codigo])) {
                $action = 'ignorada_duplicada';
            }

            // Parse date and value only for valid rows
            if ($action === 'importar') {
                // Date: may be Excel serial (numeric) or formatted string (d/m/Y or d/m/Y H:i:s)
                try {
                    if (is_numeric($rawData) && $rawData > 0) {
                        $dateObj = ExcelDate::excelToDateTimeObject((float)$rawData);
                        $data    = Carbon::instance($dateObj)->format('Y-m-d');
                    } else {
                        $dateStr = trim((string)$rawData);
                        // Brazilian format: dd/mm/yyyy or dd/mm/yyyy hh:mm:ss
                        if (preg_match('/^\d{2}\/\d{2}\/\d{4}/', $dateStr)) {
                            $fmt  = strlen($dateStr) > 10 ? 'd/m/Y H:i:s' : 'd/m/Y';
                            $data = Carbon::createFromFormat($fmt, $dateStr)->format('Y-m-d');
                        } else {
                            $data = Carbon::parse($dateStr)->format('Y-m-d');
                        }
                    }
                } catch (\Exception $e) {
                    $action   = 'erro_dados';
                    $errorMsg = 'Data inválida';
                }

                // Value: may be numeric or formatted string ("R$ 1.234,56")
                if (is_numeric($rawValor)) {
                    $valor = floatval($rawValor);
                } else {
                    $valorStr = preg_replace('/[^\d,.]/', '', (string)$rawValor);
                    if (strpos($valorStr, ',') !== false) {
                        $valorStr = str_replace('.', '', $valorStr);
                        $valorStr = str_replace(',', '.', $valorStr);
                    }
                    $valor = floatval($valorStr);
                }

                if ($valor <= 0 && $action === 'importar') {
                    $action   = 'erro_dados';
                    $errorMsg = ($errorMsg ? $errorMsg . '; ' : '') . 'Valor inválido';
                }
            }

            // Determine category
            $categoria = stripos($cliente, 'Vendas Feira') !== false ? $catFeira : $catDireta;

            $parsed[] = [
                'codigo'         => $codigo,
                'data_raw'       => $rawData,
                'data'           => $data,
                'valor_raw'      => $rawValor,
                'valor'          => $valor,
                'cliente'        => $cliente,
                'status_row'     => $statusRow,
                'categoria_nome' => $categoria ? $categoria->nome : '(categoria não encontrada)',
                'id_categoria'   => $categoria ? $categoria->id : null,
                'action'         => $action,
                'error_msg'      => $errorMsg,
                'descricao'      => "Venda {$codigo} - {$cliente}",
            ];
        }

        return $parsed;
    }
}
