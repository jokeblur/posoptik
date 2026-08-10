<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::transaction(function () {
            DB::table('frames')
                ->select('id', 'jenis_frame')
                ->orderBy('id')
                ->chunkById(500, function ($frames) {
                    foreach ($frames as $frame) {
                        $normalizedJenisFrame = $this->normalizeJenisFrame($frame->jenis_frame);

                        if ($normalizedJenisFrame === $frame->jenis_frame) {
                            continue;
                        }

                        DB::table('frames')
                            ->where('id', $frame->id)
                            ->update([
                                'jenis_frame' => $normalizedJenisFrame,
                                'updated_at' => now(),
                            ]);
                    }
                }, 'id');
        });
    }

    public function down()
    {
        // Data normalization is intentionally one-way.
    }

    private function normalizeJenisFrame($value): ?string
    {
        $trimmed = trim((string) $value);

        if ($trimmed === '') {
            return null;
        }

        $compact = preg_replace('/\s+/', ' ', $trimmed);
        $lower = strtolower($compact);

        if ($lower === 'umum') {
            return 'Umum';
        }

        if (preg_match('/^bpjs\s*(i{1,3})$/i', $compact, $matches)) {
            return 'BPJS ' . strtoupper($matches[1]);
        }

        if (str_starts_with(strtoupper($compact), 'BPJS ')) {
            return strtoupper($compact);
        }

        return $compact;
    }
};