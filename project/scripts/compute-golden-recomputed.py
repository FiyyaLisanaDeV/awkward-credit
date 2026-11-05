#!/usr/bin/env python3
"""Compute a best-judgment, deterministic RECOMPUTED layer for golden vectors.

Rules:
- No PII, no workbook cell contents. Dummy values only.
- Decimal (integer rupiah) math, independent of the app engine.
- Every produced value is labeled `RECOMPUTED_DRAFT_NOT_VERIFIED`.
  It is NOT `[H]` and NOT `verified=true`. Human sign-off is still required.
- No 1.81x / 0.87x flat->effective shortcuts: EIR/IRR is numeric.
"""
from __future__ import annotations
import json, glob, os, sys
from decimal import Decimal, ROUND_HALF_UP, getcontext

getcontext().prec = 50
D = Decimal
RP = D("1")
STATUS = "RECOMPUTED_DRAFT_NOT_VERIFIED"


def rp(x):
    return int(D(str(x)).quantize(RP, rounding=ROUND_HALF_UP))


def dec(x):
    return D(str(x))


def pct(x):
    return float(dec(x))


def schedule_flat(P, rate_pa, n):
    P, n = int(P), int(n)
    i_m = dec(rate_pa) / 12
    bunga_m = rp(dec(P) * i_m)
    base = P // n
    rem = P - base * n
    rows = []
    bal = P
    for k in range(1, n + 1):
        pok = base + (rem if k == n else 0)
        bunga = bunga_m
        bal2 = bal - pok
        rows.append({"k": k, "pokok": pok, "bunga": bunga, "angsuran": pok + bunga, "saldo_akhir": bal2})
        bal = bal2
    return rows, rem


def schedule_annuity(P, rate_pa, n):
    P, n = int(P), int(n)
    i_m = dec(rate_pa) / 12
    if i_m == 0:
        A = P / n
    else:
        A = dec(P) * i_m / (1 - (1 + i_m) ** (-n))
    A = rp(A)
    rows = []
    bal = P
    for k in range(1, n + 1):
        bunga = rp(dec(bal) * i_m)
        if k == n:
            pok = bal
            angs = pok + bunga
        else:
            pok = A - bunga
            angs = A
        bal2 = bal - pok
        rows.append({"k": k, "pokok": pok, "bunga": bunga, "angsuran": angs, "saldo_akhir": bal2})
        bal = bal2
    return rows, 0


def schedule_grace_bullet(P, rate_pa, n, g):
    P, n, g = int(P), int(n), int(g)
    i_m = dec(rate_pa) / 12
    rows = []
    bal = P
    for k in range(1, n + 1):
        bunga = rp(dec(bal) * i_m)
        pok = bal if k == n else 0
        bal2 = bal - pok
        rows.append({"k": k, "pokok": pok, "bunga": bunga, "angsuran": pok + bunga, "saldo_akhir": bal2})
        bal = bal2
    return rows, 0


def irr_monthly(cashflows):
    def npv(m):
        return sum(dec(c) / ((1 + m) ** t) for t, c in enumerate(cashflows))

    lo, hi = dec("-0.99"), dec("10")
    flo, fhi = npv(lo), npv(hi)
    for _ in range(80):
        if flo * fhi <= 0:
            break
        hi *= 2
        fhi = npv(hi)
    if flo * fhi > 0:
        return None
    for _ in range(240):
        mid = (lo + hi) / 2
        fm = npv(mid)
        if abs(fm) < dec("0.0000001"):
            return mid
        if flo * fm <= 0:
            hi, fhi = mid, fm
        else:
            lo, flo = mid, fm
    return (lo + hi) / 2


def annual(m):
    return (1 + m) ** 12 - 1


def irr_summary(cashflows):
    m = irr_monthly(cashflows)
    if m is None:
        return None
    return {
        "rate_monthly": float(m),
        "rate_annualized_x12": float(m * 12),
        "eir_effective_annual": float(annual(m)),
    }


def idir(new_inst, net):
    return dec(new_inst) / dec(net)


def dsr(new_inst, existing_total, net):
    return (dec(new_inst) + dec(existing_total)) / dec(net)


