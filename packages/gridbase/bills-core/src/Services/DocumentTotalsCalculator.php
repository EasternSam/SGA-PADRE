<?php

namespace Gridbase\BillsCore\Services;

/**
 * Gridbase Bills Core — DocumentTotalsCalculator
 * Shared tax & totals calculation engine (ITBIS 18% / 16% / 0% Exento, prorated discounts, RST mode).
 */
class DocumentTotalsCalculator
{
    /**
     * Calculates line amounts, prorated discounts, taxable/exempt bases, and tax amounts.
     *
     * @param array $rawItems
     * @param string|null $discountType 'percentage' or 'amount'
     * @param float|int|null $discountValue
     * @param float|int|null $defaultTaxRate
     * @param bool $forceExempt
     * @return array
     */
    public static function calculate(
        array $rawItems,
        ?string $discountType = 'percentage',
        $discountValue = 0.0,
        $defaultTaxRate = 18.0,
        bool $forceExempt = false
    ): array {
        $processed = [];
        $subtotal = 0.0;
        $discountType = $discountType ?: 'percentage';
        $discountValue = (isset($discountValue) && $discountValue !== '') ? max(0, (float) $discountValue) : 0.0;
        $defaultTaxRate = $forceExempt
            ? 0.0
            : ((isset($defaultTaxRate) && $defaultTaxRate !== '') ? max(0, (float) $defaultTaxRate) : 18.0);

        foreach ($rawItems as $idx => $it) {
            $qty = isset($it['quantity']) ? (float) $it['quantity'] : 1.0;
            $price = isset($it['unit_price']) ? (float) $it['unit_price'] : 0.0;
            $lineAmount = round($qty * $price, 2);
            $subtotal += $lineAmount;

            $isExempt = false;
            $lineTaxRate = null;

            if ($forceExempt) {
                $isExempt = true;
                $lineTaxRate = 0.0;
            } elseif (array_key_exists('is_exempt', $it) && $it['is_exempt'] !== null && $it['is_exempt'] !== '') {
                $isExempt = filter_var($it['is_exempt'], FILTER_VALIDATE_BOOLEAN);
            } elseif (array_key_exists('tax_rate', $it) && $it['tax_rate'] !== null && $it['tax_rate'] !== '') {
                $rateVal = (float) $it['tax_rate'];
                if ($rateVal == 0.0) {
                    $isExempt = true;
                    $lineTaxRate = 0.0;
                } else {
                    $isExempt = false;
                    $lineTaxRate = $rateVal;
                }
            } else {
                if ($defaultTaxRate == 0.0) {
                    $isExempt = true;
                    $lineTaxRate = 0.0;
                } else {
                    $isExempt = false;
                    $lineTaxRate = $defaultTaxRate;
                }
            }

            if ($isExempt) {
                $lineTaxRate = 0.0;
            } elseif ($lineTaxRate === null) {
                $lineTaxRate = isset($it['tax_rate']) ? (float) $it['tax_rate'] : $defaultTaxRate;
                if ($lineTaxRate == 0.0) {
                    $isExempt = true;
                }
            }

            $itemCopy = $it;
            $itemCopy['quantity'] = $qty;
            $itemCopy['unit_price'] = $price;
            $itemCopy['amount'] = $lineAmount;
            $itemCopy['sort_order'] = $idx;
            $itemCopy['is_exempt'] = $isExempt;
            $itemCopy['tax_rate'] = $lineTaxRate;

            $processed[] = $itemCopy;
        }

        if ($discountType === 'percentage') {
            $discountAmount = round($subtotal * ($discountValue / 100), 2);
        } else {
            $discountAmount = round(min($subtotal, $discountValue), 2);
        }

        $taxableAmount = 0.0;
        $exemptAmount = 0.0;
        $totalTaxAmount = 0.0;

        foreach ($processed as &$pItem) {
            $lineAmount = $pItem['amount'];
            $lineDiscount = ($subtotal > 0 && $discountAmount > 0)
                ? ($discountAmount * ($lineAmount / $subtotal))
                : 0.0;
            $netLineAmount = max(0.0, $lineAmount - $lineDiscount);

            if ($pItem['is_exempt']) {
                $pItem['tax_amount'] = 0.0;
                $exemptAmount += $netLineAmount;
            } else {
                $lineTax = round($netLineAmount * ($pItem['tax_rate'] / 100), 2);
                $pItem['tax_amount'] = $lineTax;
                $taxableAmount += $netLineAmount;
                $totalTaxAmount += $lineTax;
            }
        }
        unset($pItem);

        $subtotal = round($subtotal, 2);
        $taxableAmount = round($taxableAmount, 2);
        $exemptAmount = round($exemptAmount, 2);
        $totalTaxAmount = round($totalTaxAmount, 2);
        $total = round($taxableAmount + $exemptAmount + $totalTaxAmount, 2);

        $effectiveTaxRate = $defaultTaxRate;
        if ($taxableAmount == 0.0 && $exemptAmount > 0) {
            $effectiveTaxRate = 0.0;
        } elseif ($effectiveTaxRate == 0.0) {
            foreach ($processed as $pi) {
                if (!$pi['is_exempt'] && $pi['tax_rate'] > 0) {
                    $effectiveTaxRate = $pi['tax_rate'];
                    break;
                }
            }
        }

        return [
            'subtotal' => $subtotal,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_amount' => $discountAmount,
            'taxable_amount' => $taxableAmount,
            'exempt_amount' => $exemptAmount,
            'tax_rate' => $effectiveTaxRate,
            'tax_amount' => $totalTaxAmount,
            'total' => $total,
            'items' => $processed,
        ];
    }
}
