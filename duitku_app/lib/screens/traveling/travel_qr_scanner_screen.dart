import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

import '../../theme.dart';

class TravelQrScannerScreen extends StatefulWidget {
  const TravelQrScannerScreen({super.key});

  @override
  State<TravelQrScannerScreen> createState() => _TravelQrScannerScreenState();
}

class _TravelQrScannerScreenState extends State<TravelQrScannerScreen> {
  final MobileScannerController _controller = MobileScannerController(
    detectionSpeed: DetectionSpeed.normal,
    facing: CameraFacing.back,
    torchEnabled: false,
  );
  bool _scanned = false;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _onDetect(BarcodeCapture capture) {
    if (_scanned) return;
    final barcode = capture.barcodes.firstOrNull;
    final rawValue = barcode?.rawValue;
    if (rawValue != null && rawValue.isNotEmpty) {
      _scanned = true;
      _controller.stop();
      if (mounted) Navigator.pop(context, rawValue);
    }
  }

  @override
  Widget build(BuildContext context) {
    final cutOutSize = MediaQuery.of(context).size.width * 0.75;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Scan Tiket'),
        actions: [
          ValueListenableBuilder(
            valueListenable: _controller,
            builder: (context, state, _) {
              return IconButton(
                icon: Icon(
                  state.torchState == TorchState.on ? Icons.flash_on : Icons.flash_off,
                  color: state.torchState == TorchState.on ? Colors.yellow : Colors.white,
                ),
                onPressed: () => _controller.toggleTorch(),
              );
            },
          ),
        ],
      ),
      body: Stack(
        fit: StackFit.expand,
        children: [
          MobileScanner(
            controller: _controller,
            onDetect: _onDetect,
          ),
          Container(
            color: Colors.black54,
            child: Center(
              child: Container(
                width: cutOutSize,
                height: cutOutSize,
                decoration: BoxDecoration(
                  border: Border.all(color: AppColors.primary, width: 4),
                  borderRadius: BorderRadius.circular(16),
                  color: Colors.transparent,
                ),
              ),
            ),
          ),
          const Positioned(
            left: 0,
            right: 0,
            bottom: 80,
            child: Center(
              child: Text(
                'Arahkan kamera ke QR / barcode tiket',
                style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600, shadows: [
                  Shadow(color: Colors.black54, blurRadius: 4),
                ]),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