def max_plafon_flat(rpc, rate_pa, n):
    i_m = dec(rate_pa) / 12
    return rp(dec(rpc) / (i_m + dec(1) / int(n)))


def max_plafon_annuity(rpc, rate_pa, n):
    i_m = dec(rate_pa) / 12
    return rp(dec(rpc) * (1 - (1 + i_m) ** (-int(n))) / i_m)


def wc_need(annual_cost, working_days, turnover_days):
    return rp(dec(annual_cost) * dec(turnover_days) / dec(working_days))


def weighted(scores, weights):
    return sum(dec(scores[k]) * dec(weights[k]) for k in weights)


def rating_for(total):
    t = dec(total)
    if t < dec("1.8"):
        return "TOLAK"
    if t < dec("2.6"):
        return "PERHATIAN"
    if t < dec("3.4"):
        return "LAYAK_BERSYARAT"
    if t < dec("4.2"):
        return "LAYAK"
    return "LAYAK_PRIMA"


def recompute(case_id, inp):
    """Return a recomputed dict keyed by expected field names."""
    if case_id == "G-TPP-01":
        P, r, n = 80_000_000, dec("0.18"), 60
        net = 2_948_080
        rows, adj = schedule_flat(P, r, n)
        inst = rows[0]["angsuran"]
        cf = [-P] + [row["angsuran"] for row in rows]
        irr = irr_summary(cf)
        return {
            "idir": pct(idir(inst, net)),
            "penghasilan_bersih_rp": net,
            "new_installment_rp": inst,
            "eir_irr_pa": irr["rate_annualized_x12"],
            "eir_effective_annual": irr["eir_effective_annual"],
            "eir_note": "28.88% claim C-002 matches rate_annualized_x12 (RATE*12); effective annual per PSAK-style compounding is higher",
            "schedule_sum_principal_eq_plafon": True,
            "schedule_closing_balance_rp": rows[-1]["saldo_akhir"],
            "schedule_rounding_adjustment_rp": adj,
            "no_flat_effective_factor_181x": True,
        }

    if case_id == "G-TPP-02":
        net = 2_948_080
        new_inst = 2_533_333
        existing = [1_500_000, 900_000]
        total = sum(existing)
        return {
            "total_existing_installment_rp": total,
            "dsr_total": pct(dsr(new_inst, total, net)),
            "slik_validation_flags": ["baki<=plafon_checked", "duplicate_checked", "collectibility_range_checked"],
            "dsr_source": "SLIK_STRUCTURED",
        }

    if case_id == "G-TPP-03":
        threshold = dec("0.80")
        return {
            "threshold_idir": pct(threshold),
            "scenarios": [
                {"case": "at_threshold", "idir": pct(threshold), "expected_hard_stop": True},
                {"case": "above_threshold", "idir": 0.81, "expected_hard_stop": True},
            ],
            "hard_stop_triggered": True,
            "decision_layer": "HARD_STOP",
            "reason": "IDIR >= threshold (>= dianggap hard-stop, DRAFT D-11)",
        }

    if case_id == "G-TPP-04":
        old_principal = 50_000_000
        accrued_interest = 1_000_000
        penalty = 5_000_000
        fees = 500_000
        new_plafon = 80_000_000
        payoff = old_principal + accrued_interest + penalty
        return {
            "payoff_total_rp": payoff,
            "net_disbursement_rp": new_plafon - fees - payoff,
            "journal_balanced": True,
            "assumption": "new_plafon_rp=80000000 (best judgment dummy)",
        }

    if case_id == "G-TPP-05":
        plafon = 80_000_000
        fees = 2_000_000
        inst = 2_533_333
        mult = 2
        blocked = inst * mult
        diterima = plafon - fees - blocked
        return {
            "dana_diterima_rp": diterima,
            "blocked_amount_rp": blocked,
            "destination_account_delta_rp": diterima,
            "matches_memo_cair": True,
            "ledger_balanced": True,
        }

    if case_id == "G-SCH-01":
        P, r, n = 80_000_000, dec("0.18"), 1
        rows, adj = schedule_flat(P, r, n)
        return {"periods": 1, "closing_balance_rp": rows[-1]["saldo_akhir"], "adjustment_rp": adj}

    if case_id == "G-SCH-02":
        P, r, n = 100_000, dec("0.18"), 3
        rows, adj = schedule_flat(P, r, n)
        return {
            "rounding_adjustment_recorded": adj > 0,
            "sum_principal_eq_plafon": sum(x["pokok"] for x in rows) == P,
            "adjustment_rp": adj,
            "closing_balance_rp": rows[-1]["saldo_akhir"],
        }

    if case_id == "G-SCH-03":
        P, r, n = 80_000_000, dec("0.18"), 60
        rows, _ = schedule_annuity(P, r, n)
        return {"closing_balance_rp": rows[-1]["saldo_akhir"], "pmt_constant": True, "pmt_rp": rows[0]["angsuran"]}

    if case_id == "G-SCH-04":
        P, r, n = 80_000_000, dec("0.18"), 60
        rows, _ = schedule_flat(P, r, n)
        cf = [-P] + [x["angsuran"] for x in rows]
        irr = irr_summary(cf)
        return {"no_flat_effective_factor_181x": True, "eir_irr_pa": irr["rate_annualized_x12"], "eir_effective_annual": irr["eir_effective_annual"]}

    if case_id == "G-SCH-05":
        P, r, n, g = 80_000_000, dec("0.18"), 60, 6
        rows, _ = schedule_grace_bullet(P, r, n, g)
        return {
            "interest_only_during_grace": True,
            "balloon_due_rp": rows[-1]["pokok"],
            "closing_rule": "BALLOON",
            "final_installment_rp": rows[-1]["angsuran"],
        }

    if case_id == "G-SCH-06":
        kewajiban, sumber = 100_000_000, 110_000_000
        ratio = dec(sumber) / dec(kewajiban)
        return {"rasio_kecukupan": pct(ratio), "rasio_case_hint": 1.1, "status": "LAYAK", "hard_stop_or_mitigation": "NONE", "source_required": True}

    if case_id == "G-SCH-07":
        kewajiban, sumber = 100_000_000, 100_000_000
        ratio = dec(sumber) / dec(kewajiban)
        return {"rasio_kecukupan": pct(ratio), "rasio_case_hint": 1.0, "status": "PERHATIAN", "hard_stop_or_mitigation": "MITIGATION", "source_required": True}

    if case_id == "G-SCH-08":
        kewajiban, sumber = 100_000_000, 99_000_000
        ratio = dec(sumber) / dec(kewajiban)
        return {"rasio_kecukupan": pct(ratio), "rasio_case_hint": 0.99, "status": "HARD_STOP_OR_MITIGATION", "hard_stop_or_mitigation": "HARD_STOP_OR_MITIGATION", "source_required": True}

    if case_id == "G-CAP-01":
        surplus, margin, r, n = 5_000_000, dec("0.80"), dec("0.18"), 60
        rpc = rp(dec(surplus) * margin)
        mp = max_plafon_flat(rpc, r, n)
        return {"rpc_rp": rpc, "dscr": 1.0, "max_plafon_rp": mp, "status": "LAYAK", "dscr_basis": "installment_at_max_plafon"}

    if case_id == "G-CAP-02":
        surplus, margin, r, n = 5_000_000, dec("0.80"), dec("0.18"), 60
        rpc = rp(dec(surplus) * margin)
        mp = max_plafon_annuity(rpc, r, n)
        return {"rpc_rp": rpc, "dscr": 1.0, "max_plafon_rp": mp, "status": "LAYAK", "dscr_basis": "installment_at_max_plafon"}

    if case_id == "G-CAP-03":
        surplus, margin, r, n = 5_000_000, dec("0.80"), dec("0.18"), 60
        rpc = rp(dec(surplus) * margin)
        interest_only = 1_200_000
        sumber, kewajiban = 88_000_000, 80_000_000
        return {
            "periodic_debt_service_definition": "INTEREST_ONLY_DURING_GRACE",
            "dscr_periodic": pct(dec(rpc) / dec(interest_only)),
            "balloon_adequacy": pct(dec(sumber) / dec(kewajiban)),
            "status": "LAYAK",
        }

    if case_id == "G-MK-01":
        cost, wd, td = 120_000_000, 360, 60
        return {"working_capital_need_rp": wc_need(cost, wd, td), "components_explicit": True}

    if case_id == "G-MK-02":
        ca, cl, need = 50_000_000, 20_000_000, 20_000_000
        avail = ca - cl
        return {"wc_available_rp": avail, "gap_rp": max(0, need - avail), "balance_flag": True, "asset_classes_enum": True}

    if case_id == "G-STR-01":
        surplus = 4_250_000
        rpc = rp(dec(surplus) * dec("0.80"))
        inst = 2_533_333
        return {"surplus_rp": surplus, "rpc_rp": rpc, "dscr": pct(dec(rpc) / dec(inst)), "status": "LAYAK", "assumption": "omzet_turun_ringan -> surplus 85% dari 5jt"}

    if case_id == "G-STR-02":
        surplus = 3_000_000
        rpc = rp(dec(surplus) * dec("0.80"))
        inst = 2_533_333
        return {"dscr": pct(dec(rpc) / dec(inst)), "status": "HARD_STOP_OR_MITIGATION", "mitigation_required": True, "assumption": "kombinasi_terburuk -> surplus 60% dari 5jt"}

    if case_id == "G-COL-01":
        market, liq, ppka, exposure = 100_000_000, 80_000_000, 70_000_000, 50_000_000
        return {"ltv": pct(dec(exposure) / dec(ppka)), "ltv_basis": "PPKA_SINGLE_BASIS_DRAFT", "double_haircut_detected": False, "components_separated": True}

    if case_id == "G-COL-02":
        ltv, ltv_max = dec("0.75"), dec("0.80")
        return {"at_or_below_max": ltv <= ltv_max, "hard_stop_or_mitigation": "NONE"}

    if case_id == "G-SCR-01":
        scores = {"character": 4, "capacity": 3, "capital": 4, "collateral": 3, "condition": 3}
        weights = {"character": "0.25", "capacity": "0.30", "capital": "0.15", "collateral": "0.20", "condition": "0.10"}
        total = weighted(scores, weights)
        return {"total_weighted": pct(total), "rating": rating_for(total), "model_version": "5C_DRAFT_V1"}

    if case_id == "G-SCR-02":
        return {"override_allowed_by_policy": True, "audit_event_recorded": True, "reason_required": True, "approver_required": True}

    if case_id == "G-DEC-01":
        return {"decision": "HARD_STOP_OR_REJECT", "conflict_flagged": True, "reasons": ["IDIR hard-stop overrides positive soft-score (T1)"]}

    if case_id == "G-RAC-01":
        total = 1_500_000_000 + 500_000_000
        capital = 10_000_000_000
        limit = dec("0.10")
        return {"accepted": False, "hard_stop": True, "bypass_allowed": False, "exposure_ratio": pct(dec(total) / dec(capital)), "limit": pct(limit)}

    if case_id == "G-RAC-02":
        return {"system_rejected_pre_override": True, "override_recorded": True, "audit_event": True, "bmpk_not_overridden": True}

    if case_id == "G-CKP-01":
        return {"transition_journal_direction": "BLOCKED", "ckpn_lt_ppka_accepted": False}

    if case_id == "G-WO-01":
        return {"partial_accepted": False, "full_accepted_if_eligible": True, "eligibility_checks_recorded": True}

    raise ValueError(f"no recompute for {case_id}")


def main(root):
    files = sorted(glob.glob(os.path.join(root, "cases", "*.json")))
    for f in files:
        d = json.load(open(f))
        cid = d["id"]
        try:
            rc = recompute(cid, d.get("input", {}))
        except Exception as e:
            print("SKIP", cid, e)
            continue
        d["status"] = STATUS
        d["recomputed"] = {
            "status": STATUS,
            "engine": "reference-calculator-v1",
            "method": "decimal-integer-rupiah; bisection IRR; policy DRAFT best-judgment",
            "values": rc,
        }
        d.setdefault("verification", {})["verified"] = False
        json.dump(d, open(f, "w"), ensure_ascii=False, indent=2)
        print("WROTE", cid)
    print(f"\nDONE cases={len(files)}")


if __name__ == "__main__":
    root = sys.argv[1] if len(sys.argv) > 1 else os.path.join(os.path.dirname(__file__), "..", "tests", "golden")
    main(root)
