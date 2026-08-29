import 'package:flutter/material.dart';

import '../models/transaction.dart';
import '../theme.dart';
import '../utils/format.dart';
import 'category_icon.dart';

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

  @override
  Widget build(BuildContext context) {
    final color = parseColor(tx.categoryColor ?? '#2563EB');
    final isIncome = tx.type == 'income';
    final hasNote = (tx.note ?? '').trim().isNotEmpty;
    final primaryTitle = hasNote ? tx.note! : (tx.categoryName ?? 'Transaksi');
    final secondarySubtitle = hasNote
        ? '${tx.categoryName ?? "Umum"} · ${tx.walletName ?? "Dompet"}'
        : (tx.walletName ?? 'Dompet');

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border.withValues(alpha: 0.8), width: 1),
        boxShadow: AppColors.cardShadow,
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(18),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            child: Row(
              children: [
                // Category Icon with circular/squircle backdrop
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Center(
                    child: Icon(categoryIcon(tx.categoryIcon ?? 'other'), color: color, size: 22),
                  ),
                ),
                const SizedBox(width: 12),

                // Center Column: Title, Accent Progress/Category bar, Subtitle
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        primaryTitle,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w800,
                          color: AppColors.textPrimary,
                          letterSpacing: -0.2,
                        ),
                      ),
                      const SizedBox(height: 5),

                      // Accent category indicator bar
                      Container(
                        height: 3,
                        width: 72,
                        decoration: BoxDecoration(
                          color: color.withValues(alpha: 0.8),
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                      const SizedBox(height: 5),

                      Text(
                        secondarySubtitle,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 11.5,
                          fontWeight: FontWeight.w500,
                          color: AppColors.textMuted,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 10),

                // Right Column: Bold Amount & Date
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      '${isIncome ? '+' : '-'} ${symbol != 'Rp' ? symbol : 'Rp'} ${Fmt.money0(tx.amount)}',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w900,
                        color: isIncome ? AppColors.income : AppColors.textPrimary,
                        letterSpacing: -0.3,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.bg,
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(color: AppColors.borderLight),
                      ),
                      child: Text(
                        Fmt.dateDay(tx.date),
                        style: const TextStyle(
                          fontSize: 10.5,
                          fontWeight: FontWeight.w600,
                          color: AppColors.textMuted,
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
