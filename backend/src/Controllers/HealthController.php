<?php
declare(strict_types=1);

final class HealthController
{
    public function __construct(private array $config)
    {
    }

    public function index(): void
    {
        try {
            DB::connect($this->config['db'])->query('SELECT 1');
            Response::json([
                'data' => [
                    'ok' => true,
                    'database' => true,
                ],
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            Response::error('Koneksi database gagal.', 500);
        }
    }
}