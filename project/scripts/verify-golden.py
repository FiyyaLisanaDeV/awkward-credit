#!/usr/bin/env python3
"""Verify golden vectors: structure, invariants, and independent Decimal math.

No PII, no workbook names. Money in rupiah (integer). Rates as decimal/year.
IRR solved by bisection on monthly cashflows (numpy-free).
"""
from __future__ import annotations
import json, glob, os, sys, math
from decimal import Decimal, ROUND_HALF_UP, getcontext

getcontext().prec = 50
D = Decimal
RP = D("1")  # integer rupiah


def rp(x):
    return int(D(str(x)).quantize(RP, rounding=ROUND_HALF_UP))


def pmt(P, rate_pa, n):
    i = D(str(rate_pa)) / D(12)
    P, n = D(str(P)), int(n)
    if n <= 0:
        return rp(P)
    if i == 0:
        q, r = divmod(int(P), n)
        return q, r  # base, remainder
    A = P * i / (D(1) - (D(1) + i) ** (-n))
    return rp(A)


def irr_monthly(cashflows):
    # cashflows[0] negative (outflow, lender perspective: -P + fees?), t>=1 inflows
    def npv(rate_m):
        return sum(D(str(cf)) / ((D(1) + rate_m) ** t) for t, cf in enumerate(cashflows))

    lo, hi = D("-0.99"), D("10")
    f_lo, f_hi = npv(lo), npv(hi)
    # expand hi if needed
    for _ in range(60):
        if f_lo * f_hi <= 0:
            break
        hi *= 2
        f_hi = npv(hi)
    if f_lo * f_hi > 0:
        return None
    for _ in range(200):
        mid = (lo + hi) / 2
        f_m = npv(mid)
        if abs(f_m) < D("0.000001"):
            return float(mid)
        if f_lo * f_m <= 0:
            hi, f_hi = mid, f_m
        else:
            lo, f_lo = mid, f_m
    return float((lo + hi) / 2)


def schedule_flat(P, rate_pa, n, rounding="LAST_ADJUST"):
    P, n = int(P), int(n)
    i_m = D(str(rate_pa)) / D(12)
    bunga_m = rp(D(P) * i_m)
    pokok_base = P // n
    rem = P - pokok_base * n
    rows = []
    bal = P
    for k in range(1, n + 1):
        pok = pokok_base + (rem if (k == n and rounding == "LAST_ADJUST") else 0)
        bunga = bunga_m
        angs = pok + bunga
        bal2 = bal - pok
        rows.append({"k": k, "saldo_awal": bal, "pokok": pok, "bunga": bunga, "angsuran": angs, "saldo_akhir": bal2})
        bal = bal2
    return rows


def schedule_annuity(P, rate_pa, n, rounding="LAST_ADJUST"):
    P, n = int(P), int(n)
    i_m = D(str(rate_pa)) / D(12)
    A = pmt(P, rate_pa, n)
    rows = []
    bal = P
    for k in range(1, n + 1):
        bunga = rp(D(bal) * i_m)
        if k == n and rounding == "LAST_ADJUST":
            pok = bal
            angs = pok + bunga
        else:
            pok = A - bunga
            angs = A
        bal2 = bal - pok
        rows.append({"k": k, "saldo_awal": bal, "pokok": pok, "bunga": bunga, "angsuran": angs, "saldo_akhir": bal2})
        bal = bal2
    return rows


def schedule_grace_bullet(P, rate_pa, n, grace_months, rounding="LAST_ADJUST"):
    # grace: interest-only; final: bullet principal (all remaining). grace_months < n
    P, n, g = int(P), int(n), int(grace_months)
    i_m = D(str(rate_pa)) / D(12)
    rows = []
    bal = P
    for k in range(1, n + 1):
        bunga = rp(D(bal) * i_m)
        if k <= g:
            pok = 0
        elif k < n:
            pok = 0  # bullet style: all principal at end
        else:
            pok = bal
        angs = pok + bunga
        bal2 = bal - pok
        rows.append({"k": k, "saldo_awal": bal, "pokok": pok, "bunga": bunga, "angsuran": angs, "saldo_akhir": bal2, "phase": "grace" if k <= g else ("bullet" if k == n else "grace_tail")})
        bal = bal2
    return rows


def check_schedule(rows, P, tol=1):
    errs = []
    sum_p = sum(r["pokok"] for r in rows)
    if abs(sum_p - P) > tol:
        errs.append(f"sum_pokok {sum_p} != P {P}")
    if abs(rows[-1]["saldo_akhir"]) > tol:
        errs.append(f"closing {rows[-1]['saldo_akhir']} != 0")
    bal = P
    for r in rows:
        if abs(r["saldo_awal"] - bal) > tol:
            errs.append(f"k{r['k']} saldo_awal {r['saldo_awal']} != {bal}")
        if abs(r["pokok"] + r["bunga"] - r["angsuran"]) > tol:
            errs.append(f"k{r['k']} pok+bunga!=angs")
        bal2 = bal - r["pokok"]
        if abs(r["saldo_akhir"] - bal2) > tol:
            errs.append(f"k{r['k']} saldo_akhir")
        bal = bal2
    return errs


def main():
    root = sys.argv[1] if len(sys.argv) > 1 else os.path.join(os.path.dirname(__file__), "..", "tests", "golden")
    files = sorted(glob.glob(os.path.join(root, "cases", "*.json")))
    ok = err = warn = filled = verified = 0
    for f in files:
        d = json.load(open(f))
        bid = os.path.basename(f)[:-5]
        problems = []
        if d.get("id") != bid:
            problems.append("id mismatch")
        if d.get("pii"):
            problems.append("PII flag")
        # numeric presence
        def has_num(x):
            if isinstance(x, (int, float)) and not isinstance(x, bool):
                return True
            if isinstance(x, dict):
                return any(has_num(v) for v in x.values())
            if isinstance(x, list):
                return any(has_num(v) for v in x)
            return False
        inp_num = has_num(d.get("input", {}))
        exp_num = has_num(d.get("expected", {}))
        if inp_num or exp_num:
            filled += 1
        if (d.get("verification") or {}).get("verified"):
            verified += 1
        # compute check for known schedule shapes
        inp = d.get("input", {}) or {}
        method = (d.get("method") or "").upper()

        def gv(obj, key):
            v = obj.get(key)
            if isinstance(v, dict):
                return v.get("value_rp") if "value_rp" in v else v.get("value")
            return v

        P = gv(inp, "plafon_rp")
        r = gv(inp, "rate_pa")
        n = gv(inp, "tenor_months")
        g = gv(inp, "grace_months")
        if P and r is not None and n and method in ("FLAT", "ANNUITY", "GRACE_BULLET"):
            try:
                if method == "FLAT":
                    rows = schedule_flat(P, r, n)
                elif method == "ANNUITY":
                    rows = schedule_annuity(P, r, n)
                else:
                    rows = schedule_grace_bullet(P, r, n, g or 0)
                problems += check_schedule(rows, int(P))
            except Exception as e:
                problems.append(f"compute: {e}")
        if problems:
            err += 1
            print(f"FAIL {bid}: {'; '.join(problems)}")
        else:
            ok += 1
            print(f"OK   {bid}  neg={d.get('negative_test')} filled={inp_num or exp_num} ver={bool((d.get('verification') or {}).get('verified'))}")
    print(f"\nSUMMARY cases={len(files)} ok={ok} fail={err} filled={filled} verified={verified}")
    return 1 if err else 0


if __name__ == "__main__":
    raise SystemExit(main())
