<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DatabaseBackupController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->check() && auth()->user()->isSuperAdmin()) {
                return $next($request);
            }

            abort(403, 'Hanya super admin yang dapat mengunduh backup database.');
        });
    }

    public function index()
    {
        $connectionName = config('database.default');
        $connection = config("database.connections.{$connectionName}", []);

        return view('database-backup.index', [
            'connectionName' => $connectionName,
            'databaseName' => $connection['database'] ?? '-',
            'databaseHost' => $connection['host'] ?? 'localhost',
            'databasePort' => $connection['port'] ?? '3306',
        ]);
    }

    public function download(Request $request)
    {
        $connectionName = config('database.default');
        $connection = config("database.connections.{$connectionName}", []);

        if (($connection['driver'] ?? null) !== 'mysql') {
            return back()->with('error', 'Fitur backup saat ini hanya mendukung koneksi database MySQL/MariaDB.');
        }

        $database = (string) ($connection['database'] ?? '');
        $username = (string) ($connection['username'] ?? '');

        if ($database === '' || $username === '') {
            return back()->with('error', 'Konfigurasi database belum lengkap. Periksa nama database dan username pada file .env.');
        }

        $dumpBinary = $this->resolveMysqldumpBinary();
        if ($dumpBinary === null) {
            return back()->with('error', 'Executable mysqldump tidak ditemukan di server. Tambahkan ke PATH atau letakkan pada lokasi standar MySQL/XAMPP.');
        }

        $backupDirectory = storage_path('app/backups');
        if (!is_dir($backupDirectory) && !mkdir($backupDirectory, 0755, true) && !is_dir($backupDirectory)) {
            return back()->with('error', 'Folder backup database tidak dapat dibuat di storage/app/backups.');
        }

        $timestamp = now()->format('Ymd_His');
        $filename = 'backup_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $database) . '_' . $timestamp . '.sql';
        $tempFile = $backupDirectory . DIRECTORY_SEPARATOR . $filename;

        $command = $this->buildDumpCommand($dumpBinary, $connection, $tempFile);
        $env = $_ENV;
        $env['MYSQL_PWD'] = (string) ($connection['password'] ?? '');

        $result = $this->runDumpCommand($command, $env);
        if ($result['exit_code'] !== 0 || !file_exists($tempFile) || filesize($tempFile) === 0) {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }

            $message = trim((string) $result['stderr']);
            if ($message === '') {
                $message = 'Gagal membuat file backup database. Pastikan user database memiliki akses dump dan mysqldump tersedia di server.';
            }

            return back()->with('error', $message);
        }

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/sql',
        ])->deleteFileAfterSend(true);
    }

    private function resolveMysqldumpBinary(): ?string
    {
        $configured = env('MYSQLDUMP_PATH');
        $candidates = array_filter([
            $configured,
            base_path('mysql/bin/mysqldump.exe'),
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\xampp\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            'C:\\Program Files\\MariaDB 10.4\\bin\\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
        ]);

        foreach ($candidates as $candidate) {
            if ($candidate && @is_file($candidate)) {
                return $candidate;
            }
        }

        $fallback = stripos(PHP_OS_FAMILY, 'Windows') !== false ? 'where mysqldump' : 'command -v mysqldump';
        $resolved = @shell_exec($fallback);
        if (is_string($resolved)) {
            $resolved = trim(strtok($resolved, PHP_EOL));
            if ($resolved !== '') {
                return $resolved;
            }
        }

        return null;
    }

    private function buildDumpCommand(string $dumpBinary, array $connection, string $tempFile): string
    {
        $host = (string) ($connection['host'] ?? '127.0.0.1');
        $port = (string) ($connection['port'] ?? '3306');
        $username = (string) ($connection['username'] ?? 'root');
        $database = (string) ($connection['database'] ?? '');
        $charset = (string) ($connection['charset'] ?? 'utf8mb4');

        $parts = [
            escapeshellarg($dumpBinary),
            '--host=' . escapeshellarg($host),
            '--port=' . escapeshellarg($port),
            '--user=' . escapeshellarg($username),
            '--default-character-set=' . escapeshellarg($charset),
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--routines',
            '--events',
            '--triggers',
            escapeshellarg($database),
            '--result-file=' . escapeshellarg($tempFile),
        ];

        return implode(' ', $parts);
    }

    private function runDumpCommand(string $command, array $env): array
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open($command, $descriptorSpec, $pipes, base_path(), $env);
        if (!is_resource($process)) {
            return [
                'exit_code' => 1,
                'stderr' => 'Proses backup database tidak dapat dijalankan dari server.',
            ];
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        return [
            'exit_code' => (int) $exitCode,
            'stdout' => $stdout,
            'stderr' => $stderr,
        ];
    }
}