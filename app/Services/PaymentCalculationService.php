<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\Pembayaran;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;

/**
 * Service class for handling payment calculations
 * Handles DP and Pelunasan calculations for Nasi Box and Catering orders
 */
class PaymentCalculationService
{
    /**
     * Payment types constants
     */
    public const PAYMENT_TYPE_DP = 'dp';
    public const PAYMENT_TYPE_PELUNASAN = 'pelunasan';
    
    /**
     * DP percentages by order type
     */
    public const DP_PERCENTAGE_NASI_BOX = 25;
    public const DP_PERCENTAGE_CATERING = 50;
    public const DP_PERCENTAGE_DINE_IN = 100;
    
    /**
     * Order type IDs (based on jenis_pesanan_id)
     */
    public const JENIS_DINE_IN = 1;
    public const JENIS_CATERING = 2;
    public const JENIS_NASI_BOX = 3;
    
    /**
     * Calculate DP amount based on order type
     * 
     * @param Pesanan $order
     * @return float
     * @throws InvalidArgumentException
     */
    public function calculateDPAmount(Pesanan $order): float
    {
        $this->validateOrder($order);
        
        $totalAmount = (float) $order->total_tagihan;
        $percentage = $this->getDPPercentage($order->jenis_pesanan_id);
        
        return round($totalAmount * ($percentage / 100), 2);
    }
    
    /**
     * Calculate pelunasan amount (remaining payment after DP deduction)
     * 
     * @param Pesanan $order
     * @return float
     * @throws InvalidArgumentException
     */
    public function calculatePelunasanAmount(Pesanan $order): float
    {
        $this->validateOrder($order);
        
        $totalAmount = (float) $order->total_tagihan;
        $paidAmount = $this->getTotalPaidAmount($order);
        
        $remainingAmount = $totalAmount - $paidAmount;
        
        return max(0, round($remainingAmount, 2));
    }
    
    /**
     * Get the total amount already paid for an order
     * 
     * @param Pesanan $order
     * @return float
     */
    public function getTotalPaidAmount(Pesanan $order): float
    {
        $confirmedPayments = $this->getConfirmedPayments($order);
        
        return round($confirmedPayments->sum('jumlah_dibayar'), 2);
    }
    
    /**
     * Validate if payment amount is correct for the given payment type
     * 
     * @param Pesanan $order
     * @param float $amount
     * @param string $paymentType
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validatePaymentAmount(Pesanan $order, float $amount, string $paymentType): bool
    {
        $this->validateOrder($order);
        $this->validatePaymentType($paymentType);
        
        $expectedAmount = match ($paymentType) {
            self::PAYMENT_TYPE_DP => $this->calculateDPAmount($order),
            self::PAYMENT_TYPE_PELUNASAN => $this->calculatePelunasanAmount($order),
            default => throw new InvalidArgumentException("Invalid payment type: {$paymentType}")
        };
        
        // Allow small rounding differences (1 cent tolerance)
        return abs($amount - $expectedAmount) <= 0.01;
    }
    
    /**
     * Check if payment is completed for the order
     * 
     * @param Pesanan $order
     * @return bool
     */
    public function isPaymentCompleted(Pesanan $order): bool
    {
        $totalAmount = (float) $order->total_tagihan;
        $paidAmount = $this->getTotalPaidAmount($order);
        
        // Allow small rounding differences
        return ($totalAmount - $paidAmount) <= 0.01;
    }
    
    /**
     * Check if DP payment has been made
     * 
     * @param Pesanan $order
     * @return bool
     */
    public function isDPPaid(Pesanan $order): bool
    {
        $dpAmount = $this->calculateDPAmount($order);
        $paidAmount = $this->getTotalPaidAmount($order);
        
        return $paidAmount >= $dpAmount;
    }
    
    /**
     * Get payment summary for an order
     * 
     * @param Pesanan $order
     * @return array
     */
    public function getPaymentSummary(Pesanan $order): array
    {
        $this->validateOrder($order);
        
        $totalAmount = (float) $order->total_tagihan;
        $dpAmount = $this->calculateDPAmount($order);
        $paidAmount = $this->getTotalPaidAmount($order);
        $pelunasanAmount = $this->calculatePelunasanAmount($order);
        
        return [
            'total_amount' => $totalAmount,
            'dp_amount' => $dpAmount,
            'dp_percentage' => $this->getDPPercentage($order->jenis_pesanan_id),
            'paid_amount' => $paidAmount,
            'pelunasan_amount' => $pelunasanAmount,
            'remaining_amount' => $pelunasanAmount,
            'is_dp_paid' => $this->isDPPaid($order),
            'is_completed' => $this->isPaymentCompleted($order),
            'payment_status' => $this->getPaymentStatus($order),
            'order_type' => $this->getOrderTypeName($order->jenis_pesanan_id)
        ];
    }
    
