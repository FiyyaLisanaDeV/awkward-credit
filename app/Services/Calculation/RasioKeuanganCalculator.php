<?php

declare(strict_types=1);

namespace App\Services\Calculation;

use App\Support\Rounding;

final class RasioKeuanganCalculator
{
    public function currentRatio(float $aktivaLancar, float $hutangPendek): float
    {
        return $this->bagi($aktivaLancar, $hutangPendek);
    }

    public function quickRatio(float $aktivaLancar, float $persediaan, float $hutangPendek): float
    {
        return $this->bagi($aktivaLancar - $persediaan, $hutangPendek);
    }

    public function cashRatio(float $kas, float $hutangPendek): float
    {
        return $this->bagi($kas, $hutangPendek);
    }

    public function debtToAsset(float $totalHutang, float $totalAktiva): float
    {
        return $this->bagi($totalHutang, $totalAktiva);
    }

    public function debtToEquity(float $totalHutang, float $ekuitas): float
    {
        return $this->bagi($totalHutang, $ekuitas);
    }

    public function equityToAsset(float $ekuitas, float $totalAktiva): float
    {
        return $this->bagi($ekuitas, $totalAktiva);
    }

    public function netProfitMargin(float $labaBersih, float $pendapatan): float
    {
        return $this->bagi($labaBersih, $pendapatan);
    }

    public function roa(float $labaBersih, float $totalAktiva): float
    {
        return $this->bagi($labaBersih, $totalAktiva);
    }

    public function roe(float $labaBersih, float $ekuitas): float
    {
        return $this->bagi($labaBersih, $ekuitas);
    }

    /**
     * @param  array<string, float>  $data
     * @return array<string, float>
     */
    public function semua(array $data): array
    {
        return [
            'current_ratio' => $this->currentRatio($data['aktiva_lancar'], $data['hutang_pendek']),
            'quick_ratio' => $this->quickRatio($data['aktiva_lancar'], $data['persediaan'], $data['hutang_pendek']),
            'cash_ratio' => $this->cashRatio($data['kas'], $data['hutang_pendek']),
            'debt_to_asset' => $this->debtToAsset($data['total_hutang'], $data['total_aktiva']),
            'debt_to_equity' => $this->debtToEquity($data['total_hutang'], $data['ekuitas']),
            'equity_to_asset' => $this->equityToAsset($data['ekuitas'], $data['total_aktiva']),
            'net_profit_margin' => $this->netProfitMargin($data['laba_bersih'], $data['pendapatan']),
            'roa_annualized' => $this->roa($data['laba_bersih'], $data['total_aktiva']),
            'roe_annualized' => $this->roe($data['laba_bersih'], $data['ekuitas']),
        ];
    }

    private function bagi(float $pembilang, float $penyebut): float
    {
        if ($penyebut <= 0) {
            return 0.0;
        }

        return Rounding::ratio($pembilang / $penyebut);
    }
}
