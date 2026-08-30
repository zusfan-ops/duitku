import 'package:flutter/material.dart';

import '../models/transaction.dart';
import '../utils/format.dart';
import 'category_icon.dart';

class _SoftPalette {
  final Color bg;
  final Color border;
  final Color accent;
  final Color iconBg;

  const _SoftPalette({
    required this.bg,
    required this.border,
    required this.accent,
    required this.iconBg,
  });
}

const List<_SoftPalette> _curatedPalettes = [
  _SoftPalette(
    bg: Color(0xFFF0F9FF), // Sky blue soft
    border: Color(0xFFBAE6FD),
    accent: Color(0xFF0284C7),
    iconBg: Color(0xFFE0F2FE),
  ),
  _SoftPalette(
    bg: Color(0xFFFAF5FF), // Purple soft
    border: Color(0xFFE9D5FF),
    accent: Color(0xFF7C3AED),
    iconBg: Color(0xFFF3E8FF),
  ),
  _SoftPalette(
    bg: Color(0xFFFFF7ED), // Orange/Amber soft
    border: Color(0xFFFFEDD5),
    accent: Color(0xFFEA580C),
    iconBg: Color(0xFFFFEDD5),
  ),
  _SoftPalette(
    bg: Color(0xFFF0FDFA), // Teal soft
    border: Color(0xFF99F6E4),
    accent: Color(0xFF0D9488),
    iconBg: Color(0xFFCCFBF1),
  ),
  _SoftPalette(
    bg: Color(0xFFFFF1F2), // Rose soft
    border: Color(0xFFFECDD3),
    accent: Color(0xFFE11D48),
    iconBg: Color(0xFFFFE4E6),
  ),
  _SoftPalette(
    bg: Color(0xFFEEF2FF), // Indigo soft
    border: Color(0xFFC7D2FE),
    accent: Color(0xFF4F46E5),
    iconBg: Color(0xFFE0E7FF),
  ),
  _SoftPalette(
    bg: Color(0xFFF7FEE7), // Lime soft
    border: Color(0xFFD9F99D),
    accent: Color(0xFF65A30D),
    iconBg: Color(0xFFECFCCB),
  ),
];

const _incomePalette = _SoftPalette(
  bg: Color(0xFFF0FDF4), // Emerald soft
  border: Color(0xFFBBF7D0),
  accent: Color(0xFF16A34A),
  iconBg: Color(0xFFDCFCE7),
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

  _SoftPalette _resolvePalette() {
    if (tx.type == 'income') {
      return _incomePalette;
    }
    final palIndex = tx.id.hashCode.abs() % _curatedPalettes.length;
    return _curatedPalettes[palIndex];
  }

  @override
  Widget build(BuildContext context) {
    final palette = _resolvePalette();
    final isIncome = tx.type == 'income';
    final hasNote = (tx.note ?? '').trim().isNotEmpty;
    final primaryTitle = hasNote ? tx.note! : (tx.categoryName ?? 'Transaksi');
    final categoryName = tx.categoryName ?? 'Umum';
    final walletName = tx.walletName ?? 'Dompet';

    return Container(
      margin: const EdgeInsets.only(bottom: 9),
      decoration: BoxDecoration(
        color: palette.bg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: palette.border,
          width: 1.2,
        ),
        boxShadow: const [
          BoxShadow(
            color: Color(0x0A000000),
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
          splashColor: palette.accent.withValues(alpha: 0.1),
          highlightColor: palette.accent.withValues(alpha: 0.05),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            child: Row(
              children: [
                // Solid Soft Icon box
                Container(
                  width: 42,
                  height: 42,
                  decoration: BoxDecoration(
                    color: palette.iconBg,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: palette.border,
                      width: 1,
                    ),
                  ),
                  child: Center(
                    child: Icon(
                      categoryIcon(tx.categoryIcon ?? 'other'),
                      color: palette.accent,
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
                                color: palette.iconBg,
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(
                                  color: palette.border,
                                  width: 0.8,
                                ),
                              ),
                              child: Text(
                                categoryName,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w800,
                                  color: palette.accent,
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
                        color: isIncome ? const Color(0xFF16A34A) : const Color(0xFF0F172A),
                        letterSpacing: -0.3,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(color: palette.border),
                      ),
                      child: Text(
                        Fmt.dateDay(tx.date),
                        style: TextStyle(
                          fontSize: 10.5,
                          fontWeight: FontWeight.w700,
                          color: palette.accent,
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
