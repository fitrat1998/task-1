<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class PythonAnalyticsService
{
    protected string $pythonBin;
    protected string $scriptDir;
    protected string $cacheDir;

    public function __construct()
    {
        $this->pythonBin = env('PYTHON_BIN', 'python');
        $this->scriptDir = base_path('scripts');
        $this->cacheDir  = storage_path('app/cache');
    }

    public function runKbr(): array
    {
        return $this->run('kbr_analysis', 'kbr_analysis.json');
    }

    public function runVacancy(): array
    {
        return $this->run('vacancy_analysis', 'vacancy_analysis.json');
    }

    protected function run(string $script, string $cacheFile): array
    {
        $cachePath = $this->cacheDir . DIRECTORY_SEPARATOR . $cacheFile;

        // Kesh bor va yangi (1 soatdan kam) bo'lsa qaytaramiz
        if (file_exists($cachePath) && (time() - filemtime($cachePath)) < 3600) {
            $json = file_get_contents($cachePath);
            return json_decode($json, true) ?? [];
        }

        $scriptPath = $this->scriptDir . DIRECTORY_SEPARATOR . $script . '.py';
        $cmd = escapeshellcmd($this->pythonBin) . ' ' . escapeshellarg($scriptPath) . ' 2>&1';

        $output = [];
        $code   = 0;
        exec($cmd, $output, $code);

        $json = implode('', $output);

        // JSON ni topamiz (skript boshida warning bo'lishi mumkin)
        $start = strpos($json, '{');
        if ($start !== false) {
            $json = substr($json, $start);
        }

        $data = json_decode($json, true);

        if ($data === null) {
            return ['error' => 'Python script failed', 'raw' => $json];
        }

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        file_put_contents($cachePath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return $data;
    }

    public function clearCache(): void
    {
        foreach (glob($this->cacheDir . DIRECTORY_SEPARATOR . '*.json') as $file) {
            unlink($file);
        }
    }
}
