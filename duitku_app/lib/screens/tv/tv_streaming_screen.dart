import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:video_player/video_player.dart';

import '../../models/tv_channel.dart';
import '../../services/api_service.dart';
import '../../theme.dart';

class TvStreamingScreen extends StatefulWidget {
  const TvStreamingScreen({super.key});

  @override
  State<TvStreamingScreen> createState() => _TvStreamingScreenState();
}

class _TvStreamingScreenState extends State<TvStreamingScreen> {
  List<TvChannel> _channels = [];
  List<String> _categories = ['Semua'];
  String _selectedCategory = 'Semua';
  String _searchQuery = '';
  bool _loading = true;
  String? _error;

  TvChannel? _activeChannel;
  VideoPlayerController? _videoController;
  bool _isInitializingVideo = false;
  bool _hasVideoError = false;
  String? _videoErrorMessage;
  bool _isMuted = false;
  bool _showControls = true;

  @override
  void initState() {
    super.initState();
    _loadChannels();
  }

  @override
  void dispose() {
    _videoController?.dispose();
    super.dispose();
  }

  Future<void> _loadChannels() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final res = await ApiService.instance.getTvChannels(
        category: _selectedCategory == 'Semua' ? null : _selectedCategory,
      );

      final chList = ((res['channels'] as List<dynamic>?) ?? [])
          .map((e) => TvChannel.fromJson(e as Map<String, dynamic>))
          .toList();

      final catList = ((res['categories'] as List<dynamic>?) ?? ['Semua'])
          .map((e) => e.toString())
          .toList();

      if (!catList.contains('Semua')) {
        catList.insert(0, 'Semua');
      }

      setState(() {
        _channels = chList;
        _categories = catList;
        if (_activeChannel == null && _channels.isNotEmpty) {
          _activeChannel = _channels.first;
        }
        _loading = false;
      });

