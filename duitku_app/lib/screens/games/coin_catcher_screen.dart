import 'dart:async';
import 'dart:math';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:shared_preferences/shared_preferences.dart';

enum ItemType { coin, bill, diamond, bomb }

class FallingItem {
  double x; // 0.0 to 1.0
  double y; // 0.0 to 1.0
  double speed;
  ItemType type;

  FallingItem({
    required this.x,
    required this.y,
    required this.speed,
    required this.type,
  });
}

class CoinCatcherScreen extends StatefulWidget {
  const CoinCatcherScreen({super.key});

  @override
  State<CoinCatcherScreen> createState() => _CoinCatcherScreenState();
}

class _CoinCatcherScreenState extends State<CoinCatcherScreen> {
  // Player position (0.0 to 1.0)
  double playerX = 0.5;
  double basketWidth = 0.22;

  // Falling items
  List<FallingItem> items = [];
  Timer? gameLoopTimer;
  Timer? spawnTimer;

  // Game Stats
  int score = 0;
  int highScore = 0;
  int lives = 3;
  int combo = 0;
  bool isPlaying = false;
  bool isPaused = false;
  bool isGameOver = false;

  @override
  void initState() {
    super.initState();
    _loadHighScore();
    _startNewGame();
  }

  @override
  void dispose() {
    gameLoopTimer?.cancel();
    spawnTimer?.cancel();
    super.dispose();
  }

