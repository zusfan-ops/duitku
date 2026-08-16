import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../models/pos_order.dart';
import '../../theme.dart';
import '../../utils/format.dart';

class PosReceiptSheet extends StatelessWidget {
  final PosOrder order;
  final String symbol;
  final VoidCallback onNewOrder;

  const PosReceiptSheet({
    super.key,
    required this.order,
    this.symbol = 'Rp',
    required this.onNewOrder,
  });

  Future<void> _shareWhatsApp(BuildContext context) async {
    String text = '*STRUK PEMBAYARAN*\n';
    text += 'No: #${order.orderNumber}\n';
    text += 'Tgl: ${order.date}\n';
    if (order.customerName != null && order.customerName!.isNotEmpty) {
      text += 'Pelanggan: ${order.customerName}\n';
    }
    text += 'Metode: ${order.paymentMethod.toUpperCase()}\n';
    text += '--------------------------------\n';
    for (final it in order.items) {
      text += '${it.productName} x${it.qty} = ${Fmt.money(it.subtotal, symbol: symbol)}\n';
    }
    text += '--------------------------------\n';
    text += '*TOTAL: ${Fmt.money(order.totalAmount, symbol: symbol)}*\n';
    if (order.paymentMethod == 'cash') {
      text += 'Bayar: ${Fmt.money(order.cashReceived, symbol: symbol)}\n';
      text += 'Kembali: ${Fmt.money(order.changeAmount, symbol: symbol)}\n';
    }
    text += '\nTerima kasih telah berbelanja! 🙏';

    final phone = order.customerPhone?.replaceAll(RegExp(r'\D'), '') ?? '';
    final uri = phone.isNotEmpty
        ? Uri.parse('https://api.whatsapp.com/send?phone=$phone&text=${Uri.encodeComponent(text)}')
        : Uri.parse('https://api.whatsapp.com/send?text=${Uri.encodeComponent(text)}');

    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Tidak dapat membuka WhatsApp')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      initialChildSize: 0.85,
      minChildSize: 0.5,
      maxChildSize: 0.95,
      expand: false,
      builder: (ctx, scrollCtrl) {
        return Column(
          children: [
            const SizedBox(height: 12),
            Container(
              width: 36,
              height: 4,
              decoration: BoxDecoration(
                color: AppColors.border,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    '🧾 Struk Transaksi',
                    style: TextStyle(
                      fontSize: 17,
                      fontWeight: FontWeight.w800,
                      color: AppColors.textPrimary,
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, size: 20),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
            ),
            const Divider(height: 1),
            Expanded(
              child: ListView(
                controller: scrollCtrl,
                padding: const EdgeInsets.all(16),
                children: [
                  // Thermal Receipt Box
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: Colors.grey.shade300),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.04),
                          blurRadius: 8,
                          offset: const Offset(0, 3),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        const Center(
                          child: Text(
                            'DUITKU MINI POS',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w900,
                              fontFamily: 'Courier',
                              color: Colors.black87,
                            ),
                          ),
                        ),
                        Center(
                          child: Text(
                            '${order.date} ${order.createdAt != null && order.createdAt!.length >= 16 ? order.createdAt!.substring(11, 16) : ''}',
                            style: const TextStyle(
                              fontSize: 11,
                              fontFamily: 'Courier',
                              color: Colors.black54,
                            ),
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          '- - - - - - - - - - - - - - - - - - - - - - -',
                          textAlign: TextAlign.center,
                          style: TextStyle(color: Colors.black38, fontFamily: 'Courier'),
                        ),
                        const SizedBox(height: 4),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              '#${order.orderNumber}',
                              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, fontFamily: 'Courier', color: Colors.black87),
                            ),
                            Text(
                              order.paymentMethod.toUpperCase(),
                              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, fontFamily: 'Courier', color: Colors.black87),
                            ),
                          ],
                        ),
                        if (order.customerName != null && order.customerName!.isNotEmpty) ...[
                          const SizedBox(height: 2),
                          Text(
                            'Pelanggan: ${order.customerName}',
                            style: const TextStyle(fontSize: 11, fontFamily: 'Courier', color: Colors.black87),
                          ),
                        ],
                        const SizedBox(height: 4),
                        const Text(
                          '- - - - - - - - - - - - - - - - - - - - - - -',
                          textAlign: TextAlign.center,
                          style: TextStyle(color: Colors.black38, fontFamily: 'Courier'),
                        ),
                        const SizedBox(height: 6),
                        ...order.items.map((it) => Padding(
                              padding: const EdgeInsets.symmetric(vertical: 3),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Expanded(
                                    child: Text(
                                      '${it.productName} x${it.qty}',
                                      style: const TextStyle(fontSize: 12, fontFamily: 'Courier', color: Colors.black87),
                                    ),
                                  ),
                                  Text(
                                    Fmt.money(it.subtotal, symbol: symbol),
                                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, fontFamily: 'Courier', color: Colors.black87),
                                  ),
                                ],
                              ),
                            )),
                        const SizedBox(height: 6),
                        const Text(
                          '- - - - - - - - - - - - - - - - - - - - - - -',
                          textAlign: TextAlign.center,
                          style: TextStyle(color: Colors.black38, fontFamily: 'Courier'),
                        ),
                        const SizedBox(height: 4),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text(
                              'TOTAL:',
                              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w900, fontFamily: 'Courier', color: Colors.black),
                            ),
                            Text(
                              Fmt.money(order.totalAmount, symbol: symbol),
                              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w900, fontFamily: 'Courier', color: Colors.black),
                            ),
                          ],
                        ),
                        if (order.paymentMethod == 'cash') ...[
                          const SizedBox(height: 3),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text('Bayar (Tunai):', style: TextStyle(fontSize: 11, fontFamily: 'Courier', color: Colors.black87)),
                              Text(Fmt.money(order.cashReceived, symbol: symbol), style: const TextStyle(fontSize: 11, fontFamily: 'Courier', color: Colors.black87)),
                            ],
                          ),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text('Kembali:', style: TextStyle(fontSize: 11, fontFamily: 'Courier', color: Colors.black87)),
                              Text(Fmt.money(order.changeAmount, symbol: symbol), style: const TextStyle(fontSize: 11, fontFamily: 'Courier', color: Colors.black87)),
                            ],
                          ),
                        ],
                        const SizedBox(height: 8),
                        const Text(
                          '- - - - - - - - - - - - - - - - - - - - - - -',
                          textAlign: TextAlign.center,
                          style: TextStyle(color: Colors.black38, fontFamily: 'Courier'),
                        ),
                        const SizedBox(height: 6),
                        const Center(
                          child: Text(
                            'Terima kasih atas kunjungan Anda!\nSimpan struk ini sebagai bukti pembayaran.',
                            textAlign: TextAlign.center,
                            style: TextStyle(fontSize: 10, fontFamily: 'Courier', color: Colors.black54),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Action Buttons
                  ElevatedButton.icon(
                    onPressed: () => _shareWhatsApp(context),
                    icon: const Text('💬', style: TextStyle(fontSize: 18)),
                    label: const Text('Kirim Struk via WhatsApp', style: TextStyle(fontWeight: FontWeight.w800)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF25D366),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                  const SizedBox(height: 10),
                  ElevatedButton.icon(
                    onPressed: () {
                      Navigator.pop(ctx);
                      onNewOrder();
                    },
                    icon: const Icon(Icons.add_shopping_cart, size: 18),
                    label: const Text('Transaksi Baru', style: TextStyle(fontWeight: FontWeight.w800)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFEA580C),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                ],
              ),
            ),
          ],
        );
      },
    );
  }
}
