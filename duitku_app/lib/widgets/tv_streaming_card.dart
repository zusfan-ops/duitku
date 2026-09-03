import 'package:flutter/material.dart';
import 'package:video_player/video_player.dart';

import '../models/jellyfin_movie.dart';
import '../models/tv_channel.dart';
import '../screens/tv/tv_streaming_screen.dart';
import '../services/api_service.dart';
import '../theme.dart';

class TvStreamingCard extends StatefulWidget {
  final List<TvChannel> initialChannels;
  final List<JellyfinMovie> initialMovies;

  const TvStreamingCard({
    super.key,
    this.initialChannels = const [],
    this.initialMovies = const [],
  });

  @override
  State<TvStreamingCard> createState() => _TvStreamingCardState();
}

class _TvStreamingCardState extends State<TvStreamingCard> {
  int _selectedTab = 0; // 0: TV Streaming, 1: Film Streaming

  // TV State
  List<TvChannel> _channels = [];
  TvChannel? _activeChannel;

  // Film State
  List<JellyfinMovie> _movies = [];
  JellyfinMovie? _activeMovie;

  // Video Player
  VideoPlayerController? _videoController;
  bool _isPlaying = false; // NO AUTOPLAY
  bool _isInitializing = false;
  bool _hasError = false;
  bool _isMuted = false;

  @override
  void initState() {
    super.initState();
    _channels = List.from(widget.initialChannels);
    if (_channels.isNotEmpty) {
      _activeChannel = _channels.first;
    } else {
      _fetchChannels();
    }

    _movies = List.from(widget.initialMovies);
    if (_movies.isNotEmpty) {
      _activeMovie = _movies.first;
    } else {
      _fetchMovies();
    }
  }

