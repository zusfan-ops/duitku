import 'dart:async';
import 'dart:math';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:shared_preferences/shared_preferences.dart';

// Tetromino types
enum TetrominoType { I, J, L, O, S, T, Z }

// Direction for movement
enum Direction { left, right, down }

class TetrisScreen extends StatefulWidget {
  const TetrisScreen({super.key});

  @override
  State<TetrisScreen> createState() => _TetrisScreenState();
}

class _TetrisScreenState extends State<TetrisScreen> with TickerProviderStateMixin {
  static const int colCount = 10;
  static const int rowCount = 20;

  // Board representation: row x col
  late List<List<Color?>> board;

  // Current piece properties
  late TetrominoType currentPiece;
  late List<Point<int>> pieceCoordinates;
  late Color pieceColor;
  int rotationState = 0;

  // Hold piece & Next piece
  TetrominoType? holdPiece;
  bool canHold = true;
  late TetrominoType nextPiece;

  // Game loop & state
  Timer? gameTimer;
  bool isPlaying = false;
  bool isPaused = false;
  bool isGameOver = false;

  // Scoring
  int score = 0;
  int highScore = 0;
  int linesCleared = 0;
  int level = 1;

  // Animation controller for clearing lines
  List<int> clearingRows = [];

  @override
  void initState() {
    super.initState();
    _initBoard();
    _loadHighScore();
    _startNewGame();
  }

  @override
  void dispose() {
    gameTimer?.cancel();
    super.dispose();
  }

  void _initBoard() {
    board = List.generate(
      rowCount,
      (_) => List.generate(colCount, (_) => null),
    );
  }

