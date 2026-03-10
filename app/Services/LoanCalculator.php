<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Client;
use App\Models\Payment;

/**
 * Loan Calculator Service
 * Handles all loan eligibility and calculation logic per supervisor requirements
 */
class LoanCalculator
{
    /**
     * Loan tier percentages based on premiums paid
     * 60% premium paid = 30% of contract price loanable
     * 80% premium paid = 40% of contract price loanable
     * 100% premium paid = 45% of contract price loanable
     */
    const TIER_PERCENTAGES = [
        60 => 30,
        80 => 40,
        100 => 45
    ];

    const PROCESSING_FEE_PERCENT = 10;
    const INTEREST_RATE_PERCENT = 1.25;

    /**
     * Calculate loanable amount based on contract price and premiums paid percentage
     *
     * @param float $contractPrice Total contract price
     * @param int $premiumPaidPercent Percentage of premiums paid (60, 80, or 100)
     * @return float Loanable amount
     */
    public function calculateLoanableAmount(float $contractPrice, int $premiumPaidPercent): float
    {
        $tierPercent = 0;
        if (isset(self::TIER_PERCENTAGES[$premiumPaidPercent])) {
            $tierPercent = self::TIER_PERCENTAGES[$premiumPaidPercent];
        }
        $multiplier = $tierPercent / 100;
        $result = $contractPrice * $multiplier;
        return round($result, 2);
    }

    /**
     * Calculate processing fee (10% of loan amount)
     *
     * @param float $loanAmount Gross loan amount
     * @return float Processing fee
     */
    public function calculateProcessingFee(float $loanAmount): float
    {
        return round($loanAmount * (self::PROCESSING_FEE_PERCENT / 100), 2);
    }

    /**
     * Calculate net loan amount after processing fee
     *
     * @param float $loanAmount Gross loan amount
     * @return float Net amount (what client receives)
     */
    public function calculateNetLoanAmount(float $loanAmount): float
    {
        $fee = $this->calculateProcessingFee($loanAmount);
        return round($loanAmount - $fee, 2);
    }

    /**
     * Calculate total interest over loan term
     * Formula: principal × 1.25% × termMonths
     *
     * @param float $principal Loan principal
     * @param int $termMonths Number of months (2-12)
     * @return float Total interest
     */
    public function calculateInterest(float $principal, int $termMonths): float
    {
        return round($principal * (self::INTEREST_RATE_PERCENT / 100) * $termMonths, 2);
    }

    /**
     * Calculate total amount to repay (principal + interest)
     *
     * @param float $principal Loan principal
     * @param int $termMonths Number of months
     * @return float Total repayable
     */
    public function calculateTotalRepayable(float $principal, int $termMonths): float
    {
        $interest = $this->calculateInterest($principal, $termMonths);
        return round($principal + $interest, 2);
    }

    /**
     * Calculate monthly loan payment amount
     *
     * @param float $totalRepayable Total amount to repay
     * @param int $termMonths Number of months
     * @return float Monthly payment
     */
    public function calculateMonthlyLoanPayment(float $totalRepayable, int $termMonths): float
    {
        return round($totalRepayable / $termMonths, 2);
    }

    /**
     * Calculate total monthly due (loan payment + contract premium)
     *
     * @param float $monthlyLoanPayment Loan portion
     * @param float $monthlyPremium Contract premium amount
     * @return float Total monthly due
     */
    public function calculateMonthlyDue(float $monthlyLoanPayment, float $monthlyPremium): float
    {
        return round($monthlyLoanPayment + $monthlyPremium, 2);
    }

    /**
     * Get eligible tiers based on total premiums paid vs contract price
     *
     * @param float $totalPremiumsPaid Total premiums paid by client
     * @param float $contractPrice Contract price
     * @return array Eligible tier percentages [60, 80, 100] or subset
     */
    public function getEligibleTiers(float $totalPremiumsPaid, float $contractPrice): array
    {
        if ($contractPrice <= 0) {
            return [];
        }

        $percentPaid = ($totalPremiumsPaid / $contractPrice) * 100;
        $eligible = [];

        if ($percentPaid >= 60) {
            $eligible[] = 60;
        }
        if ($percentPaid >= 80) {
            $eligible[] = 80;
        }
        if ($percentPaid >= 100) {
            $eligible[] = 100;
        }

        return $eligible;
    }

    /**
     * Get the best tier available for the client
     *
     * @param float $totalPremiumsPaid Total premiums paid
     * @param float $contractPrice Contract price
     * @return int|null Highest eligible tier (60, 80, 100) or null
     */
    public function getBestTier(float $totalPremiumsPaid, float $contractPrice): ?int
    {
        $eligible = $this->getEligibleTiers($totalPremiumsPaid, $contractPrice);
        return empty($eligible) ? null : max($eligible);
    }

    /**
     * Calculate complete loan details for a contract
     *
     * @param Contract|object $contract Contract model or object with packageprice/paymenttermamount
     * @param float $totalPremiumsPaid
     * @param int $termMonths
     * @return array Complete loan calculation breakdown
     */
    public function calculateLoanDetails($contract, float $totalPremiumsPaid, int $termMonths = 12): array
    {
        $contractPrice = $contract->packageprice ?? 0;
        $monthlyPremium = $contract->paymenttermamount ?? 0;

        $bestTier = $this->getBestTier($totalPremiumsPaid, $contractPrice);

        if (!$bestTier) {
            return [
                'eligible' => false,
                'message' => 'Not eligible for loan. Minimum 60% premiums paid required.'
            ];
        }

        $loanableAmount = $this->calculateLoanableAmount($contractPrice, $bestTier);
        $processingFee = $this->calculateProcessingFee($loanableAmount);
        $netLoanAmount = $this->calculateNetLoanAmount($loanableAmount);
        $totalInterest = $this->calculateInterest($loanableAmount, $termMonths);
        $totalRepayable = $this->calculateTotalRepayable($loanableAmount, $termMonths);
        $monthlyLoanPayment = $this->calculateMonthlyLoanPayment($totalRepayable, $termMonths);
        // Monthly due is loan payment only (not including contract premium)
        $monthlyDue = $monthlyLoanPayment;

        return [
            'eligible' => true,
            'tier' => $bestTier,
            'premium_paid_percent' => $bestTier,
            'contract_price' => $contractPrice,
            'total_premiums_paid' => $totalPremiumsPaid,
            'loanable_amount' => $loanableAmount,
            'processing_fee' => $processingFee,
            'processing_fee_percent' => self::PROCESSING_FEE_PERCENT,
            'net_loan_amount' => $netLoanAmount,
            'term_months' => $termMonths,
            'interest_rate' => self::INTEREST_RATE_PERCENT,
            'total_interest' => $totalInterest,
            'total_repayable' => $totalRepayable,
            'monthly_loan_payment' => $monthlyLoanPayment,
            'monthly_contract_premium' => $monthlyPremium,
            'monthly_total_due' => $monthlyDue
        ];
    }

    /**
     * Get available term options (2-12 months)
     *
     * @return array Term options with labels
     */
    public function getTermOptions(): array
    {
        return [
            2 => '2 months',
            3 => '3 months',
            4 => '4 months',
            5 => '5 months',
            6 => '6 months',
            7 => '7 months',
            8 => '8 months',
            9 => '9 months',
            10 => '10 months',
            11 => '11 months',
            12 => '12 months'
        ];
    }
}
