function aa() {
    return S.max_series_size = 10001, S.n = function (t) {
        return t.symbol.index + 1
    }, S.nz = function (t, e) {
        return e = e || 0, isFinite(t) ? t : e
    }, S.na = function (t) {
        return 0 === arguments.length ? NaN : isNaN(t) ? 1 : 0
    }, S.isZero = function (t) {
        return Math.abs(t) <= 1e-10
    }, S.toBool = function (t) {
        return isFinite(t) && !S.isZero(t)
    }, S.eq = function (t, e) {
        return S.isZero(t - e)
    }, S.neq = function (t, e) {
        return !S.eq(t, e)
    }, S.ge = function (t, e) {
        return S.isZero(t - e) || t > e
    }, S.gt = function (t, e) {
        return !S.isZero(t - e) && t > e
    }, S.lt = function (t, e) {
        return !S.isZero(t - e) && t < e
    }, S.le = function (t, e) {
        return S.isZero(t - e) || t < e
    }, S.and = function (t, e) {
        return isNaN(t) || isNaN(e) ? NaN : S.isZero(t) || S.isZero(e) ? 0 : 1
    }, S.or = function (t, e) {
        return isNaN(t) || isNaN(e) ? NaN : S.isZero(t) && S.isZero(e) ? 0 : 1
    }, S.not = function (t) {
        return isNaN(t) ? NaN : S.isZero(t) ? 1 : 0
    }, S.greaterOrEqual = function (t, e) {
        return e - t < n
    }, S.lessOrEqual = function (t, e) {
        return t - e < n
    }, S.equal = function (t, e) {
        return Math.abs(t - e) < n
    }, S.greater = function (t, e) {
        return t - e > n
    }, S.less = function (t, e) {
        return e - t > n
    }, S.max = Math.max, S.min = Math.min, S.pow = Math.pow, S.abs = Math.abs, S.log = Math.log, S.log10 = function (t) {
        return Math.log(t) / Math.LN10
    }, S.sqrt = Math.sqrt, S.sign = function (t) {
        return isNaN(t) ? NaN : S.isZero(t) ? 0 : t > 0 ? 1 : -1
    }, S.exp = Math.exp, S.sin = Math.sin, S.cos = Math.cos, S.tan = Math.tan, S.asin = Math.asin, S.acos = Math.acos, S.atan = Math.atan, S.floor = Math.floor, S.ceil = Math.ceil, S.round = Math.round, S.avg = function (t, e, i, o, n, s) {
        var r, a;
        if (2 === arguments.length) return (t + e) / 2;
        for (r = 0, a = 0; a < arguments.length; a++) r += arguments[a];
        return r / arguments.length
    }, S.open = function (t) {
        return t.symbol.open
    }, S.high = function (t) {
        return t.symbol.high
    }, S.low = function (t) {
        return t.symbol.low
    }, S.close = function (t) {
        return t.symbol.close
    }, S.hl2 = function (t) {
        return (t.symbol.high + t.symbol.low) / 2
    }, S.hlc3 = function (t) {
        return (t.symbol.high + t.symbol.low + t.symbol.close) / 3
    }, S.ohlc4 = function (t) {
        return (t.symbol.open + t.symbol.high + t.symbol.low + t.symbol.close) / 4
    }, S.volume = function (t) {
        return t.symbol.volume
    }, S.updatetime = function (t) {
        return t.symbol.updatetime
    }, S.time = function (t, e, i) {
        return t.symbol.bartime(e, i)
    }, S.period = function (t) {
        return t.symbol.period
    }, S.tickerid = function (t) {
        return t.symbol.tickerid
    }, S.ticker = function (t) {
        return t.symbol.ticker
    }, S.interval = function (t) {
        return t.symbol.interval
    }, S.isdwm = function (t) {
        return t.symbol.isdwm()
    }, S.isintraday = function (t) {
        return !t.symbol.isdwm()
    }, S.isdaily = function (t) {
        return "D" === t.symbol.resolution
    }, S.isweekly = function (t) {
        return "W" === t.symbol.resolution
    }, S.ismonthly = function (t) {
        return "M" === t.symbol.resolution
    }, S.year = function (t) {
        return S.timepart(t.symbol, b.YEAR, arguments[1])
    }, S.month = function (t) {
        return S.timepart(t.symbol, b.MONTH, arguments[1])
    }, S.weekofyear = function (t) {
        return S.timepart(t.symbol, b.WEEK_OF_YEAR, arguments[1])
    }, S.dayofmonth = function (t) {
        return S.timepart(t.symbol, b.DAY_OF_MONTH, arguments[1])
    }, S.dayofweek = function (t) {
        return S.timepart(t.symbol, b.DAY_OF_WEEK, arguments[1])
    }, S.hour = function (t) {
        return S.timepart(t.symbol, b.HOUR_OF_DAY, arguments[1])
    }, S.minute = function (t) {
        return S.timepart(t.symbol, b.MINUTE, arguments[1])
    }, S.second = function (t) {
        return S.timepart(t.symbol, b.SECOND, arguments[1])
    }, S.iff = function (t, e, i) {
        return S.not(t) ? i : e
    }, S.rising = function (t, e) {
        for (var i = 1; i < e + 1; ++i) if (t.get(i) > t.get(0)) return 0;
        return 1
    }, S.falling = function (t, e) {
        for (var i = 1; i < e + 1; ++i) if (t.get(i) < t.get(0)) return 0;
        return 1
    }, S.timepart = function (t, e, i) {
        var o = b.utc_to_cal(t.timezone, i || t.bartime());
        return b.get_part(o, e)
    }, S.rsi = function (t, e) {
        return S.isZero(e) ? 100 : S.isZero(t) ? 0 : 100 - 100 / (1 + t / e)
    }, S.sum = function (t, e, i) {
        var o = i.new_var(),
            n = S.nz(t.get()) + S.nz(o.get(1)) - S.nz(t.get(e));
        return o.set(n), n
    }, S.sma = function (t, e, i) {
        var o = S.sum(t, e, i);
        return S.na(t.get(e - 1)) ? NaN : o / e
    }, S.rma = function (t, e, i) {
        var o = S.sum(t, e, i),
            n = e - 1,
            s = t.get(n), r = i.new_var(),
            a = r.get(1), l = t.get(),
            h = S.na(s) ? NaN : S.na(a) ? o / e : (l + a * n) / e;
        return r.set(h), h
    }, S.fixnan = function (t, e) {
        var i = e.new_var();
        return isNaN(t) ? i.get(1) : (i.set(t), t)
    }, S.tr = function (t, e) {
        var i, o, n;
        return 1 === arguments.length && (e = t, t = void 0), i = void 0 !== t && !!t, o = e.new_var(S.close(e)), n = o.get(1), i && isNaN(n) && (n = S.close(e)), S.max(S.max(S.high(e) - S.low(e), S.abs(S.high(e) - n)), S.abs(S.low(e) - n))
    }, S.atr = function (t, e) {
        var i = e.new_var(S.tr(e));
        return S.sma(i, t, e)
    }, S.ema = function (t, e, i) {
        var o = S.sum(t, e, i), n = i.new_var(), s = t.get(0), r = t.get(e - 1), a = n.get(1), l = S.na(r) ? NaN : S.na(a) ? o / e : 2 * (s - a) / (e + 1) + a;
        return n.set(l), l
    }, S.wma = function (t, e, i) {
        var o, n, s = 0;
        for (e = Math.round(e), o = e; o >= 0; o--) n = e - o, s += n * t.get(o);
        return 2 * s / (e * (e + 1))
    }, S.vwma = function (t, e, i) {
        var o = i.new_var(S.volume(i)), n = i.new_var(t.get(0) * S.volume(i));
        return S.sma(n, e, i) / S.sma(o, e, i)
    }, S.swma = function (t, e) {
        return (t.get(0) + 2 * t.get(1) + 2 * t.get(2) + t.get(3)) / 6
    }, S.lowestbars = function (e, i, o) {
        return -t(e, i, o, function (t, e) {
            return S.lt(t, e)
        }, Number.MAX_VALUE).index
    }, S.lowest = function (e, i, o) {
        return t(e, i, o, function (t, e) {
            return S.lt(t, e)
        }, Number.MAX_VALUE).value
    }, S.highestbars = function (e, i, o) {
        return -t(e, i, o, function (t, e) {
            return S.gt(t, e)
        }, Number.MIN_VALUE).index
    }, S.highest = function (e, i, o) {
        return t(e, i, o, function (t, e) {
            return S.gt(t, e)
        }, Number.MIN_VALUE).value
    }, S.cum = function (t, e) {
        var i = e.new_var(), o = S.nz(i.get(1)) + t;
        return i.set(o), o
    }, S.accdist = function (t) {
        var e = S.high(t), i = S.low(t), o = S.close(t), n = S.volume(t);
        return S.cum(o === e && o === i || e === i ? 0 : n * (2 * o - i - e) / (e - i), t)
    }, S.correlation = function (t, e, i, o) {
        var n = S.sma(t, i, o), s = S.sma(e, i, o), r = o.new_var(t.get() * e.get());
        return (S.sma(r, i, o) - n * s) / Math.sqrt(S.variance2(t, n, i) * S.variance2(e, s, i))
    }, S.stoch = function (t, e, i, o, n) {
        var r = S.highest(e, o), a = S.lowest(i, o);
        return S.fixnan(s(t.get() - a, r - a), n)
    }, S.tsi = function (t, e, i, o) {
        var n = o.new_var(S.change(t)), s = o.new_var(S.abs(S.change(t))), r = o.new_var(S.ema(n, i, o)), a = o.new_var(S.ema(s, i, o));
        return S.ema(r, e, o) / S.ema(a, e, o)
    }, S.cross = function (t, e, i) {
        function o(t) {
            return t < 0 ? -1 : 0 === t ? 0 : 1
        }

        if (isNaN(t) || isNaN(e)) return !1;
        var n = i.new_var(o(t - e));
        return !isNaN(n.get(1)) && n.get(1) !== n.get()
    }, S.linreg = function (t, e, i) {
        var o, n, s, r, a, l, h = 0, c = 0, d = 0, p = 0;
        for (o = 0; o < e; ++o) n = t.get(o), s = e - 1 - o, r = s + 1, h += r, c += n, d += r * r, p += n * r;
        return a = (e * p - h * c) / (e * d - h * h), l = c / e, l - a * h / e + a + a * (e - 1 - i)
    }, S.sar = function (t, e, i, o) {
        function n(e, i) {
            var o = m.get();
            return g.set(e), m.set(i), v.set(1e3 * t),
                y.set(o), o
        }

        var s, r, a = S.high(o), l = S.low(o), h = S.close(o), c = o.new_var(a), d = o.new_var(l), p = o.new_var(h), u = p.get(1), _ = d.get(1), f = c.get(1), m = o.new_var(), g = o.new_var(), v = o.new_var(), y = o.new_var(), b = y.get(1);
        if (isNaN(u)) return NaN;
        if (s = 1e-7, isNaN(b) && (S.ge(h, u) ? (g.set(1), m.set(Math.max(a, f)), b = Math.min(l, _)) : (g.set(-1), b = Math.max(a, f), m.set(Math.min(l, _))), v.set(1e3 * t)), 1 === g.get()) {
            if (S.gt(a, m.get()) && (m.set(a), v.set(Math.min(v.get() + 1e3 * e, 1e3 * i))), S.le(l, b)) return n(-1, l)
        } else if (S.lt(l, m.get()) && (m.set(l), v.set(Math.min(v.get() + 1e3 * e, 1e3 * i))), S.ge(a, b)) return n(1, a);
        return r = b + v.get() * (m.get() - b) / 1e3, 1 === g.get() ? S.ge(r, l) && (r = l - s) : S.le(r, a) && (r = a + s), y.set(r), r
    }, S.alma = function (t, e, i, o) {
        var n, s, r, a = Math.floor(i * (e - 1)), l = e / o * (e / o), h = [], c = 0;
        for (n = 0; n < e; ++n) s = Math.exp(-1 * Math.pow(n - a, 2) / (2 * l)), c += s, h.push(s);
        for (n = 0; n < e; ++n) h[n] /= c;
        for (r = 0, n = 0; n < e; ++n) r += h[n] * t.get(e - n - 1);
        return r
    }, S.wvap = function (t, e) {
        return t.get() - t.get(1)
    }, S.change = function (t) {
        return t.get() - t.get(1)
    }, S.roc = function (t, e) {
        var i = t.get(e);
        return 100 * (t.get() - i) / i
    }, S.dev = function (t, e, i) {
        var o = S.sma(t, e, i);
        return S.dev2(t, e, o)
    }, S.dev2 = function (t, e, i) {
        var o, n, s, r = 0;
        for (o = 0; o < e; o++) n = t.get(o), s = S.abs(n - i), r += s;
        return r / e
    }, S.stdev = function (t, e, i) {
        var o = S.variance(t, e, i);
        return S.sqrt(o)
    }, S.variance = function (t, e, i) {
        var o = S.sma(t, e, i);
        return S.variance2(t, o, e)
    },S.variance2 = function (t, e, i) {
        var o, n, s, r = 0;
        for (o = 0; o < i; o++) n = t.get(o), s = S.abs(n - e), r += s * s;
        return r / i
    },S.percentrank = function (t, e) {
        var i, o, n, s;
        if (S.na(t.get(e - 1))) return NaN;
        for (i = 0, o = t.get(), n = 1; n < e; n++) s = t.get(n), S.ge(o, s) && i++;
        return 100 * i / e
    },r.LOW = 0,r.HIGH = 1,r.prototype.isPivotFound = function () {
        return -1 !== this._pivotIndex.get()
    },r.prototype.pivotIndex = function () {
        return this._pivotIndex.get()
    },r.prototype.currentValue = function () {
        return this._currentValue.get()
    },r.prototype.pivotType = function () {
        return this._pivotType
    },r.prototype.reset = function () {
        this._currentValue.set(NaN), this._currentIndex.set(0), this._pivotIndex.set(-1)
    },r.prototype.isRightSideOk = function (t) {
        return t - this._currentIndex.get() === this._areaRight
    },r.prototype.isViolate = function (t, e) {
        if (t < 1 || isNaN(this._currentValue.get())) return !0;
        var i = this._series.get(this._index - t);
        return !!isNaN(i) || (i === this._currentValue.get() ? e : this._pivotType === r.HIGH ? i > this._currentValue.get() : i < this._currentValue.get())
    },r.prototype.processPoint = function (t) {
        this.isViolate(t, !1) && (this._currentValue.set(this._series.get()), this._currentIndex.set(t))
    },r.prototype.isRestartNeeded = function (t) {
        return t - this._currentIndex.get() > this._areaRight
    },r.prototype.update = function () {
        var t, e;
        if (this._isNewBar && this.isPivotFound() && this.reset(), this.processPoint(this._index), this.isRightSideOk(this._index)) {
            if (-1 === this._pivotIndex.get()) {
                for (t = !0, e = 0; e < this._areaLeft; ++e) if (this.isViolate(this._currentIndex.get() - 1 - e, !0)) {
                    t = !1;
                    break
                }
                t && this._pivotIndex.set(this._currentIndex.get())
            }
        } else -1 !== this._pivotIndex.get() && this._pivotIndex.set(-1);
        if (this.isRestartNeeded(this._index)) for (this.reset(), e = 0; e <= this._areaRight; ++e) this.processPoint(this._index - this._areaRight + e)
    },
        a.prototype.addPivot = function (t, e, i) {
            this._lastIndex.set(t), this._lastVal.set(e), this._lastType.set(i)
        },a.prototype.updatePivot = function (t, e) {
        this._lastIndex.set(t), this._lastVal.set(e)
    },a.prototype.lastPrice = function () {
        return this._lastVal.get()
    },a.prototype.lastIndex = function () {
        return this._lastIndex.get()
    },a.prototype.addPoint = function (t, e, i) {
        var o;
        return isNaN(this._lastVal.get()) ? void this.addPivot(t, e, i) : (o = this._lastVal.get(), this._lastType.get() === i ? void((i === r.HIGH ? e > o : e < o) && this.updatePivot(t, e)) : void(Math.abs(o - e) / e > this._deviation && this.addPivot(t, e, i)))
    },a.prototype.processPivot = function (t) {
        t.update(), this._isBarClosed && t.isPivotFound() && this.addPoint(t.pivotIndex(), t.currentValue(), t.pivotType())
    },S.zigzag = function (t, e, i) {
        return new a(t, e, i).lastPrice()
    },S.zigzagbars = function (t, e, i) {
        var o = new a(t, e, i);
        return -1 === o.lastIndex() ? NaN : o.lastIndex() - S.n(i)
    },l.prototype.new_sym = function (t, e, i) {
        return this.symbol.script.add_sym(t, e, i)
    },l.prototype.select_sym = function (t) {
        this.symbol = this.symbol.script.get_sym(t)
    },l.prototype.new_var = function (t) {
        var e, i = this.vars;
        return i.length <= this.vars_index && i.push(new h(this.symbol)),
            e = i[this.vars_index++], arguments.length > 0 && e.set(t), e
    },l.prototype.new_ctx = function () {
        return this.ctx.length <= this.ctx_index && this.ctx.push(new l(this.symbol)), this.ctx[this.ctx_index++]
    },l.prototype.prepare = function (t) {
        var e, i;
        for (this.ctx_index = 0, this.vars_index = 0, e = 0; e < this.vars.length; e++) this.vars[e].prepare(t);
        for (i = 0; i < this.ctx.length; i++) this.ctx[i].prepare(t)
    },l.prototype.stop = function () {
        this.symbol = null, this.vars = null
    },h.prototype.valueOf = function () {
        return this.get(0)
    },h.prototype.get = function (t) {
        return isNaN(t) && (t = 0), t = t || 0, this.hist ? t >= this.hist.length ? (console.error("not enough depth: " + this), NaN) : this._get(t) : (this.mindepth = S.max(this.mindepth, t), NaN)
    },h.prototype._get = function (t) {
        var e = this.hist_pos - t;
        return e < 0 && (e += this.hist.length), this.hist[e]
    },h.prototype.set = function (t) {
        this.hist && (this.hist[this.hist_pos] = t, this.modified = !0)
    },h.prototype.prepare = function (t) {
        t === this.symbol && (t.isNewBar ? (this.original = this.get(0), !this.modified && this.hist || this.add_hist()) : this.set(this.original), this.modified = !1)
    },h.prototype.add_hist = function () {
        var t, e, i;
        if (!this.hist) {
            for (t = S.na(this.mindepth) ? S.max_series_size : S.min(this.mindepth + 1, S.max_series_size), t = Math.round(t), e = Array(t), i = 0; i < t; i++) e[i] = NaN;
            this.hist = e, this.hist_pos = -1
        }
        this.hist_pos = Math.min(this.hist_pos + 1, this.hist.length), this.hist_pos === this.hist.length && (this.hist_pos = this.hist.length - 1, this.hist.shift(), this.hist.push(NaN)), this.hist[this.hist_pos] = this.original
    },h.prototype.adopt = function (t, e, i) {
        var o, n, s, r;
        return this.hist || (this.mindepth = NaN), o = e.get(), n = t.indexOf(o), 0 !== i && (s = e.get(1), S.na(s) || (r = t.indexOf(s), n = n === r ? -1 : n)), n < 0 ? NaN : this._get(n)
    },h.prototype.indexOf = function (t) {
        var e, i, n, s;
        return this.hist ? S.na(t) ? -1 : (e = this.hist.length, i = this.symbol.index + 1, n = Math.min(e, i), s = o.upperbound_int(this.hist, t, 0, n), 0 === s ? -1 : n - s) : (this.mindepth = NaN, -1)
    },d.parseTicker = function (t) {
        var e = t.indexOf(":")
        ;
        return -1 === e ? t : t.substr(e + 1)
    },d.parsePeriod = function (t) {
        var e, i, o, n, s, r, a, l;
        return t += "", e = t.slice(0), i = e.indexOf(",") >= 0, i && (n = e.split(","), o = d.parsePeriod(n[1]), e = n[0]), s = !1, r = !1, a = e[e.length - 1], -1 === "DWM".indexOf(a) && ("S" === a ? r = !0 : (s = !0, a = "")), l = parseInt(s ? e : e.length > 1 ? e.slice(0, e.length - 1) : 1), {
            resolution: a,
            interval: l,
            pureResolution: "" + l + a,
            isIntraday: s,
            isSeconds: r,
            range: o
        }
    },d.newBarBuilder = function (t, e, i) {
        var o = d.parsePeriod(t);
        return w.newBarBuilder(o.resolution, o.interval, e, i)
    },d.newSession = function (t, e) {
        var i = b.get_timezone(t);
        return (new w.Session).init(i, e)
    },d.prototype.set_symbolinfo = function (t) {
        t || console.error("WARN: symbolinfo isn't defined for " + this.tickerid), this.info = t, this.timezone = b.get_timezone(t.timezone), this.session.init(this.timezone, t.session);
        for (var e in this.other_sessions) this.other_sessions.hasOwnProperty(e) && this.other_sessions[e].init(this.timezone, e)
    },d.prototype.get_session = function (t) {
        if (!t) return this.session;
        var e = this.other_sessions[t];
        return e || (e = new w.Session, this.other_sessions[t] = e), e
    },d.prototype.isdwm = function () {
        return "" !== this.resolution && "S" !== this.resolution
    },d.prototype.enable_dwm_aligning = function (t, e) {
        this.dwm_aligner = d.newBarBuilder(this.period, t, e)
    },d.prototype.bartime = function (t, e) {
        var i, o, n, s, r = this.time;
        return t && (i = t, e && (i += e), o = this.bb_cache[i], o || (n = this.get_session(e), o = d.newBarBuilder(t, n), this.bb_cache[i] = o), r = o.alignTime(r)), !this.isdwm() || isNaN(r) ? r : (s = b.utc_to_cal(this.timezone, r), this.session.spec.correctTradingDay(s, this.timezone), b.cal_to_utc(this.timezone, s))
    },d.prototype.lastbar = function (t) {
        var e, i;
        isNaN(t.time) || (e = t.time, this.dwm_aligner && (this.dwm_aligner.moveTo(e), e = this.dwm_aligner.startOfBar(0)), i = this.time !== e, i && this.index >= 0 && !this.isBarClosed && (this.isNewBar = !1, this.isBarClosed = !0, this.script.calc(this)), this.time = e, this.open = t.open, this.high = t.high, this.low = t.low, this.close = t.close, this.volume = t.volume, this.updatetime = t.updatetime, this.isNewBar = i, this.isBarClosed = t.isBarClosed, this.isLastBar = t.isLastBar, this.isNewBar && this.index++, this.script.calc(this))
    },p.prototype.calc = function (t) {
        var e, i, o = this.ctx, n = this.body;
        o.prepare(t), e = n.main(o, this.inputCallback), i = this, !isNaN(o.symbol.time) && this.out && e && (e.nonseries ? (e.projectionTime = o.symbol.time, this.nonseriesOut(o.symbol, e)) : e.bars ? e.bars.forEach(function (t) {
            i.out(o.symbol, t)
        }) : this.out(o.symbol, e))
    },p.prototype.init = function () {
        var t = this.ctx, e = this.body;
        e.init && e.init(t, this.inputCallback), e.main(t, this.inputCallback)
    },p.prototype.add_sym = function (t, e, i, o) {
        var n = this.runner.add_sym(t, e, i, this, o);
        return this.symbols.push(n), n.isdwm() && this.symbols.length > 1 && n.enable_dwm_aligning(this.symbols[0].session, n.session), n
    },p.prototype.stop = function () {
        this.symbols = null, this.ctx.stop(), this.ctx = null
    },p.prototype.get_sym = function (t) {
        return this.symbols[t]
    },u.prototype.add_sym = function (t, e, i, o, n) {
        var s = new d(t, e, i, o, n);
        return this.symbols.push(s), s
    },u.prototype.get_sym = function (t) {
        return this.symbols[t]
    },u.prototype.out = function (t, e) {
        if (this.nonseriesUpdate) {
            var i = $.extend({}, this.nonseriesUpdate)
            ;e.splice(0, 0, t.time), i.lastBar = e, this.host.nonseriesOut(i)
        } else this.host.out(t, e)
    },u.prototype.start = function () {
        function t(t, o) {
            var n = u.feed.subscribe(t.tickerid, t.period, t.periodBase, function (t) {
                "series" == (t.nonseries ? "nonseries" : "series") ? e.update(o, t) : t.lastBar ? (e.nonseriesUpdate = t, t.lastBar.isLastBar = !0, e.symbols[0].lastbar(t.lastBar), e.nonseriesUpdate = null) : s.nonseriesOut(t)
            }, s.onErrorCallback, s.symbolInfo, s.sessionId, s.rangeExtension);
            i.push(n)
        }

        var e, i, o, n, s = this.host;
        for (this._script = new p(s.tickerid, s.period, s.periodBase || s.period, this, s.body, this.out.bind(this), s.input, s.symbolInfo.minmov / s.symbolInfo.pricescale, s.nonseriesOut), e = this, i = [], o = this.symbols, n = 0; n < o.length; n++) t(o[n], n);
        this.subscription = i
    },u.prototype.stop = function () {
        var t, e = this.subscription;
        if (!e && !this._script) return void console.warn("Recurring script engine stop happened.");
        for (t = 0; t < e.length; t++) u.feed.unsubscribe(e[t]);
        this.subscription = null, this._script.stop(), this._script = null, this.symbols = null
    },u.prototype.update = function (t, e) {
        var i, o;
        if (!e) return void console.error("Unexpected barset = null");
        i = this.symbols[t], this.isRecalculated ? (o = e.bar(e.count() - 1), o.isBarClosed = e.isLastBarClosed(), o.isLastBar = !0, i.lastbar(o)) : this.barsets[t] || (this.barsets[t] = e, i.set_symbolinfo(e.symbolinfo()), this.recalc())
    },u.prototype.recalc = function () {
        var t, e, i, o, n, s, r, a = this.symbols;
        for (t = 0; t < a.length; t++) if (!this.barsets[t]) return;
        for (e = a.length - 1; e >= 0; e--) for (i = a[e], o = this.barsets[e], n = o.count(), s = 0; s < n; s++) r = o.bar(s), r.isLastBar = s === n - 1, r.isBarClosed = !r.isLastBar || o.isLastBarClosed(), i.lastbar(r);
        this.isRecalculated = !0, this.barsets[0] && this.barsets[0].endOfData && this.host.setNoMoreData(), this.host.recalc(this, {endOfData: this.barsets[0].endOfData, nextTime: this.barsets[0].nextTime})
    },u.feed = {
        subscribe: function (t, e, i, o) {
            console.error("must be initialized with setupFeed")
        }, unsubscribe: function (t) {
            console.error("must be initialized with setupFeed")
        }
    },f.prototype.stop = function () {
        this.runner.stop()
    },m.prototype.symbolinfo = function () {
        return this.info
    },m.prototype.isLastBarClosed = function () {
        return this.isBarClosed
    },m.prototype.setLastBarClosed = function (t) {
        this.isBarClosed = t
    },m.prototype.bar = function (t) {
        return this.bars[t]
    },m.prototype.count = function () {
        return this.bars.length
    },m.prototype.add = function (t, e) {
        var i = t, o = this.bars, n = o.length, s = i.time, r = 0 === n ? NaN : o[n - 1].time;
        0 === n || r < s ? o.push(i) : r === s ? o[n - 1] = i : console.error("time order violation, prev: " + new Date(r).toUTCString() + ", cur: " + new Date(s).toUTCString()), this.isBarClosed = !!e
    },g.prototype.init = function (t) {
        this.bb = d.newBarBuilder(this.period, t.symbol.session), this.bbEmptyBars = this.generateEmptyBars ? d.newBarBuilder(this.period, t.symbol.session) : void 0
    },g.prototype.extrapolate = function (t, e) {
        return isNaN(t) || isNaN(e) ? void 0 : w.extrapolateBarsFrontToTime(this.bbEmptyBars, t, e)
    },g.prototype.main = function (t) {
        var e = t.symbol.time, i = this.bb.alignTime(e), o = t.new_var(i), n = S.na(i), s = o.get(1), r = S.na(s) ? 1 : S.neq(i, s), a = t.new_var(), l = t.new_var(), h = t.new_var(), c = t.new_var(), d = a.get(1), p = l.get(1), u = h.get(1),
            _ = c.get(1), f = n ? NaN : r ? S.open(t) : d, m = n ? NaN : r ? S.high(t) : S.max(S.high(t), p), g = n ? NaN : r ? S.low(t) : S.min(S.low(t), u), v = n ? NaN : S.close(t), y = n ? NaN : r ? S.volume(t) : S.volume(t) + _,
            b = n ? NaN : e, w = t.symbol.isBarClosed && this.bb.isLastBar(0, e), T = this.generateEmptyBars && r ? this.extrapolate(s, i) : void 0, C = t.new_var(S.close(t)), x = C.get(1), P = T instanceof Array ? x : NaN;
        return a.set(f), l.set(m), h.set(g), c.set(y), [i, f, m, g, v, y, b, w, T, P]
    },v.prototype.main = function (t) {
        return [S.open(t), S.high(t), S.low(t), S.close(t), S.volume(t), S.updatetime(t)]
    },{Std: S, Series: h, Symbol: d, SymbolInfo: c, StudyEngine: f, BarSet: m, OHLCV: v, BarBuilder: g, setupFeed: _}
}