  Future<void> _loadHighScore() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      highScore = prefs.getInt('duitku_tetris_highscore') ?? 0;
    });
  }

  Future<void> _saveHighScore() async {
    if (score > highScore) {
      highScore = score;
      final prefs = await SharedPreferences.getInstance();
      await prefs.setInt('duitku_tetris_highscore', highScore);
    }
  }

  void _startNewGame() {
    gameTimer?.cancel();
    _initBoard();
    setState(() {
      score = 0;
      linesCleared = 0;
      level = 1;
      isGameOver = false;
      isPaused = false;
      isPlaying = true;
      holdPiece = null;
      canHold = true;
      clearingRows.clear();
      nextPiece = _randomPiece();
      _spawnPiece();
    });
    _setGameLoop();
  }

  void _setGameLoop() {
    gameTimer?.cancel();
    // Speed decreases as level increases (faster drops)
    final interval = max(100, 600 - (level - 1) * 50);
    gameTimer = Timer.periodic(Duration(milliseconds: interval), (timer) {
      if (!isPaused && isPlaying && !isGameOver) {
        _movePiece(Direction.down);
      }
    });
  }

  TetrominoType _randomPiece() {
    final types = TetrominoType.values;
    return types[Random().nextInt(types.length)];
  }

  Color _getPieceColor(TetrominoType type) {
    switch (type) {
      case TetrominoType.I:
        return const Color(0xFF06B6D4); // Cyan
      case TetrominoType.J:
        return const Color(0xFF3B82F6); // Blue
      case TetrominoType.L:
        return const Color(0xFFF97316); // Orange
      case TetrominoType.O:
        return const Color(0xFFFBBF24); // Yellow
      case TetrominoType.S:
        return const Color(0xFF10B981); // Emerald Green
      case TetrominoType.T:
        return const Color(0xFFA855F7); // Purple
      case TetrominoType.Z:
        return const Color(0xFFEF4444); // Red
    }
  }

  List<Point<int>> _getPieceShape(TetrominoType type) {
    switch (type) {
      case TetrominoType.I:
        return [const Point(0, 3), const Point(0, 4), const Point(0, 5), const Point(0, 6)];
      case TetrominoType.J:
        return [const Point(0, 3), const Point(1, 3), const Point(1, 4), const Point(1, 5)];
      case TetrominoType.L:
        return [const Point(0, 5), const Point(1, 3), const Point(1, 4), const Point(1, 5)];
      case TetrominoType.O:
        return [const Point(0, 4), const Point(0, 5), const Point(1, 4), const Point(1, 5)];
      case TetrominoType.S:
        return [const Point(0, 4), const Point(0, 5), const Point(1, 3), const Point(1, 4)];
      case TetrominoType.T:
        return [const Point(0, 4), const Point(1, 3), const Point(1, 4), const Point(1, 5)];
      case TetrominoType.Z:
        return [const Point(0, 3), const Point(0, 4), const Point(1, 4), const Point(1, 5)];
    }
  }

  void _spawnPiece([TetrominoType? specificType]) {
    currentPiece = specificType ?? nextPiece;
    if (specificType == null) {
      nextPiece = _randomPiece();
    }
    pieceCoordinates = _getPieceShape(currentPiece);
    pieceColor = _getPieceColor(currentPiece);
    rotationState = 0;
    canHold = true;

    // Check if initial spawn position has collision -> Game Over
    if (_checkCollision(pieceCoordinates)) {
      _triggerGameOver();
    }
  }

  bool _checkCollision(List<Point<int>> coords) {
    for (final p in coords) {
      if (p.x < 0 || p.x >= rowCount || p.y < 0 || p.y >= colCount) {
        return true;
      }
      if (board[p.x][p.y] != null) {
        return true;
      }
    }
    return false;
  }

  void _movePiece(Direction dir) {
    if (isGameOver || isPaused) return;

    List<Point<int>> newCoords = [];
    int dx = 0;
    int dy = 0;

    switch (dir) {
      case Direction.left:
        dy = -1;
        break;
      case Direction.right:
        dy = 1;
        break;
      case Direction.down:
        dx = 1;
        break;
    }

    for (final p in pieceCoordinates) {
      newCoords.add(Point(p.x + dx, p.y + dy));
    }

    if (!_checkCollision(newCoords)) {
      setState(() {
        pieceCoordinates = newCoords;
      });
      if (dir == Direction.down) {
        HapticFeedback.selectionClick();
      }
    } else if (dir == Direction.down) {
      // Landed
      _lockPiece();
    }
  }

  void _rotatePiece() {
    if (isGameOver || isPaused || currentPiece == TetrominoType.O) return;

    // Pivot is usually coordinate 2 (index 1 or 2)
    Point<int> pivot = pieceCoordinates[1];
    List<Point<int>> rotatedCoords = [];

    for (final p in pieceCoordinates) {
      // Standard matrix rotation 90 deg clockwise around pivot:
      // new_x = pivot.x + (p.y - pivot.y)
      // new_y = pivot.y - (p.x - pivot.x)
      int newX = pivot.x + (p.y - pivot.y);
      int newY = pivot.y - (p.x - pivot.x);
      rotatedCoords.add(Point(newX, newY));
    }

    // Basic wall kick test (shift left/right if out of bounds)
    if (!_checkCollision(rotatedCoords)) {
      setState(() {
        pieceCoordinates = rotatedCoords;
      });
      HapticFeedback.selectionClick();
    } else {
      // Try wall kick 1 tile left
      final kickLeft = rotatedCoords.map((p) => Point(p.x, p.y - 1)).toList();
      if (!_checkCollision(kickLeft)) {
        setState(() {
          pieceCoordinates = kickLeft;
        });
        HapticFeedback.selectionClick();
        return;
      }
      // Try wall kick 1 tile right
      final kickRight = rotatedCoords.map((p) => Point(p.x, p.y + 1)).toList();
      if (!_checkCollision(kickRight)) {
        setState(() {
          pieceCoordinates = kickRight;
        });
        HapticFeedback.selectionClick();
      }
    }
  }

  void _hardDrop() {
    if (isGameOver || isPaused) return;

    List<Point<int>> current = List.from(pieceCoordinates);
    int dropDistance = 0;

    while (true) {
      List<Point<int>> next = current.map((p) => Point(p.x + 1, p.y)).toList();
      if (_checkCollision(next)) {
        break;
      }
      current = next;
      dropDistance++;
    }

    setState(() {
      pieceCoordinates = current;
      score += dropDistance * 2; // Hard drop bonus
    });
    HapticFeedback.mediumImpact();
    _lockPiece();
  }

  List<Point<int>> _getGhostCoordinates() {
    List<Point<int>> ghost = List.from(pieceCoordinates);
    while (true) {
      List<Point<int>> next = ghost.map((p) => Point(p.x + 1, p.y)).toList();
      if (_checkCollision(next)) {
        break;
      }
      ghost = next;
    }
    return ghost;
  }

  void _holdCurrentPiece() {
    if (!canHold || isGameOver || isPaused) return;

    HapticFeedback.selectionClick();
    if (holdPiece == null) {
      holdPiece = currentPiece;
      _spawnPiece();
    } else {
      final temp = holdPiece!;
      holdPiece = currentPiece;
      _spawnPiece(temp);
    }
    canHold = false;
    setState(() {});
  }

  void _lockPiece() {
    for (final p in pieceCoordinates) {
      if (p.x >= 0 && p.x < rowCount && p.y >= 0 && p.y < colCount) {
        board[p.x][p.y] = pieceColor;
      }
    }

    _clearCompletedLines();
  }

  void _clearCompletedLines() {
    List<int> fullRows = [];
    for (int r = 0; r < rowCount; r++) {
      bool isFull = true;
      for (int c = 0; c < colCount; c++) {
        if (board[r][c] == null) {
          isFull = false;
          break;
        }
      }
      if (isFull) {
        fullRows.add(r);
      }
    }

    if (fullRows.isNotEmpty) {
      HapticFeedback.heavyImpact();
      setState(() {
        clearingRows = fullRows;
      });

      // Brief animation delay then remove lines
      Future.delayed(const Duration(milliseconds: 160), () {
        if (!mounted) return;
        setState(() {
          for (final row in fullRows) {
            board.removeAt(row);
            board.insert(0, List.generate(colCount, (_) => null));
          }
          clearingRows.clear();

          // Calculate score
          final lines = fullRows.length;
          linesCleared += lines;
          if (lines == 1) score += 100 * level;
          if (lines == 2) score += 300 * level;
          if (lines == 3) score += 500 * level;
          if (lines >= 4) score += 800 * level; // Tetris!

          level = (linesCleared ~/ 10) + 1;
          _saveHighScore();
          _setGameLoop();
          _spawnPiece();
        });
      });
    } else {
      _spawnPiece();
    }
  }

  void _triggerGameOver() {
    gameTimer?.cancel();
    _saveHighScore();
    setState(() {
      isGameOver = true;
      isPlaying = false;
    });
    HapticFeedback.vibrate();
  }

  void _togglePause() {
    setState(() {
      isPaused = !isPaused;
    });
  }

  @override
  Widget build(BuildContext context) {
    final ghostCoords = (isPlaying && !isGameOver) ? _getGhostCoordinates() : <Point<int>>[];

    return Scaffold(
      backgroundColor: const Color(0xFF0B0F19), // Deep dark arcade background
      appBar: AppBar(
        backgroundColor: const Color(0xFF0B0F19),
        elevation: 0,
        foregroundColor: Colors.white,
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                color: const Color(0xFF2563EB).withValues(alpha: 0.2),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: const Color(0xFF3B82F6), width: 1.2),
              ),
              child: const Icon(Icons.grid_view_rounded, color: Color(0xFF60A5FA), size: 18),
            ),
            const SizedBox(width: 10),
            const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'BRICK MASTER',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 1.2,
                    color: Colors.white,
                  ),
                ),
                Text(
                  'DuitKu Retro Arcade',
                  style: TextStyle(fontSize: 10, color: Color(0xFF94A3B8)),
                ),
              ],
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: Icon(
              isPaused ? Icons.play_arrow_rounded : Icons.pause_rounded,
              color: const Color(0xFFFBBF24),
              size: 26,
            ),
            onPressed: isGameOver ? null : _togglePause,
            tooltip: isPaused ? 'Lanjutkan' : 'Jeda',
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Color(0xFF38BDF8)),
            onPressed: _startNewGame,
            tooltip: 'Mulai Ulang',
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            // Top Dashboard (Score, Level, Highscore)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
              child: Row(
                children: [
                  _buildStatCard('SKOR', '$score', const Color(0xFF10B981)),
                  const SizedBox(width: 8),
                  _buildStatCard('LEVEL', '$level', const Color(0xFF3B82F6)),
                  const SizedBox(width: 8),
                  _buildStatCard('LINES', '$linesCleared', const Color(0xFFA855F7)),
                  const SizedBox(width: 8),
                  _buildStatCard('REKOR', '$highScore', const Color(0xFFF59E0B)),
                ],
              ),
            ),

            const SizedBox(height: 6),

            // Main Play Area
            Expanded(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 12),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Left Panel: HOLD Piece
                    Column(
                      children: [
                        _buildSideBox(
                          title: 'HOLD',
                          child: holdPiece != null
                              ? _buildMiniPiecePreview(holdPiece!)
                              : const Text('KOSONG',
                                  style: TextStyle(color: Color(0xFF475569), fontSize: 9)),
                          onTap: _holdCurrentPiece,
                        ),
                        const SizedBox(height: 12),
                        // Quick instructions
                        Container(
                          width: 68,
                          padding: const EdgeInsets.all(6),
                          decoration: BoxDecoration(
                            color: const Color(0xFF1E293B),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: const Color(0xFF334155)),
                          ),
                          child: const Column(
                            children: [
                              Icon(Icons.swipe_rounded, color: Color(0xFF64748B), size: 18),
                              SizedBox(height: 4),
                              Text('Swipe & Tap Aktif',
                                  textAlign: TextAlign.center,
                                  style: TextStyle(color: Color(0xFF94A3B8), fontSize: 8)),
                            ],
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(width: 10),

                    // Center: 10x20 Game Board with Gesture Support
                    Expanded(
                      child: AspectRatio(
                        aspectRatio: colCount / rowCount,
                        child: GestureDetector(
                          onHorizontalDragUpdate: (details) {
                            if (details.primaryDelta! > 12) {
                              _movePiece(Direction.right);
                            } else if (details.primaryDelta! < -12) {
                              _movePiece(Direction.left);
                            }
                          },
                          onVerticalDragUpdate: (details) {
                            if (details.primaryDelta! > 14) {
                              _movePiece(Direction.down);
                            }
                          },
                          onTap: _rotatePiece,
                          onDoubleTap: _hardDrop,
                          child: Container(
                            decoration: BoxDecoration(
                              color: const Color(0xFF0F172A),
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(
                                color: const Color(0xFF3B82F6).withValues(alpha: 0.6),
                                width: 2,
                              ),
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(0xFF3B82F6).withValues(alpha: 0.2),
                                  blurRadius: 16,
                                  offset: const Offset(0, 4),
                                ),
                              ],
                            ),
                            child: ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: Stack(
                                children: [
                                  // Grid Matrix
                                  GridView.builder(
                                    physics: const NeverScrollableScrollPhysics(),
                                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                                      crossAxisCount: colCount,
                                    ),
                                    itemCount: rowCount * colCount,
                                    itemBuilder: (context, index) {
                                      final r = index ~/ colCount;
                                      final c = index % colCount;

                                      Color? cellColor = board[r][c];
                                      bool isCurrent = pieceCoordinates.any((p) => p.x == r && p.y == c);
                                      bool isGhost = ghostCoords.any((p) => p.x == r && p.y == c);
                                      bool isClearing = clearingRows.contains(r);

                                      if (isCurrent) {
                                        cellColor = pieceColor;
                                      }

                                      if (isClearing) {
                                        return Container(
                                          margin: const EdgeInsets.all(1),
                                          decoration: BoxDecoration(
                                            color: Colors.white,
                                            borderRadius: BorderRadius.circular(3),
                                            boxShadow: const [
                                              BoxShadow(color: Colors.white, blurRadius: 6),
                                            ],
                                          ),
                                        );
                                      }

                                      if (cellColor != null) {
                                        return _buildBlock(cellColor);
                                      }

                                      if (isGhost && !isCurrent) {
                                        return Container(
                                          margin: const EdgeInsets.all(1),
                                          decoration: BoxDecoration(
                                            border: Border.all(
                                              color: pieceColor.withValues(alpha: 0.4),
                                              width: 1.2,
                                            ),
                                            borderRadius: BorderRadius.circular(3),
                                          ),
                                        );
                                      }

                                      // Empty cell
                                      return Container(
                                        margin: const EdgeInsets.all(1),
                                        decoration: BoxDecoration(
                                          color: const Color(0xFF1E293B).withValues(alpha: 0.3),
                                          borderRadius: BorderRadius.circular(2),
                                        ),
                                      );
                                    },
                                  ),

                                  // Paused Overlay
                                  if (isPaused)
                                    Container(
                                      color: Colors.black.withValues(alpha: 0.75),
                                      alignment: Alignment.center,
                                      child: Column(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          const Icon(Icons.pause_circle_filled_rounded,
                                              color: Color(0xFFFBBF24), size: 48),
                                          const SizedBox(height: 8),
                                          const Text(
                                            'GAME DIJEDA',
                                            style: TextStyle(
                                              color: Colors.white,
                                              fontSize: 18,
                                              fontWeight: FontWeight.w900,
                                              letterSpacing: 2,
                                            ),
                                          ),
                                          const SizedBox(height: 12),
                                          ElevatedButton.icon(
                                            style: ElevatedButton.styleFrom(
                                              backgroundColor: const Color(0xFF2563EB),
                                              foregroundColor: Colors.white,
                                              padding: const EdgeInsets.symmetric(
                                                  horizontal: 20, vertical: 10),
                                            ),
                                            onPressed: _togglePause,
                                            icon: const Icon(Icons.play_arrow_rounded),
                                            label: const Text('LANJUTKAN'),
                                          ),
                                        ],
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
                                          const Icon(Icons.sentiment_very_dissatisfied_rounded,
                                              color: Color(0xFFEF4444), size: 54),
                                          const SizedBox(height: 8),
                                          const Text(
                                            'GAME OVER',
                                            style: TextStyle(
                                              color: Color(0xFFEF4444),
                                              fontSize: 22,
                                              fontWeight: FontWeight.w900,
                                              letterSpacing: 2,
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
                                              shape: RoundedRectangleBorder(
                                                  borderRadius: BorderRadius.circular(10)),
                                            ),
                                            onPressed: _startNewGame,
                                            icon: const Icon(Icons.replay_rounded),
                                            label: const Text('MAIN LAGI',
                                                style: TextStyle(fontWeight: FontWeight.bold)),
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

                    const SizedBox(width: 10),

                    // Right Panel: NEXT Piece
                    Column(
                      children: [
                        _buildSideBox(
                          title: 'NEXT',
                          child: _buildMiniPiecePreview(nextPiece),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 6),

            // Bottom Arcade Gamepad Controls
            Container(
              padding: const EdgeInsets.fromLTRB(14, 6, 14, 12),
              decoration: const BoxDecoration(
                color: Color(0xFF0F172A),
                borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
                border: Border(top: BorderSide(color: Color(0xFF1E293B), width: 1.5)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  // D-Pad Movements (Left, Down, Right)
                  Row(
                    children: [
                      _buildArcadeButton(
                        icon: Icons.arrow_back_rounded,
                        color: const Color(0xFF38BDF8),
                        onPressed: () => _movePiece(Direction.left),
                        size: 54,
                      ),
                      const SizedBox(width: 8),
                      _buildArcadeButton(
                        icon: Icons.arrow_downward_rounded,
                        color: const Color(0xFF38BDF8),
                        onPressed: () => _movePiece(Direction.down),
                        size: 54,
                      ),
                      const SizedBox(width: 8),
                      _buildArcadeButton(
                        icon: Icons.arrow_forward_rounded,
                        color: const Color(0xFF38BDF8),
                        onPressed: () => _movePiece(Direction.right),
                        size: 54,
                      ),
                    ],
                  ),

                  // Action Buttons (Rotate, Hard Drop, Hold)
                  Row(
                    children: [
                      // Hold / Swap Button
                      _buildArcadeButton(
                        icon: Icons.swap_horiz_rounded,
                        color: const Color(0xFFF59E0B),
                        label: 'HOLD',
                        onPressed: _holdCurrentPiece,
                        size: 50,
                      ),
                      const SizedBox(width: 10),
                      // Hard Drop Button
                      _buildArcadeButton(
                        icon: Icons.vertical_align_bottom_rounded,
                        color: const Color(0xFFEF4444),
                        label: 'DROP',
                        onPressed: _hardDrop,
                        size: 54,
                      ),
                      const SizedBox(width: 10),
                      // Rotate Button (Primary Big)
                      _buildArcadeButton(
                        icon: Icons.rotate_right_rounded,
                        color: const Color(0xFF10B981),
                        label: 'PUTAR',
                        onPressed: _rotatePiece,
                        size: 60,
                        isPrimary: true,
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard(String label, String value, Color accentColor) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 4),
        decoration: BoxDecoration(
          color: const Color(0xFF1E293B),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: accentColor.withValues(alpha: 0.3)),
        ),
        child: Column(
          children: [
            Text(
              label,
              style: TextStyle(
                color: accentColor,
                fontSize: 9,
                fontWeight: FontWeight.w900,
                letterSpacing: 0.5,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              value,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 13,
                fontWeight: FontWeight.w900,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSideBox({required String title, required Widget child, VoidCallback? onTap}) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        width: 68,
        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
        decoration: BoxDecoration(
          color: const Color(0xFF1E293B),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: const Color(0xFF334155)),
        ),
        child: Column(
          children: [
            Text(
              title,
              style: const TextStyle(
                color: Color(0xFF94A3B8),
                fontSize: 9,
                fontWeight: FontWeight.w900,
                letterSpacing: 1,
              ),
            ),
            const SizedBox(height: 6),
            SizedBox(
              height: 46,
              child: Center(child: child),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMiniPiecePreview(TetrominoType type) {
    final shape = _getPieceShape(type);
    final color = _getPieceColor(type);

    int minX = shape.map((p) => p.x).reduce(min);
    int maxX = shape.map((p) => p.x).reduce(max);
    int minY = shape.map((p) => p.y).reduce(min);
    int maxY = shape.map((p) => p.y).reduce(max);

    int rows = maxX - minX + 1;
    int cols = maxY - minY + 1;

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: List.generate(rows, (r) {
        return Row(
          mainAxisSize: MainAxisSize.min,
          children: List.generate(cols, (c) {
            bool hasBlock = shape.any((p) => p.x - minX == r && p.y - minY == c);
            return Container(
              width: 8,
              height: 8,
              margin: const EdgeInsets.all(1),
              decoration: BoxDecoration(
                color: hasBlock ? color : Colors.transparent,
                borderRadius: BorderRadius.circular(1.5),
              ),
            );
          }),
        );
      }),
    );
  }

  Widget _buildBlock(Color color) {
    return Container(
      margin: const EdgeInsets.all(1),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(3),
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            color.withValues(alpha: 0.95),
            color.withValues(alpha: 0.7),
          ],
        ),
        boxShadow: [
          BoxShadow(
            color: color.withValues(alpha: 0.5),
            blurRadius: 3,
            offset: const Offset(0, 1),
          ),
        ],
        border: Border.all(
          color: Colors.white.withValues(alpha: 0.35),
          width: 0.8,
        ),
      ),
    );
  }

  Widget _buildArcadeButton({
    required IconData icon,
    required Color color,
    required VoidCallback onPressed,
    double size = 52,
    String? label,
    bool isPrimary = false,
  }) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Material(
          color: Colors.transparent,
          child: InkWell(
            onTap: () {
              HapticFeedback.lightImpact();
              onPressed();
            },
            borderRadius: BorderRadius.circular(size / 2),
            child: Container(
              width: size,
              height: size,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: isPrimary ? color : const Color(0xFF1E293B),
                border: Border.all(
                  color: isPrimary ? Colors.white.withValues(alpha: 0.5) : color.withValues(alpha: 0.6),
                  width: isPrimary ? 2 : 1.5,
                ),
                boxShadow: [
                  BoxShadow(
                    color: color.withValues(alpha: isPrimary ? 0.45 : 0.2),
                    blurRadius: 10,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: Icon(
                icon,
                color: isPrimary ? Colors.white : color,
                size: size * 0.52,
              ),
            ),
          ),
        ),
        if (label != null) ...[
          const SizedBox(height: 3),
          Text(
            label,
            style: TextStyle(
              color: color,
              fontSize: 8,
              fontWeight: FontWeight.w900,
              letterSpacing: 0.5,
            ),
          ),
        ],
      ],
    );
  }
}
