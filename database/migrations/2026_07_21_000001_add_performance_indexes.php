<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'users_branch_id_index')) {
                $table->index('branch_id');
            }
            if (!$this->indexExists('users', 'users_role_index')) {
                $table->index('role');
            }
            if (!$this->indexExists('users', 'users_created_at_index')) {
                $table->index('created_at');
            }
        });

        // Penjualan table indexes - CRITICAL untuk laporan & query
        Schema::table('penjualan', function (Blueprint $table) {
            if (!$this->indexExists('penjualan', 'penjualan_branch_id_index')) {
                $table->index('branch_id');
            }
            if (!$this->indexExists('penjualan', 'penjualan_user_id_index')) {
                $table->index('user_id');
            }
            if (!$this->indexExists('penjualan', 'penjualan_pasien_id_index')) {
                $table->index('pasien_id');
            }
            if (!$this->indexExists('penjualan', 'penjualan_dokter_id_index')) {
                $table->index('dokter_id');
            }
            if (!$this->indexExists('penjualan', 'penjualan_status_pengerjaan_index')) {
                $table->index('status_pengerjaan');
            }
            if (!$this->indexExists('penjualan', 'penjualan_branch_id_created_at_index')) {
                $table->index(['branch_id', 'created_at']);
            }
            if (!$this->indexExists('penjualan', 'penjualan_status_pengerjaan_created_at_index')) {
                $table->index(['status_pengerjaan', 'created_at']);
            }
            if (!$this->indexExists('penjualan', 'penjualan_passet_by_user_id_index')) {
                $table->index('passet_by_user_id');
            }
            if (!$this->indexExists('penjualan', 'penjualan_kode_penjualan_index')) {
                $table->index('kode_penjualan');
            }
            if (!$this->indexExists('penjualan', 'penjualan_created_at_index')) {
                $table->index(['created_at']);
            }
        });

        // Penjualan Detail indexes
        if (Schema::hasTable('penjualan_details')) {
            Schema::table('penjualan_details', function (Blueprint $table) {
                if (!$this->indexExists('penjualan_details', 'penjualan_details_penjualan_id_index')) {
                    $table->index('penjualan_id');
                }
                if (!$this->indexExists('penjualan_details', 'penjualan_details_frame_id_index')) {
                    $table->index('frame_id');
                }
                if (!$this->indexExists('penjualan_details', 'penjualan_details_lensa_id_index')) {
                    $table->index('lensa_id');
                }
            });
        }

        // Frame table indexes
        if (Schema::hasTable('frames')) {
            Schema::table('frames', function (Blueprint $table) {
                if (!$this->indexExists('frames', 'frames_branch_id_index')) {
                    $table->index('branch_id');
                }
                if (!$this->indexExists('frames', 'frames_id_sales_index')) {
                    $table->index('id_sales');
                }
                if (!$this->indexExists('frames', 'frames_branch_id_stok_index')) {
                    $table->index(['branch_id', 'stok']);
                }
                if (!$this->indexExists('frames', 'frames_created_at_index')) {
                    $table->index('created_at');
                }
            });
        }

        // Lensa table indexes
        if (Schema::hasTable('lensas')) {
            Schema::table('lensas', function (Blueprint $table) {
                if (!$this->indexExists('lensas', 'lensas_branch_id_index')) {
                    $table->index('branch_id');
                }
                if (!$this->indexExists('lensas', 'lensas_id_sales_index')) {
                    $table->index('id_sales');
                }
                if (!$this->indexExists('lensas', 'lensas_branch_id_stok_index')) {
                    $table->index(['branch_id', 'stok']);
                }
                if (!$this->indexExists('lensas', 'lensas_created_at_index')) {
                    $table->index('created_at');
                }
            });
        }

        // Keuangan table indexes
        if (Schema::hasTable('keuangans')) {
            Schema::table('keuangans', function (Blueprint $table) {
                if (!$this->indexExists('keuangans', 'keuangans_branch_id_index')) {
                    $table->index('branch_id');
                }
                if (!$this->indexExists('keuangans', 'keuangans_jenis_index')) {
                    $table->index('jenis');
                }
                if (!$this->indexExists('keuangans', 'keuangans_branch_id_tanggal_index')) {
                    $table->index(['branch_id', 'tanggal']);
                }
                if (!$this->indexExists('keuangans', 'keuangans_created_at_index')) {
                    $table->index('created_at');
                }
            });
        }

        // Branch table indexes
        if (Schema::hasTable('branches')) {
            Schema::table('branches', function (Blueprint $table) {
                if (!$this->indexExists('branches', 'branches_is_active_index')) {
                    $table->index('is_active');
                }
            });
        }

        // Pasien table indexes
        if (Schema::hasTable('pasiens')) {
            Schema::table('pasiens', function (Blueprint $table) {
                if (!$this->indexExists('pasiens', 'pasiens_service_type_index')) {
                    $table->index('service_type');
                }
                if (!$this->indexExists('pasiens', 'pasiens_created_at_index')) {
                    $table->index('created_at');
                }
            });
        }
    }

    /**
     * Helper method to check if index exists
     */
    private function indexExists($table, $indexName)
    {
        return \DB::select("SELECT 1 FROM information_schema.STATISTICS WHERE table_name = ? AND index_name = ?", [$table, $indexName]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safely drop indexes if they exist
        // Since we're only adding performance indexes, we can safely skip the rollback
        // If needed to rollback, drop manually: ALTER TABLE table_name DROP INDEX index_name;
    }
};