var a = [{
    name: "Relative Strength Index",
    metainfo: {
        _metainfoVersion: 27,
        isTVScript: !1,
        isTVScriptStub: !1,
        is_hidden_study: !1,
        defaults: {
            styles: {
                plot_0: {
                    linestyle: 0,
                    linewidth: 1,
                    plottype: 0,
                    trackPrice: !1,
                    transparency: 35,
                    visible: !0,
                    color: "#800080"
                }
            },
            precision: 4,
            bands: [{
                color: "#808080",
                linestyle: 2,
                linewidth: 1,
                visible: !0,
                value: 70
            },
                {
                    color: "#808080",
                    linestyle: 2,
                    linewidth: 1,
                    visible: !0,
                    value: 30
                }],
            filledAreasStyle: {
                fill_0: {
                    color: "#800080",
                    transparency: 90,
                    visible: !0
                }
            },
            inputs: {
                in_0: 14
            }
        },
        plots: [{
            id: "plot_0",
            type: "line"
        }],
        styles: {
            plot_0: {
                title: "Plot",
                histogramBase: 0,
                joinPoints: !1
            }
        },
        description: "Relative Strength Index",
        shortDescription: "RSI",
        is_price_study: !1,
        bands: [{
            id: "hline_0",
            name: "UpperLimit"
        },
            {
                id: "hline_1",
                name: "LowerLimit"
            }],
        filledAreas: [{
            id: "fill_0",
            objAId: "hline_0",
            objBId: "hline_1",
            type: "hline_hline",
            title: "Hlines Background"
        }],
        inputs: [{
            id: "in_0",
            name: "Length",
            defval: 14,
            type: "integer",
            min: 1,
            max: 2e3
        }],
        id: "Relative Strength Index@tv-basicstudies-1",
        scriptIdPart: "",
        name: "Relative Strength Index"
    },
    constructor: function () {
        this.f_0 = function (t) {
            return o.Std.max(t, 0)
        },
            this.f_1 = function (t) {
                return -o.Std.min(t, 0)
            },
            this.f_2 = function (t, e) {
                return o.Std.eq(t, 0) ? 100 : o.Std.eq(e, 0) ? 0 : 100 - 100 / (1 + e / t)
            },
            this.main = function (t, e) {
                var i, n, s, r, a, l, h, c, d, p, u, _;
                return this._context = t,
                    this._input = e,
                    i = o.Std.close(this._context),
                    n = this._input(0),
                    s = this._context.new_var(i),
                    r = o.Std.change(s),
                    a = this.f_0(r),
                    l = this._context.new_var(a),
                    h = o.Std.rma(l, n, this._context),
                    c = this.f_1(r),
                    d = this._context.new_var(c),
                    p = o.Std.rma(d, n, this._context),
                    u = this.f_2(p, h),
                    _ = u,
                    [_]
            }
    }
}, {
    name: "Stochastic RSI",
    metainfo: {
        _metainfoVersion: 27,
        isTVScript: !1,
        isTVScriptStub: !1,
        is_hidden_study: !1,
        defaults: {
            styles: {
                plot_0: {linestyle: 0, linewidth: 1, plottype: 0, trackPrice: !1, transparency: 35, visible: !0, color: "#0000FF"},
                plot_1: {linestyle: 0, linewidth: 1, plottype: 0, trackPrice: !1, transparency: 35, visible: !0, color: "#FF0000"}
            },
            precision: 4,
            bands: [{color: "#808080", linestyle: 2, linewidth: 1, visible: !0, value: 80}, {color: "#808080", linestyle: 2, linewidth: 1, visible: !0, value: 20}],
            filledAreasStyle: {fill_0: {color: "#800080", transparency: 80, visible: !0}},
            inputs: {in_0: 14, in_1: 14, in_2: 3, in_3: 3}
        },
        plots: [{id: "plot_0", type: "line"}, {id: "plot_1", type: "line"}],
        styles: {plot_0: {title: "%K", histogramBase: 0, joinPoints: !1}, plot_1: {title: "%D", histogramBase: 0, joinPoints: !1}},
        description: "Stochastic RSI",
        shortDescription: "Stoch RSI",
        is_price_study: !1,
        bands: [{id: "hline_0", name: "UpperLimit"}, {id: "hline_1", name: "LowerLimit"}],
        filledAreas: [{id: "fill_0", objAId: "hline_0", objBId: "hline_1", type: "hline_hline", title: "Hlines Background"}],
        inputs: [
            {id: "in_0", name: "lengthRSI", defval: 14, type: "integer", min: 1, max: 1e4},
            {id: "in_1", name: "lengthStoch", defval: 14, type: "integer", min: 1, max: 1e4},
            {
            id: "in_2",
            name: "smoothK",
            defval: 3,
            type: "integer",
            min: 1,
            max: 1e4
        }, {id: "in_3", name: "smoothD", defval: 3, type: "integer", min: 1, max: 1e4}],
        id: "Stochastic RSI@tv-basicstudies-1",
        scriptIdPart: "",
        name: "Stochastic RSI"
    },
    constructor: function () {
        this.main = function (t, e) {
            var i, n, s, r, a, l, h, c, d, p, u, _, f, m, g, v;
            return this._context = t,
                this._input = e,
                i = o.Std.close(this._context),
                n = this._input(0),
                s = this._input(1),
                r = this._input(2),
                a = this._input(3),
                l = o.Std.rsi(i, n),
                h = this._context.new_var(l),
                c = this._context.new_var(l),
                d = this._context.new_var(l),
                p = o.Std.stoch(h, c, d, s, this._context),
                u = this._context.new_var(p),
                _ = o.Std.sma(u, r, this._context),
                f = this._context.new_var(_),
                m = o.Std.sma(f, a, this._context),
                g = _,
                v = m,
                [g, v]
        }
    }
}];