    /**
     * Get the next required payment amount and type
     * 
     * @param Pesanan $order
     * @return array|null
     */
    public function getNextPayment(Pesanan $order): ?array
    {
        if ($this->isPaymentCompleted($order)) {
            return null;
        }
        
        $isDPPaid = $this->isDPPaid($order);
        
        if (!$isDPPaid) {
            return [
                'type' => self::PAYMENT_TYPE_DP,
                'amount' => $this->calculateDPAmount($order),
                'description' => 'Uang Muka (DP)'
            ];
        }
        
        $pelunasanAmount = $this->calculatePelunasanAmount($order);
        
        if ($pelunasanAmount > 0) {
            return [
                'type' => self::PAYMENT_TYPE_PELUNASAN,
                'amount' => $pelunasanAmount,
                'description' => 'Pelunasan'
            ];
        }
        
        return null;
    }
    
    /**
     * Verify if a payment can be processed
     * 
     * @param Pesanan $order
     * @param float $amount
     * @param string $paymentType
     * @return array
     */
    public function verifyPayment(Pesanan $order, float $amount, string $paymentType): array
    {
        try {
            $this->validateOrder($order);
            $this->validatePaymentType($paymentType);
            
            if ($amount <= 0) {
                return [
                    'valid' => false,
                    'error' => 'Payment amount must be greater than zero'
                ];
            }
            
            if ($this->isPaymentCompleted($order)) {
                return [
                    'valid' => false,
                    'error' => 'Order is already fully paid'
                ];
            }
            
            // Check if DP is required first
            if ($paymentType === self::PAYMENT_TYPE_PELUNASAN && !$this->isDPPaid($order)) {
                return [
                    'valid' => false,
                    'error' => 'DP payment is required before pelunasan'
                ];
            }
            
            // Validate amount
            if (!$this->validatePaymentAmount($order, $amount, $paymentType)) {
                $expectedAmount = match ($paymentType) {
                    self::PAYMENT_TYPE_DP => $this->calculateDPAmount($order),
                    self::PAYMENT_TYPE_PELUNASAN => $this->calculatePelunasanAmount($order),
                };
                
                return [
                    'valid' => false,
                    'error' => "Invalid payment amount. Expected: Rp " . number_format($expectedAmount, 0, ',', '.'),
                    'expected_amount' => $expectedAmount
                ];
            }
            
            return [
                'valid' => true,
                'message' => 'Payment verification successful'
            ];
            
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get DP percentage based on order type
     * 
     * @param int $jenisId
     * @return int
     * @throws InvalidArgumentException
     */
    private function getDPPercentage(int $jenisId): int
    {
        return match ($jenisId) {
            self::JENIS_DINE_IN => self::DP_PERCENTAGE_DINE_IN,
            self::JENIS_CATERING => self::DP_PERCENTAGE_CATERING,
            self::JENIS_NASI_BOX => self::DP_PERCENTAGE_NASI_BOX,
            default => throw new InvalidArgumentException("Unknown order type ID: {$jenisId}")
        };
    }
    
    /**
     * Get confirmed payments for an order
     * 
     * @param Pesanan $order
     * @return Collection
     */
    private function getConfirmedPayments(Pesanan $order): Collection
    {
        return $order->pembayaran()
            ->whereHas('status_pembayaran', function ($query) {
                $query->whereIn('kode_status', ['CONFIRMED', 'SETTLEMENT', 'SUCCESS']);
            })
            ->get();
    }
    
    /**
     * Get payment status description
     * 
     * @param Pesanan $order
     * @return string
     */
    private function getPaymentStatus(Pesanan $order): string
    {
        if ($this->isPaymentCompleted($order)) {
            return 'Lunas';
        }
        
        if ($this->isDPPaid($order)) {
            return 'DP Dibayar';
        }
        
        return 'Belum Dibayar';
    }
    
    /**
     * Get order type name
     * 
     * @param int $jenisId
     * @return string
     */
    private function getOrderTypeName(int $jenisId): string
    {
        return match ($jenisId) {
            self::JENIS_DINE_IN => 'Dine In / Takeaway',
            self::JENIS_CATERING => 'Catering',
            self::JENIS_NASI_BOX => 'Nasi Box',
            default => 'Unknown'
        };
    }
    
    /**
     * Validate order object
     * 
     * @param Pesanan $order
     * @throws InvalidArgumentException
     */
    private function validateOrder(Pesanan $order): void
    {
        if (!$order->exists) {
            throw new InvalidArgumentException('Order does not exist');
        }
        
        if (empty($order->total_tagihan) || $order->total_tagihan <= 0) {
            throw new InvalidArgumentException('Order total amount is invalid');
        }
        
        if (empty($order->jenis_pesanan_id)) {
            throw new InvalidArgumentException('Order type is required');
        }
    }
    
    /**
     * Validate payment type
     * 
     * @param string $paymentType
     * @throws InvalidArgumentException
     */
    private function validatePaymentType(string $paymentType): void
    {
        $validTypes = [self::PAYMENT_TYPE_DP, self::PAYMENT_TYPE_PELUNASAN];
        
        if (!in_array($paymentType, $validTypes)) {
            throw new InvalidArgumentException("Invalid payment type. Must be one of: " . implode(', ', $validTypes));
        }
    }
}