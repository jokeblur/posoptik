<?php

namespace App\Services;

use App\Models\Frame;
use App\Models\Pasien;
use Illuminate\Support\Facades\Log;

class BpjsPricingService
{
    // Harga default BPJS
    const BPJS_I_PRICE = 330000;
    const BPJS_II_PRICE = 220000;
    const BPJS_III_PRICE = 165000;

    /**
     * Hitung harga frame berdasarkan jenis layanan pasien dan jenis frame
     */
    public function calculateFramePrice(Pasien $pasien, Frame $frame): array
    {
        $pasienServiceType = $pasien->service_type;
        $frameType = $frame->jenis_frame;
        
        Log::info('BPJS Pricing Calculation Start:', [
            'pasien_service_type' => $pasienServiceType,
            'frame_type' => $frameType,
            'frame_price' => $frame->harga_jual_frame
        ]);
        
        // Jika frame sama dengan layanan BPJS, harga default dan status lunas
        if ($this->isMatchingService($pasienServiceType, $frameType)) {
            $result = [
                'price' => $this->getDefaultPrice($pasienServiceType),
                'additional_cost' => 0,
                'status' => 'Lunas',
                'reason' => 'Frame sesuai dengan jenis layanan BPJS - harga default'
            ];
            Log::info('BPJS Pricing - Matching Service (Lunas):', $result);
            return $result;
        }

        // Jika jenis frame berbeda dengan layanan BPJS
        if (in_array($pasienServiceType, ['BPJS I', 'BPJS II', 'BPJS III'])) {
            $defaultPrice = $this->getDefaultPrice($pasienServiceType);
            $framePrice = $frame->harga_jual_frame ?? 0;
            
            // Harga yang dibayar = harga default BPJS
            // Kekurangan = harga frame - harga default BPJS
            $additionalCost = max(0, $framePrice - $defaultPrice);
            
            $result = [
                'price' => $defaultPrice, // Jumlah bayar = harga default
                'additional_cost' => $additionalCost, // Biaya tambahan = kekurangan
                'status' => 'Naik Kelas',
                'reason' => $pasienServiceType . ' memilih frame ' . $frameType . ' - naik kelas (biaya tambahan: ' . number_format($additionalCost) . ')'
            ];
            
            Log::info('BPJS Pricing - Different Frame (Naik Kelas):', $result);
            return $result;
        }

        // Default: harga frame normal
        $result = [
            'price' => $frame->harga_jual_frame ?? 0,
            'additional_cost' => 0,
            'status' => 'Normal',
            'reason' => 'Harga frame normal'
        ];
        Log::info('BPJS Pricing - Default:', $result);
        return $result;
    }

    /**
     * Cek apakah jenis layanan pasien cocok dengan jenis frame
     */
    private function isMatchingService(string $serviceType, string $frameType): bool
    {
        return $serviceType === $frameType;
    }

    /**
     * Dapatkan harga default berdasarkan jenis layanan
     */
    public function getDefaultPrice(string $serviceType): int
    {
        switch ($serviceType) {
            case 'BPJS I':
                return self::BPJS_I_PRICE;
            case 'BPJS II':
                return self::BPJS_II_PRICE;
            case 'BPJS III':
                return self::BPJS_III_PRICE;
            default:
                return 0;
        }
    }

    /**
     * Hitung total harga untuk transaksi
     */
    public function calculateTotalPrice(Pasien $pasien, array $items): array
    {
        $totalPrice = 0;
        $totalAdditionalCost = 0;
        $itemDetails = [];
        $additionalCosts = [];
        $transactionStatus = 'Normal';

        foreach ($items as $item) {
            if ($item['type'] === 'frame') {
                $frame = Frame::find($item['id']);
                if ($frame) {
                    $pricing = $this->calculateFramePrice($pasien, $frame);
                    $itemPrice = $pricing['price'] * $item['quantity'];
                    $totalPrice += $itemPrice;
                    $totalAdditionalCost += $pricing['additional_cost'] * $item['quantity'];
                    
                    // Update status transaksi jika ada naik kelas
                    if ($pricing['status'] === 'Naik Kelas') {
                        $transactionStatus = 'Naik Kelas';
                    }
                    
                    $itemDetails[] = [
                        'item' => $frame,
                        'price' => $pricing['price'],
                        'quantity' => $item['quantity'],
                        'subtotal' => $itemPrice,
                        'additional_cost' => $pricing['additional_cost'],
                        'status' => $pricing['status'],
                        'reason' => $pricing['reason']
                    ];

                    if ($pricing['additional_cost'] > 0) {
                        $additionalCosts[] = [
                            'item' => $frame->merk_frame,
                            'additional_cost' => $pricing['additional_cost'],
                            'reason' => $pricing['reason']
                        ];
                    }
                }
            } else {
                // Untuk lensa dan aksesoris, gunakan harga normal
                $itemModel = null;
                if ($item['type'] === 'lensa') {
                    $itemModel = \App\Models\Lensa::find($item['id']);
                } elseif ($item['type'] === 'aksesoris') {
                    $itemModel = \App\Models\Aksesoris::find($item['id']);
                }

                if ($itemModel) {
                    $itemPrice = ($itemModel->harga_jual_lensa ?? $itemModel->harga_jual ?? 0) * $item['quantity'];
                    $totalPrice += $itemPrice;
                    
                    $itemDetails[] = [
                        'item' => $itemModel,
                        'price' => $itemModel->harga_jual_lensa ?? $itemModel->harga_jual ?? 0,
                        'quantity' => $item['quantity'],
                        'subtotal' => $itemPrice,
                        'additional_cost' => 0,
                        'status' => 'Normal',
                        'reason' => 'Harga normal'
                    ];
                }
            }
        }

        return [
            'total_price' => $totalPrice,
            'total_additional_cost' => $totalAdditionalCost,
            'transaction_status' => $transactionStatus,
            'item_details' => $itemDetails,
            'additional_costs' => $additionalCosts
        ];
    }
} 