  @override
  void didUpdateWidget(covariant TvStreamingCard oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.initialChannels.isNotEmpty && widget.initialChannels != oldWidget.initialChannels) {
      setState(() {
        _channels = List.from(widget.initialChannels);
        _activeChannel ??= _channels.first;
      });
    }
    if (widget.initialMovies.isNotEmpty && widget.initialMovies != oldWidget.initialMovies) {
      setState(() {
        _movies = List.from(widget.initialMovies);
        _activeMovie ??= _movies.first;
      });
    }
  }

  @override
  void dispose() {
    _videoController?.dispose();
    super.dispose();
  }

  Future<void> _fetchChannels() async {
    try {
      final res = await ApiService.instance.getTvChannels();
      final list = (res['channels'] as List<dynamic>? ?? [])
          .map((e) => TvChannel.fromJson(e as Map<String, dynamic>))
          .toList();
      if (!mounted) return;
      setState(() {
        _channels = list;
        if (_activeChannel == null && _channels.isNotEmpty) {
          _activeChannel = _channels.first;
        }
      });
    } catch (_) {}
  }

  Future<void> _fetchMovies() async {
    try {
      final res = await ApiService.instance.get('jellyfin/movies');
      if (res['success'] == true && res['movies'] is List) {
        final list = (res['movies'] as List<dynamic>)
            .map((e) => JellyfinMovie.fromJson(e as Map<String, dynamic>))
            .toList();
        if (!mounted) return;
        setState(() {
          _movies = list;
          if (_activeMovie == null && _movies.isNotEmpty) {
            _activeMovie = _movies.first;
          }
        });
      }
    } catch (_) {}
  }

  void _switchTab(int tab) {
    if (_selectedTab == tab) return;
    _stopPlayback();
    setState(() {
      _selectedTab = tab;
    });
  }

  void _stopPlayback() {
    _videoController?.pause();
    _videoController?.dispose();
    _videoController = null;
    setState(() {
      _isPlaying = false;
      _isInitializing = false;
      _hasError = false;
    });
  }

  Future<void> _startPlayingCurrent() async {
    final url = _selectedTab == 0 ? _activeChannel?.streamUrl : _activeMovie?.streamUrl;
    if (url == null || url.isEmpty) return;

    setState(() {
      _isPlaying = true;
      _isInitializing = true;
      _hasError = false;
    });

    await _initVideoPlayer(url);
  }

  Future<void> _initVideoPlayer(String url) async {
    _videoController?.dispose();
    _videoController = null;

    try {
      final controller = VideoPlayerController.networkUrl(
        Uri.parse(url),
        videoPlayerOptions: VideoPlayerOptions(mixWithOthers: true),
      );

      await controller.initialize();
      if (!mounted) {
        controller.dispose();
        return;
      }

      await controller.setVolume(_isMuted ? 0.0 : 1.0);
      await controller.play();

      setState(() {
        _videoController = controller;
        _isInitializing = false;
        _hasError = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _isInitializing = false;
        _hasError = true;
      });
    }
  }

  void _switchChannel(TvChannel channel) {
    if (_activeChannel?.id == channel.id) return;
    setState(() {
      _activeChannel = channel;
    });

    if (_isPlaying) {
      setState(() {
        _isInitializing = true;
        _hasError = false;
      });
      _initVideoPlayer(channel.streamUrl);
    }
  }

  void _switchMovie(JellyfinMovie movie) {
    if (_activeMovie?.id == movie.id) return;
    setState(() {
      _activeMovie = movie;
    });

    if (_isPlaying) {
      setState(() {
        _isInitializing = true;
        _hasError = false;
      });
      _initVideoPlayer(movie.streamUrl);
    }
  }

  void _toggleMute() {
    if (_videoController == null) return;
    setState(() {
      _isMuted = !_isMuted;
    });
    _videoController?.setVolume(_isMuted ? 0.0 : 1.0);
  }

  void _openFullTvStreaming() {
    _stopPlayback();
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const TvStreamingScreen()),
    );
  }

  void _openJellyfinCatalogModal() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _JellyfinCatalogSheet(
        movies: _movies,
        onSelectMovie: (m) {
          Navigator.pop(ctx);
          setState(() {
            _selectedTab = 1;
          });
          _switchMovie(m);
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_channels.isEmpty && _movies.isEmpty) return const SizedBox.shrink();

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.border, width: 1),
        boxShadow: const [
          BoxShadow(
            color: Color(0x06000000),
            blurRadius: 12,
            offset: Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Row: Segmented Tabs & Badges
          Row(
            children: [
              // Segmented Tabs
              Expanded(
                child: Container(
                  padding: const EdgeInsets.all(3),
                  decoration: BoxDecoration(
                    color: AppColors.bg,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: InkWell(
                          onTap: () => _switchTab(0),
                          borderRadius: BorderRadius.circular(9),
                          child: Container(
                            padding: const EdgeInsets.symmetric(vertical: 6),
                            decoration: BoxDecoration(
                              color: _selectedTab == 0 ? AppColors.card : Colors.transparent,
                              borderRadius: BorderRadius.circular(9),
                              boxShadow: _selectedTab == 0
                                  ? const [
                                      BoxShadow(
                                        color: Color(0x0A000000),
                                        blurRadius: 6,
                                        offset: Offset(0, 2),
                                      ),
                                    ]
                                  : null,
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                const Text('📺', style: TextStyle(fontSize: 12)),
                                const SizedBox(width: 5),
                                Text(
                                  'Live TV',
                                  style: TextStyle(
                                    fontSize: 11.5,
                                    fontWeight: FontWeight.w800,
                                    color: _selectedTab == 0 ? AppColors.textPrimary : AppColors.textMuted,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 4),
                      Expanded(
                        child: InkWell(
                          onTap: () => _switchTab(1),
                          borderRadius: BorderRadius.circular(9),
                          child: Container(
                            padding: const EdgeInsets.symmetric(vertical: 6),
                            decoration: BoxDecoration(
                              color: _selectedTab == 1 ? AppColors.card : Colors.transparent,
                              borderRadius: BorderRadius.circular(9),
                              boxShadow: _selectedTab == 1
                                  ? const [
                                      BoxShadow(
                                        color: Color(0x0A000000),
                                        blurRadius: 6,
                                        offset: Offset(0, 2),
                                      ),
                                    ]
                                  : null,
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                const Text('🎬', style: TextStyle(fontSize: 12)),
                                const SizedBox(width: 5),
                                Text(
                                  'Film Streaming',
                                  style: TextStyle(
                                    fontSize: 11.5,
                                    fontWeight: FontWeight.w800,
                                    color: _selectedTab == 1 ? AppColors.textPrimary : AppColors.textMuted,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 10),

              // Status Badge
              if (_selectedTab == 0)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3.5),
                  decoration: BoxDecoration(
                    color: const Color(0xFFDC2626),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      CircleAvatar(radius: 3, backgroundColor: Colors.white),
                      SizedBox(width: 4),
                      Text(
                        'LIVE',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w900,
                          color: Colors.white,
                          letterSpacing: 0.4,
                        ),
                      ),
                    ],
                  ),
                )
              else
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3.5),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(colors: [Color(0xFF00A4DC), Color(0xFFAA5CC3)]),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: const Text(
                    'FILM HD',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w900,
                      color: Colors.white,
                      letterSpacing: 0.4,
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 12),

          // 16:9 Video Player Box (No Autoplay Initial State)
          ClipRRect(
            borderRadius: BorderRadius.circular(14),
            child: AspectRatio(
              aspectRatio: 16 / 9,
              child: Container(
                color: const Color(0xFF0B0F19),
                child: Stack(
                  children: [
                    // Video View if Playing
                    if (_isPlaying && _videoController != null && _videoController!.value.isInitialized)
                      Center(
                        child: AspectRatio(
                          aspectRatio: _videoController!.value.aspectRatio,
                          child: VideoPlayer(_videoController!),
                        ),
                      ),

                    // Initial Poster / Overlay when NOT playing
                    if (!_isPlaying)
                      InkWell(
                        onTap: _startPlayingCurrent,
                        child: Container(
                          width: double.infinity,
                          height: double.infinity,
                          decoration: BoxDecoration(
                            image: (_selectedTab == 1 && _activeMovie != null && _activeMovie!.backdrop.isNotEmpty)
                                ? DecorationImage(
                                    image: NetworkImage(_activeMovie!.backdrop),
                                    fit: BoxFit.cover,
                                    colorFilter: ColorFilter.mode(Colors.black.withValues(alpha: 0.65), BlendMode.darken),
                                  )
                                : null,
                            gradient: (_selectedTab == 0)
                                ? const RadialGradient(
                                    center: Alignment.center,
                                    radius: 0.9,
                                    colors: [Color(0xFF1E293B), Color(0xFF0F172A)],
                                  )
                                : null,
                          ),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                width: 52,
                                height: 52,
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: _selectedTab == 0 ? AppColors.primary : const Color(0xFF00A4DC),
                                  boxShadow: [
                                    BoxShadow(
                                      color: (_selectedTab == 0 ? AppColors.primary : const Color(0xFF00A4DC))
                                          .withValues(alpha: 0.45),
                                      blurRadius: 16,
                                      offset: const Offset(0, 4),
                                    ),
                                  ],
                                ),
                                child: const Center(
                                  child: Icon(Icons.play_arrow_rounded, color: Colors.white, size: 34),
                                ),
                              ),
                              const SizedBox(height: 10),
                              Text(
                                _selectedTab == 0
                                    ? (_activeChannel?.name ?? 'Live TV')
                                    : '${_activeMovie?.title ?? "Pilih Film"} (${_activeMovie?.year ?? ""})',
                                textAlign: TextAlign.center,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 13.5,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                _selectedTab == 0
                                    ? 'Ketuk untuk Memutar Siaran Langsung'
                                    : '${_activeMovie?.rating != null ? "⭐ ${_activeMovie!.rating} • " : ""}${_activeMovie?.duration ?? ""} Ketuk untuk Memutar Film',
                                style: const TextStyle(
                                  color: Color(0xFF94A3B8),
                                  fontSize: 11,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),

                    // Loading State
                    if (_isPlaying && _isInitializing)
                      Container(
                        color: Colors.black87,
                        child: const Center(
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              SizedBox(
                                width: 26,
                                height: 26,
                                child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.2),
                              ),
                              SizedBox(height: 8),
                              Text(
                                'Memuat video streaming...',
                                style: TextStyle(color: Colors.white70, fontSize: 11),
                              ),
                            ],
                          ),
                        ),
                      ),

                    // Error State
                    if (_isPlaying && _hasError)
                      Container(
                        color: Colors.black87,
                        padding: const EdgeInsets.all(16),
                        child: Center(
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.error_outline_rounded, color: Colors.orangeAccent, size: 28),
                              const SizedBox(height: 6),
                              const Text(
                                'Gagal memuat stream video',
                                style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                              ),
                              const SizedBox(height: 6),
                              TextButton.icon(
                                onPressed: _startPlayingCurrent,
                                icon: const Icon(Icons.refresh_rounded, size: 14, color: Colors.white),
                                label: const Text('Coba Lagi', style: TextStyle(color: Colors.white, fontSize: 11)),
                                style: TextButton.styleFrom(
                                  backgroundColor: Colors.white24,
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),

                    // Controls Overlay when Playing
                    if (_isPlaying && _videoController != null && _videoController!.value.isInitialized)
                      Positioned(
                        bottom: 8,
                        right: 8,
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            IconButton(
                              icon: Icon(
                                _isMuted ? Icons.volume_off_rounded : Icons.volume_up_rounded,
                                color: Colors.white,
                                size: 20,
                              ),
                              onPressed: _toggleMute,
                              style: IconButton.styleFrom(backgroundColor: Colors.black45),
                            ),
                            const SizedBox(width: 6),
                            IconButton(
                              icon: const Icon(Icons.fullscreen_rounded, color: Colors.white, size: 20),
                              onPressed: _selectedTab == 0 ? _openFullTvStreaming : null,
                              style: IconButton.styleFrom(backgroundColor: Colors.black45),
                            ),
                          ],
                        ),
                      ),
                  ],
                ),
              ),
            ),
          ),
          const SizedBox(height: 12),

          // ── TAB 0 CONTENT: TV Channel Chips ──
          if (_selectedTab == 0) ...[
            SizedBox(
              height: 38,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: _channels.length,
                separatorBuilder: (context, index) => const SizedBox(width: 8),
                itemBuilder: (context, index) {
                  final ch = _channels[index];
                  final isSelected = _activeChannel?.id == ch.id;
                  return InkWell(
                    onTap: () => _switchChannel(ch),
                    borderRadius: BorderRadius.circular(12),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 150),
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      decoration: BoxDecoration(
                        color: isSelected ? AppColors.primarySubtle : AppColors.bg,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: isSelected ? AppColors.primary : AppColors.border,
                          width: 1.2,
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Text('📺', style: TextStyle(fontSize: 13)),
                          const SizedBox(width: 6),
                          Text(
                            ch.name,
                            style: TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w700,
                              color: isSelected ? AppColors.primary : AppColors.textPrimary,
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: _openFullTvStreaming,
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 10),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  side: const BorderSide(color: AppColors.border),
                  backgroundColor: AppColors.bg,
                ),
                icon: const Text(
                  'Lihat Semua Live Streaming',
                  style: TextStyle(
                    fontSize: 12.5,
                    fontWeight: FontWeight.w800,
                    color: AppColors.textPrimary,
                  ),
                ),
                label: const Icon(Icons.arrow_forward_rounded, size: 16, color: AppColors.textPrimary),
              ),
            ),
          ],

          // ── TAB 1 CONTENT: Jellyfin Movie Slider ──
          if (_selectedTab == 1) ...[
            SizedBox(
              height: 154,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: _movies.length,
                separatorBuilder: (context, index) => const SizedBox(width: 10),
                itemBuilder: (context, index) {
                  final movie = _movies[index];
                  final isSelected = _activeMovie?.id == movie.id;
                  return InkWell(
                    onTap: () => _switchMovie(movie),
                    borderRadius: BorderRadius.circular(12),
                    child: Container(
                      width: 95,
                      decoration: BoxDecoration(
                        color: AppColors.bg,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: isSelected ? const Color(0xFF00A4DC) : AppColors.border,
                          width: isSelected ? 2 : 1,
                        ),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          ClipRRect(
                            borderRadius: const BorderRadius.vertical(top: Radius.circular(11)),
                            child: AspectRatio(
                              aspectRatio: 2 / 2.7,
                              child: Image.network(
                                movie.poster,
                                fit: BoxFit.cover,
                                errorBuilder: (context, error, stackTrace) => Container(
                                  color: const Color(0xFF1E293B),
                                  child: const Center(child: Text('🎬', style: TextStyle(fontSize: 20))),
                                ),
                              ),
                            ),
                          ),
                          Padding(
                            padding: const EdgeInsets.all(5),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  movie.title,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w800,
                                    color: AppColors.textPrimary,
                                  ),
                                ),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      movie.year,
                                      style: const TextStyle(fontSize: 9.5, color: AppColors.textMuted),
                                    ),
                                    if (movie.rating != null)
                                      Text(
                                        '★ ${movie.rating}',
                                        style: const TextStyle(
                                          fontSize: 9.5,
                                          fontWeight: FontWeight.w800,
                                          color: Color(0xFFD97706),
                                        ),
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
                },
              ),
            ),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: _openJellyfinCatalogModal,
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 10),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  side: const BorderSide(color: AppColors.border),
                  backgroundColor: AppColors.bg,
                ),
                icon: const Text(
                  'Lihat Semua Film',
                  style: TextStyle(
                    fontSize: 12.5,
                    fontWeight: FontWeight.w800,
                    color: AppColors.textPrimary,
                  ),
                ),
                label: const Icon(Icons.arrow_forward_rounded, size: 16, color: AppColors.textPrimary),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

// ── BOTTOM SHEET: KATALOG FILM ──
class _JellyfinCatalogSheet extends StatefulWidget {
  final List<JellyfinMovie> movies;
  final ValueChanged<JellyfinMovie> onSelectMovie;

  const _JellyfinCatalogSheet({
    required this.movies,
    required this.onSelectMovie,
  });

  @override
  State<_JellyfinCatalogSheet> createState() => _JellyfinCatalogSheetState();
}

class _JellyfinCatalogSheetState extends State<_JellyfinCatalogSheet> {
  String _search = '';

  @override
  Widget build(BuildContext context) {
    final filtered = widget.movies.where((m) {
      if (_search.trim().isEmpty) return true;
      final q = _search.toLowerCase();
      return m.title.toLowerCase().contains(q) ||
          m.genres.any((g) => g.toLowerCase().contains(q));
    }).toList();

    return Container(
      height: MediaQuery.of(context).size.height * 0.85,
      decoration: const BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 20),
      child: Column(
        children: [
          // Header
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Text('🎬', style: TextStyle(fontSize: 22)),
                  SizedBox(width: 8),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Katalog Film',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: AppColors.textPrimary),
                      ),
                      Text(
                        'Koleksi Film Pilihan',
                        style: TextStyle(fontSize: 11, color: AppColors.textMuted),
                      ),
                    ],
                  ),
                ],
              ),
              IconButton(icon: const Icon(Icons.close_rounded), onPressed: () => Navigator.pop(context)),
            ],
          ),
          const SizedBox(height: 12),

          // Search Field
          TextField(
            decoration: InputDecoration(
              hintText: 'Cari judul film atau genre...',
              prefixIcon: const Icon(Icons.search_rounded, size: 20),
              filled: true,
              fillColor: AppColors.bg,
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: AppColors.border),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: AppColors.border),
              ),
            ),
            onChanged: (v) => setState(() => _search = v),
          ),
          const SizedBox(height: 14),

          // Grid View
          Expanded(
            child: filtered.isEmpty
                ? const Center(
                    child: Text('Tidak ada film ditemukan', style: TextStyle(color: AppColors.textMuted)),
                  )
                : GridView.builder(
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 3,
                      childAspectRatio: 0.58,
                      crossAxisSpacing: 10,
                      mainAxisSpacing: 10,
                    ),
                    itemCount: filtered.length,
                    itemBuilder: (context, index) {
                      final m = filtered[index];
                      return InkWell(
                        onTap: () => widget.onSelectMovie(m),
                        borderRadius: BorderRadius.circular(12),
                        child: Container(
                          decoration: BoxDecoration(
                            color: AppColors.bg,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: AppColors.border),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              ClipRRect(
                                borderRadius: const BorderRadius.vertical(top: Radius.circular(11)),
                                child: AspectRatio(
                                  aspectRatio: 2 / 2.7,
                                  child: Image.network(
                                    m.poster,
                                    fit: BoxFit.cover,
                                    errorBuilder: (context, error, stackTrace) => Container(
                                      color: const Color(0xFF1E293B),
                                      child: const Center(child: Text('🎬', style: TextStyle(fontSize: 22))),
                                    ),
                                  ),
                                ),
                              ),
                              Padding(
                                padding: const EdgeInsets.all(6),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      m.title,
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      style: const TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.w800,
                                        color: AppColors.textPrimary,
                                      ),
                                    ),
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          m.year,
                                          style: const TextStyle(fontSize: 10, color: AppColors.textMuted),
                                        ),
                                        if (m.rating != null)
                                          Text(
                                            '★ ${m.rating}',
                                            style: const TextStyle(
                                              fontSize: 9.5,
                                              fontWeight: FontWeight.w800,
                                              color: Color(0xFFD97706),
                                            ),
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
                    },
                  ),
          ),
        ],
      ),
    );
  }
}
