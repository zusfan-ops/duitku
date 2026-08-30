import 'package:flutter/material.dart';

import '../models/transaction.dart';
import '../utils/format.dart';
import 'category_icon.dart';

class _CardTheme {
  final Color bg;
  final Color border;
  final Color accent;
  final Color iconBg;

  const _CardTheme({
    required this.bg,
    required this.border,
    required this.accent,
    required this.iconBg,
  });
}

// 1. Pemasukan -> Hijau (Green)
const _incomeTheme = _CardTheme(
  bg: Color(0xFFF0FDF4),
  border: Color(0xFFBBF7D0),
  accent: Color(0xFF16A34A),
  iconBg: Color(0xFFDCFCE7),
);

// 2. Pengeluaran -> Merah (Red)
const _expenseTheme = _CardTheme(
  bg: Color(0xFFFEF2F2),
  border: Color(0xFFFECDD3),
  accent: Color(0xFFDC2626),
  iconBg: Color(0xFFFEE2E2),
);

// 3. Transaksi Berulang -> Kuning (Yellow / Amber)
const _recurringTheme = _CardTheme(
  bg: Color(0xFFFEFCE8),
  border: Color(0xFFFDE68A),
  accent: Color(0xFFD97706),
  iconBg: Color(0xFFFEF3C7),
);

class TransactionTile extends StatelessWidget {
  final Transaction tx;
  final String symbol;
  final VoidCallback? onTap;

  const TransactionTile({
    super.key,
    required this.tx,
    this.symbol = 'Rp',
    this.onTap,
  });

  _CardTheme _resolveTheme() {
    if (tx.isRecurring) {
      return _recurringTheme;
    }
    if (tx.type == 'income') {
      return _incomeTheme;
    }
    return _expenseTheme;
  }

  @override
  Widget build(BuildContext context) {
    final theme = _resolveTheme();
    final isIncome = tx.type == 'income';
    final hasNote = (tx.note ?? '').trim().isNotEmpty;
    final primaryTitle = hasNote ? tx.note! : (tx.categoryName ?? 'Transaksi');
    final categoryName = tx.categoryName ?? (isIncome ? 'Pemasukan' : 'Pengeluaran');
    final walletName = tx.walletName ?? 'Dompet';

    return Container(
      margin: const EdgeInsets.only(bottom: 9),
      decoration: BoxDecoration(
        color: theme.bg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: theme.border,
          width: 1.2,
        ),
        boxShadow: const [
          BoxShadow(
            color: Color(0x08000000),
            blurRadius: 4,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(16),
          splashColor: theme.accent.withValues(alpha: 0.1),
          highlightColor: theme.accent.withValues(alpha: 0.05),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            child: Row(
              children: [
                // Solid Soft Icon box
                Container(
                  width: 42,
                  height: 42,
                  decoration: BoxDecoration(
                    color: theme.iconBg,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: theme.border,
                      width: 1,
                    ),
                  ),
                  child: Center(
                    child: Icon(
                      tx.isRecurring
                          ? Icons.sync_rounded
                          : categoryIcon(tx.categoryIcon ?? (isIncome ? 'income' : 'other')),
                      color: theme.accent,
                      size: 21,
                    ),
                  ),
                ),
                const SizedBox(width: 12),

                // Center Column: Title, Category Badge, Wallet info
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        primaryTitle,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF0F172A),
                          letterSpacing: -0.2,
                        ),
                      ),
                      const SizedBox(height: 4),

                      Row(
                        children: [
                          Flexible(
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                              decoration: BoxDecoration(
                                color: theme.iconBg,
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(
                                  color: theme.border,
                                  width: 0.8,
                                ),
                              ),
                              child: Text(
                                tx.isRecurring ? 'Berulang' : categoryName,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w800,
                                  color: theme.accent,
                                  letterSpacing: -0.1,
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 6),
                          Flexible(
                            child: Text(
                              walletName,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                fontSize: 11.5,
                                fontWeight: FontWeight.w500,
                                color: Color(0xFF64748B),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 10),

                // Right Column: Bold Amount & Date
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      '${isIncome ? '+' : '-'} ${symbol != 'Rp' ? symbol : 'Rp'} ${Fmt.money0(tx.amount)}',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w900,
                        color: theme.accent,
                        letterSpacing: -0.3,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(color: theme.border),
                      ),
                      child: Text(
                        Fmt.dateDay(tx.date),
                        style: TextStyle(
                          fontSize: 10.5,
                          fontWeight: FontWeight.w700,
                          color: theme.accent,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
