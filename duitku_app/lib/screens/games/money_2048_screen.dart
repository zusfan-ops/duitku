import 'dart:math';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:shared_preferences/shared_preferences.dart';

class Money2048Screen extends StatefulWidget {
  const Money2048Screen({super.key});

  @override
  State<Money2048Screen> createState() => _Money2048ScreenState();
}

class _Money2048ScreenState extends State<Money2048Screen> {
  static const int gridSize = 4;
  late List<List<int>> board;
  List<List<int>>? previousBoard;
  int previousScore = 0;

  int score = 0;
  int highScore = 0;
  bool isGameOver = false;
  bool hasWon = false;
  bool keepPlaying = false;

  @override
  void initState() {
    super.initState();
    _loadHighScore();
    _startNewGame();
  }

  void _initBoard() {
    board = List.generate(gridSize, (_) => List.generate(gridSize, (_) => 0));
  }

  Future<void> _loadHighScore() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      highScore = prefs.getInt('duitku_2048_highscore') ?? 0;
    });
  }

  Future<void> _saveHighScore() async {
    if (score > highScore) {
      highScore = score;
      final prefs = await SharedPreferences.getInstance();
      await prefs.setInt('duitku_2048_highscore', highScore);
    }
  }

  void _startNewGame() {
    setState(() {
      _initBoard();
      score = 0;
      isGameOver = false;
      hasWon = false;
      keepPlaying = false;
      previousBoard = null;
      _addNewTile();
      _addNewTile();
    });
  }

  void _addNewTile() {
    List<Point<int>> emptyCells = [];
    for (int r = 0; r < gridSize; r++) {
      for (int c = 0; c < gridSize; c++) {
        if (board[r][c] == 0) {
          emptyCells.add(Point(r, c));
        }
      }
    }

    if (emptyCells.isNotEmpty) {
      final randomPoint = emptyCells[Random().nextInt(emptyCells.length)];
      // 90% chance of 2, 10% chance of 4
      board[randomPoint.x][randomPoint.y] = Random().nextDouble() < 0.9 ? 2 : 4;
    }
  }

  void _savePreviousState() {
    previousBoard = board.map((row) => List<int>.from(row)).toList();
    previousScore = score;
  }

  void _undoMove() {
    if (previousBoard == null) return;
    setState(() {
      board = previousBoard!.map((row) => List<int>.from(row)).toList();
      score = previousScore;
      previousBoard = null;
      isGameOver = false;
    });
    HapticFeedback.lightImpact();
  }

  bool _swipe(int dx, int dy) {
    if (isGameOver) return false;

    _savePreviousState();
    bool moved = false;

    if (dx == -1) moved = _slideLeft();
    if (dx == 1) moved = _slideRight();
    if (dy == -1) moved = _slideUp();
    if (dy == 1) moved = _slideDown();

    if (moved) {
      HapticFeedback.selectionClick();
      setState(() {
        _addNewTile();
        _saveHighScore();
        if (!_canMove()) {
          isGameOver = true;
          HapticFeedback.vibrate();
        }
      });
    }

    return moved;
  }

  bool _slideLeft() {
    bool moved = false;
    for (int r = 0; r < gridSize; r++) {
      List<int> row = board[r].where((val) => val != 0).toList();
      List<int> newRow = [];

      for (int i = 0; i < row.length; i++) {
        if (i + 1 < row.length && row[i] == row[i + 1]) {
          int mergedVal = row[i] * 2;
          newRow.add(mergedVal);
          score += mergedVal;
          if (mergedVal == 2048 && !keepPlaying && !hasWon) {
            hasWon = true;
          }
          i++; // Skip next tile
        } else {
          newRow.add(row[i]);
        }
      }

      while (newRow.length < gridSize) {
        newRow.add(0);
      }

      for (int c = 0; c < gridSize; c++) {
        if (board[r][c] != newRow[c]) {
          moved = true;
          board[r][c] = newRow[c];
        }
      }
    }
    return moved;
  }

  bool _slideRight() {
    bool moved = false;
    for (int r = 0; r < gridSize; r++) {
      List<int> row = board[r].where((val) => val != 0).toList();
      List<int> newRow = [];

      for (int i = row.length - 1; i >= 0; i--) {
        if (i - 1 >= 0 && row[i] == row[i - 1]) {
          int mergedVal = row[i] * 2;
          newRow.insert(0, mergedVal);
          score += mergedVal;
          if (mergedVal == 2048 && !keepPlaying && !hasWon) {
            hasWon = true;
          }
          i--;
        } else {
          newRow.insert(0, row[i]);
        }
      }

      while (newRow.length < gridSize) {
        newRow.insert(0, 0);
      }

      for (int c = 0; c < gridSize; c++) {
        if (board[r][c] != newRow[c]) {
          moved = true;
          board[r][c] = newRow[c];
        }
      }
    }
    return moved;
  }

  bool _slideUp() {
    bool moved = false;
    for (int c = 0; c < gridSize; c++) {
      List<int> col = [];
      for (int r = 0; r < gridSize; r++) {
        if (board[r][c] != 0) col.add(board[r][c]);
      }

      List<int> newCol = [];
      for (int i = 0; i < col.length; i++) {
        if (i + 1 < col.length && col[i] == col[i + 1]) {
          int mergedVal = col[i] * 2;
          newCol.add(mergedVal);
          score += mergedVal;
          if (mergedVal == 2048 && !keepPlaying && !hasWon) {
            hasWon = true;
          }
          i++;
        } else {
          newCol.add(col[i]);
        }
      }

      while (newCol.length < gridSize) {
        newCol.add(0);
      }

      for (int r = 0; r < gridSize; r++) {
        if (board[r][c] != newCol[r]) {
          moved = true;
          board[r][c] = newCol[r];
        }
      }
    }
    return moved;
  }

  bool _slideDown() {
    bool moved = false;
    for (int c = 0; c < gridSize; c++) {
      List<int> col = [];
      for (int r = 0; r < gridSize; r++) {
        if (board[r][c] != 0) col.add(board[r][c]);
      }

      List<int> newCol = [];
      for (int i = col.length - 1; i >= 0; i--) {
        if (i - 1 >= 0 && col[i] == col[i - 1]) {
          int mergedVal = col[i] * 2;
          newCol.insert(0, mergedVal);
          score += mergedVal;
          if (mergedVal == 2048 && !keepPlaying && !hasWon) {
            hasWon = true;
          }
          i--;
        } else {
          newCol.insert(0, col[i]);
        }
      }

      while (newCol.length < gridSize) {
        newCol.insert(0, 0);
      }

      for (int r = 0; r < gridSize; r++) {
        if (board[r][c] != newCol[r]) {
          moved = true;
          board[r][c] = newCol[r];
        }
      }
    }
    return moved;
  }

  bool _canMove() {
    for (int r = 0; r < gridSize; r++) {
      for (int c = 0; c < gridSize; c++) {
        if (board[r][c] == 0) return true;
        if (c + 1 < gridSize && board[r][c] == board[r][c + 1]) return true;
        if (r + 1 < gridSize && board[r][c] == board[r + 1][c]) return true;
      }
    }
    return false;
  }

  String _formatNominal(int val) {
    if (val == 0) return '';
    if (val == 2) return '1K';
    if (val == 4) return '2K';
    if (val == 8) return '5K';
    if (val == 16) return '10K';
    if (val == 32) return '20K';
    if (val == 64) return '50K';
    if (val == 128) return '100K';
    if (val == 256) return '200K';
    if (val == 512) return '500K';
    if (val == 1024) return '1JT';
    if (val == 2048) return '2JT';
    if (val == 4096) return '5JT';
    return '${val ~/ 1000}JT';
  }

  String _formatSublabel(int val) {
    if (val == 0) return '';
    if (val == 2) return 'Rp 1.000';
    if (val == 4) return 'Rp 2.000';
    if (val == 8) return 'Rp 5.000';
    if (val == 16) return 'Rp 10.000';
    if (val == 32) return 'Rp 20.000';
    if (val == 64) return 'Rp 50.000';
    if (val == 128) return 'Rp 100.000';
    if (val == 256) return 'Rp 200.000';
    if (val == 512) return 'Rp 500.000';
    if (val == 1024) return 'Rp 1.000.000';
    if (val == 2048) return 'Rp 2.000.000';
    return 'SULTAN';
  }

  Color _getTileColor(int val) {
    switch (val) {
      case 2:
        return const Color(0xFFE2E8F0); // Gray 1K
      case 4:
        return const Color(0xFFFED7AA); // Orange subtle 2K
      case 8:
        return const Color(0xFFFDBA74); // Amber 5K
      case 16:
        return const Color(0xFFF87171); // Light Red 10K
      case 32:
        return const Color(0xFF34D399); // Light Green 20K
      case 64:
        return const Color(0xFF60A5FA); // Blue 50K
      case 128:
        return const Color(0xFFF43F5E); // Merah 100K
      case 256:
        return const Color(0xFFA855F7); // Ungu 200K
      case 512:
        return const Color(0xFF059669); // Emerald 500K
      case 1024:
        return const Color(0xFFD97706); // Emas 1JT
      case 2048:
        return const Color(0xFFDC2626); // Sultan Merah 2JT
      default:
        return const Color(0xFF7C3AED); // Deep Purple
    }
  }

  Color _getTextColor(int val) {
    return (val <= 4) ? const Color(0xFF1E293B) : Colors.white;
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
            Icon(Icons.monetization_on_rounded, color: Color(0xFFFBBF24), size: 22),
            SizedBox(width: 8),
            Text(
              'MONEY 2048',
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
            icon: const Icon(Icons.undo_rounded, color: Color(0xFF38BDF8)),
            onPressed: previousBoard != null ? _undoMove : null,
            tooltip: 'Batalkan Langkah',
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Color(0xFF10B981)),
            onPressed: _startNewGame,
            tooltip: 'Mulai Ulang',
          ),
          const SizedBox(width: 6),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            // Score Header
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Row(
                children: [
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: const Color(0xFF1E293B),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFF334155)),
                      ),
                      child: Column(
                        children: [
                          const Text('SKOR CUAN',
                              style: TextStyle(color: Color(0xFF10B981), fontSize: 10, fontWeight: FontWeight.bold)),
                          const SizedBox(height: 2),
                          Text('$score',
                              style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w900)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: const Color(0xFF1E293B),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFF334155)),
                      ),
                      child: Column(
                        children: [
                          const Text('REKOR TERTINGGI',
                              style: TextStyle(color: Color(0xFFF59E0B), fontSize: 10, fontWeight: FontWeight.bold)),
                          const SizedBox(height: 2),
                          Text('$highScore',
                              style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w900)),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const Padding(
              padding: EdgeInsets.symmetric(horizontal: 16),
              child: Text(
                'Geser (Swipe) untuk menggabungkan pecahan uang hingga Rp 2.000.000!',
                textAlign: TextAlign.center,
                style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
              ),
            ),

            const Spacer(),

            // 4x4 Grid Board with Swipe Gestures
            Center(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: GestureDetector(
                  onHorizontalDragEnd: (details) {
                    if (details.primaryVelocity! > 100) {
                      _swipe(1, 0); // Right
                    } else if (details.primaryVelocity! < -100) {
                      _swipe(-1, 0); // Left
                    }
                  },
                  onVerticalDragEnd: (details) {
                    if (details.primaryVelocity! > 100) {
                      _swipe(0, 1); // Down
                    } else if (details.primaryVelocity! < -100) {
                      _swipe(0, -1); // Up
                    }
                  },
                  child: AspectRatio(
                    aspectRatio: 1.0,
                    child: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: const Color(0xFF1E293B),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFF334155), width: 2),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.3),
                            blurRadius: 16,
                            offset: const Offset(0, 6),
                          ),
                        ],
                      ),
                      child: Stack(
                        children: [
                          // Grid
                          GridView.builder(
                            physics: const NeverScrollableScrollPhysics(),
                            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: gridSize,
                              crossAxisSpacing: 8,
                              mainAxisSpacing: 8,
                            ),
                            itemCount: gridSize * gridSize,
                            itemBuilder: (context, index) {
                              final r = index ~/ gridSize;
                              final c = index % gridSize;
                              final val = board[r][c];

                              if (val == 0) {
                                return Container(
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF0F172A).withValues(alpha: 0.6),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                );
                              }

                              return AnimatedContainer(
                                duration: const Duration(milliseconds: 120),
                                decoration: BoxDecoration(
                                  color: _getTileColor(val),
                                  borderRadius: BorderRadius.circular(8),
                                  boxShadow: [
                                    BoxShadow(
                                      color: _getTileColor(val).withValues(alpha: 0.4),
                                      blurRadius: 6,
                                      offset: const Offset(0, 2),
                                    ),
                                  ],
                                ),
                                child: Center(
                                  child: Column(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Text(
                                        _formatNominal(val),
                                        style: TextStyle(
                                          color: _getTextColor(val),
                                          fontSize: val >= 1024 ? 14 : 17,
                                          fontWeight: FontWeight.w900,
                                        ),
                                      ),
                                      Text(
                                        _formatSublabel(val),
                                        style: TextStyle(
                                          color: _getTextColor(val).withValues(alpha: 0.8),
                                          fontSize: 7.5,
                                          fontWeight: FontWeight.w600,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              );
                            },
                          ),

                          // Game Over Overlay
                          if (isGameOver)
                            Container(
                              decoration: BoxDecoration(
                                color: Colors.black.withValues(alpha: 0.85),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              alignment: Alignment.center,
                              child: Column(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(Icons.mood_bad_rounded, color: Color(0xFFEF4444), size: 48),
                                  const SizedBox(height: 6),
                                  const Text(
                                    'UANG KELUAR SEMUA!',
                                    style: TextStyle(
                                      color: Color(0xFFEF4444),
                                      fontSize: 16,
                                      fontWeight: FontWeight.w900,
                                      letterSpacing: 1.5,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Text('Skor: $score',
                                      style: const TextStyle(color: Colors.white, fontSize: 13)),
                                  const SizedBox(height: 12),
                                  ElevatedButton(
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: const Color(0xFF10B981),
                                      foregroundColor: Colors.white,
                                    ),
                                    onPressed: _startNewGame,
                                    child: const Text('COBA LAGI'),
                                  ),
                                ],
                              ),
                            ),

                          // Win 2048 Overlay
                          if (hasWon && !keepPlaying)
                            Container(
                              decoration: BoxDecoration(
                                color: Colors.black.withValues(alpha: 0.85),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              alignment: Alignment.center,
                              child: Column(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(Icons.stars_rounded, color: Color(0xFFFBBF24), size: 52),
                                  const SizedBox(height: 6),
                                  const Text(
                                    'SELAMAT ANDA SULTAN!',
                                    style: TextStyle(
                                      color: Color(0xFFFBBF24),
                                      fontSize: 17,
                                      fontWeight: FontWeight.w900,
                                      letterSpacing: 1.5,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  const Text('Berhasil mencapai Rp 2.000.000',
                                      style: TextStyle(color: Colors.white, fontSize: 12)),
                                  const SizedBox(height: 12),
                                  Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      ElevatedButton(
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: const Color(0xFF2563EB),
                                          foregroundColor: Colors.white,
                                        ),
                                        onPressed: () {
                                          setState(() {
                                            keepPlaying = true;
                                          });
                                        },
                                        child: const Text('LANJUTKAN'),
                                      ),
                                      const SizedBox(width: 8),
                                      OutlinedButton(
                                        style: OutlinedButton.styleFrom(
                                          foregroundColor: Colors.white,
                                          side: const BorderSide(color: Colors.white),
                                        ),
                                        onPressed: _startNewGame,
                                        child: const Text('MAIN LAGI'),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ),

            const Spacer(),

            // D-Pad Quick Arrow Controls
            Padding(
              padding: const EdgeInsets.only(bottom: 16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  _buildDirectionButton(Icons.arrow_back_rounded, () => _swipe(-1, 0)),
                  const SizedBox(width: 12),
                  Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      _buildDirectionButton(Icons.arrow_upward_rounded, () => _swipe(0, -1)),
                      const SizedBox(height: 12),
                      _buildDirectionButton(Icons.arrow_downward_rounded, () => _swipe(0, 1)),
                    ],
                  ),
                  const SizedBox(width: 12),
                  _buildDirectionButton(Icons.arrow_forward_rounded, () => _swipe(1, 0)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDirectionButton(IconData icon, VoidCallback onPressed) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () {
          HapticFeedback.lightImpact();
          onPressed();
        },
        borderRadius: BorderRadius.circular(25),
        child: Container(
          width: 50,
          height: 50,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: const Color(0xFF1E293B),
            border: Border.all(color: const Color(0xFF38BDF8).withValues(alpha: 0.5), width: 1.5),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF38BDF8).withValues(alpha: 0.2),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Icon(icon, color: const Color(0xFF38BDF8), size: 26),
        ),
      ),
    );
  }
}