      // Auto play channel pertama jika belum ada controller
      if (_channels.isNotEmpty && _videoController == null) {
        _playStream(_channels.first, autoPlay: true);
      }
    } catch (e) {
      setState(() {
        _error = 'Gagal memuat saluran TV: $e';
        _loading = false;
      });
    }
  }

  List<TvChannel> get _filteredChannels {
    return _channels.where((ch) {
      final matchesSearch = _searchQuery.isEmpty ||
          ch.name.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          ch.category.toLowerCase().contains(_searchQuery.toLowerCase());
      final matchesCat = _selectedCategory == 'Semua' || ch.category == _selectedCategory;
      return matchesSearch && matchesCat;
    }).toList();
  }

  Future<void> _playStream(TvChannel ch, {bool autoPlay = true}) async {
    if (_activeChannel?.id == ch.id && _videoController != null && !_hasVideoError) {
      if (!_videoController!.value.isPlaying && autoPlay) {
        _videoController!.play();
      }
      return;
    }

    setState(() {
      _activeChannel = ch;
      _isInitializingVideo = true;
      _hasVideoError = false;
      _videoErrorMessage = null;
    });

    try {
      // Dispose old controller
      final oldController = _videoController;
      _videoController = null;
      await oldController?.dispose();

      final uri = Uri.parse(ch.streamUrl);
      final controller = VideoPlayerController.networkUrl(
        uri,
        videoPlayerOptions: VideoPlayerOptions(mixWithOthers: true),
      );

      _videoController = controller;

      controller.addListener(() {
        if (mounted) {
          setState(() {});
        }
      });

      await controller.initialize();
      await controller.setVolume(_isMuted ? 0.0 : 1.0);

      if (autoPlay) {
        await controller.play();
      }

      if (mounted) {
        setState(() {
          _isInitializingVideo = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isInitializingVideo = false;
          _hasVideoError = true;
          _videoErrorMessage = 'Gagal memuat siaran live: $e';
        });
      }
    }
  }

  void _togglePlayPause() {
    if (_videoController == null || !_videoController!.value.isInitialized) return;
    if (_videoController!.value.isPlaying) {
      _videoController!.pause();
    } else {
      _videoController!.play();
    }
    setState(() {});
  }

  void _toggleMute() {
    if (_videoController == null) return;
    _isMuted = !_isMuted;
    _videoController!.setVolume(_isMuted ? 0.0 : 1.0);
    setState(() {});
  }

  void _openInExternalPlayer() async {
    if (_activeChannel == null) return;
    final uri = Uri.tryParse(_activeChannel!.streamUrl);
    if (uri != null && await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  void _openFullScreen() {
    if (_videoController == null || !_videoController!.value.isInitialized) return;

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => _FullScreenTvPlayer(
          controller: _videoController!,
          channel: _activeChannel!,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('TV & Live Streaming'),
        centerTitle: false,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Segarkan',
            onPressed: _loadChannels,
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24.0),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.error_outline_rounded, size: 48, color: Colors.red),
                        const SizedBox(height: 12),
                        Text(_error!, textAlign: TextAlign.center, style: const TextStyle(color: Colors.red)),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: _loadChannels,
                          icon: const Icon(Icons.refresh),
                          label: const Text('Coba Lagi'),
                          style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary),
                        )
                      ],
                    ),
                  ),
                )
              : _channels.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.live_tv_rounded, size: 64, color: Colors.grey.shade400),
                          const SizedBox(height: 12),
                          const Text('Belum ada saluran TV tersedia.', style: TextStyle(fontWeight: FontWeight.w600)),
                          const SizedBox(height: 6),
                          Text('Admin dapat menambahkan saluran di Admin Panel.', style: TextStyle(color: Colors.grey.shade500, fontSize: 12)),
                        ],
                      ),
                    )
                  : ListView(
                      padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
                      children: [
                        // In-App Live Video Player Box
                        _buildVideoPlayerSection(),

                        const SizedBox(height: 20),

                        // Search box
                        TextField(
                          decoration: InputDecoration(
                            hintText: 'Cari saluran TV atau kategori...',
                            prefixIcon: const Icon(Icons.search_rounded, size: 20),
                            filled: true,
                            fillColor: AppColors.card,
                            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(14),
                              borderSide: const BorderSide(color: AppColors.border),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(14),
                              borderSide: const BorderSide(color: AppColors.border),
                            ),
                          ),
                          onChanged: (val) {
                            setState(() {
                              _searchQuery = val.trim();
                            });
                          },
                        ),

                        const SizedBox(height: 14),

                        // Category chips
                        SizedBox(
                          height: 38,
                          child: ListView.separated(
                            scrollDirection: Axis.horizontal,
                            itemCount: _categories.length,
                            separatorBuilder: (_, _) => const SizedBox(width: 8),
                            itemBuilder: (context, idx) {
                              final cat = _categories[idx];
                              final isSelected = cat == _selectedCategory;
                              return ChoiceChip(
                                label: Text(cat),
                                selected: isSelected,
                                selectedColor: AppColors.primary,
                                labelStyle: TextStyle(
                                  color: isSelected ? Colors.white : AppColors.textPrimary,
                                  fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                                  fontSize: 12.5,
                                ),
                                backgroundColor: AppColors.card,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(20),
                                  side: BorderSide(
                                    color: isSelected ? AppColors.primary : AppColors.border,
                                  ),
                                ),
                                onSelected: (_) {
                                  setState(() {
                                    _selectedCategory = cat;
                                  });
                                },
                              );
                            },
                          ),
                        ),

                        const SizedBox(height: 18),

                        // Section Title
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'DAFTAR SALURAN (${_filteredChannels.length})',
                              style: const TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w800,
                                color: AppColors.textSecondary,
                                letterSpacing: 0.8,
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: Colors.red.withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(Icons.circle, color: Colors.red, size: 8),
                                  SizedBox(width: 4),
                                  Text(
                                    'LIVE IN-APP PLAYER',
                                    style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: Colors.red),
                                  ),
                                ],
                              ),
                            )
                          ],
                        ),

                        const SizedBox(height: 12),

                        // Channels Grid
                        _filteredChannels.isEmpty
                            ? Container(
                                padding: const EdgeInsets.all(30),
                                alignment: Alignment.center,
                                child: Text(
                                  'Tidak ada saluran ditemukan untuk "$_searchQuery".',
                                  style: const TextStyle(color: AppColors.textSecondary),
                                ),
                              )
                            : GridView.builder(
                                shrinkWrap: true,
                                physics: const NeverScrollableScrollPhysics(),
                                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                                  crossAxisCount: 3,
                                  crossAxisSpacing: 10,
                                  mainAxisSpacing: 10,
                                  childAspectRatio: 0.88,
                                ),
                                itemCount: _filteredChannels.length,
                                itemBuilder: (context, idx) {
                                  final ch = _filteredChannels[idx];
                                  final isActive = _activeChannel?.id == ch.id;
                                  return _buildChannelCard(ch, isActive);
                                },
                              ),
                      ],
                    ),
    );
  }

  Widget _buildVideoPlayerSection() {
    final ch = _activeChannel;
    if (ch == null) {
      return const SizedBox.shrink();
    }

    final isInitialized = _videoController != null && _videoController!.value.isInitialized;
    final isPlaying = isInitialized && _videoController!.value.isPlaying;
    final isBuffering = isInitialized && _videoController!.value.isBuffering;

    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFF0B1120),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.25),
            blurRadius: 18,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Video Player Aspect Ratio Box
          GestureDetector(
            onTap: () {
              setState(() {
                _showControls = !_showControls;
              });
            },
            child: AspectRatio(
              aspectRatio: 16 / 9,
              child: Stack(
                alignment: Alignment.center,
                children: [
                  // Actual Video Stream
                  if (isInitialized && !_hasVideoError)
                    SizedBox.expand(
                      child: FittedBox(
                        fit: BoxFit.cover,
                        child: SizedBox(
                          width: _videoController!.value.size.width > 0
                              ? _videoController!.value.size.width
                              : 1280,
                          height: _videoController!.value.size.height > 0
                              ? _videoController!.value.size.height
                              : 720,
                          child: VideoPlayer(_videoController!),
                        ),
                      ),
                    )
                  else
                    Container(
                      color: const Color(0xFF0F172A),
                      child: Center(
                        child: ch.logoUrl != null && ch.logoUrl!.isNotEmpty
                            ? Image.network(
                                ch.logoUrl!,
                                width: 80,
                                height: 80,
                                fit: BoxFit.contain,
                                errorBuilder: (_, _, _) => const Icon(Icons.tv_rounded, size: 64, color: Colors.blueGrey),
                              )
                            : const Icon(Icons.tv_rounded, size: 64, color: Colors.blueGrey),
                      ),
                    ),

                  // Loading / Buffering Spinner
                  if (_isInitializingVideo || isBuffering)
                    Container(
                      color: Colors.black.withValues(alpha: 0.45),
                      child: const Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            CircularProgressIndicator(color: AppColors.primary),
                            SizedBox(height: 10),
                            Text(
                              'Menghubungkan siaran live...',
                              style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                            ),
                          ],
                        ),
                      ),
                    ),

                  // Error Overlay
                  if (_hasVideoError)
                    Container(
                      color: Colors.black.withValues(alpha: 0.8),
                      padding: const EdgeInsets.all(16),
                      child: Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.error_outline_rounded, color: Colors.redAccent, size: 36),
                            const SizedBox(height: 8),
                            Text(
                              _videoErrorMessage ?? 'Format siaran tidak didukung secara in-app.',
                              style: const TextStyle(color: Colors.white, fontSize: 11),
                              textAlign: TextAlign.center,
                              maxLines: 2,
                            ),
                            const SizedBox(height: 10),
                            Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                ElevatedButton.icon(
                                  onPressed: () => _playStream(ch),
                                  icon: const Icon(Icons.refresh, size: 16),
                                  label: const Text('Coba Lagi', style: TextStyle(fontSize: 12)),
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: AppColors.primary,
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                  ),
                                ),
                                const SizedBox(width: 8),
                                OutlinedButton.icon(
                                  onPressed: _openInExternalPlayer,
                                  icon: const Icon(Icons.open_in_new_rounded, size: 16, color: Colors.white),
                                  label: const Text('Player Eksternal', style: TextStyle(fontSize: 12, color: Colors.white)),
                                  style: OutlinedButton.styleFrom(
                                    side: const BorderSide(color: Colors.white70),
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ),

                  // Overlay Controls (when tapped & playing)
                  if (_showControls && isInitialized && !_hasVideoError)
                    Container(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: [
                            Colors.black.withValues(alpha: 0.6),
                            Colors.transparent,
                            Colors.black.withValues(alpha: 0.7),
                          ],
                          begin: Alignment.topCenter,
                          end: Alignment.bottomCenter,
                        ),
                      ),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          // Top Bar
                          Padding(
                            padding: const EdgeInsets.fromLTRB(12, 10, 12, 0),
                            child: Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: Colors.red,
                                    borderRadius: BorderRadius.circular(4),
                                  ),
                                  child: const Text(
                                    'LIVE',
                                    style: TextStyle(color: Colors.white, fontSize: 9.5, fontWeight: FontWeight.w900),
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    ch.name,
                                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 13),
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                                IconButton(
                                  icon: Icon(
                                    _isMuted ? Icons.volume_off_rounded : Icons.volume_up_rounded,
                                    color: Colors.white,
                                    size: 20,
                                  ),
                                  onPressed: _toggleMute,
                                ),
                                IconButton(
                                  icon: const Icon(Icons.fullscreen_rounded, color: Colors.white, size: 22),
                                  onPressed: _openFullScreen,
                                ),
                              ],
                            ),
                          ),

                          // Center Play / Pause
                          IconButton(
                            iconSize: 48,
                            icon: Icon(
                              isPlaying ? Icons.pause_circle_filled_rounded : Icons.play_circle_fill_rounded,
                              color: Colors.white.withValues(alpha: 0.9),
                            ),
                            onPressed: _togglePlayPause,
                          ),

                          // Bottom Bar
                          Padding(
                            padding: const EdgeInsets.fromLTRB(12, 0, 12, 8),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  ch.category,
                                  style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 11),
                                ),
                                InkWell(
                                  onTap: () => _playStream(ch),
                                  child: Row(
                                    children: [
                                      Icon(Icons.refresh_rounded, size: 14, color: Colors.white.withValues(alpha: 0.8)),
                                      const SizedBox(width: 4),
                                      Text(
                                        'Reconnect',
                                        style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 11),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                ],
              ),
            ),
          ),

          // Player Info Banner below video
          Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                Container(
                  width: 42,
                  height: 42,
                  padding: const EdgeInsets.all(4),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: ch.logoUrl != null && ch.logoUrl!.isNotEmpty
                      ? Image.network(
                          ch.logoUrl!,
                          fit: BoxFit.contain,
                          errorBuilder: (_, _, _) => const Icon(Icons.tv_rounded, color: Colors.blueGrey),
                        )
                      : const Icon(Icons.tv_rounded, color: Colors.blueGrey, size: 24),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        ch.name,
                        style: const TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.w800),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Kategori: ${ch.category}',
                        style: TextStyle(color: Colors.grey.shade400, fontSize: 11.5),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  onPressed: _openFullScreen,
                  icon: const Icon(Icons.fullscreen_rounded, color: AppColors.primary, size: 28),
                  tooltip: 'Layar Penuh',
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildChannelCard(TvChannel ch, bool isActive) {
    return InkWell(
      onTap: () => _playStream(ch),
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isActive ? AppColors.primary : AppColors.border,
            width: isActive ? 2 : 1,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.02),
              blurRadius: 6,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Stack(
              alignment: Alignment.center,
              children: [
                Container(
                  width: 48,
                  height: 48,
                  padding: const EdgeInsets.all(6),
                  decoration: BoxDecoration(
                    color: AppColors.bg,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: ch.logoUrl != null && ch.logoUrl!.isNotEmpty
                      ? Image.network(
                          ch.logoUrl!,
                          fit: BoxFit.contain,
                          errorBuilder: (_, _, _) => const Icon(Icons.tv_rounded, color: AppColors.primary, size: 24),
                        )
                      : const Icon(Icons.tv_rounded, color: AppColors.primary, size: 24),
                ),
                Positioned(
                  top: 0,
                  right: 0,
                  child: Container(
                    width: 9,
                    height: 9,
                    decoration: BoxDecoration(
                      color: isActive ? Colors.red : Colors.green,
                      shape: BoxShape.circle,
                      border: Border.all(color: Colors.white, width: 1.5),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              ch.name,
              style: TextStyle(
                fontSize: 12.5,
                fontWeight: isActive ? FontWeight.w800 : FontWeight.w600,
                color: isActive ? AppColors.primary : AppColors.textPrimary,
              ),
              textAlign: TextAlign.center,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 2),
            Text(
              ch.category,
              style: const TextStyle(
                fontSize: 10.5,
                color: AppColors.textSecondary,
              ),
              textAlign: TextAlign.center,
              maxLines: 1,
            ),
          ],
        ),
      ),
    );
  }
}