  Future<void> _loadHighScore() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      highScore = prefs.getInt('duitku_catcher_highscore') ?? 0;
    });
  }

  Future<void> _saveHighScore() async {
    if (score > highScore) {
      highScore = score;
      final prefs = await SharedPreferences.getInstance();
      await prefs.setInt('duitku_catcher_highscore', highScore);
    }
  }

  void _startNewGame() {
    gameLoopTimer?.cancel();
    spawnTimer?.cancel();
    setState(() {
      score = 0;
      lives = 3;
      combo = 0;
      playerX = 0.5;
      items.clear();
      isGameOver = false;
      isPaused = false;
      isPlaying = true;
    });

    // 60 FPS physics loop (~16ms)
    gameLoopTimer = Timer.periodic(const Duration(milliseconds: 16), (timer) {
      if (!isPaused && isPlaying && !isGameOver) {
        _updatePhysics();
      }
    });

    // Item spawner loop
    spawnTimer = Timer.periodic(const Duration(milliseconds: 700), (timer) {
      if (!isPaused && isPlaying && !isGameOver) {
        _spawnItem();
      }
    });
  }

  void _spawnItem() {
    final rand = Random();
    final itemX = 0.05 + rand.nextDouble() * 0.9;
    final speed = 0.007 + (score ~/ 100) * 0.001 + rand.nextDouble() * 0.004;

    ItemType type;
    double roll = rand.nextDouble();
    if (roll < 0.50) {
      type = ItemType.coin; // 50%
    } else if (roll < 0.70) {
      type = ItemType.bill; // 20%
    } else if (roll < 0.85) {
      type = ItemType.diamond; // 15%
    } else {
      type = ItemType.bomb; // 15%
    }

    setState(() {
      items.add(FallingItem(x: itemX, y: 0.0, speed: speed, type: type));
    });
  }

  void _updatePhysics() {
    List<FallingItem> activeItems = [];
    bool stateChanged = false;

    for (final item in items) {
      item.y += item.speed;

      // Check collision with player basket (around y = 0.88)
      if (item.y >= 0.84 && item.y <= 0.92) {
        double leftBound = playerX - (basketWidth / 2);
        double rightBound = playerX + (basketWidth / 2);

        if (item.x >= leftBound && item.x <= rightBound) {
          // Caught!
          _handleCaughtItem(item);
          stateChanged = true;
          continue; // Don't keep in active items
        }
      }

      // Check if dropped past bottom
      if (item.y > 1.0) {
        if (item.type == ItemType.coin || item.type == ItemType.diamond) {
          // Missed positive coin -> reset combo
          combo = 0;
        }
        stateChanged = true;
        continue;
      }

      activeItems.add(item);
    }

    if (stateChanged) {
      setState(() {
        items = activeItems;
      });
    }
  }

  void _handleCaughtItem(FallingItem item) {
    switch (item.type) {
      case ItemType.coin:
        combo++;
        score += 10 * (combo > 5 ? 2 : 1);
        HapticFeedback.lightImpact();
        break;
      case ItemType.bill:
        combo++;
        score += 25 * (combo > 5 ? 2 : 1);
        HapticFeedback.selectionClick();
        break;
      case ItemType.diamond:
        combo += 2;
        score += 50 * (combo > 5 ? 2 : 1);
        HapticFeedback.mediumImpact();
        break;
      case ItemType.bomb:
        combo = 0;
        lives--;
        HapticFeedback.heavyImpact();
        if (lives <= 0) {
          _triggerGameOver();
        }
        break;
    }
    _saveHighScore();
  }

  void _triggerGameOver() {
    gameLoopTimer?.cancel();
    spawnTimer?.cancel();
    _saveHighScore();
    setState(() {
      isGameOver = true;
      isPlaying = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F172A),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0F172A),
        elevation: 0,
        foregroundColor: Colors.white,
        title: const Row(
          children: [
            Icon(Icons.savings_rounded, color: Color(0xFFFBBF24), size: 22),
            SizedBox(width: 8),
            Text(
              'TANGKAP CUAN',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w900,
                letterSpacing: 1.2,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: Icon(
              isPaused ? Icons.play_arrow_rounded : Icons.pause_rounded,
              color: const Color(0xFFFBBF24),
            ),
            onPressed: isGameOver
                ? null
                : () {
                    setState(() {
                      isPaused = !isPaused;
                    });
                  },
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Color(0xFF38BDF8)),
            onPressed: _startNewGame,
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            // Status Bar (Lives, Score, Combo, Highscore)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  // Lives / Hearts
                  Row(
                    children: List.generate(3, (index) {
                      return Icon(
                        index < lives ? Icons.favorite_rounded : Icons.favorite_border_rounded,
                        color: const Color(0xFFEF4444),
                        size: 22,
                      );
                    }),
                  ),
                  // Combo
                  if (combo >= 3)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF59E0B),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        '${combo}x COMBO!',
                        style: const TextStyle(
                          color: Colors.black,
                          fontSize: 10,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                    ),
                  // Score & Highscore
                  Row(
                    children: [
                      const Icon(Icons.stars_rounded, color: Color(0xFFFBBF24), size: 18),
                      const SizedBox(width: 4),
                      Text(
                        '$score',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        'Rekor: $highScore',
                        style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            // Game Arena with Touch Detection
            Expanded(
              child: LayoutBuilder(
                builder: (context, constraints) {
                  return GestureDetector(
                    onHorizontalDragUpdate: (details) {
                      setState(() {
                        playerX += details.delta.dx / constraints.maxWidth;
                        playerX = playerX.clamp(0.1, 0.9);
                      });
                    },
                    child: Container(
                      margin: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFF1E293B),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: const Color(0xFF334155), width: 2),
                        gradient: const LinearGradient(
                          begin: Alignment.topCenter,
                          end: Alignment.bottomCenter,
                          colors: [
                            Color(0xFF0F172A),
                            Color(0xFF1E293B),
                          ],
                        ),
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(14),
                        child: Stack(
                          children: [
                            // Falling Items
                            ...items.map((item) {
                              return Positioned(
                                left: item.x * constraints.maxWidth - 16,
                                top: item.y * constraints.maxHeight - 16,
                                child: _buildItemWidget(item.type),
                              );
                            }),

                            // Player Basket / Wallet at bottom
                            Positioned(
                              left: (playerX - (basketWidth / 2)) * constraints.maxWidth,
                              top: 0.86 * constraints.maxHeight,
                              width: basketWidth * constraints.maxWidth,
                              height: 48,
                              child: Container(
                                decoration: BoxDecoration(
                                  gradient: const LinearGradient(
                                    colors: [Color(0xFF059669), Color(0xFF10B981)],
                                  ),
                                  borderRadius: BorderRadius.circular(14),
                                  border: Border.all(color: const Color(0xFF34D399), width: 2),
                                  boxShadow: [
                                    BoxShadow(
                                      color: const Color(0xFF10B981).withValues(alpha: 0.4),
                                      blurRadius: 10,
                                      offset: const Offset(0, 3),
                                    ),
                                  ],
                                ),
                                child: const Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Icon(Icons.account_balance_wallet_rounded,
                                        color: Colors.white, size: 24),
                                    SizedBox(width: 4),
                                    Text(
                                      'DOMPET',
                                      style: TextStyle(
                                        color: Colors.white,
                                        fontSize: 10,
                                        fontWeight: FontWeight.w900,
                                        letterSpacing: 1,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),

                            // Game Over Overlay
                            if (isGameOver)
                              Container(
                                color: Colors.black.withValues(alpha: 0.85),
                                alignment: Alignment.center,
                                child: Column(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Icon(Icons.heart_broken_rounded,
                                        color: Color(0xFFEF4444), size: 54),
                                    const SizedBox(height: 8),
                                    const Text(
                                      'NYAWA HABIS!',
                                      style: TextStyle(
                                        color: Color(0xFFEF4444),
                                        fontSize: 20,
                                        fontWeight: FontWeight.w900,
                                        letterSpacing: 1.5,
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      'Skor Akhir: $score',
                                      style: const TextStyle(
                                          color: Colors.white,
                                          fontSize: 15,
                                          fontWeight: FontWeight.bold),
                                    ),
                                    const SizedBox(height: 14),
                                    ElevatedButton.icon(
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: const Color(0xFF10B981),
                                        foregroundColor: Colors.white,
                                        padding: const EdgeInsets.symmetric(
                                            horizontal: 22, vertical: 10),
                                      ),
                                      onPressed: _startNewGame,
                                      icon: const Icon(Icons.replay_rounded),
                                      label: const Text('MAIN LAGI'),
                                    ),
                                  ],
                                ),
                              ),
                          ],
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),

            // On-screen Left / Right Helper Buttons
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 0, 20, 14),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF1E293B),
                      foregroundColor: const Color(0xFF38BDF8),
                      padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 12),
                      side: const BorderSide(color: Color(0xFF334155)),
                    ),
                    onPressed: () {
                      setState(() {
                        playerX = (playerX - 0.1).clamp(0.1, 0.9);
                      });
                    },
                    icon: const Icon(Icons.arrow_back_rounded),
                    label: const Text('KIRI'),
                  ),
                  const Text(
                    'Geser Layar / Tombol',
                    style: TextStyle(color: Color(0xFF64748B), fontSize: 10),
                  ),
                  ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF1E293B),
                      foregroundColor: const Color(0xFF38BDF8),
                      padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 12),
                      side: const BorderSide(color: Color(0xFF334155)),
                    ),
                    onPressed: () {
                      setState(() {
                        playerX = (playerX + 0.1).clamp(0.1, 0.9);
                      });
                    },
                    icon: const Icon(Icons.arrow_forward_rounded),
                    label: const Text('KANAN'),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildItemWidget(ItemType type) {
    switch (type) {
      case ItemType.coin:
        return Container(
          width: 32,
          height: 32,
          decoration: const BoxDecoration(
            shape: BoxShape.circle,
            color: Color(0xFFFBBF24),
            boxShadow: [
              BoxShadow(color: Color(0xFFFBBF24), blurRadius: 8),
            ],
          ),
          child: const Center(
            child: Text(
              '🪙',
              style: TextStyle(fontSize: 18),
            ),
          ),
        );
      case ItemType.bill:
        return Container(
          width: 34,
          height: 24,
          decoration: BoxDecoration(
            color: const Color(0xFF10B981),
            borderRadius: BorderRadius.circular(4),
            boxShadow: const [
              BoxShadow(color: Color(0xFF10B981), blurRadius: 8),
            ],
          ),
          child: const Center(
            child: Text(
              '💵',
              style: TextStyle(fontSize: 16),
            ),
          ),
        );
      case ItemType.diamond:
        return const Center(
          child: Text(
            '💎',
            style: TextStyle(fontSize: 26),
          ),
        );
      case ItemType.bomb:
        return const Center(
          child: Text(
            '💣',
            style: TextStyle(fontSize: 26),
          ),
        );
    }
  }
}
