import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'coin_catcher_screen.dart';
import 'money_2048_screen.dart';
import 'tetris_screen.dart';

class GameHubScreen extends StatefulWidget {
  const GameHubScreen({super.key});

  @override
  State<GameHubScreen> createState() => _GameHubScreenState();
}

class _GameHubScreenState extends State<GameHubScreen> {
  int tetrisHighScore = 0;
  int money2048HighScore = 0;
  int catcherHighScore = 0;
  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadAllHighScores();
  }

  Future<void> _loadAllHighScores() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      tetrisHighScore = prefs.getInt('duitku_tetris_highscore') ?? 0;
      money2048HighScore = prefs.getInt('duitku_2048_highscore') ?? 0;
      catcherHighScore = prefs.getInt('duitku_catcher_highscore') ?? 0;
      isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0B0F19), // Dark arcade theme
      appBar: AppBar(
        backgroundColor: const Color(0xFF0B0F19),
        elevation: 0,
        foregroundColor: Colors.white,
        title: const Row(
          children: [
            Icon(Icons.sports_esports_rounded, color: Color(0xFF38BDF8), size: 24),
            SizedBox(width: 8),
            Text(
              'DUITKU ARCADE',
              style: TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.w900,
                letterSpacing: 1.5,
              ),
            ),
          ],
        ),
      ),
      body: RefreshIndicator(
        onRefresh: _loadAllHighScores,
        color: const Color(0xFF38BDF8),
        backgroundColor: const Color(0xFF1E293B),
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 40),
          children: [
            // Arcade Header Banner
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF1E1B4B), Color(0xFF312E81), Color(0xFF1E293B)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(18),
                border: Border.all(color: const Color(0xFF4338CA).withValues(alpha: 0.8), width: 1.5),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF4338CA).withValues(alpha: 0.3),
                    blurRadius: 20,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: const Color(0xFF10B981),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Text(
                          'MINI-GAMES',
                          style: TextStyle(
                            color: Colors.black,
                            fontSize: 10,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 1,
                          ),
                        ),
                      ),
                      const Spacer(),
                      const Icon(Icons.flash_on_rounded, color: Color(0xFFFBBF24), size: 20),
                      const SizedBox(width: 4),
                      const Text(
                        'Anti-Stres & Kasual',
                        style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  const Text(
                    'Istirahat Sejenak & Raih Skor Tertinggi!',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.w900,
                      height: 1.2,
                    ),
                  ),
                  const SizedBox(height: 6),
                  const Text(
                    'Mainkan game retro klasik seru kapan saja tanpa perlu koneksi internet.',
                    style: TextStyle(color: Color(0xFFCBD5E1), fontSize: 12),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 22),

            const Text(
              'PILIH PERMAINAN',
              style: TextStyle(
                color: Color(0xFF94A3B8),
                fontSize: 11,
                fontWeight: FontWeight.w900,
                letterSpacing: 1.2,
              ),
            ),
            const SizedBox(height: 12),

            // Game 1: Tetris (Brick Master)
            _buildGameCard(
              title: 'Brick Master (Tetris)',
              subtitle: 'Susun balok neon retro, bersihkan baris dan raih kombo 4-line Tetris!',
              icon: Icons.grid_view_rounded,
              gradient: const [Color(0xFF0284C7), Color(0xFF06B6D4)],
              borderColor: const Color(0xFF06B6D4),
              highScore: tetrisHighScore,
              badge: 'POPULER',
              badgeColor: const Color(0xFF06B6D4),
              onPlay: () async {
                await Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const TetrisScreen()),
                );
                _loadAllHighScores();
              },
            ),

            const SizedBox(height: 14),

            // Game 2: Money Merge 2048
            _buildGameCard(
              title: 'Money Merge 2048',
              subtitle: 'Gabungkan koin dan pecahan rupiah hingga mencapai Rp 2.000.000!',
              icon: Icons.monetization_on_rounded,
              gradient: const [Color(0xFFD97706), Color(0xFFF59E0B)],
              borderColor: const Color(0xFFF59E0B),
              highScore: money2048HighScore,
              badge: 'ASAH OTAK',
              badgeColor: const Color(0xFFF59E0B),
              onPlay: () async {
                await Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const Money2048Screen()),
                );
                _loadAllHighScores();
              },
            ),

            const SizedBox(height: 14),

            // Game 3: Coin Catcher (Tangkap Koin Cuan)
            _buildGameCard(
              title: 'Tangkap Cuan (Coin Catcher)',
              subtitle: 'Gerakkan dompet menangkap koin emas & hindari tagihan impulsif!',
              icon: Icons.savings_rounded,
              gradient: const [Color(0xFF059669), Color(0xFF10B981)],
              borderColor: const Color(0xFF10B981),
              highScore: catcherHighScore,
              badge: 'REFLEKS',
              badgeColor: const Color(0xFF10B981),
              onPlay: () async {
                await Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const CoinCatcherScreen()),
                );
                _loadAllHighScores();
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildGameCard({
    required String title,
    required String subtitle,
    required IconData icon,
    required List<Color> gradient,
    required Color borderColor,
    required int highScore,
    required String badge,
    required Color badgeColor,
    required VoidCallback onPlay,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: borderColor.withValues(alpha: 0.4), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: borderColor.withValues(alpha: 0.15),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onPlay,
          borderRadius: BorderRadius.circular(16),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Icon Box
                    Container(
                      width: 50,
                      height: 50,
                      decoration: BoxDecoration(
                        gradient: LinearGradient(colors: gradient),
                        borderRadius: BorderRadius.circular(14),
                        boxShadow: [
                          BoxShadow(
                            color: gradient.first.withValues(alpha: 0.4),
                            blurRadius: 10,
                            offset: const Offset(0, 3),
                          ),
                        ],
                      ),
                      child: Icon(icon, color: Colors.white, size: 28),
                    ),
                    const SizedBox(width: 14),
                    // Title & Description
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Expanded(
                                child: Text(
                                  title,
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 15,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                decoration: BoxDecoration(
                                  color: badgeColor.withValues(alpha: 0.15),
                                  borderRadius: BorderRadius.circular(6),
                                  border: Border.all(color: badgeColor.withValues(alpha: 0.5)),
                                ),
                                child: Text(
                                  badge,
                                  style: TextStyle(
                                    color: badgeColor,
                                    fontSize: 8.5,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(
                            subtitle,
                            style: const TextStyle(
                              color: Color(0xFF94A3B8),
                              fontSize: 11,
                              height: 1.3,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                const Divider(color: Color(0xFF334155), height: 1),
                const SizedBox(height: 10),
                // Footer: High Score & Play Button
                Row(
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.military_tech_rounded, color: Color(0xFFFBBF24), size: 18),
                        const SizedBox(width: 4),
                        Text(
                          'Rekor Terbaik: $highScore',
                          style: const TextStyle(
                            color: Color(0xFFE2E8F0),
                            fontSize: 11.5,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                    const Spacer(),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                      decoration: BoxDecoration(
                        gradient: LinearGradient(colors: gradient),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            'MAIN',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                              fontWeight: FontWeight.w900,
                              letterSpacing: 0.8,
                            ),
                          ),
                          SizedBox(width: 4),
                          Icon(Icons.play_arrow_rounded, color: Colors.white, size: 16),
                        ],
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