/// FullScreen Player Screen
class _FullScreenTvPlayer extends StatefulWidget {
  final VideoPlayerController controller;
  final TvChannel channel;

  const _FullScreenTvPlayer({
    required this.controller,
    required this.channel,
  });

  @override
  State<_FullScreenTvPlayer> createState() => _FullScreenTvPlayerState();
}

class _FullScreenTvPlayerState extends State<_FullScreenTvPlayer> {
  bool _showControls = true;

  @override
  void initState() {
    super.initState();
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.immersiveSticky);
  }

  @override
  void dispose() {
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.edgeToEdge);
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isPlaying = widget.controller.value.isPlaying;

    return Scaffold(
      backgroundColor: Colors.black,
      body: GestureDetector(
        onTap: () {
          setState(() {
            _showControls = !_showControls;
          });
        },
        child: Stack(
          alignment: Alignment.center,
          children: [
            Center(
              child: AspectRatio(
                aspectRatio: widget.controller.value.aspectRatio > 0
                    ? widget.controller.value.aspectRatio
                    : 16 / 9,
                child: VideoPlayer(widget.controller),
              ),
            ),

            // Controls
            if (_showControls)
              Container(
                color: Colors.black.withValues(alpha: 0.4),
                child: SafeArea(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      // Top Bar
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                        child: Row(
                          children: [
                            IconButton(
                              icon: const Icon(Icons.arrow_back_rounded, color: Colors.white),
                              onPressed: () => Navigator.pop(context),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              widget.channel.name,
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 16),
                            ),
                            const Spacer(),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: Colors.red,
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: const Text('LIVE', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 11)),
                            ),
                          ],
                        ),
                      ),

                      // Play/Pause center button
                      IconButton(
                        iconSize: 64,
                        icon: Icon(
                          isPlaying ? Icons.pause_circle_filled_rounded : Icons.play_circle_fill_rounded,
                          color: Colors.white.withValues(alpha: 0.9),
                        ),
                        onPressed: () {
                          if (isPlaying) {
                            widget.controller.pause();
                          } else {
                            widget.controller.play();
                          }
                          setState(() {});
                        },
                      ),

                      // Bottom exit fullscreen
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              widget.channel.category,
                              style: const TextStyle(color: Colors.white70, fontSize: 13),
                            ),
                            IconButton(
                              icon: const Icon(Icons.fullscreen_exit_rounded, color: Colors.white, size: 28),
                              onPressed: () => Navigator.pop(context),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
